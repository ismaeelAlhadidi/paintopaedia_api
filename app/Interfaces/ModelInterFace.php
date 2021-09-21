<?php 

namespace App\Interfaces;

interface ModelInterFace {
    
    public static function all();

    public static function find($id);

    public static function create(array $record);

    public function get();

    public function first();

    public function delete();

    public function update(array $record);

    public function paginate(int $count);

    public function select(array $columns);
    
    public function where(string $right, string $opereator, string $left = null);

    public function orWhere(string $right, string $opereator, string $left);

    public function orderBy(array $columns);

    public function limit(int $max);

}