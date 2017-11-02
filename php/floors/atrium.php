

<?php

/*
Atrium
1 = "Living Room"
2 = "Multi-Purpose Room"
3 = "Exhibition Room"
4 = "Tables Under Stairs"
5 = "Seating Outside 001 and 002"


//we need to do some special checking for the multipurpose room.  
//if it currently has an event going on, then it needs to display details of the event 
//and be purple

*/

if (isset($reservedRooms["7681"])) {
    $event_name = $reservedRooms["7681"]["groupname"];
    $end_time = "Until " . $reservedRooms["7681"]["end"];
    $multiDisplay = "event";

} else {
    $event_name = "";
    $end_time = "";
    $multiDisplay = $traffic["2"];
}

?>


<div class="areas atrium-floor">
                <div class="here"><span class="star">&#9733;</span></div>
                <div class="elevator">Elevator</div>
                <div id="atrium_exhibition_room" class="<?php echo $traffic[3];?>">Exhibition Room</div>
                <div id="atrium_seating_area" class="<?php echo $traffic[5]; ?>">Seating Area</div>
                <div id="atrium_multipurpose_room" class="<?php echo $multiDisplay ?>">
                    <span>Multipurpose<br>Room</span><br>
                    
                
                    <span id="mp-event-name"><?php echo $event_name ?></span><br>
                    <span id="mp-event-times"><?php echo $end_time ?></span>
                
                </div>
               
                <div id="atrium_living_room" class="<?php echo $traffic[1];?>">Living<br>Room</div>
                <div id="atrium_under_stairs" class="<?php echo $traffic[4];?>">Tables under Stairs</div>
            </div>