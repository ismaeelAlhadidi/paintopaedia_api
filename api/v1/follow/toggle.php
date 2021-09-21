<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "POST") App\Config\Response::toJson([], 0, "", 404);

new App\Middlewares\Auth();

if(request()->get('user_id') == null) App\Config\Response::toJson([], 0, "request not valid", 200);

if(! filter_var(request()->get('user_id'), FILTER_VALIDATE_INT)) App\Config\Response::toJson([], 0, "request not valid", 200);

$user = App\Models\User::find(request()->get('user_id'));

if(! $user instanceof App\Models\User ) App\Config\Response::toJson([], 0, "request not valid", 200);

$follow = App\Models\Following::where('followed_id', request()->get('user_id'))->where('follower_id', auth()->id())->first();

if( ! $follow ) {

    $follow = App\Models\Following::create([
        'follower_id' => auth()->id(),
        'followed_id' => request()->get('user_id')
    ]);

    if( ! $follow ) App\Config\Response::toJson([], 0, "error in add following", 200);

    App\Config\Response::toJson([], true, "", 200);
}

if( ! $follow->delete() ) App\Config\Response::toJson([], 0, "error in remove following", 200);

App\Config\Response::toJson([], true, "", 200);