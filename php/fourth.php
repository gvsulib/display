<?php
/*
Fourth Floor
14 => "West Wing (Collaborative Space)"
15 => "East Wing (Quiet Space)"
16 => "Reading Room"
*/

?>


<div class="areas fourth-floor">
                <div class="here"><span class="star">&#9733;</span></div>
                <div class="elevator">Elevator</div>
                <div id="fourth_reading_room" class="<?php echo $traffic[16];?>">Reading Room</div>
                <div id="fourth_collaboration_space" class="<?php echo $traffic[14];?>">Collaborative<br>Space</div>
                <div id="fourth_quiet_space" class="<?php echo $traffic[15];?>">Quiet Space</div>
</div>