<?php
include('connection.php');
$query = "SELECT DATE_FORMAT(time, '%h:%m %p') FROM entries WHERE entryID = (select max(entryID) from entries);";
$db_result = $con->query($query);
$lastUpdated = $db_result->fetch_row();
$return['updated'] = $lastUpdated[0];
echo json_encode($return);