<?php 

namespace App\Middlewares;

abstract class Middleware {

    public function __construct () {

        if( ! $this->handle() ) {

            $this->response();

        }
    }

    public function handle() : bool {}

    public function response() {}
}