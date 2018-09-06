<?php

function abort($code = 500,$message = null){
	$message = $message ?? '服务器错误';
	throw new Exception($message);
}

/**
 * 异常报告
 */
function exception($message = null){
	$message = $message ?? '服务器异常，请稍后重试';
	abort(500,$message);
}

/**
 * session 设置和获取
 * @param string|array $key
 * @return mixed
 */
function session($key){
	if(is_array($key)){
		return $_SESSION[$key[0]] = $key[1];
	}
	return $_SESSION[$key];
}
