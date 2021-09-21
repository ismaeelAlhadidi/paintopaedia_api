<?php

namespace App\Config;

class File {

    static $storage = null;

    static $public_storage = 'http://localhost/paintopaedia_api/api';

    static function delete($paths) {

        if(! is_array($paths) ) return;

        foreach($paths as $path) {

            @unlink($path);
        }

    }

    protected $file_name;
    protected $type;
    protected $size;
    protected $temp_name;
    protected $error;
    protected $extension;

    public function __construct ($file) {

        if(static::$storage == null) {
            
            static::$storage = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'paintopaedia_api' . DIRECTORY_SEPARATOR .'api';
        }

        $this->file_name = $file['name'];
        $this->type = $file['type'];
        $this->size = $file['size'];
        $this->temp_name = $file['tmp_name'];
        $this->error = $file['error'];

        $this->extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        if($this->extension == '') $this->extension = $this->get_extension($this->type);
    }

    public function store($driver = null) {

        $new_path = static::$storage . ( $driver == null ? '' : DIRECTORY_SEPARATOR . $driver );

        $new_name = uniqid(rand() , true);

        $new_name = str_replace('.', '_', $new_name);

        $file = $new_path . DIRECTORY_SEPARATOR . $new_name . '.' . $this->extension;

        if(! move_uploaded_file($this->temp_name, $file)) return null;

        return static::$public_storage . ( $driver == null ? '' : '/' . $driver ) . '/' . $new_name . '.' . $this->extension;
    }

    public function valid($types, $size, $extensions = null) {

        return ( 
            $this->check_type($types) &&
            $this->check_size($size) && 
            ( 
                $extensions != null ? $this->check_extension($extensions) : true
            )
        );
    }

    public function fail() {

        return $this->error != 0;
    }

    public function check_extension(array $extensions) {

        return in_array($this->extension, $extensions);
    }

    public function check_type(array $types) {

        return in_array($this->type, $types);
    }

    public function check_size($size) {

        return $this->size <= $size;
    }

    public function get_extension($mimiType) {


        $types = [
            'video/x-flv' => 'flv',
            'video/mp4' => 'mp4',
            'application/x-mpegURL' => 'm3u8',
            'video/MP2T' => 'ts',
            'video/3gpp' => '3gp',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
            'video/x-ms-wmv' => 'wmv',
            'image/svg+xml' => 'svg'
        ];

        if( array_key_exists($mimiType, $types) ) {

            return $types[$mimiType];
        }

        return explode('/', $mimiType)[1];
    }
}