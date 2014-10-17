<?php
include('connection.php');
$data;
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set("log_errors", 1);
ini_set("error_log", "/tmp/log.log");


/*
echo '<pre>';
print_r($xml);
echo '</pre>';
*/

$atrium = array(
    "Atrium: Living Room" => getSpaceTrafficFromID(1),
    "Atrium: Multi-Purpose Room" => getSpaceTrafficFromID(2),
    "Atrium: Exhibition Room" => getSpaceTrafficFromID(3),
    "Atrium: Tables under Stairs" => getSpaceTrafficFromID(4),
    "Atrium: Seating Outside 001 and 002" => getSpaceTrafficFromID(5)
);

$first = array(
    "1st Floor: Knowledge Market" => getSpaceTrafficFromID(6),
    "1st Floor: Cafe Seating" => getSpaceTrafficFromID(7),
);

$second = array(
    "2nd Floor: West Wing (Collaborative Space)" => getSpaceTrafficFromID(8),
    "2nd Floor: East Wing (Quiet Space)" => getSpaceTrafficFromID(9)
);

$third = array(
    "3rd Floor: West Wing (Collaborative Space)" => getSpaceTrafficFromID(10),
    "3rd Floor: East Wing (Quiet Space)" => getSpaceTrafficFromID(11),
    "3rd Floor: Reading Room" => getSpaceTrafficFromID(12),
    "3rd Floor: Innovation Zone" => getSpaceTrafficFromID(13),
);

$fourth = array(
    "4th Floor: West Wing (Collaborative Space)" => getSpaceTrafficFromID(14),
    "4th Floor: East Wing (Quiet Space)" => getSpaceTrafficFromID(15),
    "4th Floor: Reading Room" => getSpaceTrafficFromID(16)
);



if (isset($_GET['floor'])) {

    switch ($_GET['floor']) {
        case 'atrium':
            echo json_encode($atrium);
            break;
        case 'first':
            echo json_encode($first);
            break;
        case 'second':
            echo json_encode($second);
            break;
        case 'third':
            echo json_encode($third);
            break;
        case 'fourth':
            echo json_encode($fourth);
            break;
    }

} else {
    echo json_encode(array_merge($atrium, $first, $second, $third, $fourth));
}

function loadDatafromDb(){
    $con = getConnection();
    global $data;
    if (1==1){
        $query = "SELECT space, level FROM traffic WHERE entryID = (select max(entryID) from entries);";
        $db_result = $con->query($query);
        while ($space = $db_result->fetch_row()) {
            $data[$space[0]] = $space[1];
        }
        return $data;
    } else {
        return $data;
    }
}

function getSpaceTrafficFromID($id){
    $data = loadDatafromDb();
    return $data[$id];
}
?>
