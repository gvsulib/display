<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$url = 'https://spreadsheets.google.com/feeds/list/0AtK8MmFmQfL1dFMzazNNRzZBRDNkOGdOLVBTelNSLXc/od4/public/basic';

$xml = new SimpleXMLElement(file_get_contents($url));

/*
echo '<pre>';
print_r($xml);
echo '</pre>';
*/

$atrium = array(
    "Atrium: Living Room" => GetTraffic($xml->entry[0]->content->__toString()),
    "Atrium: Multi-Purpose Room" => GetTraffic($xml->entry[1]->content->__toString()),
    "Atrium: Exhibition Room" => GetTraffic($xml->entry[2]->content->__toString()),
    "Atrium: Tables under Stairs" => GetTraffic($xml->entry[3]->content->__toString()),
    "Atrium: Seating Outside 001 and 002" => GetTraffic($xml->entry[4]->content->__toString())
);

$first = array(
    "1st Floor: Knowledge Market" => GetTraffic($xml->entry[5]->content->__toString()),
    "1st Floor: Cafe Seating" => GetTraffic($xml->entry[6]->content->__toString())
);

$second = array(
    "2nd Floor: West Wing (Collaborative Space)" => GetTraffic($xml->entry[7]->content->__toString()),
    "2nd Floor: East Wing (Quiet Space)" => GetTraffic($xml->entry[8]->content->__toString())
);

$third = array(
    "3rd Floor: West Wing (Collaborative Space)" => GetTraffic($xml->entry[9]->content->__toString()),
    "3rd Floor: East Wing (Quiet Space)" => GetTraffic($xml->entry[10]->content->__toString()),
    "3rd Floor: Reading Room" => GetTraffic($xml->entry[11]->content->__toString()),
    "3rd Floor: Innovation Zone" => GetTraffic($xml->entry[12]->content->__toString())
);

$fourth = array(
    "4th Floor: West Wing (Collaborative Space)" => GetTraffic($xml->entry[13]->content->__toString()),
    "4th Floor: East Wing (Quiet Space)" => GetTraffic($xml->entry[14]->content->__toString()),
    "4th Floor: Reading Room" => GetTraffic($xml->entry[15]->content->__toString())
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


function GetTraffic($pool) {

    $var1 = ": ";
    $var2 = ",";

    $temp1 = strpos($pool,$var1)+strlen($var1);
    $result = substr($pool,$temp1,strlen($pool));
    $dd=strpos($result,$var2);
    if($dd == 0){
        $dd = strlen($result);
    }

    return substr($result,0,$dd);
}
