<?php
function checkIP(){
    $ip = explode(".",$_SERVER['REMOTE_ADDR']);
    if (!(($ip[0] == '::1') ||
        ($ip[0] == "127" && $ip[1] == "0" && $ip[2] == "0" && $ip[3] == "1") ||
        ($ip[0] == "148" && $ip[1] == "61") ||
        ($ip[0] == "35" && $ip[1] == "40") ||
        ($ip[0] == "207" && $ip[1] == "72" &&
            ($ip[2] >= 160 && $ip[2] <= 191)
            )
        ){
        die();
}
}
checkIP();
date_default_timezone_set('America/Detroit');




if (isset($_GET['roomId'])) {


    $today = new dateTime();
    $today = $today->format('Y-m-d');

	//neet to use HTTP authentication to get at API calls now
	//get the credentials from an external file
	require('authentication.php');

    	$url =  'http://gvsu.edu/reserve/files/cfc/functions.cfc?method=bookings&roomId='.$_GET['roomId'].'&startDate='.$today.'&endDate='.$today.'';
 	$ch = curl_init();

	//curl seems to be the only option on our server in which to negociate HTTP authentication in PHP

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	$result = curl_exec($ch);
	
	//parse result as XML.  there's a problem here where if there are no bookings, 
	//the API returns non-valid XML, in which case the next statement fails.
	//I'm not fixing this because it results in an open room on the display,
	//which is the desired behavior.
	$xml = new SimpleXMLElement($result);
	curl_close($ch);

    
    echo $xml;

    $sortable = array();
    foreach($xml->Data as $node) {
        $sortable[] = $node;
    }
    usort($sortable,'compareTime');

    foreach ($sortable as $reservation) {

        $timeStart = substr($reservation->TimeEventStart, strpos($reservation->TimeEventStart, "T") + 1);
        $timeEnd = substr($reservation->TimeEventEnd, strpos($reservation->TimeEventEnd, "T") + 1);

        $now = new dateTime();
        $now_format = $now->format('H:i:00');

        $hour_from_now = $now->add(new DateInterval('PT1H'));
        $hour_from_now = $hour_from_now->format('H:i:00');

        $reservationID = $reservation->ReservationID;

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
                "Status" => "reserved",
                "ReservationId" => $reservationID
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
                "Status" => "reserved_soon",
                "ReservationId" => $reservationID
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

function compareTime($a,$b){
    return strnatcmp($a->TimeEventStart, $b->TimeEventStart);
}
