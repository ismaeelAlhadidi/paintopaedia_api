<?php

namespace App\Config;

use App\Config\File;

class Request {

    protected $request = [];

    protected $files = [];

    protected $method = null;

    public function __construct () {

        $this->fetch_request();
    }

    private function fetch_request() {
        
        $this->method = $_SERVER['REQUEST_METHOD'];

        $this->request = $_REQUEST;

        unset($_REQUEST);

        if(! $this->method == "POST") return;

        foreach($_FILES as $key => $file) {

            if(is_array($file['tmp_name'])) {

                $filesCount = count($file['tmp_name']);

                $files = [];

                for($i = 0; $i < $filesCount; $i++) {

                    $singleFile = [];
                    
                    $singleFile['name'] = $file['name'][$i];
                    $singleFile['type'] = $file['type'][$i];
                    $singleFile['size'] = $file['size'][$i];
                    $singleFile['tmp_name'] = $file['tmp_name'][$i];
                    $singleFile['error'] = $file['error'][$i];

                    array_push($files, new File($singleFile));
                }
                
                $this->files[$key] = $files;

                continue;
            }

            $this->files[$key] = new File($file);
        }
        
        unset($_FILES);
    }

    public function file($key) {

        return isset($this->files[$key]) ? $this->files[$key] : null;
    }

    public function get($key) {

        return isset($this->request[$key]) ? $this->request[$key] : null;
    }

    public function only(array $keys) {

        $temp = [];

        foreach($keys as $key) {
            
            if(! isset($this->request[$key])) continue;
            $temp[$key] = $this->request[$key];
        }

        return $temp;
    }

    public function method() {

        return $this->method;
    }

    public function all() {

        return $this->request;
    }
}