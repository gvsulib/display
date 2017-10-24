<?php

if(session_status() != PHP_SESSION_ACTIVE) {
	session_start();
}

if (!isset($_SESSION['check'])) {$_SESSION['check'] = false;}

require 'password.php';
include 'connection.php';


if ($_SESSION['check'] == false) { 
	if (isset($_POST['password'])){
		
		if (password_verify($_POST["password"], $password)){
			$_SESSION['check'] = true;
			
		} 
	} 
}


if (!$_SESSION["check"]) {

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


if (isset($_POST["messagesubmit"])){
	$sql = "INSERT INTO status_messages (entryDate, expirationDate, heading, body, display) VALUES (STR_TO_DATE('" . $_POST['entryDate'] . " " . $_POST['entryTime'] . "', '%m/%d/%Y %H:%i'), STR_TO_DATE('" . $_POST['expirationDate'] . " " . $_POST['expirationTime'] . "', '%m/%d/%Y %H:%i'),  '" . $_POST['heading'] . "', '" . $_POST['body'] . "', '2')";
	if ($con->query($sql)){
		$m = "Message added successfully.";
		$e = FALSE;
	} else {
		$m = "Error adding message.";
		$e = TRUE;
	}
}
if (!isset($_POST["messagesubmit"]) && isset($_GET['delete'])){
	$sql = "DELETE FROM `status_messages` WHERE messageId = " . $_GET['delete'];
	if ($con->query($sql)){
		$m = "Message deleted successfully";
		$e = FALSE;
	} else {
		$m = "Error deleting message.";
		$e = TRUE;
	}
}
$sql = "SELECT * FROM `status_messages` WHERE entryDate < NOW() AND NOW() < expirationDate";
$res = $con->query($sql);
if ($res && $res->num_rows > 0){
	$messages = $res->fetch_assoc();
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
	<?php if (isset($messages)){ ?>

		<table cellpadding="5">
			<thead>
				<tr>
					<th>Post Date</th>
					<th>Expiration Date</th>
					<th>Heading</th>
					<th>Body</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo $messages['entrydate']; ?></td>
					<td><?php echo $messages['expirationdate']; ?></td>
					<td><?php echo $messages['heading']; ?></td>
					<td><?php echo $messages['body']; ?></td>
					<td><a href="addMessage.php?delete=<?php echo $messages['messageid']; ?>">Delete</a></td>
				</tr>
			</tbody>
		</table>
	<?php } else { ?>
	<h2>No current message. Why not add a new one?</h2>
<?php } ?>
<h1>Add Status Message</h1>
<?php if (isset($m)){?>
<h2 style="color: <?php echo $e ? 'red' : 'darkgreen'; ?>"><?php echo $m;?></h2>
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
	<label for="body">Message</label><br>
	<textarea rows="4" cols="50"name="body" required></textarea><br>
	<input type="submit" name="messagesubmit" value="Submit">
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