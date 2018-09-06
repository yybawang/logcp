<?php
namespace App\Models;

/**
 * 保存所有节点信息
 * Class CpProcesses
 * @package App\Models
 */
class CpProject extends CommonModel {
	
	public function scopeActive($query){
		return $query->where(['status'=>1]);
	}
}