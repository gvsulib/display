<?php
include('connection.php');
$con = getConnection();
$sql = "SELECT * FROM `status_messages` WHERE entryDate < NOW() AND NOW() < expirationDate AND display IN (0,2)";
$db_result = $con->query($sql)->fetch_assoc();
echo json_encode($db_result);
?>