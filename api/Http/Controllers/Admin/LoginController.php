<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CpAdmin;
use App\Models\CpAdminToken;
use Illuminate\Http\Request;

class LoginController extends Controller {
	
	public function login(Request $request){
		return view('admin.login');
	}
	
	/**
	 * post 提交登录，只用路由中间件验证
	 * @param Request $request
	 * @return array
	 */
	public function login_submit(Request $request){
		$name = $request->input('name');
		$password = $request->input('password');
		$password_md5 = CpAdmin::md5($password);
		if(empty($name)){
			exception('请输入登录名');
		}
		if(empty($password)){
			exception('请输入密码');
		}
		
		if(CpAdmin::count() <= 0){
			$uid = CpAdmin::create([
				'name'		=> $name,
				'password'	=> $password_md5,
			])->id;
		}else{
			$uid = CpAdmin::where(['name'=>$name,'password'=>$password_md5])->value('id');
			if(!$uid){
				exception('登录信息错误');
			}
		}
		CpAdmin::login($uid);
		return json_success('验证成功，正在跳转主页');
	}
	
	/**
	 * @param Request $request
	 * @return array
	 */
	public function logout(Request $request){
//		CpAdmin::logout();
		return response()->json(json_success('已退出登录'))->cookie('admin_uid',null);
//		return json_success('已退出登录');
	}
}