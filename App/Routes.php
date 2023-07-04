<?php
require_once 'Middleware.php';

$this->app->mount('/authenticate', new Component\oAuth\Controllers\Authenticate()); 
$this->app->mount('/company', new Component\oAuth\Controllers\Company());
$this->app->mount('/forgetpass', new Component\oAuth\Controllers\ForgetPass());
$this->app->mount('/socialoauth', new Component\SocialMedia\Controllers\Social());
$this->app->mount('/user', new Component\oAuth\Controllers\User());
$this->app->mount('/twofactor', new Component\oAuth\Controllers\TwoFactor());
$this->app->mount('/admin', new Component\Admin\Controllers\Admin());
$this->app->mount('/accounting', new Component\Admin\Controllers\Accounting());

$this->app->mount('/v1/pricing', new Controllers\PricingController());
$this->app->mount('/v1/permissions', new Controllers\PermissionController());
$this->app->mount('/v1/setting', new Controllers\SettingController());

$this->app->mount('/', new Controllers\PublicController());
