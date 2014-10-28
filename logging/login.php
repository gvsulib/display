<?php
session_start();
include 'password.php';
if ($_SESSION['loggedIn'] == true){
	header('location: index.php');
}
if (isset($_POST['password'])) {
	if (sha1($_POST['password']) == $password){
		$_SESSION['loggedIn'] = true;
		header('location: index.php');
	} else {
		$error = "Invalid password.";
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>GVSU MIP Library Traffic</title>
	<style type="text/css">
	html,body{
		font-family: Helvetica;
	}
	</style>
</head>
<body>
	<form action='login.php' method="POST">
		<div style="margin: 0 auto;text-align: center;width:500px">
			<h2>Enter password:</h2>
			<h3 style="color:red;"><?php echo $error;?></h3>
			<input name="password" type="password">
			<input type="submit" value="Submit">
		</div>
	</form>

</body>
</html>	
