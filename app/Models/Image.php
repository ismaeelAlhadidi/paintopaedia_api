<?php

namespace App\Models;

use App\Models\Model;
use App\Models\Post;

class Image extends Model {

    protected $table = 'images';
    protected $primaryKey = 'id';

    public function post() {

        return $this->belongsTo(Post::class, 'posts', 'post_id', 'id');
    }

}