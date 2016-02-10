#!/usr/bin/env php

<?php




date_default_timezone_set('America/Detroit');

//list of rooms and their EMS identification codes
/*
    7678 / 003 - Media Prep Room
    7679 / 004 - Media Prep Room
    7680 / 005 - Media Prep Room
    7686 / 133 - Playback
    7687 / 134 - Presentation Practice
    7688 / 135 - Presentation Practice
    7689 / 202 - Conference Style
    7690 / 203 - Conference Style
    7801 / 204 - Lounge Style
    7691 / 205 - Conference Style
    7692 / 216 - Seminar Room
    7693 / 302 - Conference Style
    7694 / 303 - Lounge Style
    7695 / 304 - Conference Style
    7696 / 305 - Conference Style
    7698 / 404 - Conference Style
    7699 / 405 - Conference Style
    7681 / 030 - Multi-Purpose Room
*/


$roomIDs = array(
	
	 '7678' => '003', 
    '7679' => '004',
    '7680' => '005',
    '7686' => '133',
    '7687' => '134',
    '7688' => '135', 
    '7689' => '202', 
    '7690' => '203', 
    '7801' => '204', 
    '7691' => '205', 
    '7692' => '216', 
    '7693' => '302', 
    '7694' => '303', 
    '7695' => '304',
    '7696' => '305', 
    '7698' => '404', 
    '7699' => '405', 
    '7681' => '030', 
);

//begin constructing the XML file we will use to store the room data.
//displays will access the data from that file.
//we start by overwriting the file, if one is already there.
$XML_File = fopen("RoomReservationData.xml", "w");
$string = "<bookings>";

//now open it to append.
fwrite($XML_File, $string);
fclose($XML_File);
 $XML_File = fopen("RoomReservationData.xml", "a");

//the API requires that we reqyest data on each room as a separate URL.  So prepare to cycle throught he list of rooms, 
//requesting data for each one, and storing it in the XML file as it's retrieved.

foreach ($roomIDs as $EMSID => $roomNumber) {
	$today = new dateTime();
    $today = $today->format('Y-m-d');
	
	//need to use HTTP authentication to get at API calls now
	//get the credentials from an external file
	
	require('authentication.php');
	
	//construct the request URL
    $url = 'http://www.gvsu.edu/reserve/files/cfc/functions.cfc?method=bookings&roomId='. $EMSID.'&startDate='.$today.'&endDate='.$today.'';
 	
	$ch = curl_init();
	
	//curl seems to be the only option on our server in which to negociate HTTP authentication in PHP
	//which is a requirement of the API
	curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	$result = curl_exec($ch);
	
	//check the CURL request and make sure there's content.  If not, write curl errors to a file for debugging.
	if ($result) {
		
 		//parse result as XML.  there's a problem here where if there are no bookings,
        //the API returns non-valid XML, in which case the next statement fails.
	    //if that happens, I'm catching the error and logging it, then writing an empty 
	    //xml document and terminating the program.
        try {
        	$xml = new SimpleXMLElement($result);
            
        } catch (Exception $e) {
            $errlog = fopen("php_error_log.log", "a");
            $string = "XML error: " . $e->getMessage() . ":" . $result . $url . "\n";
            fwrite($errlog, $string);
            fclose($errlog);
            
			$string = "</bookings>";
			
			fwrite($XML_File, $string);
			fclose($XML_File);
			curl_close($ch);
			exit;
                        
        }
	} else {
		//if the curl request returns no data, log the error url and time and send an email
		//then close and delete the XML file so that the display does not show old data
		//then terminate the program
		$now = date('H:i:00');
		$errlog = fopen("php_error_log.log", "a");
        $string = "Curl Error: " . curl_error($ch) . ":" . $url . ": " . $now . "\n" ;
        fwrite($errlog, $string);
        fclose($errlog);
        mail("felkerk@gvsu.edu", "Room bookings error", $now . ": " .  $string);
        fclose($XML_File);
        unlink('RoomReservationData.xml');
        curl_close($ch);
        exit;
                
		}
		
	//did we make it this far?  Yay, we have valid XML bookings data!
	//let's start parsing it!
	curl_close($ch);
    $sortable = array();
    
    //get each booking from the results.  Each booking is enclosed in <data> tags
    
    foreach($xml->Data as $node) {
        $sortable[] = $node;
    }
    
    //sort them by time
    usort($sortable,'compareTime');
   
   	// loop through the bookings, extracting the information from each one we will need to construct the xml document  
   
   
    foreach ($sortable as $reservation) {
        
        $timeStart = substr($reservation->TimeEventStart, strpos($reservation->TimeEventStart, "T") + 1);
        $timeEnd = substr($reservation->TimeEventEnd, strpos($reservation->TimeEventEnd, "T") + 1);
        
        //we are potentially interested in two types of booking: those goign on now,
        //and those happening an hour from now.  We need two dates to use to identify those bookings.
        
        $now = date('H:i:00');
        
		$timestamp = time() + (60 * 60);
		
		$hour_from_now = date('H:i:00',$timestamp);
		
        $reservationID = $reservation->ReservationID;
        
     	//the structure here should ensure that when there is both a current and upcoming reservation,
     	//only the current reservation gets logged to the file.  We don't want to display a reservation an hour from now if there's
     	//someone in there now!
     	
     	//also, I log a lot more information to the XML file than we need, but that's to make troubleshooting easier 
     	//if there's a problem.  also, we neeed more data for the multipurpose room display, which has to show event name and times
     	//of the event if it's reserved.
        if (strtotime($now) > strtotime($timeStart) && strtotime($now) < strtotime($timeEnd)) {
        	
			$string = "<room>";
            
            $string .= '<roomcode>' . $reservation->RoomID . '</roomcode>';
            $string .= '<roomnumber>' . $reservation->RoomCode . '</roomnumber>';
            $string .= '<roomname>' . (string)$reservation->Room . '</roomname>';
            $string .= '<groupname>' . groupName((string)$reservation->GroupName, $timeEnd, $timeStart, $reservation) . '</groupname>';
            $string .=  '<timestart>' . $timeStart . '</timestart>';
            $string .= '<timeend>' . $timeEnd . '</timeend>';
            $string .=  '<now>' . $now . '</now>';
            $string .=  '<eventname>' . formatEventName((string)$reservation->EventName) . '</eventname>';
            $string .= '<status>reserved</status>';
        	$string .= '<reservationid>' . $reservationID . '</reservationid>';
            $string .= '</room>';    
                
            fwrite($XML_File, $string);
			
            break;
        } else if (strtotime($hour_from_now) > strtotime($timeStart) && strtotime($hour_from_now) < strtotime($timeEnd)) {
        
            
			$string = "<room>";
            $string .= '<roomcode>' . $reservation->RoomID . '</roomcode>';
            $string .= '<roomnumber>' . $reservation->RoomCode . '</roomnumber>';
            $string .= '<roomname>' . (string)$reservation->Room . '</roomname>';
            $string .= '<groupname>' . groupName((string)$reservation->GroupName, $timeEnd, $timeStart, $reservation) . '</groupname>';
            $string .=  '<timestart>' . $timeStart . '</timestart>';
            $string .= '<timeend>' . $timeEnd . '</timeend>';
            $string .=  '<now>' . $now . '</now>';
            $string .=  '<eventname>' . formatEventName((string)$reservation->EventName) . '</eventname>';
            $string .= '<status>reserved_soon</status>';
        	$string .= '<reservationid>' . $reservationID . '</reservationid>';
            $string .= '</room>';    
            $XML_File = fopen("RoomReservationData.xml", "a");
            fwrite($XML_File, $string);
			
            break;
            
        }
        
    }
}


$string = '</bookings>';

fwrite($XML_File, $string);
fclose($XML_File);

//this function extracts the groupname and does not show groupnames for bookings made by admins

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

//formats the event name.

function formatEventName($EventName) {
	//clean off the user name part of the string because it is a "security risk."  Whatever.
	if (strpos($EventName, "-") !== FALSE) {
		$start = strpos($EventName, "-") + 1;
		return substr($EventName, $start);
       
	} else {
       return $EventName;
   }
}
//compares time values for sorting bookings

function compareTime($a,$b){
    return strnatcmp($a->TimeEventStart, $b->TimeEventStart);
}
