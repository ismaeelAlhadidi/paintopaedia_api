<?php

use App\Models\Model;

include_once ('User.php');
include_once ('Comment.php');
include_once ('Like.php');
include_once ('Video.php');

class Post extends Model {

    protected $table = "posts_for_test_relationships";

    public function user() {

        return $this->belongsTo(User::class, 'users_for_test_relationships', 'id', 'user_id');
    }

    public function video() {

        return $this->hasOne(Video::class, 'videos_for_test_relationships', 'video_id', 'id');
    }

    public function comments() {

        return $this->hasMany(Comment::class, 'comments_for_test_relationships', 'post_id', 'id');
    }

    public function likes() {

        return $this->morphTo(Like::class, 'likes_for_test_relationships', 'component_id', 'component_type', 'id');
    }

    public function getKeys() {
        
        return $this->keys;
    }
}