<?php
include '../connection.php';
$feedbackId = strlen($_POST['feedbackId']) ? $_POST['feedbackId'] : 'NULL';
$emotionId = $_POST['emotionId'];
$sql = "INSERT INTO feedback_response (response_id, option_id, emotion_id, timestamp) VALUES (NULL,$feedbackId,$emotionId,NULL)";
if ($con->query($sql)) {
	echo json_encode(array('success' => true));
}
?>