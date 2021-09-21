<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "POST") App\Config\Response::toJson([], 0, "", 404);

$add_reply_request = new App\Requests\AddReplyRequest(request()->all());

if($add_reply_request->fail()) {

    App\Config\Response::toJson([], 0, "request not valid", 200);
}

$reply_data = $add_reply_request->get();

$reply_data['user_id'] = auth()->id();

$notification_user = $reply_data['notification_user'];

unset($reply_data['notification_user']);

$reply = App\Models\Reply::create($reply_data);

if( ! $reply ) App\Config\Response::toJson( [], false, "add reply failed", 200);

if($notification_user != auth()->id()) push_notification($notification_user, $reply->id, 'Reply');

$response_data = $reply->get();

$response_data['time'] = App\Helpers\TimeTransformer::beforeHowMuch($response_data['created_at']);
unset($response_data['created_at']);

$response_data['userId'] = $response_data['user_id'];
unset($response_data['user_id']);

$response_data['userName'] = auth()->user()->first_name . ' ' . auth()->user()->last_name;

$response_data['userPicture'] = auth()->user()->profile_picture;

if(! $response_data['userPicture']) $response_data['userPicture'] = DEFAULT_USER_PICTURE;

App\Config\Response::toJson($response_data, true, "add reply succeed", 200);