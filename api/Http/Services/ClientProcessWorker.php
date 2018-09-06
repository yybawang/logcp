<?php
namespace App\Http\Services;

use App\Http\Services\Ssh;
use App\Models\CpProject;
use App\Models\CpProjectsFile;

class ClientProcessWorker {
	
	protected $host;
	protected $port;
	
	protected $ssh;
	// 设置哪个项目，CpProject Model 对象
	protected $project;
	protected $fd;
	public $client;
	
	// 保存文件队列信息
	public $file_list = [];
	
	public function __construct(CpProject $project)
	{
		$this->project = $project;
	}
	
	public function run(){
		$ssh = new Ssh($this->project->host,$this->project->port,$this->project->username,$this->project->password,$this->project->public_key,$this->project->private_key);
		$this->ssh = $ssh;
		while(true){
			$this->_sshScan();
		}
	}
	
	/**
	 * 开始执行比对，遍历一个项目路径
	 * @return bool
	 */
	private function _sshScan(){
		$output = $this->ssh->exec('ls -gR '.$this->project->listen_path);
		$this->_scanDir($this->project->id,$output);
		return true;
	}
	
	/**
	 * 远程文件格式化，存入/更新数据库，交由定时任务对比更新
	 * todo 由于 ls -R 深度是二级状显示的，所以需要自己匹配出深度存入数据库
	 * @param $project_id
	 * @param $output
	 * @return bool
	 */
	private function _scanDir(string $project_id,string $output){
		$output_parse = explode("\n",$output);
		$result = [];
		$dir = '';
		foreach($output_parse as $v){
			$first = substr($v,0,1);
			switch ($first){
				case '/' :
					$dir = substr($v,0,-1).'/';
					break;
				case '-' :
					$v = preg_replace('/\s+/',' ',$v);
					$file_info = explode(' ',$v);
					$file_name = array_pop($file_info);
					// 正则匹配
					if(!preg_match($this->project->preg,$file_name)){
						break;
					}
					$result[$dir][] = [
						'size'	=> $file_info[3],
						'name'	=> $file_name,
					];
					break;
				default :
					break;
			}
			
		}
		
		// 二维数组，一维是文件夹名，二维是文件名和大小
		foreach($result as $dir => $file_list){
			$dir_model = CpProjectsFile::firstOrCreate(['name'=>$dir,'project_id'=>$project_id],[
				'parent_id'		=> 0,
				'size'			=> 0,
				'size_upload'	=> 0,
				'type'			=> 0,
			]);
			
			foreach($file_list as $k => $info){
				$File = CpProjectsFile::firstOrCreate(['name'=>$info['name'],'parent_id'=>$dir_model->id],[
					'project_id'	=> $project_id,
					'size'			=> 0,
					'size_upload'	=> 0,	// 已更新的文件大小
					'type'			=> 1,
				]);
				
				// -----------------------------------------------
				// 考虑到目标服务器会定期删除（不考虑修改日志），如果已上传大于未上传，就从0开始上传
				// -----------------------------------------------
				if($File->size_upload > $info['size']){
					$File->size_upload = 0;
				}
				$File->size = $info['size'];
				$File->save();
				
				// 开始分发执行文件比对，发送给服务器
				$this->_sshCat($File);
				sleep(1);
			}
		}
		return true;
	}
	
	/**
	 * 开始从服务器返回指定文件内容
	 * @param string $dir
	 * @param CpProjectsFile $file
	 * @return mixed
	 */
	private function _sshCat(CpProjectsFile $File){
		// 最大上传 0.5M 大小，避免网络峰值过高，如果超出需要剔除最后一个换行，避免一行变两行上传
		$M = 1024 * 258 * 1;
		$pop_flag = false;	// 是否需要截取最后一个换行
		$dir = CpProjectsFile::where(['id'=>$File->parent_id])->value('name');
		// 计算剩余需要上传的文件大小
		$size_out = $size_remain = $File->size - $File->size_upload;
		if($size_remain > $M){
			$pop_flag = true;
			$size_out = $M;
		}
		$output = $this->ssh->exec("tail -c  {$size_remain} " . $dir.$File->name . " | head -c {$size_out}");
		
		if($pop_flag){
			$sublen = 0;
			for($i = strlen($output)-1; $i >= 0; $i--){
				if($output{$i} !== "\n"){
					$sublen--;
				}else{
					break;
				}
			}
			$output = substr($output,0,$sublen);
		}
		// 无文件差异不需要上传
		if(strlen($output) <= 0){
			return true;
		}
		return $this->_save($File,'/'.$this->project->name.$dir.$File->name,$output);
	}
	
	/**
	 * 存入文件，按照目录层级建立文件夹
	 * @param CpProjectsFile $File
	 * @param string $file_path 路径数据总是以 "/" 打头
	 * @param string $content
	 * @return mixed
	 */
	public function _save(CpProjectsFile $File, string $file_path, string $content){
		$file_path = ('./logs') . $file_path;
		$dir = substr($file_path,0,strrpos($file_path,'/'));
		if(!file_exists($dir)){
			mkdir($dir,0777,true);
		}
		$writed = file_put_contents($file_path,$content,FILE_APPEND);
		if($writed){
			$File->size_upload += strlen($content);
			$File->save();
		}
	}
}