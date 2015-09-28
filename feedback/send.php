<?php
include '../connection.php';
$emotionId = $_POST['emotionId'];
$sql = "INSERT INTO feedback_response (response_id, emotion_id) VALUES (NULL,$emotionId)";
if ($con->query($sql)) {
	echo json_encode(array('success' => true));
} else {
	echo json_encode(array('success' => false, 'error' => $con->error));
}
?>