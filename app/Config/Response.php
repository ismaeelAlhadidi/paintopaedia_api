<?php

namespace App\Config;

class Response {

    public static function toJson(array $data, $status, $msg, $code = 200) {

        http_response_code($code);

        echo json_encode(
            array (
                'status' => $status,
                'msg' => $msg,
                'data' => $data
            )
        );

        exit;
    }
}