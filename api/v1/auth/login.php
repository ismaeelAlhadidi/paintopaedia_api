<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "POST") App\Config\Response::toJson([], 0, "", 404);

if(request()->get('email') == null) App\Config\Response::toJson([], 0, "request not valid", 200);

if(request()->get('password') == null) App\Config\Response::toJson([], 0, "request not valid", 200);

if( ! auth()->attempt( request()->only(['email', 'password']) )) {

    App\Config\Response::toJson([], 0, "check your email and password", 200);
}

$response = auth()->user()->get();

if(! isset($response['profile_picture']) || $response['profile_picture'] == null || ! is_string($response['profile_picture'])) {

    $response['profile_picture'] = DEFAULT_USER_PICTURE;
}

$response['jwt'] = auth()->get_token();

App\Config\Response::toJson($response, true, "login successed", 200);