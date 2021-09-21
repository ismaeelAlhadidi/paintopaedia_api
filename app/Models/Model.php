<?php

namespace App\Models;

use App\Interfaces\ModelInterFace;
use App\Config\DataBase;
use \IteratorAggregate;
use \ArrayIterator;
use App\Helpers\Paginator;
use App\Exceptions\ModelException;
use App\Traits\HasRelationships;
use App\Collections\RelationshipsSqlData;

abstract class Model implements ModelInterFace, IteratorAggregate, \ArrayAccess, \JsonSerializable {

    use HasRelationships;

    protected $table = null;
    protected $fillable = null;
    protected $hidden = [];
    protected $primaryKey;

    protected static $connection = null;

    protected $data = [];
    protected $keys = [];

    private $select = null;
    private $where = null;
    private $orderBy = null;
    private $limit = null;

    private $selects = [];

    protected static $columns = [];
    protected static $indexes = [];

    public function __construct() {

        if(Model::$connection != null) return;
            
        $db = new DataBase();

        Model::$connection = $db->getConnection();
    }

    protected function getPrimaryKey() {

        $model = ( isset($this) ) ? $this : new static();

        return $model->primaryKey == null ? 'id' : $model->primaryKey;
    }

    protected function getTable() {

        $model = ( isset($this) ) ? $this : new static();

        if($model->table != null) return $model->table;

        $modelName = explode('\\', static::class);

        $modelName = $modelName[count($modelName) - 1];

        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $modelName)), '_') . 's';
    }

    public static function getColumns() {
        
        $table = static::getTable();

        if( isset(static::$columns[$table]) ) return static::$columns[$table];

        static::$columns[$table] = [];
        
        $query = "SHOW FULL FIELDS FROM " . $table . ";";
        
        try {

            $prepare = self::$connection->prepare ($query);

			if(! $prepare->execute()) return null;

            $result = $prepare->fetchAll();

            foreach($result as $row) array_push(static::$columns[$table], $row['Field']);            

        } catch(PDOException $e) {

            return null;
        }

        return static::$columns[$table];
    }

    public static function getIndexes() {

        $table = static::getTable();

        if( isset(static::$indexes[$table]) ) return static::$indexes[$table];

        static::$indexes[$table] = [];
        
        $query = "SHOW INDEX FROM  " . $table . ";";
        
        try {

            $prepare = self::$connection->prepare ($query);

			if(! $prepare->execute()) return null;

            $result = $prepare->fetchAll();

            foreach($result as $row) array_push(static::$indexes[$table], $row['Column_name']);            

        } catch(PDOException $e) {

            return null;
        }

        return static::$indexes[$table];
    }

    protected function getModelFromRelationshipsSqlData(RelationshipsSqlData $element) {

        $model = new static();
        
        self::setData($element->data, $model);

        self::setChilds($element->childs, $model, $this);
        
        return $model;
    }

    protected static function setData($record, $model) {

        foreach($record as $key => $value) {

            $key = str_replace($model->getTable() . '.', '', $key, $replaced);

            if($replaced != 1) $key = str_replace($model->getTable() . '_' . get_class($model) . '_', '', $key);

            if(is_array($value) && count($model->with) > 0 && ! $model->has_many) {

                if( count($value) != count($model->with) + 1) throw new ModelException (
                    'you have multiple columns with the same name ' .
                    'please set columns on ' . get_class($model) . ' and his childes model'
                );

                for($i = 1; $i < count($value); $i++) {
                    $model->data[$model->with[$i-1]][$key] = $value[$i];
                }

                $value = $value[0];
            }

            if($replaced != 1) {

                $key = str_replace('index_' . $model->getTable() . '_', '', $key, $replaced);

                if($replaced == 1) {
                    $model->keys[$key] = $value;
                    continue;
                }
            }

            if(! in_array($key, $model->hidden)) $model->data[$key] = $value;

        }
    }

    protected static function setChilds($childs, $model, $parent) {
        
        foreach($childs as $child => $elements) {
            
            $result = $parent->$child();
            
            if(! isset($model->data[$child])) {

                $model->data[$child] = [];
            }

            if( $result['relationship'] == 'one' ) {

                $child_model = new $result['model']();

                $result['model']::setData($elements->first()->value, $child_model);

                $model->data[$child] = $child_model;

                continue;
            }

            foreach($elements as $element) {
                
                $child_model = new $result['model']();

                $result['model']::setData($element, $child_model);

                array_push ( $model->data[$child],  $child_model );

            }
        }
    }

    protected function setDataFromLeftJoinWithSeperated($record, $model, $seperator) {

        $data = [];

        $temp_childs = [];

        $cuurent_method = null;

        foreach($record as $key => $value) {
            
            if($key[0] == $seperator && $key[strlen($key)-1] == $seperator) {
                $cuurent_method = trim($key, $seperator);
                if(! in_array($cuurent_method, $this->with)) $cuurent_method = null;
                continue;
            }

            if($cuurent_method == null) {
                if(! in_array($key, $model->hidden)) $data[$key] = $value;
                continue;
            }

            if(! array_key_exists($cuurent_method, $temp_childs)) $temp_childs[$cuurent_method] = [];

            $result = $this->$cuurent_method();

            $fullkey = ( $result['table'] . '_' . $result['model'] . '_' . $result['model']::getPrimaryKey() );

            if( $key == $fullkey ) $key = $result['model']::getPrimaryKey();

            $temp_childs[$cuurent_method][$key] = $value;
        }
        
        self::setData($data, $model);

        foreach($this->with as $method) {
            if(! array_key_exists($method, $temp_childs)) continue;

            $result = $this->$method();

            if($result['relationship'] != 'one') return null;

            $model->data[$method] = $result['model']::buildModelFromRow($temp_childs[$method]);
        }
    }
    protected function setDataFromLeftJoin($record, $model) {

        if(isset($record[':' . $this->with[0] . ':'])) return $this->setDataFromLeftJoinWithSeperated($record, $model, ':');

        $temp_childs = [];

        $data = [];

        foreach($record as $key => $value) {

            $key = str_replace($model->getTable() . '.', '', $key, $replaced);

            if($replaced == 1) {
                if(! in_array($key, $model->hidden)) $model->data[$key] = $value;
                continue;
            }

            foreach($this->with as $method) {

                $result = $this->$method();

                if($result['relationship'] != 'one') return null;
                    
                $key = str_replace($result['table'] . '.', '', $key, $replaced);

                if($replaced == 1) {

                    if(! array_key_exists($method, $temp_childs)) $temp_childs[$method] = [];

                    $temp_childs[$method][$key] = $value;

                    break;
                }
            }

        }

        self::setData($data, $model);

        foreach($this->with as $method) {
            if(! array_key_exists($method, $temp_childs)) continue;

            $result = $this->$method();

            if($result['relationship'] != 'one') return null;

            $model->data[$method] = $result['model']::buildModelFromRow($temp_childs[$method]);
        }
    }

    private function getSelectQuery() {

        if( count($this->with) < 1 ) return (
            ( $this->select == null ) ? 
            ( "select * from " . $this->getTable() . " ") : 
            ( $this->select )
        );

        $select = "select ";

        $columns = ( $this->select == null ) ? $this->getColumns() : $this->selects;

        if($columns != null) foreach($columns as $column) {

            $select .= $this->getTable() . '.' . $column . ' as "' . $this->getTable() . '.' . $column . '",'; 
        }
        else $select .= $this->getTable() . '.*,' .
            $this->getTable() . '.' . $this->getPrimaryKey() . ' as ' .
            $this->getTable() . '_' . static::class . '_' . 
            $this->getPrimaryKey() . ',';

        foreach(static::getIndexes() as $index) {
            if( ! in_array($index, $columns) ) {
                $select .= $this->getTable() . '.' . $index . " as " . "index_" . $this->getTable() . "_" . $index . ",";
            }
        }
        
        foreach($this->with as $method) {

            $result = $this->$method();

            $columns = $result['model']::getColumns();

            if($columns != null) foreach($columns as $column) {

                $select .= $result['table'] . '.' . $column . ' as "' . $result['table'] . '.' . $column . '",'; 
            }
            else $select .= '\':' . $method . ':\',' . $result['table'] . '.*,' .
                    $result['table'] . '.' . $result['model']::getPrimaryKey() . ' as ' .
                    $result['table'] . '_' . $result['model'] . '_' . 
                    $result['model']::getPrimaryKey() . ',';
        }

        return rtrim($select, ',') . ' from ' . $this->getTable() . ' ';
    }

    private function getWhereQuery() {

        return ( $this->where == null ) ? "" : $this->where;
    }

    private function getOrderByQuery() {

        return ( $this->orderBy == null ) ? "" : $this->orderBy;
    }

    private function getLimitQuery() {

        return ( $this->limit == null ) ? "" : $this->limit;
    }

    private function getFullQuery() {

        $query = $this->getSelectQuery();

        $query .= $this->getLeftJoinQuerys();
        
        $query .= $this->getWhereQuery();

        $query .= $this->getOrderByQuery();

        $query .= ( $this->has_many ) ? '' : $this->getLimitQuery();
        
        return $query . ";";
    }

    private function buildModelFromRow($row) {
        $model = new static();

        if(! isset($this)) {
            self::setData($row, $model);
            return $model;
        }

        if(count($this->with) < 1) {
            self::setData($row, $model);
            return $model;
        }

        $this->setDataFromLeftJoin($row, $model);

        return $model;
    }

    public static function find($id) {

        $model = new static();

        try {

            $prepare = self::$connection->prepare (
                "select * from " . $model->getTable() . " where " . $model->getPrimaryKey() . "='" . $id . "';"
            );

			$prepare->execute();

            $row = $prepare->fetchAll();

            if(count($row) == 0) return false;

            self::setData($row[0], $model);

			return $model;

        } catch(PDOException $e) {

            return false;
        }
    }

    public static function create(array $record) {
        
        $newRow = new static();

        $query = "insert into " . $newRow->getTable();

        $columns = "(";
        $values = "values(";

        $needCheck = true;
        if(! is_array($newRow->fillable)) $needCheck = false;

        foreach($record as $key => $value) {
            if($needCheck && ! in_array($key, $newRow->fillable)) continue;

            $columns .= $key . ",";
            $values .= "'" . $value . "',";
        }

        $columns = rtrim($columns , ',') . ")";
        $values = rtrim($values , ',') . ")";

        $query .= $columns . " " . $values . ";";
        
        try {
            
            $prepare = self::$connection->prepare($query);

            if(! $prepare->execute()) return false;

        } catch (PDOException $e) {
            
            return false;
        }
        
        /* select new record */
        $query = "select * from " . $newRow->getTable() . " ";

        if(array_key_exists($newRow->getPrimaryKey(), $record)) {
            
            $query .= "where " . ( $newRow->getPrimaryKey() . "='" . $record[$newRow->getPrimaryKey()] . "';" ); 
        } else {
            // if primarykey is auto_increment
            $query .= "order by " . $newRow->getPrimaryKey() . " DESC limit 1;";
        }
        
        try {

            $prepare = self::$connection->prepare($query);

            if(! $prepare->execute()) return false;

            $row = $prepare->fetch();

            if(! is_array($row)) return false;

            self::setData($row, $newRow);

        } catch (PDOException $e) {

            return false;
        }

        return $newRow;
    }

    public static function all() {

        $newRow = new static();
        try {
            
            $query = "select * from " . $newRow->getTable() . ";";
            
			$prepare = self::$connection->prepare ($query);

			$prepare->execute();

			return $prepare->fetchAll();

		} catch(PDOException $e) {

			return false;
		}
    }

    private function modelHasQueryToExecute() {
        return ( 
            $this->select != null || 
            $this->where != null || 
            $this->orderBy != null || 
            $this->limit != null ||
            count( $this->with ) > 0
        );
    }

    public function get() {

        if(! $this->modelHasQueryToExecute()) return $this->data;

        $query = $this->getFullQuery();
        
        try {
            
            $prepare = self::$connection->prepare($query);

            if(! $prepare->execute()) return false;

            if($this->has_many) {

                if( $this->limit != null ) {
                    
                    $data = $this->getLimitedModelFromMultiRecords($prepare, $this->limit_max, $this->limit_offset);

                    if(count($data) == 0) return false;

                    return $data;
                }

                return $this->getModelStructureFromOneToManyLeftJoinResult($prepare);
            }

            $rows = [];

            while($row = $prepare->fetch()) {

                $model = $this->buildModelFromRow($row);

                array_push($rows, $model);
            }

        } catch(PDOException $e) {

            return false;
        }

        return $rows;
    }

    public function first() {
        if(! $this->modelHasQueryToExecute()) return false;

        $query = $this->getFullQuery();

        if($this->limit == null && count($this->with) < 1) {
            $query = rtrim($query, ';') . 'limit 1;';
        }

        try {
            
            $prepare = self::$connection->prepare($query);

            if(! $prepare->execute()) return false;
            
            if(count($this->with) > 0) {

                $data = $this->getLimitedModelFromMultiRecords($prepare, 1);

                if(count($data) == 0) return false;

                return $data[0];
            }

            $row = $prepare->fetch();
            
            if(! is_array($row)) return false;

        } catch(PDOException $e) {

            return false;
        }

        return $this->buildModelFromRow($row);
    }

    public function delete($id = null) {

        if((isset($this))) {

            if($id != null) {
                throw new \InvalidArgumentException('delete method of instance don\'t has argument');
            }

            $id = $this->data[$this->getPrimaryKey()];
        }

        return static::deleteThis($id);
    }

    public function update(array $record) {

        if($this->data == null) return false;
        if(! array_key_exists($this->getPrimaryKey(), $this->data)) return false;

        if($this->fillable != null) {
            $temp = [];
            foreach($record as $key => $value) {

                if(in_array($key, $this->fillable)) $temp += [ $key => $value ];
            }
            $record = $temp;
        }

        $query = "update " . $this->getTable() . " set ";

        foreach($record as $key => $value) {

            $query .= $key . "='" . $value . "',";
        }

        $query = rtrim($query, ",") . " where " . $this->getPrimaryKey() . "='" . $this->data[$this->getPrimaryKey()] . "';";

        try {
            
            $prepare = self::$connection->prepare($query);
            
            if(! $prepare->execute()) return false;

        } catch(PDOException $e) {
            
            return false;
        }

        foreach($record as $key => $value) {
            
            if(! in_array($key, $this->hidden)) $this->data[$key] = $value;
        }

        return $this;
    }

    public function select(array $columns) {

        if(!(isset($this))) return static::selectAndReturnInstance($columns);

        if($this->select != null) {

            throw new ModelException('select method used in current chain');
        }

        $this->selects = $columns;

        $this->select = "select ";

        foreach($columns as $column) {

            $this->select .= $column . ",";
        }

        foreach(static::getIndexes() as $index) {
            if( ! in_array($index, $columns) ) {
                $this->select .= $this->getTable() . '.' . $index . " as " . "index_" . $this->getTable() . "_" . $index . ",";
            }
        }
        
        $this->select = rtrim($this->select , ',') . " from " . $this->getTable() . " ";

        return $this;
    }

    public function where(string $right, string $opereator, string $left = null) {

        if($left == null) { 
            $left = $opereator;
            $opereator = "=";
        }

        if(!(isset($this))) return static::whereAndReturnInstance($right, $opereator, $left);

        if($this->where == null) {

            $this->where = "where " . $this->getTable() . '.' . $right . $opereator . "'" . $left . "' ";
            return $this;
        }

        $this->where .= 'and ' . $this->getTable() . '.' . $right . $opereator . "'" . $left . "' ";
        
        return $this;
    }

    public function orWhere(string $right, string $opereator, string $left = null) {

        if($left == null) { 
            $left = $opereator;
            $opereator = "=";
        }

        if($this->where == null) {

            $this->where = "where " . $right . $opereator . "'" . $left . "' ";
            return $this;
        }

        $this->where .= 'or ' . $right . $opereator . "'" . $left . "' ";
        
        return $this;
    }

    public function orderBy(array $orderByInputs) {

        if(count($orderByInputs) == 1) {

            $orderByInputs[] = "ASC";
        }

        if(!(isset($this))) return static::orderByAndReturnInstance($orderByInputs);

        $this->orderBy = "order by " . $this->getTable() . '.' . $orderByInputs[0] . " " . $orderByInputs[1] . " ";

        return $this;
    }

    public function limit(int $max, $offset = null) {

        if($max < 1) {
            throw new \InvalidArgumentException("limit must be bigeer than 0");
        }

        if(!(isset($this))) return static::limitAndReturnInstance($max, $offset);

        $this->limit_max = $max;
        $this->limit_offset = $offset;

        $this->limit = "limit " . ($offset == null ? "" : $offset . " , ") . $max;

        return $this;
    }

    public function paginate(int $per_page) {

        if($per_page < 1) {
            throw new \InvalidArgumentException("count in page must be bigeer than 0");
        }

        // get count of rows :
        $current_page = 1;

        if(isset($_GET['page'])) {

            if(filter_var($_GET['page'], FILTER_VALIDATE_INT)) if($_GET['page'] > 0) $current_page = $_GET['page'];

        }

        if(isset($this)) {

            if($this->limit != null) {

                throw new ModelException('use paginate method with limit method');
            }

            $query = "select count(*) as count from " . $this->getTable() . " ";

            if($this->where != null) $query .= $this->where;

            try {

                $prepare = self::$connection->prepare ($query);
    
                $prepare->execute();
    
                $count = $prepare->fetchAll();
    
            } catch(PDOException $e) {
    
                return false;
            }

        } else {

            $count = static::selectAndReturnInstance(['count(*) as count'])->get();
        }

        if(! is_array($count)) return false;
        if(count($count) != 1) return false;
        if(isset($count[0]['count'])) $count = $count[0]['count'];

        $offset = ( $current_page - 1 ) * $per_page;
        if($offset > $count) return new Paginator([], $count, $per_page, $current_page);;

        if(!(isset($this))) return static::paginateAll($per_page, $offset, $current_page, $count);

        $rows = $this->limit($per_page, $offset)->get();

        return new Paginator($rows, $count, $per_page, $current_page);
    }

    public function __set($name, $value) {

        $this->data[$name] = $value;
    }

    public function __get($name) {

        if(! isset($this->data[$name])){

            if( method_exists($this, $name) ) {

                $reflection = new \ReflectionMethod($this, $name);

                if( 
                    $reflection->isPublic() && ! 
                    $reflection->isStatic() && 
                    $reflection->getDeclaringClass()->name != self::class
                ) {
                    return $this->$name();
                }
            }

            return false;
        }

        return $this->data[$name];
    }

    protected static function selectAndReturnInstance(array $columns) {
        
        $model = new static();

        $model->selects = $columns;

        $model->select = "select ";

        foreach($columns as $column) {
            $model->select .= $column . ",";
        }

        foreach(static::getIndexes() as $index) {
            if( ! in_array($index, $columns) ) {
                $model->select .= $model->getTable() . '.' . $index . " as " . "index_" . $model->getTable() . "_" . $index . ",";
            }
        }

        $model->select = rtrim($model->select , ',') . " from " . $model->getTable() . " ";

        return $model;
    }

    protected static function whereAndReturnInstance(string $right, string $opereator, string $left = null) {
        
        $model = new static();
        
        $model->where = "where " . $model->getTable() . '.' . $right . $opereator . "'" . $left . "' ";

        return $model;
    }

    protected static function orderByAndReturnInstance(array $orderByInputs) {

        $model = new static();

        $model->orderBy = "order by " . $model->getTable() . '.' . $orderByInputs[0] . " " . $orderByInputs[1] . " ";

        return $model;
    }

    protected function limitAndReturnInstance(int $max, int $offset = null) {
        $model = new static();

        $model->limit_max = $max;
        $model->limit_offset = $offset;

        $model->limit = "limit " . ($offset == null ? "" : $offset . " , ") . $max;

        return $model;
    }

    protected function paginateAll(int $per_page, $offset, $current_page, $count) {

        $rows = static::limitAndReturnInstance($per_page, $offset)->get();

        return new Paginator($rows, $count, $per_page, $current_page);
    }

    protected static function deleteThis($id) {
        
        $model = new static();

        try {

            $prepare = self::$connection->prepare (
                "delete from " . $model->getTable() . " where " . $model->getPrimaryKey() . "='" . $id . "';"
            );

            return $prepare->execute();

        } catch(PDOException $e) {

            return false;
        }
    }

    public function getIterator() {

        return new ArrayIterator($this->data);
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function jsonSerialize() {

        return $this->data;
    }

}