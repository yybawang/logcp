<?php
namespace App\Http\Controllers\Admin;

use App\Models\CpProject;
use App\Models\CpProjectsFile;
use App\Models\CpProjectsFilesContent;
use Illuminate\Http\Request;

class ContentController extends CommonController {
	
	/**
	 * 初始显示项目列表
	 * @param Request $request
	 * @return array
	 */
	public function index(Request $request){
		$data['project'] = CpProject::active()->orderByDesc('id')->get();
		return json_success('OK',$data);
	}
	
	/**
	 * 项目筛选文件
	 * @param Request $request
	 * @return array
	 */
	public function project_file(Request $request){
		$project_id = $request->input('project_id.0');
		$data = (new CpProjectsFile())->childTree($project_id);
		return json_success('Ok',$data);
	}
	
	/**
	 * 文件内容分页
	 * @param Request $request
	 * @return array
	 */
	public function file_content(Request $request){
		$param['project_id'] = $request->input('project_id.0');
		$param['file_id'] = $request->input('file_id.0');
		
		$data = CpProjectsFilesContent::where($param)
			->when($request->input('keyword'),function($query) use ($request){
				$query->where('content','like','%'.$request->input('keyword').'%');
			})
			->orderByDesc('id')->pages();
		$data->each(function($v) use ($request,$param){
			$v->prev = $request->input('keyword') ? CpProjectsFilesContent::where($param)->where('id','>',$v->_id)->orderBy('_id')->limit(5)->get()->reverse()->values() : [];
			$v->next = $request->input('keyword') ? CpProjectsFilesContent::where($param)->where('id','<',$v->_id)->orderByDesc('_id')->limit(5)->get() : [];
		});
		return json_success('OK',$data);
	}
}