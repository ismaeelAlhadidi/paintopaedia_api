<?php

namespace App\Models;

use App\Models\Model;
use App\Models\User;
use App\Models\Comment;
use App\Models\Like;

class Reply extends Model {

    protected $table = 'replies';
    protected $primaryKey = 'id';

    public function user() {
        
        return $this->belongsTo(User::class, 'users', 'id', 'user_id');
    }

    public function comment() {

        return $this->belongsTo(Comment::class, 'comments', 'id', 'comment_id');
    }

    public function likes() {

        return $this->morphTo(Like::class, 'likes', 'component_id', 'component_type', 'id');
    }
}