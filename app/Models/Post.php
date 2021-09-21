<?php

namespace App\Models;

use App\Models\Model;
use App\Models\User;
use App\Models\Category;
use App\Models\Video;
use App\Models\Image;
use App\Models\Comment;

class Post extends Model {
    protected $table = 'posts';
    protected $primaryKey = 'id';


    public function user() {
        
        return $this->belongsTo(User::class, 'users', 'user_id', 'id');
    }

    public function category() {

        return $this->belongsTo(Category::class, 'categories', 'category_id', 'id');
    }

    public function video() {

        return $this->belongsTo(Video::class, 'videos', 'id', 'video_id');
    }

    public function images() {

        return $this->hasMany(Image::class, 'images', 'post_id', 'id');
    }

    public function comments() {
        
        return $this->hasMany(Comment::class, 'comments', 'post_id', 'id');
    }

    public function likes() {
        
        return $this->morphTo(Like::class, 'likes', 'component_id', 'component_type', 'id');
    }
}