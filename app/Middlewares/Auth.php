<?php 

namespace App\Middlewares;

use App\Config\Response;
use App\Config\DataBase;

class Auth extends Middleware {

    public function handle() : bool {

        if(! auth()) return false;

        if( ! auth()->check() ) return false;
        
        return true;
    }

    public function response() {

        Response::toJson([], 0, "Unauthorized", 401);
        
    }
}