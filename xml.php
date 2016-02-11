<?
 $outPut = new SimpleXMLElement("<bookings></bookings>");



for ($i = 0; $i < 4; $i++) {


$test = $outPut->addChild('test');

$test->othertest = "this is an ampersand &";
$test->roomcode = "code";

}

echo($outPut->asXML());


?>
