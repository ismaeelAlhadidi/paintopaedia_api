<?php

namespace App\Requests;

class AddPostRequest extends RequestForm {


    protected $excepted_data = [
        /*'key' => 'required',*/
        'content' => '',
        'category_name' => 'required'
    ];

    protected $sanitized_keys = [
        /*'excepted__data_key' => 'new_key',*/
        'content' => 'description',
    ];
 
    protected function validate() : bool {

        if(isset($this->request['description'])) {

            if(! is_string($this->request['description'])) return false;
            $this->request['description'] = filter_var($this->request['description'], FILTER_SANITIZE_STRING);
            if(strlen($this->request['description']) > 5000) return false;

            if($this->request['description'] == '') {

                unset($this->request['description']);
            }
        }
        
        return true;
    }

    protected function authorized() : bool {

        if(! auth()) return false;

        if( ! auth()->check() ) return false;

        return true;
    }
}