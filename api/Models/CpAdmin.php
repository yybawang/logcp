<?php
namespace App\Models;

class CpAdmin extends CommonModel {
	
	public static function uid(){
		$value = session('admin_uid');
		return $value;
	}
	
	public static function user(){
		$uid = self::uid();
		$Admin = new CpAdmin();
		$user = $Admin->find($uid);
		return $user;
	}
	
	// 方法失效，需要放到 response()
	public static function login($uid){
		return session(['admin_uid'=>$uid]);
	}
	
	// 方法失效，需要放到 response()
	public static function logout(){
		return session(['admin_uid'=>null]);
	}
	
	public static function md5($password){
		$return = '';
		if($password){
			$return = md5($password.'_logcp2018');
		}
		return $return;
	}
}