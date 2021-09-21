<?php

use App\Models\Model;

include_once ('User.php');

class Like extends Model {

    protected $table = "likes_for_test_relationships";

    public function user() {

        return $this->belongsTo(User::class, 'users_for_test_relationships', 'id', 'user_id');
    }
}