<?php
function checkIP(){
    $ip = explode(".",$_SERVER['REMOTE_ADDR']);
    var_dump($ip);
    if (!(($ip[0] == '::1') ||
        ($ip[0] == "148" && $ip[1] == "61") ||
        ($ip[0] == "35" && $ip[1] == "40") ||
        ($ip[0] == "207" && $ip[1] == "72" &&
            ($ip[2] >= 160 && $ip[2] <= 191)
        ))
    ){
        die();
    }
}
checkIP();
?>