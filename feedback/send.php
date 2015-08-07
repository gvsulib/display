<?php
include '../connection.php';
$emotionId = $_POST['emotionId'];
$sql = "INSERT INTO feedback_response (response_id, emotion_id, timestamp) VALUES (NULL,$emotionId,NULL)";
if ($con->query($sql)) {
	echo json_encode(array('success' => true));
}
?>