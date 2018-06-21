<?php

require 'php/messagePass.php';

if (!isset($_COOKIE["login"])) {
	setcookie("login", "", 0, "/");
    $_COOKIE["login"] = "";
}

if (isset($_POST['password'])){
		
	if ($password == $_POST['password']){
		setcookie("login", "true", 0, "/");
		$_COOKIE["login"] = "true";
		
	}
}



$errMsg = "";
function insertMessage($entryDate, $expirationDate, $heading, $body, $display, $con) {
	$sql = "INSERT INTO `status_messages` (entryDate, expirationDate, heading, body, display) VALUES (STR_TO_DATE(?, '%m/%d/%Y %H:%i'), STR_TO_DATE(?, '%m/%d/%Y %H:%i'), ?, ?,?)";
	$stmt = $con->prepare($sql);
	if (!$stmt->bind_param("ssssi", $entrydate, $expiration, $_POST['heading'], $_POST['body'], $_POST['display'])) {
		return "Error adding status:" . $stmt->error;
		
	}
	
	if ($stmt->execute()){
		return true;
	} else {
		return "Error adding message:". $stmt->error;
		
	}

}

function deleteMessage($messageID, $con) {
	$sql = "DELETE FROM `status_messages` WHERE messageId = ?";
	$stmt = $con->prepare($sql);
	if (!$stmt->bind_param("i", $messageID)) {
		return "Error removing status:" . $stmt->error;
		
	}
	if ($stmt->execute()){
		return true;
		
	} else {
		return "Error deleting message:" . $stmt->error;
		
	}
}

function getMessages($con) {
	$sql = "SELECT * FROM status_messages";
	$stmt = $con->prepare($sql);

	if ($stmt->execute()){
		$messages = array();
		$stmt->store_result();
		$stmt->bind_result($messageId, $entryDate, $expirationDate, $heading, $body, $display);
		while ($stmt->fetch()) {
			$messages[] = array("messageID" => $messageId, "entryDate" => $entryDate, "expirationDate" => $expirationDate, "heading" => $heading, "body" => $body, "display" => $display);
		}

		return $messages;
		
	} else {
		return "Error getting messages:" . $stmt->error;
		
	}

}



require 'php/connection.php';


if ($_COOKIE["login"] == ""){ 
	
	
        echo <<<EOF
	<!DOCTYPE html>
	<html>
	<head>
		<title>Login</title>
		<style>
			html, body{
			font-family: Arial, Helvetica;
			text-align: center;
			}
		</style>
	</head>
	<body>
		<h1>Login</h1>
		<form method="post">
			<input type="password" name="password">
			<input type="submit" value="Go">
		</form>
	</body>
	</html>
EOF;
die();
    
}


if (isset($_POST["post"])){
	
	$entryDate = $_POST['entryDate'] . " " .$_POST['entryTime'];
	$expirationDate = $_POST['expirationDate'] . " " . $_POST['expirationTime'];

	$messages = insertMessage($entryDate, $expirationDate, $_POST['heading'], $_POST['body'], $_POST['display'], $con);

	if (is_string($messages)){
		$errMssg = "Problem retrieving messages:" . $messages;

	} else {
		$errMssg = "";
	}
	
	
}
if (isset($_GET['delete'])){
	$isDeleted = deleteMessage($_GET['delete'], $con);
	if (is_string($isDeleted)) {
		$errMsg = "Could not delete message: " . $isDeleted;

	} else {
		$errMsg = "Message Deleted.";
	}
	
}
$messages = getMessages($con);

if (is_string($messages)){
	$errMsg = "Could not get messages: " . $messages;

}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Add Status Message</title>
	<style>
		html, body{
			font-family: Arial, Helvetica;
		}
	</style>
	<link rel="stylesheet" type="text/css" href="css/jquery.timepicker.css">
	<link rel="stylesheet" type="text/css" href="css/jquery.datepick.css">
</head>
<body>


	<h1>Current Message</h1>
	<?php if (!is_string($messages) && !empty($messages)){ ?>

		<table cellpadding="5">
			<thead>
				<tr>
					<th>Post Date</th>
					<th>Expiration Date</th>
					<th>Heading</th>
					<th>Body</th>
                    <th>Display</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($messages as $message) { ?>
				<tr>
					<td><?php echo $message['entryDate']; ?></td>
					<td><?php echo $message['expirationDate']; ?></td>
					<td><?php echo $message['heading']; ?></td>
					<td><?php echo $message['body']; ?></td>
                    <td><?php
                        switch ($message['display']) {
                            case "0":
                            echo "All Displays";
                            break;
                            case "1":
                            echo "Event";
                            break;
                            case "2":
                            echo "Interactive";
                            break;

                        }
                    
                    
                    ?></td>
					<td><a href="addMessage.php?delete=<?php echo $message['messageID']; ?>">Delete</a></td>
				</tr>
					<?php } //end foreach ?>
			</tbody>
		</table>
	<?php } else { ?>
	<h2>No current message. Why not add a new one?</h2>
<?php } ?>
<h1>Add Status Message</h1>
<?php if (!$errMsg == ""){?>
<h2 style="color: <?php echo $e ? 'red' : 'darkgreen'; ?>"><?php echo $errMsg;?></h2>
<?php } ?>

<form method="post">
	<label for="expirationDate">Entry Date/Time</label><br>
	<input type="text" class="date" name="entryDate" id="entryDate" size="25" required>
	<input type="text" class="time" name="entryTime" id="entryTime" size="10" required><br>
	<label for="expirationDate">Expiration Date/Time</label><br>
	<input type="text" class="date" name="expirationDate" id="expirationDate" size="25" required>
	<input class="time" type="text" name="expirationTime" id="expirationTime" size="10" required><br>
	<label for="heading">Heading</label><br>
	<input type="text" size="50" maxlength="255"name="heading" required><br>
	<label for="body">Expiration Date/Time</label><br>
	<textarea rows="4" cols="50"name="body" required></textarea><br>
    <label for="display">Display</label><br>
    <select name="display">
    <option value="0" selected>All Displays</option>
    <option value="1">Event</option>
    <option value="2">Interactive</option>
    </select>

	<input type="submit" name="post" value="Submit">
</form>
<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/jquery.timepicker.js"></script>
<script src="js/jquery.plugin.js"></script>
<script src="js/jquery.datepick.js"></script>
<script>
jQuery(document).ready(function(){
	jQuery('.time').timepicker({
        timeFormat: 'H:i'
    });
	jQuery('.date').datepick({minDate: new Date()});
	jQuery('#entryDate').datepick('setDate', new Date());
	//round to nearest 5 minutes:
	var coeff = 1000 * 60 * 5;
	var rounded = new Date(
		Math.round(
			(new Date).getTime() / coeff) * coeff);
	jQuery('#entryTime').val(rounded.getHours() + ':' + rounded.getMinutes()).trigger('change');
});

</script>
</body>
</html>