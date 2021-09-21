<?php

namespace App\Requests;

use App\Models\Comment;

class AddReplyRequest extends RequestForm {


    protected $excepted_data = [
        /*'key' => 'required',*/
        'content' => 'required',
        'comment_id' => 'required'
    ];

    protected $sanitized_keys = [
        /*'excepted__data_key' => 'new_key',*/
    ];
 
    protected function validate() : bool {

        if(! is_string($this->request['content'])) return false;
        $this->request['content'] = filter_var($this->request['content'], FILTER_SANITIZE_STRING);
        if(strlen($this->request['content']) > 5000) return false;

        if(trim($this->request['content']) == '') return false;
        

        if(! filter_var($this->request['comment_id'], FILTER_VALIDATE_INT)) return false;

        $comment = Comment::find($this->request['comment_id']);

        if( ! $comment ) return false;
        
        $this->request['notification_user']= $comment->user_id;

        return true;
    }

    protected function authorized() : bool {

        if(! auth()) return false;

        if( ! auth()->check() ) return false;

        return true;
    }
}