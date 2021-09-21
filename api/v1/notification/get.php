<?php

include_once __DIR__ . "/../../config.php";

if(request()->method() != "GET") App\Config\Response::toJson([], 0, "", 404);

new App\Middlewares\Auth();

$notifications = App\Models\Notification::where('user_id', auth()->id())->orderBy(['id', 'DESC'])->paginate(NOTIFICATIONS_COUNT_IN_ONE_FETCH);

if( ! $notifications instanceof App\Helpers\Paginator ) App\Config\Response::toJson([], 0, "error in fetch notifications", 200);

$notifications = $notifications->items();

if( ! is_array($notifications)) App\Config\Response::toJson([], 0, "error in fetch notifications", 200);

$response_data = sanitizeNotifications($notifications);

readNotifications($notifications);

App\Config\Response::toJson($response_data, true, "notifications fetched", 200);