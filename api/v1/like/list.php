<?php 

include_once __DIR__ . "/../../config.php";

if(request()->method() != "GET") App\Config\Response::toJson([], 0, "", 404);

new App\Middlewares\Auth();

if(request()->get('component_id') == null) App\Config\Response::toJson([], 0, "request not valid", 200);

if(request()->get('component_type') == null) App\Config\Response::toJson([], 0, "request not valid", 200);

if(! in_array( request()->get('component_type'), LIKEABLE_COMPONENTS ) ) App\Config\Response::toJson([], 0, request()->get('component_type') . " not component", 200);

$Model = "App\\Models\\" . request()->get('component_type');

if(! filter_var(request()->get('component_id'), FILTER_VALIDATE_INT)) App\Config\Response::toJson([], 0, "request not valid", 200);

$component = $Model::find(request()->get('component_id'));

if( ! $component ) App\Config\Response::toJson([], 0, request()->get('component_type') . " not found", 200);

$likes = App\Models\Like::where('component_id', request()->get('component_id'))
    ->where('component_type', request()->get('component_type'))->get();

if(! is_array($likes) ) App\Config\Response::toJson([], 0, "error in fetch likes", 200);

$response_data = [];

foreach($likes as $like) {

    $like_data = [];

    $like_data['id'] = $like->id;

    $like_data['userId'] = $like->user_id;

    $user = $like->user;

    $like_data['followers'] = 0;

    $like_data['following'] = false;

    if( $user instanceof App\Models\User ) {

        $like_data['userName'] = $user->first_name . ' ' . $user->last_name;

        $like_data['userPicture'] = $user->profile_picture;

        if(! is_string($like_data['userPicture']) ) $like_data['userPicture'] = DEFAULT_USER_PICTURE;

        $followers = $user->followers;

        if(is_array($followers)) $like_data['followers'] = count($followers);
    }

    if(! isset($like_data['userPicture']) ) $like_data['userPicture'] = DEFAULT_USER_PICTURE;

    if(auth()->check()) {

        $follow = App\Models\Following::where('follower_id', auth()->id())->where('followed_id', $like_data['userId'])->first();

        if( $follow instanceof App\Models\Following ) $like_data['following'] = true;
    }

    array_push($response_data, $like_data);
}

App\Config\Response::toJson($response_data, true, "", 200);