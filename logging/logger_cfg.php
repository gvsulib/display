<?php

if (!in_array($method = $_SERVER['REQUEST_METHOD'], array('GET', 'POST')))
    return;
require 'LoggerControl.php';

if ($method == 'GET') {
    $ret = LoggerConfig::get();
    if ($ret) {
        header('Content-Type: application/json');
        echo json_encode($ret);
    }
    else
        header(LoggerConfig::buildFailCode());
}
else if ($HTTP_RAW_POST_DATA) {
    if (LoggerConfig::save(json_decode($HTTP_RAW_POST_DATA, true))) {
        $obj = new LoggerControl();
        $obj->cleanFiles();
    }
    else
        header(LoggerConfig::buildFailCode());
}

