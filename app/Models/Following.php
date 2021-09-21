<?php

namespace App\Models;

use App\Models\Model;
use App\Models\User;

class Following extends Model {

    protected $table = 'followings';
    protected $primaryKey = 'id';


    public function follower() {

        return $this->belongsTo(User::class, 'users', 'id', 'follower_id');
    }

    public function followed() {

        return $this->belongsTo(User::class, 'users', 'id', 'followed_id');
    }

}