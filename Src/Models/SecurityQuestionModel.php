<?php
namespace Models;

use Illuminate\Pagination\Paginator;
use Illuminate\Database\Query\Expression as raw;

class SecurityQuestionModel extends \Illuminate\Database\Eloquent\Model{

	protected $table = 'sequrity_question';
	protected $app;
	public function __construct($app){
		$this->app = $app;
    }
	
}