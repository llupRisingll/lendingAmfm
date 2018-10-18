<?php
namespace Plugins;

class RecordTime {
    private static $ruStart = 0;
    private static $ruEnd = 0;

    public static function start(){
        self::$ruStart = getrusage();
    }

    public static function end(){
        self::$ruEnd = getrusage();
    }

    private static function rutime($ru, $rus) {
        $utime = ($ru["ru_utime.tv_sec"]*1000 + intval($ru["ru_utime.tv_usec"]/1000))
            - ($rus["ru_utime.tv_sec"]*1000 + intval($rus["ru_utime.tv_usec"]/1000));
        $stime = ($ru["ru_stime.tv_sec"]*1000 + intval($ru["ru_stime.tv_usec"]/1000))
            - ($rus["ru_stime.tv_sec"]*1000 + intval($rus["ru_stime.tv_usec"]/1000));
        return $utime + $stime;
    }

    public static function getTotal(){
        return self::rutime(self::$ruEnd, self::$ruStart);
    }
}
