<?php 
//database connection for traffic heatmap
include 'connection.php';

//authenitcatiuon data for the room reservation API
include 'authentication.php';
include 'getRoomReservationData.php';
include 'getTraffic.php';
date_default_timezone_set('America/Detroit');



//check the cached XML room data.  If it's unopenable, unreadable, or older than an hour, try to get new data

echo checkRoomReservationData();

if (!checkRoomReservationData()) {
    getNewRoomData($username, $password);  
    
}

//unfortunately, the page tries to load broken data before the process of writing the new file is finished, 
//so we still have to check for errors

//get room reservation data XML object set up for use later
$XML_File = fopen("RoomReservationData.xml", "r");
if ($XML_File) {
    $rawXML = fread($XML_File, filesize("RoomReservationData.xml"));
    try {$roomXML = new SimpleXMLElement($rawXML);} catch (Exception $e) {
        $roomXML = false;
    }
} else {
    $roomXML = false;
}
if ($roomXML) {
    $reservedRooms = array();

    foreach ($roomXML->room as $booking) {
        //make a simple array of room codes and reserved statuses for display
        //include start and end times, primarily so I'll have them for the multipurpose room display

        $reservedRooms[(string) $booking->roomcode] = array(
            "groupname" => (string) $booking->groupname, 
            "reserved" => (string) $booking->status,
            "start" => (string) $booking->timestart,
            "end" => (string) $booking->timeend,
        );
    } 
}

//has the user selected an emoji?  log it!

if (isset($_GET["emoji"])) {
    $emoji = $_GET["emoji"];
    if (postFeedback($emoji, $con)) {
        $feedbackMessage = "Feedback logged!";
    } else {
        $feedbackMessage = "ERROR: Feedback not logged!";
    }

}



//figure out what floor the user wants to see
if (isset($_GET['floor'])) {
    
    $floorDisplay = (int) $_GET['floor'];
} else {
    $floorDisplay = 1;
}
//get traffic data from database

$traffic = getTrafficData($con);

//map traffic levels to the colors for display

foreach ($traffic as $roomID => $level) {
    switch ($level) {
        case '4':
        $traffic[$roomID] = "red";
        break;
        case '-1':
        $traffic[$roomID] = "red";
        break;
        case '3':
        $traffic[$roomID] = "orange";
        break;
        case '2':
        $traffic[$roomID] = "yellow";
        break;
        case '1':
        $traffic[$roomID] = "green";
        break;
        case '0':
        $traffic[$roomID] = "green";
        break;
        
    }
}

//get last update time of traffic from database

$trafficUpdate = getLastUpdatedTraffic($con);

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


<div class="messageContainer" ID="messageContainer"> 
    <h2 class="message-heading" ID="heading"></h2>
    <div class="message-text">
    <span ID="time"></span></br>
    <span ID="body"></span>
    </div>
</div>



	<div class="logo-container">
		<img src="img/gvsu_logo.png">
	</div>

	<div class="date-time-container" ID="date-time-container">
		<h3 id="day"></h3>
		<h4 id="time"></h4>
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
    
		<h2 data-refresh="3">Study Room Availability <span id="last-updated">Last Updated: <?php if ($roomXML != false) {echo (string) $roomXML->timedisplay;} ?></span></h2>

       <ul class="traffic-legend" style="display: block;" id="room-traffic-legend">
            <li class="low"><div></div>Available</li>
            <li class="medium"><div></div>Reserved soon</li>
            <li class="full"><div></div>Reserved</li>
        </ul>
        <ul class="room-availability-floors">
        <?php

        //set up an array of floors and associated rooms to use to build the display
        if ($roomXML != false) {
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

                    
                    //is the room in our array of reserved rooms?  If so, set it's status and display the groupname
                    if (isset($reservedRooms[$roomID])) {

                        $groupName = $reservedRooms[$roomID]["groupname"];
                        if ($reservedRooms[$roomID]["reserved"] == "reserved") {
                            $reservedDisplay = " reserved";
                        } else {
                            $reservedDisplay = " reserved_soon";
                        }
                    //otherwise, mark it as availabile
                    } else {
                        $groupName = "";
                        $reservedDisplay = " available";
                    }

                    echo '<li class="room-container' . $reservedDisplay . '" id="' . $roomID . '">';
                    echo '<span class="room-name">'. $roomName . '</span>';
                    echo '<span class="reserved-by">' . $groupName . '</span></li>';    
                }

            echo '</ul></li>'; 


            }
        }   else {
            echo "Error: Cannot get room reservation data.";
        }
        ?>
		

	</div>


	<div class="traffic-map-container">
		<h2 data-refresh="4">Library Traffic Last Updated: <span id="last-updated"><?php echo $trafficUpdate; ?></span></h2>

       

		<ul class="traffic-legend" id="area-traffic-legend">
            <li class="display"><span class="star">&#9733;</span>Display</li>
			<li class="low"><div></div>Low Traffic</li>
			<li class="medium"><div></div>Medium Traffic</li>
			<li class="high"><div></div>High Traffic</li>
            <li class="full"><div></div>Full</li>
            <li class="event"><div></div>Event</li>
		</ul>

        <div class="areas-container">
        <?php
           
        //show the traffic map for the selected floor.  if no floor is specfied, the default is 1
        switch ($floorDisplay) {
            case 0:
            include 'php/atrium.php';
            break;
            case 1:
            include 'php/first.php';
            break;
            case 2:
            include 'php/second.php';
            break;
            case 3:
            include 'php/third.php';
            break;
            case 4:
            include 'php/fourth.php';
            break;


        }
            

         ?>   
        </div>

	</div>
<!--button sto select floors-->
    <div class="floor-toggle">
        
        <ul class="floors">
        <a href="index.php?floor=0"><li class="atrium-floor-button <?php if ($floorDisplay == 0) {echo "selected";}?>">Atrium</li></a>
            <a href="index.php?floor=1"><li class="first-floor-button <?php if ($floorDisplay == 1) {echo "selected";}?>">1st Floor</li></a>
            <a href="index.php?floor=2"><li class="second-floor-button <?php if ($floorDisplay == 2) {echo "selected";}?>">2nd Floor</li></a>
            <a href="index.php?floor=3"><li class="third-floor-button <?php if ($floorDisplay == 3) {echo "selected";}?>">3rd Floor</li></a>
            <a href="index.php?floor=4"><li class="fourth-floor-button <?php if ($floorDisplay == 4) {echo "selected";}?>">4th Floor</li></a>
        </ul>
        
    </div>

    <!--emojis-->
    <div class="feedback">
        <h2>How was your library experience today? Touch below to let us know!</h2>
        <div class="emoji-container">
        <a class="emojilink" href="index.php?floor=<?php echo $floorDisplay; ?>&emoji=5"><img class="emoji" src="emojis/1f60d.png"></a> 
        <a class="emojilink" href="index.php?floor=<?php echo $floorDisplay; ?>&emoji=4"><img class="emoji" src="emojis/1f60c.png"><a> 
        <a class="emojilink" href="index.php?floor=<?php echo $floorDisplay; ?>&emoji=3"><img class="emoji" src="emojis/1f610.png"></a>
        <a class="emojilink" href="index.php?floor=<?php echo $floorDisplay; ?>&emoji=2"><img class="emoji" src="emojis/1f620.png"></a> 
        <a class="emojilink" href="index.php?floor=<?php echo $floorDisplay; ?>&emoji=1"><img class="emoji" src="emojis/1f92c.png"></a>
        </div>
        <!--confirmation message for touching an emoji-->
        <div ID="modal" class="modal<?php if (isset($emoji)) {echo "show";} else {echo "hide";} ?>">
           <?php


            if (isset($emoji)) {
                echo "<P>" . $feedbackMessage . "<P>";
                if ($emoji >= 3) {
                    
                    echo  "<p>Thank you for your feedback!</p>";
                } else {
                    echo "<p>
                    We want you to have a great library experience! Text us at (616) 818-0219 and let us know how we can make it better.
                </p>
                <p>
                    You can also contact us at library@gvsu.edu or talk to a staff member at the Service Desk!
                </p>";
                }

             }

            

           
    ?>
    <a href="index.php?floor=<?php echo $floorDisplay; ?>">Close</a>
</div>

</body>

<script src="js/jquery-1.11.1.min.js" ></script>
<script src="js/jquery.simpleWeather.min.js"></script>
<script src="js/jquery-idletimer.js"></script>
<script src="js/moment.js"></script>
<script src="js/scripts.js"></script>


<?php
//code to relead the page if the emoji confirmation window is showing.  
//Reloading the page will reset the visibility class on the div, hiding it.
if (isset($emoji)) {
            echo <<<EOT


            <script>

            function hideModalReset(s){
                setTimeout(function(){
                    window.location.href='index.php?floor=$floorDisplay';
                }, s * 1000);
            }
            hideModalReset(20);

            </script>


EOT;
}

?>
</html>
