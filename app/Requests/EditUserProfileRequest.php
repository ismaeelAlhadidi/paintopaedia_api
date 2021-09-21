<?php

namespace App\Requests;

use App\Models\User;

class EditUserProfileRequest extends RequestForm {


    protected $excepted_data = [
        /*'key' => 'required',*/
        'email' => '',
        'first_name' => '',
        'last_name' => '',
        'password' => ''
    ];

    protected $sanitized_keys = [
        /*'excepted__data_key' => 'new_key',*/

    ];
 
    protected function validate() : bool {

        if(isset($this->request['first_name'])) {

            if(! is_string($this->request['first_name'])) return false;
            $this->request['first_name'] = filter_var($this->request['first_name'], FILTER_SANITIZE_STRING);
            if(strlen($this->request['first_name']) > 255) return false;
        }

        if(isset($this->request['last_name'])) {
            if(! is_string($this->request['last_name'])) return false;
            $this->request['last_name'] = filter_var($this->request['last_name'], FILTER_SANITIZE_STRING);
            if(strlen($this->request['last_name']) > 255) return false;
        }

        if(isset($this->request['email'])) {
            if(! is_string($this->request['email'])) return false;
            if(! filter_var($this->request['email'], FILTER_VALIDATE_EMAIL) ) return false;
            if(strlen($this->request['email']) > 255) return false;
            if(User::where('email', $this->request['email'])->where('email', '!=', auth()->user()->email)->first() != false) {

                return false;
            }
        }

        if(isset($this->request['password'])) $this->request['password'] = password_hash($this->request['password'], PASSWORD_DEFAULT);

        return true;
    }

    protected function authorized() : bool {

        if(! auth()) return false;

        if( ! auth()->check() ) return false;

        return true;
    }
}