<?php

namespace App\Helpers;

class TimeTransformer {

    public static function beforeHowMuch($date) {

        if(empty($date)) return false;

		$periods = array("just now", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths = array("60", "60", "24", "7", "4.35", "12", "10");
		$now = time();

		$unix_date = strtotime($date);
		if(empty($unix_date)) {   
			return false;
		}
		if($now > $unix_date) {   
			$difference = $now - $unix_date;
			$tense = "ago";
		   
		} else {
			return "just now";
			// $difference = $unix_date - $now;
			// $tense = "from now";
		}
	   
        if($difference <= 60) return $periods[0];

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}
	   
		$difference = round($difference);
	   
		if($difference != 1) {
			$periods[$j] .= "s";
		}
	   
		return ( $difference != 1 ? ( $difference . " " ) : "" )  . "$periods[$j] {$tense}";
    }
}