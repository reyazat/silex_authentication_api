<?php

function dumper(){
	$args = func_get_args();
	foreach($args as $arg){
		dump($arg);
	}
	exit;
}

function pre(){
	$args = func_get_args();
	foreach($args as $arg){
		print_r($arg);
	}
	exit;
}

function setResponse($params = []){
	
	$msg['meta']['code'] = (isset($params['code']))?(int)$params['code']:0;
	$msg['meta']['status'] = (isset($params['status']))?$params['status']:'';
	$msg['meta']['message'] = (isset($params['message']))?$params['message']:'';
	
	$msg['data'] = (isset($params['data']))? $params['data']:[];
	if(isset($params['pagination']) && !empty($params['pagination'])) $msg['pagination'] = $params['pagination'];

	$res = (object) $msg ;
	return new Symfony\Component\HttpFoundation\JsonResponse($res);
}