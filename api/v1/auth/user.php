<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "GET") App\Config\Response::toJson([], 0, "", 404);

if(request()->get('user_id') == null && ! auth()->check()) App\Config\Response::toJson([], 0, "request not valid", 200);

if(request()->get('user_id') != null && ! filter_var(request()->get('user_id'), FILTER_VALIDATE_INT)) App\Config\Response::toJson([], 0, "request not valid", 200);

if(auth()->check()) $user_id = auth()->id();

if(request()->get('user_id') != null) $user_id = request()->get('user_id');

$user = App\Models\User::find($user_id);

if( ! $user instanceof App\Models\User ) App\Config\Response::toJson([], 0, "user not found", 200);

$response_data = [];

$response_data['first_name'] = $user->first_name;
$response_data['last_name'] = $user->last_name;
$response_data['profile_picture'] = $user->profile_picture;
$response_data['posts'] = 0;
$response_data['followers'] = 0;
$response_data['following'] = 0;
$response_data['follow'] = false;

if(! is_string($response_data['profile_picture'])) $response_data['profile_picture'] = DEFAULT_USER_PICTURE;

$posts = $user->posts;

if(is_array($posts)) $response_data['posts'] = count($posts);

$followers = $user->followers;

if(is_array($followers)) $response_data['followers'] = count($followers);

$following = $user->following;

if(is_array($following)) $response_data['following'] = count($following);

if(auth()->check() && request()->get('user_id') != null) {
    
    $follow = App\Models\Following::where('follower_id', auth()->id())->where('followed_id', request()->get('user_id'))->first();
    
    if($follow instanceof App\Models\Following) $response_data['follow'] = true;
}

App\Config\Response::toJson($response_data, true, "", 200);