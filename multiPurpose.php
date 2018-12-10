<?php
date_default_timezone_set('America/Detroit');

include 'php/connection.php';
include 'php/functions.php';
include 'php/authentication.php';

$reservations = getReservationXML($username, $password, "7681", false);

//var_dump($reservations);

if ($reservations) {
	$parsedReservations = parseReservationData($reservations);
} else {
	$parsedReservations = false;
}
/*
$xmlFile = fopen("xml.xml", "w");
fwrite($xmlFile, $parsedReservations->asXML());
fclose($xmlFile);

$xmlFile = fopen("xml.xml", "r");

$rawXML = fread($xmlFile, filesize("xml.xml"));
$parsedReservations = new SimpleXMLElement($rawXML);

fclose($xmlFile);
*/

//check to see if room is currently reserved
$inUse = false;

if ($parsedReservations) {
	foreach ($parsedReservations->event as $event) {
		if (time() >= strtotime($event->timestart) && time() <= strtotime($event->timeend)) {
			$inUse = true;
			$eventName = $event->eventname;
			$timeStart = formatDate($event->timestart);
			$timeEnd = formatDate($event->timeend);

		}
	}

} 
//if it isn't currently reserved, get the current traffic level and map that to a color
if (!$inUse) {
	$level = getRoomTrafficByDatabaseID("2");
	switch ($level) {
        case '4':
        $level = "high";
        break;
        case '-1':
        $level = "event";
        break;
        case '3':
        $level = "high";
        break;
        case '2':
        $level = "medium";
        break;
        case '1':
        $level = "low";
        break;
        case '0':
        $level = "empty";
        break;
        
    }
}


?>
<html>
<head>
	<title>Multi-purpose Room Status</title>
	<link rel="stylesheet" type="text/css" href="css/reset.css">
	<link rel="stylesheet" type="text/css" href="css/events.css">
	<meta http-equiv="refresh" content="900">
</head>
<body>

<div class="messageContainer" ID="messageContainer"> 
    <div class="message-heading alert" ID="heading"></div>
    <div class="message-text alert">
    <span ID="msgtime"></span></br>
    <span ID="msgbody"></span>
    </div>
</div>

	<header><h2>LIB 030 - Multi-purpose Room<span id="time"></span></h2>
	</header>
	<h2 id="events-header">Today's Events: <span id="date"><?php echo date('D, j M'); ?></span></h2>
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
	<div id="traffic-container">
		<h2>Current Traffic</h2>
		<div id="traffic-level" class="<?php if ($inUse) {echo "event";} else {echo $level;} ?>">
		<div class="traffic-text-container">
		<?php
		if ($inUse) {
			echo '<span class="eventname">Event</span>';
			echo '<span class="eventtimes"> Until  ' . $timeEnd . "</span>";

		} else {
			echo $level;

		}

		?>
			</div>
		</div>
		<span id="open-close"></span>
	</div>
	

	<script src="js/jquery-1.11.1.min.js"></script>
	<script src="js/moment.js"></script>
	<script src="js/eventMessage.js"></script>
	
</body>
</html>