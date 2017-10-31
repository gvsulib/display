<?php

include 'php/connection.php';
include 'php/functions.php';
include 'php/authentication.php';

if (isset($_GET["room"])) {

    $room = $_GET["room"];
} else {
    $room = "7653";
}

$reservations = getReservationXML($username, $password, $room, false);

//var_dump($reservations);

if ($reservations) {
	$parsedReservations = parseReservationData($reservations);
} else {
	$parsedReservations = false;
}
$name = array(
    "7653" => "LIB 001 - Biblio Demo Lab",
    "7677"=>"LIB 002 - Learn Lab",
    "7678"=>"LIB 003 - Media Prep Room",
    "7679"=>"LIB 004 - Media Prep Room",
    "7680"=>"LIB 005 - Media Prep Room",
    "7681"=>"LIB 030 - Multi-Purpose Room",
    "7682"=>"LIB 040 - Exhibition Room",
    "7686"=>"LIB 133 - Playback",
    "7687"=>"LIB 134 - Presentation Practice",
    "7688"=>"LIB 135 - Presentation Practice",
    "7689"=>"LIB 202 - Conference Style",
    "7690"=>"LIB 203 - Conference Style",
    "7801"=>"LIB 204 - Lounge Style",
    "7691"=>"LIB 205 - Conference Style",
    "7692"=>"LIB 216 - Seminar Room",
    "7693"=>"LIB 302 - Conference Style",
    "7694"=>"LIB 303 - Lounge Style",
    "7695"=>"LIB 304 - Multi-purpose Room",
    "7696"=>"LIB 305 - Conference Style",
    "7698"=>"LIB 404 - Conference Style",
    "7699"=>"LIB 405 - Conference Style"


);


?>


<!DOCTYPE html>
<html>
<head>
	<title>Room Status</title>
	<link rel="stylesheet" type="text/css" href="css/reset.css">
	<link rel="stylesheet" type="text/css" href="css/select2.css">
	<link rel="stylesheet" type="text/css" href="css/events.css">
	
</head>
<body>
	<header>
		<form action="pick.php" method="GET">
		<select name="room" id='room-picker'>
			<option value="7653">LIB 001 - Biblio Demo Lab</option>
			<option value="7677">LIB 002 - Learn Lab</option>
			<option value="7678">LIB 003 - Media Prep Room</option>
		    <option value="7679">LIB 004 - Media Prep Room</option>
		    <option value="7680">LIB 005 - Media Prep Room</option>
		    <option value="7681">LIB 030 - Multi-Purpose Room</option>
		    <option value="7682">LIB 040 - Exhibition Room</option>
		    <option value="7686">LIB 133 - Playback</option>
		    <option value="7687">LIB 134 - Presentation Practice</option>
		    <option value="7688">LIB 135 - Presentation Practice</option>
		    <option value="7689">LIB 202 - Conference Style</option>
		    <option value="7690">LIB 203 - Conference Style</option>
		    <option value="7801">LIB 204 - Lounge Style</option>
		    <option value="7691">LIB 205 - Conference Style</option>
		    <option value="7692">LIB 216 - Seminar Room</option>
		    <option value="7693">LIB 302 - Conference Style</option>
		    <option value="7694">LIB 303 - Lounge Style</option>
		    <option value="7695">LIB 304 - Multi-purpose Room</option>
		    <option value="7696">LIB 305 - Conference Style</option>
		    <option value="7698">LIB 404 - Conference Style</option>
		    <option value="7699">LIB 405 - Conference Style</option>
        </select>
        <input type="Submit" value="submit">
        </form>
	</header>
    <h2 id="events-header">Today's Events in <?php echo $name[$room];  ?></h2>
    <h2 id="events-time-header"><?php echo date('F d');  ?></h2>
	<section id="events-container">
    <?php
    if ($parsedReservations) {
        echo '<ul>';
        foreach ($parsedReservations->event as $event) {
            echo '<li class="mpevents">' . formatDate($event->timestart) . " to " . formatDate($event->timeend) . " " . $event->eventname
         . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<ul>
                <li id="none">No Events Currently Scheduled</li>
            </ul>';
    }
    ?>
	</section>
	
		
	<script src="js/jquery-1.11.1.min.js"></script>
	<script src="js/moment.js"></script>
	
</body>
</html>