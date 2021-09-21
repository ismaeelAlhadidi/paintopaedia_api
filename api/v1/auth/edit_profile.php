<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "POST") App\Config\Response::toJson([], 0, "", 404);

$edit_profile_request = new App\Requests\EditUserProfileRequest(request()->all());

if($edit_profile_request->fail()) {

    App\Config\Response::toJson([], 0, "request not valid", 200);
}

$user_data = $edit_profile_request->get();

$profile_picture = request()->file('profile_picture');

if( $profile_picture != null ) {

    if( request()->file('profile_picture')->fail() ) {

        App\Config\Response::toJson([], 0, "error in upload profile picture", 200);
    }

    $types = ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml'];

    $size = 5*MB;

    if( ! request()->file('profile_picture')->valid($types, $size, null) ) {

        $msg = " ";

        foreach($types as $type) $msg .= $type . ' ';

        App\Config\Response::toJson([], 0, "profile picture not valid please upload file of these types :" . $msg . "and size must be less than 5 MB", 200);
    }

    $profile_picture_path = request()->file('profile_picture')->store('images');

    if($profile_picture_path == null) {

        return App\Config\Response::toJson([], 0, "edit profile failed please try agin", 200);
    }

    $user_data['profile_picture'] = $profile_picture_path;
}

if( ! auth()->user()->update($user_data) ) return App\Config\Response::toJson([], 0, "edit profile failed please try agin", 200);

auth()->refresh();

$response = auth()->user()->get();

if(! isset($response['profile_picture']) || $response['profile_picture'] == null || ! is_string($response['profile_picture'])) {

    $response['profile_picture'] = DEFAULT_USER_PICTURE;
}

$response['jwt'] = auth()->get_token();

App\Config\Response::toJson($response, true, "edit profile successed", 200);