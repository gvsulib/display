<?php
include 'connection.php';

//This function drives the parts of the display that show emergency messages.  There's a back-end interface for inputting
//the messageat display/addMessage.php

//An ajax script checks this for messages every few minutes and dynamically displays them (see scripts.js)

$sql = "SELECT * FROM `status_messages` WHERE entryDate < NOW() AND NOW() < expirationDate AND display IN (0,1)";
$db_result = $con->query($sql);

if ($db_result->num_rows > 0) {
    echo json_encode($db_result->fetch_assoc());
} else {
    echo '["none"]';
}


?>