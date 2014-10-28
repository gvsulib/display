<?php
require_once 'JsLogFlush.php';

class LoggerConfig
{
    const FILE_CFG = 'cache/config.dat';

    static protected $aCfg = array(
        'dir' => 'logs/',
        'app_urls' => array(),
        'buff_size' => 10000,
        'interval' => 1,
        'interval_bk' => 30,
        'expire' => 3,
        'requests_limit' => 0,
        'log_timeshifts' => 1,
        'subst_console' => 1,
        'minify' => 0,
    );

    static function get($bAbsDir = false) {
        if (!is_readable($file = self::abspath(self::FILE_CFG)))
            return self::$aCfg;
        if (!($s = file_get_contents($file)))
            return self::$aCfg;
        $arr = unserialize($s);
        $ret = is_array($arr)? array_merge(self::$aCfg, $arr) : self::$aCfg;
        if ($bAbsDir) $ret['dir'] = self::abspath($ret['dir']);
        return $ret;
    }

    static function save($arr) {
        if (!$arr || array_diff_key($arr, self::$aCfg))
            return false;
        file_put_contents(self::abspath(self::FILE_CFG),
            serialize(array_map(array(self, 'fixVal'), $arr))
        );
        return true;
    }

    static function fixVal($v) {
        return is_string($v) && is_numeric($v)? intval($v) : $v;
    }

    static function buildFailCode() {
        return $_SERVER["SERVER_PROTOCOL"].' 404 Not Found';
    }

    static function abspath($path) {
        return dirname(__FILE__). '/'. $path;
    }
}
