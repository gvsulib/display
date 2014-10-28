<?php

if (!in_array($method = $_SERVER['REQUEST_METHOD'], array('GET', 'POST')))
    return;
require 'LoggerControl.php';

$obj = new LoggerControl();
if ($method == 'GET') {
    $ret = $obj->getList(isset($_GET['stamp'])? $_GET['stamp'] : 0);
    if ($ret) {
        header('Content-Type: application/json');
        echo json_encode($ret);
    }
    else
        header(LoggerConfig::buildFailCode());
}
else if (isset($_POST['id'])) {
    $obj->deleteFile($_POST['id']);
}
