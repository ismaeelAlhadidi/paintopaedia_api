<?php

use App\Models\Model;

include_once ('User.php');
include_once ('Post.php');
include_once ('Like.php');

class Comment extends Model {

    protected $table = "comments_for_test_relationships";

    public function user() {

        return $this->belongsTo(User::class, 'users_for_test_relationships', 'id', 'user_id');
    }

    public function post() {

        return $this->belongsTo(Post::class, 'posts_for_test_relationships', 'id', 'post_id');
    }

    public function likes() {

        return $this->morphTo(Like::class, 'likes_for_test_relationships', 'component_id', 'component_type', 'id');
    }

}