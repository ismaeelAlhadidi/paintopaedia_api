<?php

use \Firebase\JWT\JWT;

$key = "hidden-secret-key";

$token_time_out = ( 24 * 60 * 60 ); // one day

function get_user_id_from_sse_token($jwt) {

    global $key;

    try {

        $decoded_token = JWT::decode($jwt, $key, array('HS256'));

        $decoded_token = ( array ) $decoded_token;

        if(! isset($decoded_token['id'])) return false;

        return $decoded_token['id'];

    } catch (Exception $e) {

        return false;
    }
}