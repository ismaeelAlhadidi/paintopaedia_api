<?php

namespace App\Authentecation;

use App\Models\Model;

abstract class AuthModel extends Model {

    protected static $handler;
    protected static $password;
    protected static $password_column_name;

    public function attempt(array $data) {

        $query = "select * from " . static::getTable() . " where " . static::$handler . "=:handler limit 0, 1;";

        try {
            
            $prepare = self::$connection->prepare ($query);

            $prepare->bindValue(':handler', $data[static::$handler], \PDO::PARAM_STR);

            if(! $prepare->execute()) return false;
            
            $result = $prepare->fetch();
            
            if( ! is_array($result) ) return false;
            
            if( ! array_key_exists(static::$password_column_name, $result) ) return false;
            
            if( ! password_verify( $data[static::$password], $result[static::$password_column_name] ) ) return false;

        } catch(PDOException $e) {
            
            return false;
        }

        $gaurd = new static();
        
        static::setData($result, $gaurd);

        return $gaurd;
    }

    public function change_password($password) {

        return $this->update([
            static::$password_column_name => password_hash($password, PASSWORD_DEFAULT)
        ]);

    }
}