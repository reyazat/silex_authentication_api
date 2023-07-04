<?php
//Allow PHP's built-in server to serve our static content in local dev:
$sapi_type = php_sapi_name();
if(substr($sapi_type, 0, 3) == 'cli') {
	
	return false;
}

try {
    // Base path
    if (!defined('BASEDIR')) {
        define('BASEDIR', realpath(__DIR__ . '/../'));
    }
	
    require_once BASEDIR . '/App/Bootstrap.php';
	
    $bootstrap = new Bootstrap();
    $bootstrap->run();
	
} catch (\Exception $exc) {
    // catch and report any stray exceptions...
    echo json_encode('Internal Server Error (Index SECTION) : '.$exc->getMessage());
}

