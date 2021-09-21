<?php

include_once __DIR__ . "/../../config.php";

include_once __DIR__ . '/auth_sse_config.php';

$jwt = request()->get('st');

if( $jwt == null ) App\Config\Response::toJson([], 0, "Unauthorized", 401);

$user_id = get_user_id_from_sse_token($jwt);

if( ! $user_id ) App\Config\Response::toJson([], 0, "Unauthorized", 401);

use Hhxsv5\SSE\Event;
use Hhxsv5\SSE\SSE;
use Hhxsv5\SSE\StopSSEException;

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

$callback = function () {

    $id = mt_rand(1, 1000);

    global $user_id;

    $notifications = App\Models\Notification::where('user_id', $user_id)->where('readed', false)->orderBy(['id', 'DESC'])->get();

    if (! $notifications) {

        return false;
    }

    readNotifications($notifications);

    $notifications = sanitizeNotifications($notifications);

    $shouldStop = false;

    if ($shouldStop) {

        throw new StopSSEException();
    }

    return json_encode(compact('notifications'));
};

(new SSE(new Event($callback, 'notifications')))->start();