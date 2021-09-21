<?php 

namespace App\Models;

use App\Authentecation\AuthModel;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\Like;
use App\Models\Notification;
use App\Models\Following;

class User extends AuthModel {

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $hidden = ['password'];

    protected static $handler = 'email';
    protected static $password = 'password';
    protected static $password_column_name = 'password';

    public function posts() {
        
        return $this->hasMany(Post::class, 'posts', 'user_id', 'id');
    }

    public function comments() {
        
        return $this->hasMany(Comment::class, 'comments', 'user_id', 'id');
    }

    public function replies() {
        
        return $this->hasMany(Reply::class, 'replies', 'user_id', 'id');
    }

    public function likes() {
        
        return $this->hasMany(Like::class, 'likes', 'user_id', 'id');
    }

    public function notifications() {

        return $this->hasMany(Notification::class, 'notifications', 'user_id', 'id');
    }

    public function followers() {

        return $this->hasMany(Following::class, 'followings', 'followed_id', 'id');
    }

    public function following() {

        return $this->hasMany(Following::class, 'followings', 'follower_id', 'id');
    }

}