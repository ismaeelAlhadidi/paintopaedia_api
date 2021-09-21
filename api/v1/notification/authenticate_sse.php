<?php

include_once __DIR__ . "/../../config.php";

new App\MiddleWares\Auth();

// authorize server-sent event token for current client //

include_once __DIR__ . '/auth_sse_config.php';

use \Firebase\JWT\JWT;

$time = time();

$payload = [
    'iat' => $time,
    'exp' => $time + $token_time_out,

    'id' => auth()->id()
];

$st = JWT::encode($payload, $key);

$response_data = [
    'st' => $st,
];

App\Config\Response::toJson($response_data, true, "", 200);