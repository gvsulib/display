<?php
$sql = 'SELECT feedback_id, title FROM feedback_options WHERE deleted = 0';
$res = $con->query($sql);
while ($option = $res->fetch_assoc()) {
	echo "<li data-id=\"" . $option['feedback_id'] . "\">" . $option['title'] . "</li>";
}
?>