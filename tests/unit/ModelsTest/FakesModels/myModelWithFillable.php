<?php 

use App\Models\Model;

class myModelWithFillable extends myModel {

    protected $primarykey = "id";
    
    protected $fillable = [
        'id', 'first_name', 'last_name', 'username', 'phone'
    ];
}