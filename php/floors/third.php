
<?php

/*
Third Floor
10 => "West Wing (Collaborative Space)"
11 => "East Wing (Quiet Space)"
12 => "Reading Room"
13 => "Innovation Zone"
*/


?>

<div class="areas third-floor">
                <div class="here"><span class="star">&#9733;</span></div>
                <div class="elevator">Elevator</div>
                <div id="third_innovation_zone" class="<?php echo $traffic[13];?>">Innovation<br>Zone</div>
                <div id="third_reading_room" class="<?php echo $traffic[12];?>">Reading Room</div>
                <div id="third_collaboration_space" class="<?php echo $traffic[10];?>">Collaborative<br>Space</div>
                <div id="third_quiet_space" class="<?php echo $traffic[11];?>">Quiet Space</div>
            </div>