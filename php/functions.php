<?php
date_default_timezone_set ( "America/Detroit" );

include "apiKey.php";
//EMOJI FUNCTIONS

function postFeedback ($feedback) {
	

	$feedBackLevel = "feedBackLevel=$feedback";

	$curl = curl_init("https://prod.library.gvsu.edu/trafficapi/feedback/");

	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($curl, CURLOPT_USERPWD, ":$apiKey");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $feedBackLevel);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HEADER  , true);  // we want headers
	

	curl_exec($curl);

	$httpcode =  (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

	curl_close ($curl);
	

    if ($httpcode == 200) {
	    return true;
    } else {
	    return "api returned error code: " . $httpcode;
    }
}


//CHECKS IP ADDRESS

function checkIP(){
    $db = getConnection();
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $db->query("INSERT INTO `access_log`
            (accessid, system, ip, useragent, timestamp) VALUES
            (NULL, 'display', '$ip', '$ua', SYSDATE())");
    $ip = explode(".",$ip);
    if (!(
        ($ip[0] == "::1") ||
        ($ip[0] == "127" && $ip[1] == "0" && $ip[2] == "0" && $ip[3] == "1") ||
        ($ip[0] == "148" && $ip[1] == "61") ||
        ($ip[0] == "35" && $ip[1] == "40") ||
        ($ip[0] == "207" && $ip[1] == "72" &&
            ($ip[2] >= 160 && $ip[2] <= 191)
        ))

    ){
        return false;
    } else {
		return true;
	}
}

//TRAFFIC DATA

/*

spaces and codes:

Atrium
1 = "Living Room"
2 = "Multi-Purpose Room"
3 = "Exhibition Room"
4 = "Tables Under Stairs"
5 = "Seating Outside 001 and 002"

First Floor
6 = "Knowledge Market"
7 = "Cafe Seating"

Second Floor
8 = "West Wing (Collaborative Space)"
9 = "East Wing (Quiet Space)"

Third Floor
10 => "West Wing (Collaborative Space)"
11 => "East Wing (Quiet Space)"
12 => "Reading Room"
13 => "Innovation Zone"

Fourth Floor
14 => "West Wing (Collaborative Space)"
15 => "East Wing (Quiet Space)"
16 => "Reading Room"
*/

function getLastUpdatedTraffic() {
	//get the entire list of entries.  Someday I'll add a fucntion to the API so that you can request only the most current
	$entriesJSON = file_get_contents('https://prod.library.gvsu.edu/trafficapi/entries');

	if ($entriesJSON === false) {
		return "Unable to get traffic entry data.";
	}

	$entries = json_decode($entriesJSON, TRUE);

	if (is_null($entries)) {
		return "Unable to parse JSON entry data.";
	}

	//the first entry should be the most recent, pull the timestamp from there

	$timestamp = $entries[0]["time"];

	$unixTimeStamp = strtotime($timestamp);

	//get rid of the huge list of entries, we don't need it anymore and we don't want it cluttering up memory
	unset($entriesJSON);

	unset($entries);

	return $unixTimeStamp;

	



}

//Get the current traffic data
function getTrafficData(){

	//get the entire list of entries.  Someday I'll add a fucntion to the API so that you can request only the most current
	$entriesJSON = file_get_contents('https://prod.library.gvsu.edu/trafficapi/entries');

	if ($entriesJSON === false) {
		return "Unable to get traffic entry data.";
	}

	$entries = json_decode($entriesJSON, TRUE);

	if (is_null($entries)) {
		return "Unable to parse JSON entry data.";
	}


	//the first entry should be the most recent
	$entrynum = $entries[0]["entryID"];

	//get rid of the huge list of entries, we don't need it anymore and we don't want it cluttering up memory
	unset($entriesJSON);

	unset($entries);

	$trafficData = file_get_contents("https://prod.library.gvsu.edu/trafficapi/entries/$entrynum/traffic");

	if ($trafficData === false) {
		return "Unable to get traffic data.";
	}

	$trafficArray = json_decode($trafficData, TRUE);

	if (is_null($trafficArray)) {
		return "Unable to parse JSON traffic data.";
	}
	

	return $trafficArray;
    
     
}

function getRoomTrafficByDatabaseID($id){
	//get the entire list of entries.  Someday I'll add a fucntion to the API so that you can request only the most current
	

	$trafficData = file_get_contents("https://prod.library.gvsu.edu/trafficapi/spaces/$id/traffic");

	if ($trafficData === false) {
		return "Unable to get traffic data.";
	}

	$trafficArray = json_decode($trafficData, TRUE);

	if (is_null($trafficArray)) {
		return "Unable to parse JSON traffic data.";
	}

	//most recent entry should be first

	$traffic = $trafficArray[0];

	return $traffic["level"];

	
}


//ROOM RESERVATION DATA

//checks the last updated date on the room reservation data, if it's more than an hour old, 
//or if it's missing or unreadable, return false.  otherwise, return true.
 
function checkRoomReservationData() {
	date_default_timezone_set('America/Detroit');
	$XML_File = fopen("RoomReservationData.xml", "r");
	if (!$XML_File) {
		return false;
		fclose($XML_File);
	}



	if (filesize("RoomReservationData.xml") == 0 ) {
		fclose($XML_File);
		return false;
	}
	
	$rawXML = fread($XML_File, filesize("RoomReservationData.xml"));
	try {
		$xml = new SimpleXMLElement($rawXML);
			
	} catch (Exception $e) {
				//if the XML in the file can't be parsed, return false
		fclose($XML_File);
		return false;
						
	}
	
	//okay, now extract the timestamp from the file.
	$timestamp = (int) $xml->timestamp;

	//see if the timestamp on the file is older than thirty minutes
	$now = time();

	$diff = round(($now - $timestamp) / 60);

	if ($diff >= 30) {
		fclose($XML_File);
		return false;

	} else {
		fclose($XML_File);
		return true;
	}


	
}



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
	7653 / lab 01
	7677 / lab 02
	7700 / 440 - Meeting Room
	
*/

// Generate an array of room IDs


//uber-function that gets new room reservation data, and packs it into a file (RoomReservationData.xml)

function refreshRoomData($username, $password) {
	
	$roomIDs = array(
		
		'7700',
		'7653',
		'7677',
		'7678', 
		'7679',
		'7680',
		'7686',
		'7687',
		'7688', 
		'7689', 
		'7690', 
		'7801', 
		'7691', 
		'7692', 
		'7693', 
		'7694', 
		'7695',
		'7696', 
		'7698', 
		'7699', 
		'7681' 
	);


	$nowdisplay = date('h:s a');  
	$timestamp = strtotime(date('Y-m-d\TH:i:s')); 
	
	$formattedTimeStamp = date("Fj-Y-g:ia");   
	$outPut = new SimpleXMLElement("<bookings><timedisplay>" . $nowdisplay . "</timedisplay><timestamp>" . $timestamp . "</timestamp></bookings>");
	$rawData = "";
	//the API requires that we request data on each room as a separate URL.  So prepare to cycle through the list of rooms, 
	//requesting data for each one, and storing it in the XML file as it's retrieved.

	foreach ($roomIDs as $roomNumber) {
		$week = false;
		//get the raw reservation data for today from for the room
		$reservationXML = getReservationXML($username, $password, $roomNumber, $week);

		
		
		if ($reservationXML) {
			//clean up and sort the raw reservation data 
			$parsedXML = parseReservationData($reservationXML);
			$rawData = $rawData . "\n\nfor room: " . $roomNumber . "\n\n" . $reservationXML->asXML();
		} else {
			//if there's no parseable XML, skip to the next room
			continue;
		}
		
		//we are potentially interested in two types of booking: those goign on now,
		//and those happening an hour from now.  We need two dates to use to identify those bookings.


		//get the top of the current hour as a unix timestamp.  I've found through trial and error that 
		//strtotime translates differently formatted strings for the same time into wildly different timestamps, so the formatting of the string
		//here has to match the formatting of the times in the xml files I want to examine
		$now = strtotime(date('Y-m-d\TH:00:00'));
		
		$hour_from_now = $now + (60 * 60);
		
		// loop through the bookings, extracting the information from each one we will need to construct the xml document  
	
	
		foreach ($parsedXML->event as $event) {
			
			$timeStart = $event->timestart;
			$timeEnd = $event->timeend;

			//the structure here should ensure that when there is both a current and upcoming reservation,
			//only the current reservation gets logged to the file.  We don't want to display a reservation an hour from now if there's
			//someone in there now!
			
			//also, I log a lot more information to the XML file than we need, but that's to make troubleshooting easier 
			//if there's a problem.  We need more data for the multipurpose room display, which has to show event name and times
			//of the event if it's reserved.
			if ($now >= strtotime($timeStart) && $now < strtotime($timeEnd)) {
				//simpleXML is anything but simple to work with if you're constructing an XML object.
				//in order to get it to escape characters properly and create the correct document structure,
				//this is the bizarre syntax I have to use.  Took me hours to work this out, and the documentation is NOT HELPFUL.
				$room = $outPut->addChild('room');
				
				$room->roomcode = $event->roomcode;
				
				$room->roomnumber = $event->roomnumber;
				
				$room->roomname = $event->roomname;
				
				$room->groupname = $event->groupname;
				
				$room->timestart = $timeStart;
				
				$room->timeend = $timeEnd;
				
				$room->eventname = $event->eventname;
				
				$room->status = "reserved";
				
				
				//stop checking reservations for the current room if we find one happening now
				break;
					
			} else if ($hour_from_now == strtotime($timeStart)) {
					
				$room = $outPut->addChild('room');
				
				$room->roomcode = $event->roomcode;
				
				$room->roomnumber = $event->roomnumber;
				
				$room->roomname = $event->roomname;
				
				$room->groupname = $event->groupname;
				
				$room->timestart = $timeStart;
				
				$room->timeend = $timeEnd;
				
				$room->eventname = $event->eventname;
				
				$room->status = "reserved_soon";
				
				
				//stop checking reservations from the current room if we find a future one
				break;
				
			}
			
		}
	}

	//log the final output
	/*
	$finalXMLLog= fopen($finalXMLContent, "a");
	fwrite($finalXMLLog, $outPut->asXML());
	fclose($finalXMLLog);*/
	//begin constructing the XML file we will use to store the room data.
	//displays will access the data from that file.
	//we start by overwriting the file, if one is already there.
	$XML_File = fopen("RoomReservationData.xml", "w");
	//$LOG_File = fopen("logs/roomsRes" . $formattedTimeStamp, "w");
	//flock($LOG_File, LOCK_EX);
	flock($XML_File, LOCK_EX);
	//fwrite($LOG_File, $rawData);
	fwrite($XML_File, $outPut->asXML());
	fclose($XML_File);
	//fclose($LOG_File);
	//echo $outPut->asXML(); //echo contents of file for debugging purposes.
	return true;
}

//this function is used to retrieve xml reservation data.
function getReservationXML($username, $password, $EMSID, $week) {
	if ($week) {
		$today = new dateTime();
		$plusSeven = new dateTime();
		$plusSeven->modify('+7 days');
		$start = $today->format('Y-m-d');
		$end = $plusSeven->format('Y-m-d');

	} else {
		$today = new dateTime();
		$start = $today->format('Y-m-d');
		$end = $today->format('Y-m-d');
	}

	
	$ch = curl_init();
	$url = 'https://www.gvsu.edu/reserve/files/cfc/functions.cfc?method=bookings&roomId='. $EMSID.'&startDate='.$start.'&endDate='.$end.'';
	$reqlog = fopen("logs/reservationRequest.log", "a");
    $string = $url . "\n";
    fwrite($reqlog, $string);
    fclose($reqlog);
            

    //curl seems to be the only option on our server in which to negociate HTTP authentication in PHP
    //which is a requirement of the API
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $result = curl_exec($ch);

    //check the CURL request and make sure there's content.  If not, write curl errors to a file for debugging.
    if ($result) {
        $now = date('H:i:00 F-d-Y');
        //$rawXMLLogname = "logs/rawXML" . $now . ".xml";
        //$finalXMLContent = "logs/outputXML" . $now . ".xml";

        //logging raw xml for debugging
        /*$rawXMLLog = fopen($rawXMLLogname, "a");
        fwrite($rawXMLLog, $result);
        fclose($rawXMLLog);*/
        //parse result as XML. If the API is returning non-parseable XML, log that in the error log.
        try {
            $xml = new SimpleXMLElement($result);

        } catch (Exception $e) {
            $errlog = fopen("php_error_log.log", "a");
            $string = "XML error: " . $e->getMessage() . ":" . $result . $url . "\n";
            fwrite($errlog, $string);
            fclose($errlog);
            curl_close($ch);
            return false;

        }
    } else {
      	//if the curl request returns no data, log the error url and time
        //for right now, I'm returning an empty bookings list for rooms that return errors.
        $now = date('H:i:00 F-d-Y');
        $errlog = fopen("php_error_log.log", "a");
        $string = "Curl Error: " . curl_error($ch) . ":" . $url . ": " . $now . "\n" ;
        fwrite($errlog, $string);
        fclose($errlog);
        return false;

    }

    //did we make it this far?  Yay, we have valid XML bookings data!
    //let's start parsing it!
	curl_close($ch);
	if (@count($xml->children()) == 0) {return false;}
	return $xml;
}

//cleans and pardses the raw XML data-it has a lot more information than we actually need.
//also sorts it by date, so we get a nice clean easy to work with xml object.
function parseReservationData($xml) {
	$nowdisplay = date('h:s a');
	

	$outPut = new SimpleXMLElement("<bookings><timestamp>" . $nowdisplay . "</timestamp></bookings>");
	foreach($xml->Data as $node) {
		$sortable[] = $node;
	}
	
	//sort them by time
	usort($sortable,'compareTime');

	//simpleXML is anything but simple to work with if you're constructing an XML object.
    //in order to get it to escape characters properly and creete the correct document structure,
	//this is the bizarre syntax I have to use.  Took me hours to work this out, and the documentation is NOT HELPFUL.
	foreach($sortable as $reservation) {
		if ($reservation->EventTypeDescription == "Private Use") {
			continue;
		}
		//screen out cancelled events
		if ($reservation->StatusID == "1005") {
			continue;
		}

    	$event = $outPut->addChild('event');
                
    	$event->roomcode = $reservation->RoomID;
                        
    	$event->roomnumber = $reservation->RoomCode;
                        
    	$event->roomname = (string)$reservation->Room;
            
		$event->groupname = groupName((string)$reservation->GroupName, $reservation);
            
    	$event->timestart = (string) $reservation->TimeEventStart;
                        
    	$event->timeend = (string) $reservation->TimeEventEnd;
                        
    	$event->eventname = formatEventName((string) $reservation->EventName);
                        
    	//$event->reservationid = $reservation->$ReservationID;
	}
return $outPut;
}

//FORMATTING FUNCTIONS

//reformats the date string used in the reservations for display
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('g:i A', $timestamp);



}

//this function extracts the groupname and does not show groupnames for bookings made by admins

function groupName($group_name, $reservation) {
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
	//clean off the user name part of the string because it is a "security risk."
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

function formatGroupName($group_name) {
    if ($group_name == "wall mounted device scheduled") {
        //return "Local Reservation";
        return "";
    } else if ($group_name == "GVSU-API User") {
        return (string)$reservation->EventName;
    } else {
        return $group_name;
    }
}

?>