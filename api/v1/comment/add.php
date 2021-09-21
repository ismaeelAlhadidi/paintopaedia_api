<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "POST") App\Config\Response::toJson([], 0, "", 404);

$add_comment_request = new App\Requests\AddCommentRequest(request()->all());

if($add_comment_request->fail()) {

    App\Config\Response::toJson([], 0, "request not valid", 200);
}

$comment_data = $add_comment_request->get();

$comment_data['user_id'] = auth()->id();

$notification_user = $comment_data['notification_user'];

unset($comment_data['notification_user']);

$comment = App\Models\Comment::create($comment_data);

if( ! $comment ) App\Config\Response::toJson( [], false, "add comment failed", 200);

if($notification_user != auth()->id()) push_notification($notification_user, $comment->id, 'Comment');

$response_data = $comment->get();

$response_data['time'] = App\Helpers\TimeTransformer::beforeHowMuch($response_data['created_at']);
unset($response_data['created_at']);

$response_data['userId'] = $response_data['user_id'];
unset($response_data['user_id']);

$response_data['userName'] = auth()->user()->first_name . ' ' . auth()->user()->last_name;

$response_data['userPicture'] = auth()->user()->profile_picture;

if(! $response_data['userPicture']) $response_data['userPicture'] = DEFAULT_USER_PICTURE;

App\Config\Response::toJson($response_data, true, "add comment succeed", 200);