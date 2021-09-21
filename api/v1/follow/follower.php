<?php 

include_once __DIR__ . "/../../config.php";

if(request()->method() != "GET") App\Config\Response::toJson([], 0, "", 404);

new App\Middlewares\Auth();

if(request()->get('user_id') == null) App\Config\Response::toJson([], 0, "request not valid", 200);


if(! filter_var(request()->get('user_id'), FILTER_VALIDATE_INT)) App\Config\Response::toJson([], 0, "request not valid", 200);

$followers = App\Models\Following::where('followed_id', request()->get('user_id'))->get();

if(! is_array($followers) ) App\Config\Response::toJson([], 0, "error in fetch followers", 200);

$response_data = [];

foreach($followers as $follower) {

    $follower_data = [];

    $follower_data['id'] = $follower->id;

    $follower_data['userId'] = $follower->followed_id;

    $user = $follower->follower;

    $follower_data['followers'] = 0;

    $follower_data['following'] = false;

    if( $user instanceof App\Models\User ) {

        $follower_data['userName'] = $user->first_name . ' ' . $user->last_name;

        $follower_data['userPicture'] = $user->profile_picture;

        if(! is_string($follower_data['userPicture']) ) $follower_data['userPicture'] = DEFAULT_USER_PICTURE;

        $followers = $user->followers;

        if(is_array($followers)) $follower_data['followers'] = count($followers);
    }

    if(! isset($follower_data['userPicture']) ) $follower_data['userPicture'] = DEFAULT_USER_PICTURE;

    if(auth()->check()) {

        $follow = App\Models\Following::where('follower_id', auth()->id())->where('followed_id', $follower_data['userId'])->first();

        if( $follow instanceof App\Models\Following ) $follower_data['following'] = true;
    }

    array_push($response_data, $follower_data);
}

App\Config\Response::toJson($response_data, true, "", 200);