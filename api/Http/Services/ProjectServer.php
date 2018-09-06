<?php
/**
 * 项目服务，原生swoole
 * 1. 新建server
 * 2. 新建 process，每个项目对应一个，process 运行 new ClientProcess
 * Created by PhpStorm.
 * User: yybaw
 * Date: 2018-08-18
 * Time: 10:06 PM
 */

namespace App\Http\Services;


use App\Models\CpProject;

class ProjectServer
{
	public $server;
	public $projects;	// 需要监听的项目
	
	public function start()
	{
		$this->projects = CpProject::active()->oldest()->get();
		$this->projects->each(function($project){
			$process_worker = new ClientProcessWorker($project);
			$process_worker->run();
		});
		
	}
}