<?php
require '../vendor/autoload.php';
session_start();


$capsule = new Illuminate\Database\Capsule\Manager();
$capsule->addConnection([
	'driver'    => 'mysql',
	'host'      => 'mysql',
	'database'  => 'logcp',
	'username'  => 'root',
	'password'  => '123456',
	'charset'   => 'utf8mb4',
	'collation' => 'utf8mb4_unicode_ci',
	'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$ProjectServer = new \App\Http\Services\ProjectServer();
$ProjectServer->start();