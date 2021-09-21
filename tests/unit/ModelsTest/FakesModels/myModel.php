<?php

use App\Models\Model;

class myModel extends Model {

    protected $table = "table_for_test_model";

    // ################ mehtod for test ############# //
    public function getStaticConnectionForTest() {
        return Model::$connection;
    }

    public function getMyPrimaryKey() {

        return $this->getPrimaryKey();
    }

    public function getFillable() {

        return $this->fillable;
    }

    public static function get_table() {
        
        $temp = new static();

        return $temp->table;
    }
    // ############################################# //
}