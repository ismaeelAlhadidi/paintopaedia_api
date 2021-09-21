<?php

use App\Models\Model;

include_once ('Post.php');
include_once ('Comment.php');
include_once ('Like.php');

class User extends Model {

    protected $table = "users_for_test_relationships";
    
    public function posts() {
        return $this->hasMany(Post::class, 'posts_for_test_relationships', 'user_id', 'id');
    }

    public function comments() {
        return $this->hasMany(Comment::class, 'comments_for_test_relationships', 'user_id', 'id');
    }

    public function likes() {
        return $this->hasMany(Like::class, 'likes_for_test_relationships', 'user_id', 'id');
    }
}