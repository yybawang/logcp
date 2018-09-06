<?php
namespace App\Http\Controllers\Admin;


use App\Models\CpProject;
use Illuminate\Http\Request;

/**
 * 项目节点管理
 * Class ProjectController
 * @package App\Http\Controllers\Admin
 */
class ProjectController extends CommonController {
	
	public function list(Request $request){
		$data['list'] = CpProject::when($request->input('keyword'),function($query) use ($request){
				$query->where('name','like','%'.$request->input('keyword').'%');
			})
			->when($request->input('host'),function($query) use ($request){
				$query->where('host',$request->input('host'));
			})
			->orderByDesc('id')->pages();
		return json_success('OK',$data);
	}
	
	public function detail(Request $request){
		$id = $request->input('id');
		$data = CpProject::firstOrNew(['id'=>$id],[
			'name'			=> '',
			'host'			=> '',
			'port'			=> '22',
			'username'		=> 'root',
			'password'		=> '',
			'public_key'	=> '',
			'private_key'	=> '',
			'listen_path'	=> '',
			'status'		=> 1,
		]);
		return json_success('OK',$data);
	}
	
	public function add(Request $request){
		$post = $request->validate([
			'name'		=> 'required',
			'host'		=> 'required',
			'port'		=> 'required|integer|min:1',
			'username'	=> 'required',
			'password'	=> 'required',
			'public_key'=> '',
			'private_key'=> '',
			'listen_path'=> 'required',
			'status'	=> 'required|integer',
		]);
		$post['status'] = intval($post['status']);
		$id = $request->input('id');
		CpProject::updateOrCreate(['id'=>$id],$post);
		return json_success('OK');
	}
	
	public function delete(Request $request){
		$id = $request->input('id');
		$var = CpProject::destroy($id);
		return json_return($var);
	}
}