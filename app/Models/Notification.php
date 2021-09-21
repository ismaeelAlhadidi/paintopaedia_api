<?php

namespace App\Models;

use App\Models\Model;
use App\Models\User;

class Notification extends Model {

    protected $table = 'notifications';
    protected $primaryKey = 'id';

    public function user() {

        return $this->belongsTo(User::class, 'users', 'user_id', 'id');
    }

    public function component() {

        // [ like / follow / comment / reply ]

        /*
            id : 
            content :
            image : of user who push the notification 
            time : of commponent
            url : ?? 
        */
    }
}