<?php
require_once 'Middleware.php';


$this->app->mount('/', new Controllers\PublicController());

$this->app->mount('/error', new Controllers\ErrorController());




