<?php
date_default_timezone_set('America/Detroit');

include 'php/connection.php';
include 'php/functions.php';
include 'php/authentication.php';

function extractDate($date) {
    $timestamp = strtotime($date);
    return date('M j', $timestamp);
}

function extractDay($date) {
    $timestamp = strtotime($date);
    return date('l', $timestamp);
}


$rawXML = getReservationXML($username, $password, "7681", true);

if ($rawXML) {
    $events = parseReservationData($rawXML);

} else {
    $events = false;
}

?>


<!DOCTYPE html>
<html>
<head>
	<title>This Week's Library Events</title>
	<link rel="stylesheet" type="text/css" href="css/reset.css">
	
	<link rel="stylesheet" type="text/css" href="css/events.css">
	<meta http-equiv="refresh" content="900">
	<style>
		body{
    		overflow: hidden;
		}
	</style>
</head>
<body>
<div class="messageContainer" ID="messageContainer"> 
    <div class="message-heading alert" ID="heading"></div>
    <div class="message-text alert">
    <span ID="msgtime"></span></br>
    <span ID="msgbody"></span>
    </div>
</div>
	<header><h2>This Week in the Library<span id="time"></span></h2>
	</header>
	<h2 id="events-header">All Events in LIB 030 - Multipurpose Room<span id="date"><?php echo date('F d');  ?></span></h2>
	<section id="events-container">

        <?php

        if ($events) {
            
            foreach ($events->event as $event) {
				if (!(strpos($event->eventname, "HOLD") === false)) {
					continue;
				}
				echo '<div class="eventcontainer">';
				echo '<div class="datetime"><span class="weekday">' . extractDay($event->timestart) . 
				'</span></br><span class="date">' . extractDate($event->timestart) . '</span></div>';

				
				echo '<div class="eventdetails">' . $event->eventname . '<br><span class="eventtimes">' . formatDate($event->timestart) . '-' .
				formatDate($event->timestart) . '</span></div>';

				echo '<div class="clear"></div>';
				echo '</div>';

            }
            
        } else {
			echo '<div class="eventcontainer">No Events this week.</div>';
		}


        ?>
	</section>
	<aside id="mood">
	<h2>Average Library Visitor Experience</h2>
	<figure></figure>
	<h3>How was your experience today?<br>
	Use our interactive signs to tell us!</h3>
</aside>

	

	<script src="js/jquery-1.11.1.min.js"></script>
	<script src="js/emojione.min.js"></script>
		<script src="js/moment.js"></script>
		<script src="js/jquery.swap.js"></script>
		
		<script src="js/eventMessage.js"></script>
		<script>
			function displayEmoji(){
				emojione.imageType = 'svg';
				emojione.sprites = true;
				emojione.imagePathSVGSprites = 'img/emojione.sprites.svg'
				var emojis = {
					1: ':rage:',
					2: ':unamused:',
					3: ':neutral_face:',
					4: ':smile:',
					5: ':heart_eyes:'
				};

				jQuery.ajax({
					type: 'get',
					url: 'php/mood.php',
					dataType: 'json',
					success: function(data){
						jQuery('#mood figure').html(emojione.toImage(emojis[data.level]));
					},
					error: function (XMLreq, msg, errThrwn) {	
						console.log(XMLreq);
						console.log(msg);
						console.log(errThrwn);

					}
				});
			}

			displayEmoji();
			setInterval(displayEmoji(), 50000);
		</script>
		
	</body>
</html>
