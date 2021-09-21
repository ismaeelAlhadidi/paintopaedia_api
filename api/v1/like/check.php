<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "POST") App\Config\Response::toJson([], 0, "", 404);

new App\Middlewares\Auth();

if(request()->get('component_id') == null) App\Config\Response::toJson([], 0, "request not valid", 200);

if(request()->get('component_type') == null) App\Config\Response::toJson([], 0, "request not valid", 200);

if(! in_array( request()->get('component_type'), LIKEABLE_COMPONENTS ) ) App\Config\Response::toJson([], 0, request()->get('component_type') . " not component", 200);

$Model = "App\\Models\\" . request()->get('component_type');

if(! filter_var(request()->get('component_id'), FILTER_VALIDATE_INT)) App\Config\Response::toJson([], 0, "request not valid", 200);

$component = $Model::find(request()->get('component_id'));

if( ! $component ) App\Config\Response::toJson([], 0, request()->get('component_type') . " not found", 200);

$like = App\Models\Like::where('component_id', request()->get('component_id'))
    ->where('component_type', request()->get('component_type'))->where('user_id', auth()->id())->first();

if( ! $like ) App\Config\Response::toJson(['like' => false], true, '', 200);

App\Config\Response::toJson(['like' => true], true, '', 200);