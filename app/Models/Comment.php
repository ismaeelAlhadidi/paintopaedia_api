<?php

namespace App\Models;

use App\Models\Model;
use App\Models\User;
use App\Models\Post;
use App\Models\Reply;

class Comment extends Model {

    protected $table = 'comments';
    protected $primaryKey = 'id';

    public function user() {
        
        return $this->belongsTo(User::class, 'users', 'id', 'user_id');
    }

    public function post() {

        return $this->belongsTo(Post::class, 'posts', 'post_id', 'id');
    }

    public function replies() {
        
        return $this->hasMany(Reply::class, 'replies', 'comment_id', 'id');
    }

    public function likes() {

        return $this->morphTo(Like::class, 'likes', 'component_id', 'component_type', 'id');
    }
}