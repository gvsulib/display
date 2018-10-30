<?php 
//database connection for traffic heatmap
include 'php/connection.php';

//authenitcatiuon data for the room reservation API
include 'php/authentication.php';
include 'php/functions.php';

date_default_timezone_set('America/Detroit');



//get room reservation data XML object set up for use later
$XML_File = fopen("RoomReservationData.xml", "r");

if ($XML_File) {
    //check the cached XML room data.  If it's unreadable, set the variable to false-this will produce an error message
    //the check function later should catch this and regenerate the file

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
fclose($XML_File);


//check the data file for problems and to see if it's fresh enough.  If not, regenerate it
if (!checkRoomReservationData()) {
    $result = refreshRoomData($username, $password);  
    
}

//has the user selected an emoji?  log it!

if (isset($_GET["emoji"])) {
    $emoji = trim($_GET["emoji"]);
    $emoji = (int) $emoji;
    
    $posted = postFeedback($emoji);
    if ($posted === true) {
        $feedbackMessage = "Feedback logged!";
    } else {
        $feedbackMessage = "ERROR: Feedback not logged!" . $posted;
    }

}



//figure out what floor the user wants to see
if (isset($_GET['floor'])) {
    
    $floorDisplay = (int) $_GET['floor'];
} else {
    $floorDisplay = 1;
}

//get last update time of traffic from database


$timeCheck = getLastUpdatedTraffic();

$fiveHoursAgo = strtotime("-5 hours");

//check to see how long ago the last update was.  If it's more than five hours, the library probably closed.
//so instead of showing data from the day before,  show all spaces open until a roam happens

if (strtotime($timeCheck <= $fiveHoursAgo)) {
    $refresh = false;
} else {
    $refresh = true;
}

$trafficRaw = getTrafficData();

$traffic = array();

if ($refresh) {
    

    
    $trafficUpdate = date("h:i A", $timeCheck);
    //map traffic levels to the colors for display
    foreach ($trafficRaw as $entry) {
        switch ($entry["level"]) {
            case '4':
            $traffic[$entry["space"]] = "red";
            break;
            case '-1':
            $traffic[$entry["space"]] = "red";
            break;
            case '3':
            $traffic[$entry["space"]] = "orange";
            break;
            case '2':
            $traffic[$entry["space"]] = "yellow";
            break;
            case '1':
            $traffic[$entry["space"]] = "green";
            break;
            case '0':
            $traffic[$entry["space"]] = "green";
            break;
        
        }
    }
} else {
    foreach ($trafficRaw as $entry) {
        $trafficUpdate = date("h:i A");
        $traffic[$entry["space"]] = "green";
    }
}


?>



<!DOCTYPE html>
<html>

<head>
	<link rel="stylesheet" type="text/css" href="css/reset.css">
    <link rel="stylesheet" type="text/css" href="https://prod.library.gvsu.edu/labs/opac/css/fonts.css" />
    <link rel="stylesheet" type="text/css" href="css/interactive.css">
    
    <META HTTP-EQUIV="refresh" CONTENT="900">

</head>

<body>

<div class="page-container">


    <div class="messageContainer" ID="messageContainer"> 
        <h2 class="message-heading" ID="heading"></h2>
        <div class="message-text">
        <span ID="msgtime"></span></br>
        <span ID="msgbody"></span>
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
        /*
        8552 is the id for the MIP, 8922 is the id for argo tea
        */
        

        //get JSON for all hours and format them for display
        $handle = fopen("https://api3.libcal.com/api_hours_today.php?iid=1647&lid=0&format=json&systemTime=1", "r");
        $output = fread($handle, 4000);
        $hours = json_decode($output, true);
        fclose($handle);

        $locations = $hours["locations"];
        $MIPString = "Mary Idema Pew Library: ";
        $argoString = "Argo Tea: ";

        foreach($locations as $location) {
            if ($location["lid"] == "8552") {
              $times = $location["times"];
              if ($times["currently_open"]) {
                $hours = $times["hours"][0];
                $MIPString = $MIPString . " Open Until " . $hours["to"];
              } else {
                if (array_key_exists("note", $times)) {
                    $MIPString = $MIPString . " " . $times["note"];
                } else {
                    $MIPString = $MIPString . " Closed";
                }
          
              }
            }
              if ($location["lid"] == "8922") {
                $times = $location["times"];
                if ($times["currently_open"]) {
                  $hours = $times["hours"][0];
                  $argoString = $argoString . "Open Until " . $hours["to"];
                } else {
                  if (array_key_exists("note", $times)) {
                    $argoString = $argoString . " " . $times["note"];
                  } else {
                    $argoString = $argoString . "Closed";
                  }
            
                }
              }
            }
        
        //for mary I and argo tea, format the string differently and wrap a different div around them
        echo '<div class="hours-display-primary">';
        echo $MIPString;
        echo "</div>";
                

        echo '<div class="hours-display-secondary">';
        echo $argoString;
        echo "</div>";

            

        
        
        

        ?>
        <div id="hours"><h4 class="message-post-time"></h4>
        </div>
        
  </div>
  
  <div class="computer-availability-container">
       <h2 data-refresh="2">Library Computer Availability</h2>
      <iframe width="100%" height="250" id="cpumap" style="padding-left: 20px; padding-right: 10px; padding-top: 10px;" src="https://prod.library.gvsu.edu/computer_availability/?x=true&amp;library=maryi&amp;notitle"></iframe>
	</div>

	<div class="room-availability-container">
    
		<h2 data-refresh="3">Study Room Availability <span id="last-updated">Last Updated: <?php if ($roomXML) {echo (string) $roomXML->timedisplay;} ?></span></h2>

       <ul class="traffic-legend" style="display: block;" id="room-traffic-legend">
            <li class="low"><div></div>Available</li>
            <li class="medium"><div></div>Reserved soon</li>
            <li class="full"><div></div>Reserved</li>
        </ul>
        <ul class="room-availability-floors">
        <?php

        //set up an array of floors and associated rooms to use to build the display
        if ($roomXML) {
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
            include 'php/floors/atrium.php';
            break;
            case 1:
            include 'php/floors/first.php';
            break;
            case 2:
            include 'php/floors/second.php';
            break;
            case 3:
            include 'php/floors/third.php';
            break;
            case 4:
            include 'php/floors/fourth.php';
            break;


        }
            

         ?>   
        </div>
        <!--buttons to select floors-->
       

	</div>
    <div class="floor-toggle">
        
        <ul class="floors">
        <a href="interactive.php?floor=0"><li class="atrium-floor-button <?php if ($floorDisplay == 0) {echo "selected";}?>">Atrium</li></a>
            <a href="interactive.php?floor=1"><li class="first-floor-button <?php if ($floorDisplay == 1) {echo "selected";}?>">1st Floor</li></a>
            <a href="interactive.php?floor=2"><li class="second-floor-button <?php if ($floorDisplay == 2) {echo "selected";}?>">2nd Floor</li></a>
            <a href="interactive.php?floor=3"><li class="third-floor-button <?php if ($floorDisplay == 3) {echo "selected";}?>">3rd Floor</li></a>
            <a href="interactive.php?floor=4"><li class="fourth-floor-button <?php if ($floorDisplay == 4) {echo "selected";}?>">4th Floor</li></a>
        </ul>
        
    </div>
    

    <!--emojis-->
    <div class="feedback">
        <h2>How was your library experience today? Touch below to let us know!</h2>
        <div class="emoji-container">
        <a class="emojilink" href="interactive.php?floor=<?php echo $floorDisplay; ?>&emoji=5"><img class="emoji" src="img/emojis/1f60d.png"></a> 
        <a class="emojilink" href="interactive.php?floor=<?php echo $floorDisplay; ?>&emoji=4"><img class="emoji" src="img/emojis/1f60c.png"><a> 
        <a class="emojilink" href="interactive.php?floor=<?php echo $floorDisplay; ?>&emoji=3"><img class="emoji" src="img/emojis/1f610.png"></a>
        <a class="emojilink" href="interactive.php?floor=<?php echo $floorDisplay; ?>&emoji=2"><img class="emoji" src="img/emojis/1f620.png"></a> 
        <a class="emojilink" href="interactive.php?floor=<?php echo $floorDisplay; ?>&emoji=1"><img class="emoji" src="img/emojis/1f621.png"></a>
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

            <a href="interactive.php?floor=<?php echo $floorDisplay; ?>">Close</a>
     </div>
</div>

</body>

<script src="js/jquery-1.11.1.min.js" ></script>
<script src="js/jquery.simpleWeather.min.js"></script>
<script src="js/jquery-idletimer.js"></script>
<script src="js/moment.js"></script>
<script src="js/interactive.js"></script>


<?php
//code to relead the page if the emoji confirmation window is showing.  
//Reloading the page will reset the visibility class on the div, hiding it.
if (isset($emoji)) {
            echo <<<EOT


            <script>

            function hideModalReset(s){
                setTimeout(function(){
                    window.location.href='interactive.php?floor=$floorDisplay';
                }, s * 1000);
            }
            hideModalReset(20);

            </script>


EOT;
}

?>
</html>
