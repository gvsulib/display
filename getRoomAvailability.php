<?php

date_default_timezone_set('America/Detroit');

if (isset($_GET['roomId'])) {

    $today = new dateTime();
    $today = $today->format('Y-m-d');

    $url = 'http://gvsu.edu/reserve/files/cfc/functions.cfc?method=bookings&roomId='.$_GET['roomId'].'&startDate='.$today.'&endDate='.$today.'';
    $xml = new SimpleXMLElement(file_get_contents($url));
    
    echo $xml;

    foreach ($xml->Data as $reservation) {

        $timeStart = substr($reservation->TimeEventStart, strpos($reservation->TimeEventStart, "T") + 1);
        $timeEnd = substr($reservation->TimeEventEnd, strpos($reservation->TimeEventEnd, "T") + 1);

        $now = new dateTime();
        $now_format = $now->format('H:i:00');

        $hour_from_now = $now->add(new DateInterval('PT1H'));
        $hour_from_now = $hour_from_now->format('H:i:00');

        /*
        echo strtotime($hour_from_now);
        echo '<br>' . strtotime($timeStart);
        echo '<br>' . strtotime($timeEnd);

        echo '<br>' . $hour_from_now;
        echo '<br>' . $timeStart;
        echo '<br>' . $timeEnd;

        echo '<br>' . strtotime($now_format);
        echo '<br>' . strtotime($timeStart);
        echo '<br>' . strtotime($timeEnd);
        */

        // Ignore GVSU API User
        //if ($reservation->GroupName != "GVSU-API User") {

            //
            if (strtotime($now_format) > strtotime($timeStart) && strtotime($now_format) < strtotime($timeEnd)) {
                $reservations = array(
                    "Room" => (string)$reservation->Room,
                    "GroupName" => groupName((string)$reservation->GroupName, $timeEnd, $timeStart, $reservation),
                    "TimeStart" => $timeStart,
                    "TimeEnd" => $timeEnd,
                    "Now" => $now_format,
                    "EventName" => formatEventName((string)$reservation->EventName),
                    "Status" => "reserved"
                );

                echo json_encode($reservations);
                break;

            } else if (strtotime($hour_from_now) > strtotime($timeStart) && strtotime($hour_from_now) < strtotime($timeEnd)) {

                $reservations = array(
                    "Room" => (string)$reservation->Room,
                    "GroupName" => groupName((string)$reservation->GroupName, $timeEnd, $timeStart, $reservation),
                    "TimeStart" => $timeStart,
                    "TimeEnd" => $timeEnd,
                    "Now" => $now_format,
                    "EventName" => formatEventName((string)$reservation->EventName),
                    "Status" => "reserved_soon"
                );

                echo json_encode($reservations);
                break;
            }

        //}

    }
}

function groupName($group_name, $timeEnd, $timeStart, $reservation) {
    if ($group_name == "wall mounted device scheduled") {
        //return "Local Reservation";
        return "";
    } else if ($group_name == "GVSU-API User") {
        return (string)$reservation->EventName;
    } else {
        return $group_name;
    }
}

function formatEventName($EventName) {
	//clean off the user name part of the string because it is a "security risk."  Whatever.
	if (strpos($EventName, "-") !== FALSE) {
		$start = strpos($EventName, "-") + 1;
		return substr($EventName, $start);
	
	} else {
	return $EventName;
	}

}