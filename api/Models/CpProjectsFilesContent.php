<?php
namespace App\Models;

/**
 * 文件内容表
 * Class CpProjectsFileContent
 * @package App\Models
 */
class CpProjectsFilesContent extends CommonModel {
	protected $page_size = 300;
	
	public function toSearchableArray(){
		return $this->only('project_id','file_id','content');
	}
}