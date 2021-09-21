<?php 

namespace App\Collections;
use Ds\Map;
use App\Exceptions\ModelException;

class RelationshipsSqlData {

    public $data = [];
    public $childs = [];
    public $keys = [];
    
    public function __construct($childs_types) {

        foreach($childs_types as $child_type) {

            $this->childs[$child_type] = new Map();
        }
    }

    public static function setData(array $record, array $hidden, string $table, array $with, $childs_primary_keys, $model, RelationshipsSqlData $temp) {

        foreach($record as $key => $value) {

            $key = str_replace($table . '.', '', $key, $replaced);

            if($replaced != 1) $key = str_replace($table . '_' . get_class($model) . '_', '', $key);

            if(is_array($value) && count($with) > 0) {

                if( count($value) != count($with) + 1 ) throw new ModelException(
                    'you have multiple columns with the same name ' .
                    'please set columns on ' . get_class($model) . ' and his childes model'
                );

                for($i = 1; $i < count($value); $i++) {

                    if(! isset($childs_primary_keys[$with[$i-1]]) ) {
                        
                        if($value[$i] == null) continue;
                    }

                    $child_primary_keys = $childs_primary_keys[$with[$i-1]];
                    
                    $new_element = $temp->childs[$with[$i-1]]->get($child_primary_keys);
                    
                    $new_element[$key] = $value[$i];

                    $temp->childs[$with[$i-1]]->put($child_primary_keys, $new_element);
                }

                $value = $value[0];
            }

            if($replaced != 1) {

                $key = str_replace('index_' . $table . '_', '', $key, $replaced);

                if($replaced == 1) {
                    $temp->keys[$key] = $value;
                    continue;
                }
            }

            if(! in_array($key, $hidden)) $temp->data[$key] = $value;
        }
    }
}