<?php
namespace App\Models;

class CpProjectsFile extends CommonModel {

	
	public function childTree($project_id){
		$data = $this->_childTree($project_id,'');
		return $data;
	}
	
	private function _childTree($project_id,$parent_id){
		$list = $this->where(['project_id'=>$project_id,'parent_id'=>$parent_id])->orderByDesc('_id')->get()->each(function($v) use ($project_id){
			$v->children = $this->_childTree($project_id,$v->_id);
		});
		return $list;
	}
}