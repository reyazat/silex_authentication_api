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
		~d($arg);
	}
	exit;
}