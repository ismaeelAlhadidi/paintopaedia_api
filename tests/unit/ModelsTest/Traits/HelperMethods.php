<?php 

use App\Config\Database;

trait HelperMethods {

    protected static $connection = null;

    private function rowsEquals(array $first_rows, array $second_rows) {
        
        if(count($first_rows) != count($second_rows)) return false;
        
        foreach($first_rows as $first_row) {
            $same_row_not_founded = true;

            foreach($second_rows as $second_row) {
                
                if($this->rowEquals($first_row, $second_row)) {
                    $same_row_not_founded = false;
                    break;
                }
            }

            if($same_row_not_founded) return false;
        }

        return true;
    }

    private function rowEquals($first, $second) {
        
        if(! is_array($first)) {
            $first = $first->get();
            if(! is_array($first)) return false;
        }

        if(! is_array($second)) {
            $second = $second->get();
            if(! is_array($second)) return false;
        }
        
        if(count($first) != count($second)) return false;

        foreach($first as $key => $value) {
            
            if( ! array_key_exists($key, $second) ) return false;

            if( is_array($second[$key]) && is_array($value) &&
                ! $this->is_associative_array($value) &&
                ! $this->is_associative_array($second[$key]) ) {
                
                if($this->rowsEquals($second[$key], $value)) continue;
            }

            if( (is_array($second[$key]) || is_object($second[$key])) &&
                (is_array($value) || is_object($value)) && 
                (! (is_null($value) || is_null($second[$key]))) ) {
                
                if($this->rowEquals($second[$key], $value)) continue;
            }
            
            if( $second[$key] != $value ) {
                
                
                return false;
            }
        }

        return true;
    }

    private function rowsInRows(array $rows, array $search_rows) {

        foreach($rows as $row) {

            if($this->rowInRows($row, $search_rows)) continue;

            return false;
        }
        return true;
    }

    private function rowInRows($row, array $search_rows) {

        foreach($search_rows as $search_row) {

            if(! is_array($row)) {
                $row = $row->get();
                if(! is_array($row)) return false;
            }

            if(! is_array($search_row)) {
                $search_row = $search_row->get();
                if(! is_array($row)) return false;
            }

            if($this->rowEquals($row, $search_row)) return true;
        }

        return false;
    }

    private static function executeQuery($query) {
        if(self::$connection == null) {
            $database = new Database();

            self::$connection = $database->getConnection();
        }
        try {

            $statement = self::$connection->prepare($query);

            $statement->execute();

        } catch(PDOException $e) { }
    }

    private static function executeQueryForResult($query) {
        if(self::$connection == null) {
            $database = new Database();

            self::$connection = $database->getConnection();
        }
        try {

            $statement = self::$connection->prepare($query);

            if(! $statement->execute()) return false;

            return $statement->fetchAll();

        } catch(PDOException $e) {

        }

        return false;
    }

    public function is_associative_array($array) {

        if(! is_array($array) ) return false;

        if (array() === $array) return false;

        return (
            array_keys($array) !== range(0, count($array) - 1)
        );
    }

}