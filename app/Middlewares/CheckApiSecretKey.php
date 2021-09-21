<?php 

namespace App\Middlewares;

use App\Config\Response;
use App\Config\DataBase;

class CheckApiSecretKey extends Middleware {

    public function handle() : bool {
        
        if(request()->get(DataBase::$api_key) != DataBase::$api_secret) return false;

        return true;
    }

    public function response() {

        Response::toJson([], 0, "Unauthorized", 200);

    }
}