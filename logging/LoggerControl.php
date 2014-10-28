<?php
require_once dirname(__FILE__). '/LoggerConfig.php';

class LoggerControl
{
    const FILE_LIST = 'cache/filelist.dat';

    protected $oLf = 0;
    protected $aCfg = 0;

    function __construct() {
        $this->oLf = new JsLogFlush();
        $this->aCfg = LoggerConfig::get(true);
    }

    function saveCfg($json) {
        LoggerConfig::save($json);
    }

    function getList($tLast = 0) {
        if (!$this->aCfg) return false;
        ($a_list = $this->restoreList()) || ($a_list = array());
        $t_last = 0;
        if (isset($a_list['stamp'])) {
            $t_last = $a_list['stamp'];
            unset($a_list['stamp']);
        }
        $a_files_old = array_keys($a_list); $n0 = count($a_files_old);
        $a_files = $this->scanList(); $n = count($a_files);
        if ($tLast && $n == $n0 &&
            (!$n || count(array_intersect($a_files, $a_files_old)) == $n)) {
            if ($t_last && $t_last <= $tLast) return false;
            $a_list['stamp'] = $t_last;
            return $a_list;
        }
        if ($n) {
            $a_list_new = array_combine($a_files, $a_files);
            $a_list = array_merge(
                array_intersect_key($a_list, $a_list_new),
                array_filter(array_map(
                    array($this, 'readFileHead'),
                    array_diff_key($a_list_new, $a_list)
                ))
            );
        }
        else {
            $a_list = array();
        }
        $a_list['stamp'] = time();
        $this->saveList($a_list);
        return $a_list;
    }

    function cleanFiles() {
        $a_urls = $this->aCfg['app_urls'];
        if (!$a_urls) return;
        $a_list = $this->getList();
        if (!$a_list) return;
        $n = 0;
        foreach ($a_list as $file => $a_nfo) {
            if ($file == 'stamp' || in_array($a_nfo[0], $a_urls)) continue;
            unlink($this->aCfg['dir'].$file); $n++;
        }
        if ($n) $this->getList();
    }

    function deleteFile($file) {
        if (!$file || !file_exists($file = $this->aCfg['dir'].$file))
            return false;
        unlink($file);
        return true;
    }

    protected function scanList() {
        return is_array($a_files = scandir(rtrim($this->aCfg['dir'])))?
            array_filter($a_files, array($this->oLf, 'validateFilename')) :
            array();
    }

    protected function readFileHead($file) {
        return $this->oLf->readFileHead($this->aCfg['dir']. $file);
    }

    protected function restoreList() {
        if (!is_readable($file = LoggerConfig::abspath(self::FILE_LIST)))
            return false;
        if (!($s = file_get_contents($file)))
            return false;
        $a_list = unserialize($s);
        return is_array($a_list)? $a_list : false;
    }

    protected function saveList($aList) {
        if ($aList) file_put_contents(LoggerConfig::abspath(self::FILE_LIST),
            serialize($aList)
        );
    }
}
