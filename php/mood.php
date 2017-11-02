
<?php

//retrives and averages the emoji data for the display in the entryway, which shows "average mood"

include 'connection.php';
$sql = 'SELECT ROUND(avg(emotion_id)) as level, COUNT(emotion_id) as count from feedback_response WHERE DATE(timestamp) = CURDATE()';
$res = $con->query($sql);
if ($res) {
	$mood = $res->fetch_assoc();
	if ($mood['count'] > 2) {
		echo json_encode($mood);
		die();
	}
}

//if there's not enough mood data, we show a happy face.
echo json_encode(array('level' => 4));
?>
