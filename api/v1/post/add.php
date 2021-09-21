<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "POST") App\Config\Response::toJson([], 0, "", 404);

$add_post_request = new App\Requests\AddPostRequest(request()->all());

if($add_post_request->fail()) {

    App\Config\Response::toJson([], 0, "request not valid", 200);
}

$images = request()->file('images');

$videos = request()->file('videos');

if($images == null && $videos == null) App\Config\Response::toJson([], 0, "plases upload images or videos with post", 200);

if( ! is_array($images) && ! is_array($videos) ) App\Config\Response::toJson([], 0, "plases upload images or videos with post", 200);

if(is_array($videos))if(count($videos) > MAX_NUMBER_OF_VIDEOS_IN_POST) App\Config\Response::toJson([], 0, MAX_NUMBER_OF_VIDEOS_IN_POST . " is max number of videos with one post !", 200);

if(is_array($images))if(count($images) > MAX_NUMBER_OF_IMAGES_IN_POST) App\Config\Response::toJson([], 0, MAX_NUMBER_OF_IMAGES_IN_POST . " is max number of images with one post !", 200);

$images_path = [];

$videos_path = [];

if($images != null) {

    if(! is_array($images)) $images = [ $images ];

    foreach($images as $image) {

        if( $image->fail() ) {

            App\Config\File::delete($images_path);

            App\Config\Response::toJson([], 0, "error in upload images", 200);
        }

        $types = ['image/gif', 'image/jpeg', 'image/png', 'image/svg+xml'];

        $size = 5*MB;

        if( ! $image->valid($types, $size, null) ) {

            App\Config\File::delete($images_path);

            $msg = " ";

            foreach($types as $type) $msg .= $type . ' ';

            App\Config\Response::toJson([], 0, "images not valid please upload file of these types :" . $msg . "and size must be less than 5 MB", 200);
        }

        $image_path = $image->store('images');

        if($image_path == null) {

            App\Config\File::delete($images_path);

            App\Config\Response::toJson([], 0, "store images failed please try agin", 200);
        }

        array_push($images_path, $image_path);
    }
}

if($videos != null) {

    if(! is_array($videos)) $videos = [ $videos ];

    foreach($videos as $video) {

        if( $video->fail() ) {

            App\Config\File::delete($videos_path);
            App\Config\File::delete($images_path);

            App\Config\Response::toJson([], 0, "error in upload videos", 200);
        }

        $types = ['video/x-flv', 'video/mp4', 'video/MP2T', 'video/3gpp', 'video/quicktime'];

        $size = 20*MB;

        if( ! $video->valid($types, $size, null) ) {

            App\Config\File::delete($videos_path);
            App\Config\File::delete($images_path);

            $msg = " ";

            foreach($types as $type) $msg .= $type . ' ';

            App\Config\Response::toJson([], 0, "videos not valid please upload file of these types :" . $msg . "and size must be less than [ 20 MB ]", 200);
        }

        $video_path = $video->store('videos');

        if($video_path == null) {

            App\Config\File::delete($videos_path);
            App\Config\File::delete($images_path);

            App\Config\Response::toJson([], 0, "store videos failed please try agin", 200);
        }

        array_push($videos_path, $video_path);
    }
}

$post_data = $add_post_request->get();

$category = App\Models\Category::where('name', $post_data['category_name'])->first();

if( ! $category ) {

    App\Config\File::delete($videos_path);
    App\Config\File::delete($images_path);

    App\Config\Response::toJson([], 0, "request not valid we dont't have this category", 200);
}

$post_data['category_id'] = $category->id;

unset($post_data['category_name']);

$post_data['user_id'] = auth()->id();

$post_data['images_count'] = count($images_path);

$video = null;

if(count($videos_path) == 1) {

    $video = App\Models\Video::create(['src' => $videos_path[0]]);

    if(! $video ) {

        App\Config\File::delete($videos_path);
        App\Config\File::delete($images_path);

        App\Config\Response::toJson([], 0, "error in create video please try agin !!", 200);
    }

    $post_data['video_id'] = $video->id;
}

$post = App\Models\Post::create($post_data);

if( ! $post ) {

    App\Config\File::delete($videos_path);
    App\Config\File::delete($images_path);

    if($video != null) $video->delete();

    App\Config\Response::toJson([], 0, "error in create post please try agin !!", 200);
}

foreach($images_path as $image_path) {
    
    $image = App\Models\Image::create(['src' => $image_path, 'post_id' => $post->id]);

    if( ! $image ) {

        App\Config\File::delete($videos_path);
        App\Config\File::delete($images_path);

        if($video != null) $video->delete();

        $post->delete();

        App\Config\Response::toJson([], 0, "error in create images please try agin !!", 200);
    }

}

$new_post_data = $post->get();

unset($new_post_data['user_id']);
unset($new_post_data['category_id']);
unset($new_post_data['video_id']);
unset($new_post_data['images_count']);

$new_post_data['content'] = $new_post_data['description'];
unset($new_post_data['description']);

$new_post_data['time'] = App\Helpers\TimeTransformer::beforeHowMuch($new_post_data['created_at']);
unset($new_post_data['created_at']);

$new_post_data['images'] = $images_path;

$new_post_data['videos'] = $videos_path;

$new_post_data['commentsUrl'] = API_URL . '/comment/get.php?post_id=' . $post->id;

App\Config\Response::toJson( $new_post_data, true, "add post succeed", 200 );