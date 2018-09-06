<?php
namespace App\Http\Services;

/**
 * php-ssh2 扩展操作类
 * Class Ssh2
 * @package App\Libraries
 */
class Ssh {
	
	protected $connect;
	protected $host;
	protected $port;
	protected $username;
	protected $password;
	protected $public_key;
	protected $private_key;
	
	public function __construct($host,$port,$username,$password = '',$public_key = '', $private_key = ''){
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->public_key = $public_key;
		$this->private_key = $private_key;
	}
	
	public function connect(){
		if($this->isConnect()){
			return $this;
		}
		if(empty($this->host) || empty($this->port) || empty($this->username)){
			exception('连接参数缺失');
		}
		
		
		// 默认密码链接，否则私钥连接
		if($this->password){
			$this->connect = ssh2_connect($this->host,$this->port);
			$auth = ssh2_auth_password($this->connect,$this->username,$this->password);
		}else{
			$this->connect = ssh2_connect($this->host,$this->port,['hostkey'=>'ssh-rsa']);
			$auth = ssh2_auth_pubkey_file($this->connect,$this->username,$this->public_key,$this->private_key,'secret');
		}
		if(!$auth){
			exception('SSH Authentication Failed');
		}
		return $this;
	}
	
	
	public function exec($command){
		$this->connect();
		$stream = @ssh2_exec($this->connect,$command.PHP_EOL);
		$stream_err = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		stream_set_blocking($stream,true);
		stream_set_blocking($stream_err,true);
		$result = stream_get_contents( $stream );
		if($error = stream_get_contents($stream_err)){
			exception($error);
		}
		fclose($stream);
		fclose($stream_err);
		return $result;
	}
	
	
	public function isConnect(){
		return (bool)$this->connect;
	}
}