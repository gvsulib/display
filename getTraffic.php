<?php

function postFeedback ($feedback, $con) {

    $sql = "INSERT INTO feedback_response (response_id, emotion_id) VALUES (NULL,$feedback)";
    if ($con->query($sql)) {
	    return true;
    } else {
	    return false;
    }
}

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
        die();
    }
}


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set("log_errors", 1);
ini_set("error_log", "/tmp/log.log");

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


function getTrafficData($con){
    
    $query = "SELECT space, level FROM traffic WHERE entryID = (select max(entryID) from entries);";
    $db_result = $con->query($query);
    if (!$db_result) {

        return $con->error;
    } 
    
    if ($db_result->num_rows == 0) {
        return "No Traffic Data found.";

    }

    while ($space = $db_result->fetch_row()) {
       $data[$space[0]] = $space[1];
    }

    return $data;
    
}

function getLastUpdatedTraffic($con){
    
    $query = "SELECT DATE_FORMAT(time,'%h:%i %p') FROM entries WHERE entryID = (select max(entryID) from entries);";
    $db_result = $con->query($query);

    if (!$db_result) {
        return $con->error;

    }

    if ($db_result->num_rows == 0) {
        return "Could not get time.";
    }

    $lastUpdated = $db_result->fetch_row();
    
    return $lastUpdated[0];
     
}


?>
