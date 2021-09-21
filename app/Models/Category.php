<?php

namespace App\Models;

use App\Models\Model;
use App\Models\Post;

class Category extends Model {
    protected $table = 'categories';
    protected $primaryKey = 'id';

    public function posts() {
        
        return $this->hasMany(Post::class, 'posts', 'category_id', 'id');
    }

}