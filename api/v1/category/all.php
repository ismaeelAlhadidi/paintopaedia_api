<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "GET") App\Config\Response::toJson([], 0, "", 404);

$categories = App\Models\Category::all();

if( ! is_array( $categories ) ) App\Config\Response::toJson([], 0, "error on fetch categories", 200);

App\Config\Response::toJson($categories, true, "", 200);