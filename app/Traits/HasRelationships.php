<?php 

namespace App\Traits;
use App\Models\Model;
use App\Collections\RelationshipsSqlData;
use App\Exceptions\ModelException;
use Ds\Map;

trait HasRelationships {

    protected $with = [];

    protected $has_many = false;

    protected function leftJoinQuery($Model, $table, $forgenKey, $localKey, $relationship, $type_column = null) {

        return [
            'table' => $table,
            'sql' => 'LEFT JOIN ' . $table . ' ON ' . static::getTable() . '.' . $localKey . '=' . $table . '.' . $forgenKey
            . ($type_column == null ? '' : ' AND ' . $table . '.' . $type_column . "='" . static::class . "'"),
            'model' => $Model,
            'relationship' => $relationship,
        ];
    }

    public function with($childs_or_parents) {

        $model = ( isset($this) ? $this : new static() );

        if(! is_array($childs_or_parents)) $childs_or_parents = [ $childs_or_parents ];

        foreach($childs_or_parents as $temp) {
            if( ! method_exists($model, $temp)) throw new \InvalidArgumentException('model don\'t have ' . $temp);

            array_push($model->with, $temp);

            if($model->$temp['relationship'] == 'many') $model->has_many = true;
        }
        
        return $model;
    }

    public function getLeftJoinQuerys() {
        $query = "";

        foreach($this->with as $method) {

            $result = $this->$method();

            $query .= $result['sql'] . ' ';
        }

        return $query;
    }

    protected function hasOne($Model, $table, $forgenKey, $localKey) {
        if( count($this->with) > 0 ) return $this->leftJoinQuery($Model, $table, $localKey, $forgenKey, 'one');

        $forgenKey_value = ( isset($this->data[$forgenKey]) ? $this->data[$forgenKey] : $this->keys[$forgenKey] );

        return $Model::where($localKey, $forgenKey_value)->first();
    }

    protected function belongsTo($Model, $table, $forgenKey, $localKey) {
        if( count($this->with) > 0 ) return $this->leftJoinQuery($Model, $table, $forgenKey, $localKey, 'one');

        $localKey_value = ( isset($this->data[$localKey]) ? $this->data[$localKey] : $this->keys[$localKey] );

        return $Model::where($forgenKey, $localKey_value)->first();
    }

    protected function hasMany($Model, $table, $forgenKey, $localKey) {
        if( count($this->with) > 0 ) return $this->leftJoinQuery($Model, $table, $forgenKey, $localKey, 'many');

        $localKey_value = ( isset($this->data[$localKey]) ? $this->data[$localKey] : $this->keys[$localKey] );

        return $Model::where($forgenKey, $localKey_value)->get();
    }

    protected function morphTo($Model, $table, $forgenKey, $type_column, $localKey) {
        if( count($this->with) > 0 ) return $this->leftJoinQuery($Model, $table, $forgenKey, $localKey, 'many', $type_column);

        $localKey_value = ( isset($this->data[$localKey]) ? $this->data[$localKey] : $this->keys[$localKey] );
        
        $component_type = explode('\\', static::class);
        
        $component_type = $component_type[count($component_type) - 1];
        
        return $Model::where($forgenKey, $localKey_value)->where($type_column, $component_type)->get();
    }


    private function getLimitedModelFromMultiRecords($statement, $records_count = 1, $records_start = 0) {

        $elements = new Map();

        $fullPrimaryKey = $this->getTable() . '_' . static::class . '_' . $this->getPrimaryKey();

        if($this->select != null || count($this->selects) > 0) {

            $fullPrimaryKey = "index_" . $this->getTable() . "_" . $this->getPrimaryKey() . "";
        }

        $target_id = null;
        $fetched_count = 0;

        while($record = $statement->fetch()) {
            
            $primaryKey = $fullPrimaryKey;

            if(! isset($record[$primaryKey])) {

                $primaryKey = $this->getTable() . '.' . $this->getPrimaryKey();

            } elseif($this->select == null) {
                $primaryKey_value = $record[$primaryKey];
                unset($record[$primaryKey]);
                $record[$this->getPrimaryKey()] = $primaryKey_value;
                $primaryKey = $this->getPrimaryKey();
            }

            
            if($target_id == null) $target_id = $record[$primaryKey];
            elseif($target_id != $record[$primaryKey]) {

                $fetched_count++;
                $target_id = $record[$primaryKey];
            }

            if($fetched_count == $records_start + $records_count) return $this->fetchRelationshipsDataAsArray($elements);
            
            if($fetched_count < $records_start) continue;

            if(! $elements->hasKey($record[$primaryKey])) {

                $data = new RelationshipsSqlData($this->with);

                $data = $this->addRecordToData($record, $data);

                $elements->put($record[$primaryKey], $data);
                
                continue;
            }
            
            $elements->put ( 
                $record[$primaryKey], 
                $this->addRecordToData($record, $elements->get($record[$primaryKey]))
            );
        }

        return $this->fetchRelationshipsDataAsArray($elements);
    }

    private function getModelStructureFromOneToManyLeftJoinResult($statement) {

        $elements = new Map();

        $fullPrimaryKey = $this->getTable() . '_' . static::class . '_' . $this->getPrimaryKey();

        if($this->select != null || count($this->selects) > 0) {

            $fullPrimaryKey = "index_" . $this->getTable() . "_" . $this->getPrimaryKey() . "";
        }

        while($record = $statement->fetch()) {

            $primaryKey = $fullPrimaryKey;

            if(! isset($record[$primaryKey])) {

                $primaryKey = $this->getTable() . '.' . $this->getPrimaryKey();

            } elseif($this->select == null && count($this->selects) < 1) {
                $primaryKey_value = $record[$primaryKey];
                unset($record[$primaryKey]);
                $record[$this->getPrimaryKey()] = $primaryKey_value;
                $primaryKey = $this->getPrimaryKey();
            }

            if(! $elements->hasKey($record[$primaryKey])) {

                $data = new RelationshipsSqlData($this->with);

                $data = $this->addRecordToData($record, $data);

                $elements->put($record[$primaryKey], $data);
                
                continue;
            }
            
            $elements->put ( 
                $record[$primaryKey], 
                $this->addRecordToData($record, $elements->get($record[$primaryKey]))
            );
        }
        
        return $this->fetchRelationshipsDataAsArray($elements);
    }

    private function addRecordToDataWithSeperated($record, RelationshipsSqlData $data, $seperator) {
        $childs = [];

        $cuurent_method = null;
        
        $data_in_data = [];

        foreach($record as $key => $value) {
            
            if($key[0] == $seperator && $key[strlen($key)-1] == $seperator) {
                $cuurent_method = trim($key, $seperator);
                if(! in_array($cuurent_method, $this->with)) $cuurent_method = null;
                continue;
            }

            if($cuurent_method == null) {
                $data_in_data[$key] = $value;
                continue;
            }

            if(! array_key_exists($cuurent_method, $childs)) $childs[$cuurent_method] = [];

            if(is_array($value)) {

                if(count($value) != count($this->with)) throw new ModelException (
                    'you have multiple columns with the same name ' .
                    'please set columns on ' . static::class . ' and his childes model'
                );

                for($i = 1; $i < count($value); $i++) {

                    if(! array_key_exists($this->with[$i], $childs)) $childs[$this->with[$i]] = [];
                    $childs[$this->with[$i]][$key] = $value[$i];
                }
                $value = $value[0];
            }
            
            $childs[$cuurent_method][$key] = $value;
        }

        $childs_primary_keys = [];

        foreach($childs as $child => $element) {
            $result = $this->$child();

            $fullkey = ( $result['table'] . '_' . $result['model'] . '_' . $result['model']::getPrimaryKey() );

            if(! isset($element[$fullkey])) continue;
            $primaryKey = $element[$fullkey];
            $element[$result['model']::getPrimaryKey()] = $primaryKey;
            unset($element[$fullkey]);

            $data->childs[$child]->put($primaryKey, $element);

            $childs_primary_keys[$child] = $primaryKey;
        }
        
        RelationshipsSqlData::setData($data_in_data, $this->hidden, $this->getTable(), $this->with, $childs_primary_keys, $this, $data);

        return $data;
    }
    private function addRecordToData($record, RelationshipsSqlData $data) {

        if(isset($record[':' . $this->with[0] . ':'])) return $this->addRecordToDataWithSeperated($record, $data, ':');

        $data_in_data = [];
        
        $childs = [];

        foreach($record as $key => $value) {

            $key = str_replace($this->getTable() . '.', '', $key, $replaced);

            if($replaced == 1) {
                $data_in_data[$key] = $value;
                continue;
            }

            foreach($this->with as $method) {

                $result = $this->$method();

                $key = str_replace($result['table'] . '.', '', $key, $replaced);

                if($replaced != 1) continue;

                $primaryKey = $record[$result['table'] . '.' . $result['model']::getPrimaryKey()];

                if(! array_key_exists($method, $childs)) $childs[$method] = [];

                $childs[$method][$key] = $value;

                break;
            }
        }

        $childs_primary_keys = [];

        foreach($childs as $child => $element) {

            $result = $this->$child();

            if(! isset($element[$result['model']::getPrimaryKey()])) continue;
            $primaryKey_value = $element[$result['model']::getPrimaryKey()];

            $data->childs[$child]->put($primaryKey_value, $element);

            $childs_primary_keys[$child] = $primaryKey_value;
        }

        RelationshipsSqlData::setData($data_in_data, $this->hidden, $this->getTable(), $this->with, $childs_primary_keys, $this, $data);

        return $data;
    }

    private function fetchRelationshipsDataAsArray($elements) {

        $fetchedData = [];

        foreach($elements as $primaryKey => $element) {

            $element = $this->getModelFromRelationshipsSqlData($element);

            array_push($fetchedData, $element);
        }

        return $fetchedData;
    }
}