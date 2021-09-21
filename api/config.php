<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');


include_once __DIR__ . "/../core/bootstrap.php";

define('KB', 1024);
define('MB', 1048576);
define('GB', 1073741824);
define('TB', 1099511627776);

define('MAX_NUMBER_OF_VIDEOS_IN_POST', 1);

define('MAX_NUMBER_OF_IMAGES_IN_POST', 6);

define('NOTIFICATIONS_COUNT_IN_ONE_FETCH', 9);

define('LIKEABLE_COMPONENTS', [
    'Post',
    'Comment',
    'Reply'
]); 

define('MAX_POSTS_IN_PAGE', 1099511627776);

define('API_URL', 'http://localhost/paintopaedia_api/api/v1');

define('DEFAULT_USER_PICTURE', 'http://localhost/paintopaedia_api/api/images/static/defualt_user_profile_pricture.png');


// add comment 
// add reply
// toggle like

function push_notification($user_id, $component_id, $component_type) {

    App\Models\Notification::create([
        'user_id' => $user_id,
        'component_id' => $component_id,
        'component_type' => $component_type,
        'readed' => false,
        'opened' => false
    ]);
}

function get_notification_message($user_name, $component_type) {

    switch($component_type) {
        case 'Comment':
            return $user_name . ' comment on your post';
        case 'Reply':
            return $user_name . ' reply to your comment';
        case 'Like':
            return $user_name . ' Like your post';
        default:
            return '';
    }
}

function get_notification_URL($component ,$component_type) {

    switch($component_type) {

        case 'Comment':

            return '#post=' . $component->post_id . '&comment=' . $component->id;

        case 'Reply':

            $comment = $component->comment;

            if( ! $comment ) return '';

            return '#post=' . $comment->post_id . '&comment=' . $comment->id . '&reply=' . $component->id;

        case 'Like':

            $ComponentModel = 'App\\Models\\' . $component->component_type;

            $inner_component = $ComponentModel::find($component->component_id);

            return get_notification_URL($inner_component, $component->component_type) . '&like=' . $component->id;

        default:

            return '';
    }

}

function sanitizeNotifications ($notifications) {

    $response_data = [];

    foreach($notifications as $notification) {

        $notification_data = [];

        $notification_data['id'] = $notification->id;

        $componentModel = "App\\Models\\" . $notification->component_type;

        $component = $componentModel::find($notification->component_id);

        if(! $component) continue;

        $user = $component->user;

        if( ! $user instanceof App\Models\User ) continue;

        $user_name = $user->first_name . ' ' . $user->last_name;

        $notification_data['content'] = get_notification_message($user_name, $notification->component_type);

        $notification_data['image'] = $user->profile_picture;

        if( ! is_string($notification_data['image']) ) $notification_data['image'] = DEFAULT_USER_PICTURE;

        $notification_data['time'] = App\Helpers\TimeTransformer::beforeHowMuch($component->created_at);

        $notification_data['readed'] = $notification->readed;

        $notification_data['url'] = get_notification_URL($component, $notification->component_type);

        array_push($response_data, $notification_data);
    }

    return $response_data;
}

function readNotifications($notifications) {

    foreach($notifications as $notification) {

        if($notification instanceof App\Models\Notification) $notification->update([ 'readed' => true ]);
    }
}
// for test placeholders
sleep(1);