<?php

namespace App\Models;

use App\Models\Model;
use App\Models\User;

class Like extends Model {

    protected $table = 'likes';
    protected $primaryKey = 'id';

    public function user() {

        return $this->belongsTo(User::class, 'users', 'id', 'user_id');
    }

}