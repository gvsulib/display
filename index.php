<?php include 'connection.php';?>
<!DOCTYPE html>
<html>

<head>
	<link rel="stylesheet" type="text/css" href="css/reset.css">
    <link rel="stylesheet" type="text/css" href="https://prod.library.gvsu.edu/labs/opac/css/fonts.css" />
    <link rel="stylesheet" type="text/css" href="css/emojione.min.css" />
	<link rel="stylesheet" type="text/css" href="css/styles.css">
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

	<div class="notifications-container">
		<h2 data-refresh="1">Notifications</h2>
		
        <div id="message"><h4 class="message-post-time"></h4>
        <h4 class="message-heading"></h4><p></p></div>
        <iframe scrolling="no" width="100%" height="190" id="notifications" src="https://www.gvsu.edu/events/slideshow-index.htm?slideshowId=48DC2DE5-F7C1-B484-FB7A1B7AAD7F9050"></iframe>
  </div>
  <div class="computer-availability-container">
       <h2 data-refresh="2">Library Computer Availability</h2>
      <iframe width="100%" height="250" id="cpumap" style="padding-left: 20px; padding-right: 10px; padding-top: 10px;" src="https://prod.library.gvsu.edu/computer_availability/?x=true&amp;library=maryi&amp;notitle"></iframe>
	</div>

	<div class="room-availability-container">
    
		<h2 data-refresh="3">Study Room Availability<small> Last Updated: <span id="last-updated-rooms"></span></small></h2>

       <ul class="traffic-legend" style="display: block;" id="room-traffic-legend">
            <li class="low"><div></div>Available</li>
            <li class="medium"><div></div>Reserved soon</li>
            <li class="full"><div></div>Reserved</li>
        </ul>

		<ul class="room-availability-floors">
            <li class="floor-container">
                <h4 class="floor-title">Atrium</h4>
                <ul>
                    <li class="room-container grey" id="7678">
                        <span class="room-name">003 - Media Prep</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7679">
                        <span class="room-name">004 - Media Prep</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7680">
                        <span class="room-name">005 - Media Prep</span>
                        <span class="reserved-by"></span>
                    </li>
                </ul>
            </li>
            <li class="floor-container">
                <h4 class="floor-title">1st Floor</h4>
                <ul>
                    <li class="room-container grey" id="7686">
                        <span class="room-name">133 - Media Prep</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7687">
                        <span class="room-name">134 - Presentation Practice</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7688">
                        <span class="room-name">135 - Presentation Practice</span>
                        <span class="reserved-by"></span>
                    </li>
                </ul>
            </li>
            <li class="floor-container">
                <h4 class="floor-title">2nd Floor</h4>
                <ul>
                    <li class="room-container grey" id="7689">
                        <span class="room-name">202 - Conference Style</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7690">
                        <span class="room-name">203 - Conference Style</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7801">
                        <span class="room-name">204 - Lounge Style</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7691">
                        <span class="room-name">205 - Conference Style</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7692">
                        <span class="room-name">216 - Seminar Room</span>
                        <span class="reserved-by"></span>
                    </li>
                </ul>
            </li>
            <li class="floor-container">
                <h4 class="floor-title">3rd Floor</h4>
                <ul>
                    <li class="room-container grey" id="7693">
                        <span class="room-name">302 - Conference Style</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7694">
                        <span class="room-name">303 - Lounge Style</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7695">
                        <span class="room-name">304 - Conference Style</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7696">
                        <span class="room-name">305 - Conference Style</span>
                        <span class="reserved-by"></span>
                    </li>
                </ul>
            </li>
            <li class="floor-container">
                <h4 class="floor-title">4th Floor</h4>
                <ul>
                    <li class="room-container grey" id="7698">
                        <span class="room-name">404 - Conference Style</span>
                        <span class="reserved-by"></span>
                    </li>
                    <li class="room-container grey" id="7699">
                        <span class="room-name">405 - Conference Style</span>
                        <span class="reserved-by"></span>
                    </li>
                </ul>
            </li>
        </ul>

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
<script src="js/scripts.js"></script>

</html>
