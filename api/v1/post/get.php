<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "GET") App\Config\Response::toJson([], 0, "", 404);

use App\Models\Post;
use App\Models\Video;
use App\Models\Like;
use App\Models\Category;

if( request()->get('post_id') != null && filter_var(request()->get('post_id'), FILTER_VALIDATE_INT) ) {

    $post = Post::find(request()->get('post_id'));

    if( ! $post ) {

        App\Config\Response::toJson([], 0, "error in fetch post, post may be deleted", 200);
    }

    $posts = [ $post ];

} else if( request()->get('user_id') != null && filter_var(request()->get('user_id'), FILTER_VALIDATE_INT) ) {

    $posts = Post::where('user_id', request()->get('user_id'))->orderBy(['created_at', 'DESC'])->paginate(MAX_POSTS_IN_PAGE);

} elseif( request()->get('category') != null ) {

    $category = Category::where('name', request()->get('category'))->first();

    if($category instanceof Category) $posts = Post::where('category_id', $category->id)->orderBy(['created_at', 'DESC'])->paginate(MAX_POSTS_IN_PAGE);

    else $posts = Post::orderBy(['created_at', 'DESC'])->paginate(MAX_POSTS_IN_PAGE);

} else $posts = Post::orderBy(['created_at', 'DESC'])->paginate(MAX_POSTS_IN_PAGE);



if(! $posts instanceof App\Helpers\Paginator) {

    if(request()->get('post_id') == null || ! is_array( $posts ) ) {

        App\Config\Response::toJson([], 0, "error in fetch posts", 200);
    }

} else $posts = $posts->items();

$response_data = [];

foreach($posts as $post) {

    $post_data = [];

    $post_data['postId'] = $post->id;

    $post_data['content'] = $post->description;

    $post_data['time'] = App\Helpers\TimeTransformer::beforeHowMuch($post->created_at);

    $post_data['images'] = [];
    $post_data['videos'] = [];
    $post_data['likesCount'] = 0;
    $post_data['isLiked'] = false;
    $post_data['commentsUrl'] = API_URL . '/comment/get.php?post_id=' . $post->id;
    $post_data['commentsCount'] = 0;


    $images = $post->images;

    if(is_array($images)) {

        foreach($images as $image) {

            array_push($post_data['images'], $image->src);      
        }
    }

    if(is_string($post->video_id)) {

        $video = $post->video;

        if($video instanceof Video) {

            if(is_string($video->dash_path)) {

                array_push($post_data['videos'], $video->dash_path);

            } else {

                array_push($post_data['videos'], $video->src);
            }
        }
    }

    $likes = $post->likes;
    
    if(is_array($likes)) $post_data['likesCount'] = count($likes);

    if(auth()->check()) {

        $like = Like::where('user_id', auth()->id())
            ->where('component_id', $post->id)
            ->where('component_type', 'Post')->first();

        if( $like instanceof Like ) $post_data['isLiked'] = true;
    }
    

    $comments = $post->comments;

    if(is_array($comments)) $post_data['commentsCount'] = count($comments);

    array_push($response_data, $post_data);
}

App\Config\Response::toJson($response_data, true, "", 200);