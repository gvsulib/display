<?php
session_start();
$m = null;
if (!isset($_SESSION['check'])){ 
	if (isset($_POST['password'])){
		require 'password.php';
		if ($password == sha1($_POST['password'])){
			$_SESSION['check'] = TRUE;
		}
	} else { ?>
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
	<?php
	die();
}
}
include 'connection.php';
$con = getConnection();
if ($_POST){
	if ($_POST['entryDate'] && $_POST['expirationDate'] && $_POST['expirationTime'] && $_POST['entryTime'] && $_POST['heading'] && $_POST['body']){
		$sql = "INSERT INTO `status_messages` (messageId, entryDate, expirationDate, heading, body, display) VALUES (0, STR_TO_DATE('" . $_POST['entryDate'] . " " . $_POST['entryTime'] . "', '%m/%d/%Y %H:%i'), STR_TO_DATE('" . $_POST['expirationDate'] . " " . $_POST['expirationTime'] . "', '%m/%d/%Y %H:%i'),  '" . $_POST['heading'] . "', '" . $_POST['body'] . "', '" . $_POST['display'] . "')";
		if ($con->query($sql)){
			$m = "Message added successfully.";
			$e = FALSE;
		} else {
			$m = "Error adding message.";
			$e = TRUE;
		}
	}
}

if (isset($_GET['delete'])){
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
	<?php if ($res->num_rows){ ?>

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
		<?php while ($message = $res->fetch_assoc()){ ?>
			<tr>
				<td><?php echo $message['entrydate']; ?></td>
				<td><?php echo $message['expirationdate']; ?></td>
				<td><?php echo $message['heading']; ?></td>
				<td><?php echo $message['body']; ?></td>
				<td>
					<?php switch($message['display']){
						case 0:
							echo "Both";
							break;
						case 1:
							echo "Event";
							break;
						case 2:
							echo "Interactive";
							break;
						} ?>
				</td>
				<td><a href="addmessage.php?delete=<?php echo $message['messageid']; ?>">Delete</a></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<h2>No current message. Why not add a new one?</h2>
	<?php }?>
	<h1>Add Status Message</h1>
	<?php if ($m){?>
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
		<label for="display">Where to display</label>
		<select name="display" id="display">
			<option value="0">Both</option>
			<option value="1">Event</option>
			<option value="2">Interactive</option>
		</select><br>
		<input type="submit" value="Submit">
	</form>
	<script src="js/jquery-1.11.1.min.js"></script>
	<script src="js/jquery.timepicker.js"></script>
	<script src="js/jquery.plugin.js"></script>
	<script src="js/jquery.datepick.js"></script>
	<script>

		function padDigits(number, digits) {
			return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
		}
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
			jQuery('#entryTime').val(rounded.getHours() + ':' + padDigits(rounded.getMinutes(),2)).trigger('change');
});

	</script>
</body>
</html>