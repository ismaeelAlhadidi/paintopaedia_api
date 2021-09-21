<?php

use App\Models\Model;

include_once ('Post.php');

class Video extends Model {

    protected $table = "videos_for_test_relationships";

    public function post() {
        
        return $this->belongsTo(Post::class, 'posts_for_test_relationships', 'id', 'video_id');
    }
}