<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "GET") App\Config\Response::toJson([], 0, "", 404);

if(request()->get('post_id') == null) App\Config\Response::toJson([], 0, "request not valid", 200);

$comments = App\Models\Comment::where('post_id', request()->get('post_id'))->get();

if(! is_array($comments) ) App\Config\Response::toJson([], 0, "error in fetch comments", 200);

$response_data = [];

foreach($comments as $comment) {

    $comment_data = [];

    $comment_data['id'] = $comment->id;
    
    $comment_data['content'] = $comment->content;

    $comment_data['time'] = App\Helpers\TimeTransformer::beforeHowMuch($comment->created_at);

    $comment_data['userId'] = $comment->user_id;

    $user = $comment->user;

    if($user instanceof App\Models\User) {

        $comment_data['userName'] = $user->first_name . ' ' . $user->last_name;

        $comment_data['userPicture'] = $user->profile_picture;

        if(! is_string($comment_data['userPicture'])) $comment_data['userPicture'] = DEFAULT_USER_PICTURE;
    }

    if(! isset($comment_data['userPicture'])) $comment_data['userPicture'] = DEFAULT_USER_PICTURE;

    $comment_data['liked'] = false;
    
    if(auth()->check()) {

        $like = App\Models\Like::where('user_id', auth()->id())
            ->where('component_id', $comment->id)
            ->where('component_type', 'Comment')->first();

        if( $like instanceof App\Models\Like ) $comment_data['liked'] = true;
    }

    $comment_data['likesCount'] = 0;

    $likes = $comment->likes;
    
    if(is_array($likes)) $comment_data['likesCount'] = count($likes);

    $comment_data['replies'] = [];

    $replies = $comment->replies;

    if(is_array($replies)) {

        foreach($replies as $reply) {

            $reply_data = [];

            $reply_data['id'] = $reply->id;

            $reply_data['content'] = $reply->content;

            $reply_data['time'] = App\Helpers\TimeTransformer::beforeHowMuch($reply->created_at);

            $reply_data['userId'] = $reply->user_id;

            $user = $reply->user;

            if($user instanceof App\Models\User) {

                $reply_data['userName'] = $user->first_name . ' ' . $user->last_name;

                $reply_data['userPicture'] = $user->profile_picture;

                if(! is_string($reply_data['userPicture'])) $reply_data['userPicture'] = DEFAULT_USER_PICTURE;
            }

            if(! isset($reply_data['userPicture'])) $reply_data['userPicture'] = DEFAULT_USER_PICTURE;

            $reply_data['liked'] = false;
    
            if(auth()->check()) {

                $like = App\Models\Like::where('user_id', auth()->id())
                    ->where('component_id', $reply->id)
                    ->where('component_type', 'Reply')->first();

                if( $like instanceof App\Models\Like ) $reply_data['liked'] = true;
            }

            $reply_data['likesCount'] = 0;

            $likes = $reply->likes;
            
            if(is_array($likes)) $reply_data['likesCount'] = count($likes);

            array_push($comment_data['replies'], $reply_data);
        }
    }

    array_push($response_data, $comment_data);
}

App\Config\Response::toJson($response_data, true, "", 200);