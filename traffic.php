<?

if ($_GET) {

	date_default_timezone_set ("America/Detroit");
	
	$query = "SELECT ID, name FROM space_labels";

	$result = mysqli_query ( $con , $query);
	
	$error = array();

	if ($result == FALSE) {
		$error[] = "Error retrieving space data, traffic not logged, call Kyle.";
	
	} else {

		while ($row = $result->fetch_row()) {
        	$splabels[$row[0]] = $row[1];
        	if ($_GET[$row[0]] === "#") {
        		$error[] = "Did not fill out " . $row[1];
        	}
    	}

	}
	
	if (!$_GET["initials"]) {
		$error[] = "Did not fill out initials.";
	
	}
	
	if (empty($error)) {
	
		$time = time();
	
		$query = "INSERT INTO entries (time, use) VALUES ($time, 0)";
	
		$result = mysqli_query ( $con , $query);
	
		if (!$result) { exit("Can't create line in entry table, Call Kyle.");}
	
		$entryID = mysqli_insert_id($con);
	
		foreach ($_GET as $ID=>$value) {
			if (array_key_exists($ID, $splabels)) {
		
				$valuestring = $value . "," . $entryID . "," . $ID;
				$query = "INSERT INTO traffic (level, entryID, space) VALUES ($valuestring)";
				$result = mysqli_query ( $con , $query);
				if (!$result) {  exit("Failed to input row:" . $valuestring );
				}
		
			}
	
		}	
	
		

}
?>
<HTML>

<HEAD>

<link rel="stylesheet" type="text/css" href="http://gvsu.edu/cms3/assets/741ECAAE-BD54-A816-71DAF591D1D7955C/libui.css" />

</HEAD>

<BODY>

<?php

include 'connection.php';




	$query = "SELECT ID, name FROM space_labels";

	$result = mysqli_query($con,$query);

	if ($result == FALSE) {
		exit("Error retrieving space data, call Kyle.");
	
	} else {

		while ($row = $result->fetch_row()) {
        	$splabels[$row[0]] = $row[1];
        
    	}

	}

	$query = "SELECT ID, name FROM traffic_labels";

	$result = mysqli_query ( $con , $query);

	if ($result == FALSE) {
		exit("Error retrieving traffic data, call Kyle.");
	
	} else {

		while ($row = $result->fetch_row()) {
        $trlabels[$row[0]] = $row[1];
    	}

	}

	mysqli_close($con);
	
	if (!empty($errors)) {
		foreach ($errors as $key=>$value) {
			echo "<P>" . $value . "</P>";
		
		}
		
	}
	
	echo <<<END

	<div class="lib-form">
	<form action="" method="get" name="traffic-form">
		
	
		<div class="line">
			<div class="span1 unit">
				<label for="initials">Initials</label>
END;
				
				echo "<input name='initials' type='text' MaxLength='3' value='";
				if ($_GET["initials"]) {echo $_GET["initials"];}
				
				echo "' required/>
			</div>
			
			<div class='span1 unit '>";
			


		
	foreach ($splabels as $ID=>$name) {
		echo "<P><label class='lib-inline'>$name</label>
			<select name=" . $ID . ">";
		echo "<option value='#'>----------</option>";
		foreach ($trlabels as $trID=>$trname) {					
			echo "<option value=" . $trID;
			if ($_GET[$ID] && $_GET[$ID] == $trID) {echo "selected";}
			echo  ">" . $trname . "</option>";
						
			}
		echo "</select></P>";
		}
			
	echo <<<END
				<input class="lib-button" name="submit" type="submit" value="Submit" />
			</div>	
		</div>
	</form>	
	</div>


	</form>
</div>

END;

} else {
	date_default_timezone_set ("America/Detroit");
	
	$query = "SELECT ID, name FROM space_labels";

	$result = mysqli_query ( $con , $query);
	
	$error = array();

	if ($result == FALSE) {
		exit("Error retrieving space data, call Kyle.");
	
	} else {

		while ($row = $result->fetch_row()) {
        	$splabels[$row[0]] = $row[1];
        	if ($_GET[$row[0]] === "#") {
        		$error[] = $row[0];
        	}
    	}

	}
	
	if (!$_GET["initials"]) {
		$error["initials"] = 0;
	
	}
	
	if (empty($error)) {
	
		$time = time();
	
		$query = "INSERT INTO entries (time, use) VALUES ($time, 0)";
	
		$result = mysqli_query ( $con , $query);
	
		if (!$result) { exit("Can't create line in entry table, Call Kyle.");}
	
		$entryID = mysqli_insert_id($con);
	
		foreach ($_GET as $ID=>$value) {
			if (array_key_exists($ID, $splabels)) {
		
				$valuestring = $value . "," . $entryID . "," . $ID;
				$query = "INSERT INTO traffic (level, entryID, space) VALUES ($valuestring)";
				$result = mysqli_query ( $con , $query);
				if (!$result) {  exit("Failed to input row:" . $valuestring );
				}
		
			}
	
		}	
	
		echo "<P>Traffic Logged.<P>";
		
	} else {
	
		header('Location: traffic.php' . $_SERVER(QUERY_STRING) . "&error=true");
	
	}

}

?>

</BODY>



</HTML>

