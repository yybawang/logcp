<?php
namespace App\Http\Controllers\Admin;

use App\Models\CpAdmin;
use App\Models\CpProject;
use App\Models\CpProjectsFile;
use App\Models\CpProjectsFilesContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends CommonController {
	
	public function index(Request $request){
		return view('admin.index');
	}
	
	/**
	 * 页面菜单/用户信息初始化
	 * @param Request $request
	 * @return array
	 */
	public function init(Request $request){
		$user = CpAdmin::user();
		$data['user'] = $user;
		$data['menu'] = init_menu();
		return json_success('OK',$data);
	}
	
	/**
	 * 首页欢迎页
	 * @param Request $request
	 * @return array
	 */
	public function welcome(Request $request){
		
		return json_success();
	}
	
	/**
	 * 服务器版本信息
	 * @param Request $request
	 * @return array
	 */
	public function sysinfo(Request $request){
		$data = [
			'服务器系统'		=> php_uname(),
			'运行模式'		=> php_sapi_name(),
			'运行用户名'		=> Get_Current_User(),
			'PHP版本'		=> PHP_VERSION,
			'运行时最大内存'	=> ini_get('memory_limit'),
			'时区'			=> config('app.timezone'),
			'允许上传大小'	=> ini_get('upload_max_filesize'),
			'数据库版本'		=> 'MongoDB',
			'服务器IP'		=> GetHostByName($_SERVER['SERVER_NAME']),
			'客户端IP'		=> $_SERVER['REMOTE_ADDR'],
		];
		return json_success('OK',$data);
	}
	
	/**
	 * 一键清空数据
	 * @param Request $request
	 * @return array
	 */
	public function delete(Request $request){
//		CpProject::truncate();
		CpProjectsFile::truncate();
//		CpProjectsFilesContent::truncate();
		return json_success('OK');
	}
}