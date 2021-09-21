<?php

namespace App\Requests;

use App\Models\Post;

class AddCommentRequest extends RequestForm {


    protected $excepted_data = [
        /*'key' => 'required',*/
        'content' => 'required',
        'post_id' => 'required'
    ];

    protected $sanitized_keys = [
        /*'excepted__data_key' => 'new_key',*/
    ];
 
    protected function validate() : bool {

        if(! is_string($this->request['content'])) return false;
        $this->request['content'] = filter_var($this->request['content'], FILTER_SANITIZE_STRING);
        if(strlen($this->request['content']) > 5000) return false;

        if(trim($this->request['content']) == '') return false;
        

        if(! filter_var($this->request['post_id'], FILTER_VALIDATE_INT)) return false;

        $post = Post::find($this->request['post_id']);

        if( ! $post ) return false;

        $this->request['notification_user'] = $post->user_id;
        
        return true;
    }

    protected function authorized() : bool {

        if(! auth()) return false;

        if( ! auth()->check() ) return false;

        return true;
    }
}