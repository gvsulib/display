<?php 
//database connection for traffic heatmap
include 'connection.php';

//authenitcatiuon data for the room reservation API
include 'authentication.php';
include 'getRoomReservationData.php';

date_default_timezone_set('America/Detroit');

//check the cached XML room data.  If it's unopenable, unreadable, or older than an hour, try to get new data
if (!checkRoomReservationData()) {
    getNewRoomData($username, $password); 
    
}

//get room reservation data XML object set up for use later
$XML_File = fopen("RoomReservationData.xml", "r");
$rawXML = fread($XML_File, filesize("RoomReservationData.xml"));
$roomXML = new SimpleXMLElement($rawXML);

//make a simple array of room codes and reserved statuses for display

$reservedRooms = array();

foreach ($roomXML->room as $booking) {
    $reservedRooms[(string) $booking->roomcode] = array("groupname" => (string) $booking->groupname, "reserved" => (string) $booking->status);

}

?>
<!DOCTYPE html>
<html>

<head>
	<link rel="stylesheet" type="text/css" href="css/reset.css">
    <link rel="stylesheet" type="text/css" href="https://prod.library.gvsu.edu/labs/opac/css/fonts.css" />
    <link rel="stylesheet" type="text/css" href="css/emojione.min.css" />
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    
    <META HTTP-EQUIV="refresh" CONTENT="18000">

</head>

<body>

<div class="page-container">

	<div class="logo-container">
		<img src="img/gvsu_logo.png">
	</div>

	<div class="date-time-container">
		<h3 id="day">Month 00</h3>
		<h4 id="time">00:00 AM/PM</h4>
	</div>

	<div class="weather-container">
		<h3 id="weather">--<span>&deg;F</span></h3>
	</div>

	<div class="hours-container">
		<h2 data-refresh="1">Today's Hours</h2>
        
        <?php
        //code that gets hours from the hours API

        $hours =array(1 => "Mary%20Idema%20Pew", 
                        6 => "Argo%20Tea",
                        2 => "Steelcase", 
                        3 => "Frey", 
                        4 => "Seidman%20House", 
                        5 => "Curriculum%20Materials%20Library"
        );

        //get JSON for all hours and format them for display
        $hoursOutput = array();
        foreach ($hours as $order => $string) {
            $handle = fopen("http://prod.library.gvsu.edu/hours_api/index.php?order=$order&string=$string", "r");
            $output = fread($handle, 800);
            $hoursFormat = json_decode($output, true);
            $string = urldecode($string);

            //for mary I and argo tea, format the string differently and wrap a different div around them
            if ($order == 1 || $order == 6) {
                $divopen = '<div class="hours-display-primary">';
                $hoursFormat[$string] = "Open Until " . substr($hoursFormat[$string], (strpos($hoursFormat[$string], '-') + 2) , (strlen($hoursFormat[$string]) - 1) );

            } else {
                $divopen = '<div class="hours-display-secondary">';
            }

            $hoursOutput[] = $divopen . $string . " : " .  $hoursFormat[$string] . '</div>';
            fclose($handle);

        }
        
        foreach ($hoursOutput as $html) {
            echo $html;
        }

        ?>
        <div id="hours"><h4 class="message-post-time"></h4>
        </div>
        
  </div>
  <!--
  <div class="computer-availability-container">
       <h2 data-refresh="2">Library Computer Availability</h2>
      <iframe width="100%" height="250" id="cpumap" style="padding-left: 20px; padding-right: 10px; padding-top: 10px;" src="https://prod.library.gvsu.edu/computer_availability/?x=true&amp;library=maryi&amp;notitle"></iframe>
	</div>
-->
	<div class="room-availability-container">
    
		<h2 data-refresh="3">Study Room Availability<small> Last Updated: <span id="last-updated-rooms"><?php echo (string) $roomXML->timedisplay; ?></span></small></h2>

       <ul class="traffic-legend" style="display: block;" id="room-traffic-legend">
            <li class="low"><div></div>Available</li>
            <li class="medium"><div></div>Reserved soon</li>
            <li class="full"><div></div>Reserved</li>
        </ul>
        <ul class="room-availability-floors">
        <?php

        //set up an array of floors and associated rooms to sue to build the display

        $studyRooms = array(
            "atrium" => array("7678" => "003 - Media Prep", "7679" => "004 - Media Prep", "7680" => "005 - Media Prep"),
            "1st Floor" => array("7686"=> "133 - Media Prep", "7687" => "134 - Presentation Practice", "7688" => "135 - Presentation Practice"),
            "2nd Floor" => array("7689" => "202 - Conference Style", "7690" => "203 - Conference Style", "7801" => "204 - Lounge Style", "7691" => "205 - Conference Style", "7692" => "216 - Seminar Room"),
            "3rd Floor" => array("7693" => "302 - Conference Style", "7694" => "303 - Lounge Style", "7695" => "304 - Conference Style", "7696" => "305 - Conference Style"),
            "4th Floor" => array("7698" => "404 - Conference Style", "7699" => "405 - Conference Style")
    
        );

        //start iterating through the array and setting up the room display

        foreach ($studyRooms as $floor => $rooms) {
            echo '<li class="floor-container">';
            echo '<h4 class="floor-title">' . $floor . '</h4>';
            echo '<ul>';
            foreach ($rooms as $roomID => $roomName) {

                

                if (isset($reservedRooms[$roomID])) {

                    $groupName = $reservedRooms[$roomID]["groupname"];
                    if ($reservedRooms[$roomID]["reserved"] == "reserved") {
                        $reservedDisplay = " reserved";
                    } else {
                        $reservedDisplay = " reserved_soon";
                    }

                } else {
                    $groupName = "";
                    $reservedDisplay = "available";
                }

                echo '<li class="room-container' . $reservedDisplay . '" id="' . $roomID . '">';
                echo '<span class="room-name">'. $roomName . '</span>';
                echo '<span class="reserved-by">' . $groupName . '</span></li>';    
            }

           echo '</ul></li>'; 


        }

        ?>
		

	</div>


	<div class="traffic-map-container">
		<h2 data-refresh="4">Library Traffic<small>Updated: <span id="last-updated"></span></small></h2>

        <div class="spinner"></div>

		<ul class="traffic-legend" id="area-traffic-legend">
            <li class="display"><span class="star">&#9733;</span>Display</li>
			<li class="low"><div></div>Low Traffic</li>
			<li class="medium"><div></div>Medium Traffic</li>
			<li class="high"><div></div>High Traffic</li>
            <li class="full"><div></div>Full</li>
            <li class="event"><div></div>Event</li>
		</ul>

        <div class="areas-container">

            <div class="areas atrium-floor">
                <div class="here"><span class="star">&#9733;</span></div>
                <div class="elevator">Elevator</div>
                <div id="atrium_exhibition_room" class="grey">Exhibition Room</div>
                <div id="atrium_seating_area" class="grey">Seating Area</div>
                <div id="atrium_multipurpose_room" class="grey">
                    <span>Multipurpose<br>Room</span>
                </div>
                <div id="mp-event">
                Event:<br>
                    <span id="mp-event-name"></span><br>
                    <span id="mp-event-times"></span>
                </div>
                <div id="atrium_living_room" class="grey">Living<br>Room</div>
                <div id="atrium_under_stairs" class="grey">Tables under Stairs</div>
            </div>

            <div class="areas first-floor">
                <div class="here here-1"><span class="star">&#9733;</span></div>
                <div class="here here-2"><span class="star">&#9733;</span></div>
                <div class="elevator">Elevator</div>
                <div id="first_knowledge_market" class="grey">Knowledge Market</div>
                <div id="first_cafe_seating" class="grey">Cafe Seating</div>
            </div>

            <div class="areas second-floor">
                <div class="here"><span class="star">&#9733;</span></div>
                <div class="elevator">Elevator</div>
                <div id="second_collaboration_space" class="grey">Collaborative<br>Space</div>
                <div id="second_quiet_space" class="grey">Quiet Space</div>
            </div>

            <div class="areas third-floor">
                <div class="here"><span class="star">&#9733;</span></div>
                <div class="elevator">Elevator</div>
                <div id="third_innovation_zone" class="grey">Innovation<br>Zone</div>
                <div id="third_reading_room" class="grey">Reading Room</div>
                <div id="third_collaboration_space" class="grey">Collaborative<br>Space</div>
                <div id="third_quiet_space" class="grey">Quiet Space</div>
            </div>

            <div class="areas fourth-floor">
                <div class="here"><span class="star">&#9733;</span></div>
                <div class="elevator">Elevator</div>
                <div id="fourth_reading_room" class="grey">Reading Room</div>
                <div id="fourth_collaboration_space" class="grey">Collaborative<br>Space</div>
                <div id="fourth_quiet_space" class="grey">Quiet Space</div>
            </div>

        </div>

	</div>

    <div class="floor-toggle">

        <ul class="floors">
            <li class="atrium-floor-button first 0">Atrium</li>
            <li class="first-floor-button 1">1st Floor</li>
            <li class="second-floor-button 2">2nd Floor</li>
            <li class="third-floor-button selected 3">3rd Floor</li>
            <li class="fourth-floor-button last 4">4th Floor</li>
        </ul>

    </div>
    <div class="feedback">
        <h2>How was your library experience today? Touch below to let us know!</h2>
        <ul class="emojis">
        	<li data-emoji=":heart_eyes:" data-level="5"></li>
        	<li data-emoji=":smile:" data-level="4"></li>
        	<li data-emoji=":neutral_face:" data-level="3"></li>
        	<li data-emoji=":unamused:" data-level="2"></li>
        	<li data-emoji=":rage:" data-level="1"></li>
            
        </ul>
        <div class="modal modal1" >
           
           <p>We are sorry you are sad! Is there anything we can do to help?</p>
           <p><span>Yes, I have a complaint or need help!</span><p>
           
        </div>
        <div class="modal modal2">
            <p>
                We want you to have a great library experience! Text us at (616) 818-0219 and let us know how we can make it better.
            </p>
            <p>
                You can also contact us at library@gvsu.edu or talk to a staff member at the Service Desk!
            </p>
        </div>
        <div class="modal modal3">
            <p>
                Thank you for your feedback!
            </p>
        </div>
    </div>
    <div class="modal close">Close</div>
</div>

</body>
<script>
    var floor = <?php echo isset($_GET['floor']) ? $_GET['floor'] : 1;?>;
   
</script>
<script src="js/jquery-1.11.1.min.js" ></script>
<script src="js/jquery.simpleWeather.min.js"></script>
<script src="js/jquery-idletimer.js"></script>
<script src="js/moment.js"></script>
<script src="js/emojione.min.js"></script>
<!--<script src="js/scripts.js"></script>-->

</html>
