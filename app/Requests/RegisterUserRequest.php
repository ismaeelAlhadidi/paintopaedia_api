<?php

namespace App\Requests;

use App\Models\User;

class RegisterUserRequest extends RequestForm {


    protected $excepted_data = [
        /*'key' => 'required',*/
        'email' => 'required',
        'first_name' => 'required',
        'last_name' => 'required',
        'password' => 'required'
    ];

    protected $sanitized_keys = [
        /*'excepted__data_key' => 'new_key',*/

    ];
 
    protected function validate() : bool {

        if(! is_string($this->request['first_name'])) return false;
        $this->request['first_name'] = filter_var($this->request['first_name'], FILTER_SANITIZE_STRING);
        if(strlen($this->request['first_name']) > 255) return false;

        if(! is_string($this->request['last_name'])) return false;
        $this->request['last_name'] = filter_var($this->request['last_name'], FILTER_SANITIZE_STRING);
        if(strlen($this->request['last_name']) > 255) return false;

        if(! is_string($this->request['email'])) return false;
        if(! filter_var($this->request['email'], FILTER_VALIDATE_EMAIL) ) return false;
        if(strlen($this->request['email']) > 255) return false;

        $this->request['password'] = password_hash($this->request['password'], PASSWORD_DEFAULT);
        
        if(User::where('email', $this->request['email'])->first() != false) {

            return false;
        }

        return true;
    }

    protected function authorized() : bool {

        return true;
    }
}