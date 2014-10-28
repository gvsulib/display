<?php

require 'JsLogFlush.php';

$obj = new JsLogFlush(array(
    'interval' => 1,
    'expire' => 0.5,
    'dir' => 'logs/',
));
if ($ret = $obj->process()) {
    header('Content-Type: text/javascript');
    echo $ret;
}