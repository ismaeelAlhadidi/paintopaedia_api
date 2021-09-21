<?php

namespace App\Requests;

abstract class RequestForm {

    protected $valid = true;

    protected $request = [];

    protected $excepted_data = [
        /*'key' => 'required',*/
    ];

    protected $sanitized_keys = [
        /*'excepted__data_key' => 'new_key',*/
    ];

    public function __construct (array $request) {

        $this->set($request);

        if($this->valid) if($this->authorized() && $this->validate()) {

            $this->valid = true;

            return $this;
        }

        $this->valid = false;
    }

    public function fail() {

        return ! $this->valid;
    }

    public function get() {
        
        return $this->request;
    }

    abstract protected function validate() : bool;

    abstract protected function authorized() : bool;

    protected function set(array $request) {

        foreach($this->excepted_data as $key => $value) {

            if( array_key_exists($key, $request) ) {

                $this->request[$key] = $request[$key];
                continue;
            }

            if($value == 'required') {

                $this->valid = false;
                break;
            }
        }

        $this->sanitize();
    }

    protected function sanitize() {

        foreach($this->sanitized_keys as $excepted_data_key => $sanitized_key) {

            if(! array_key_exists($excepted_data_key, $this->request)) continue;

            if( array_key_exists($sanitized_key, $this->request) ) {

                throw new RequestFormCollisionKeysException (
                    "$sanitized_key collision with $excepted_data_key in Request " . static::class
                );
            }

            $excepted_data_value = $this->request[$excepted_data_key];

            $this->request[$sanitized_key] = $excepted_data_value;

            unset($this->request[$excepted_data_key]);
        }
    }

}