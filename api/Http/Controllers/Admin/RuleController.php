<?php
namespace App\Http\Controllers\Admin;

use App\Models\CpAdmin;
use App\Models\CpAdminGroup;
use Illuminate\Http\Request;

class RuleController extends CommonController {
	
	/**
	 * 权限用户管理
	 * @param Request $request
	 * @return array
	 */
	public function user(Request $request){
		$data['group'] = CpAdminGroup::where(['status'=>1])->pluck('name','id');
		$data['list'] = CpAdmin::when($request->input('name'),function($query) use ($request){
			$query->where('name','like','%'.$request->input('name').'%');
		})
			->when($request->input('realname'),function($query) use ($request){
				$query->where('realname','like','%'.$request->input('realname').'%');
			})
			->when($request->input('mobile'),function($query) use ($request){
				$query->where('mobile','like','%'.$request->input('mobile').'%');
			})
			->orderBy('id','desc')
			->pages();
		return json_success('OK',$data);
	}
	
	public function user_detail(Request $request){
		$id = $request->input('id');
		$data = CpAdmin::firstOrNew(['id'=>$id],[
			'name'		=> '',
			'password'	=> '',
			'realname'	=> '',
			'email'		=> '',
			'mobile'	=> '',
			'group_id'	=> 0,
			'status'	=> 1,
			'remarks'	=> '',
		]);
		$data->password = '';
		return json_success('OK',$data);
	}
	
	public function user_add(Request $request){
		request()->validate([
			'group_id'		=> 'required',
			'name'			=> 'required',
			'realname'		=> 'required',
		]);
		$post = $request->all();
		if(!$post['password']){
			unset($post['password']);
		}else{
			$post['password'] = CpAdmin::md5($post['password']);
		}
		
		$id = CpAdmin::updateOrCreate(['id'=>$post['id']],$post)->id;
		return json_return($id,'','',['id'=>$id]);
	}
	
	public function user_del(Request $request){
		$id = $request->input('id');
		$var = CpAdmin::destroy($id);
		return json_return($var);
	}
	
	/**
	 * 权限组管理
	 * @param Request $request
	 * @return array
	 */
	public function group(Request $request){
		$data['menu'] = init_menu();
		$data['list'] = CpAdminGroup::when($request->input('name'),function($query) use ($request){
			$query->where('name','like','%'.$request->input('name').'%');
		})
			->orderBy('id','desc')
			->pages();
		return json_success('OK',$data);
	}
	
	public function group_detail(Request $request){
		$id = $request->input('id');
		$data = CpAdminGroup::firstOrNew(['id'=>$id],[
			'name'		=> '',
			'rule'		=> '[]',
			'status'	=> 1,
			'remarks'	=> '',
		]);
		$data['rule'] = json_decode($data['rule'],true);
		return json_success('OK',$data);
	}
	
	public function group_add(Request $request){
		request()->validate([
			'name'	=> 'required',
		]);
		$post = $request->all();
		$post['rule'] = json_encode($post['rule']);
		$id = CpAdminGroup::updateOrCreate(['id'=>$post['id']],$post)->id;
		return json_return($id,'','',['id'=>$id]);
	}
	
	public function group_del(Request $request){
		$id = $request->input('id');
		$var = CpAdminGroup::destroy($id);
		return json_return($var);
	}
	
	/**
	 * 登录密码重置
	 * @param Request $request
	 * @return array
	 */
	public function password_reset(Request $request){
		$user = admin_user();
		$post = request()->validate([
			'password_old'		=> 'required|min:6',
			'password_new'		=> 'required|min:6|confirmed',
			'password_new_confirmation'	=> '',
		]);
		$password_old_md5 = CpAdmin::md5($post['password_old']);
		$password_new_md5 = CpAdmin::md5($post['password_new']);
		if(strcmp($user->password,$password_old_md5) !== 0){
			exception('原登录密码不匹配');
		}
		$var = CpAdmin::where(['id'=>$user->id])->update(['password'=>$password_new_md5]);
		return json_return($var);
	}
}