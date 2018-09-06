<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CpAdmin;
use App\Models\CpAdminToken;

class CommonController extends Controller {
	
	public $user;
	public $uid;
	
	public function __construct(){
		$this->uid = CpAdmin::uid();
	}
}