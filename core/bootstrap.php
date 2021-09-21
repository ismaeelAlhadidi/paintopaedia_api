<?php 

require_once __DIR__ . '/../vendor/autoload.php';

function auth() {

    static $auth = null;

    if($auth == null) $auth = new App\Authentecation\Auth();

    return $auth;
}

function request() {

    static $request = null;

    if($request == null) $request = new App\Config\Request();

    return $request;
}

new App\Middlewares\CheckApiSecretKey();