<?php
namespace addons\bbs\library;

use addons\bbs\library\htmlpurifier\HTMLPurifier;

class Common
{
    /**
     * 计算几分钟前、几小时前、几天前、几月前、年-月-日。
     * $agoTime string Unix时间
     * @author tangxinzhuan
     * @version 2016-10-28
     */
    static function time_ago($agoTime)
    {
        if ($agoTime <= 0) {
            return '--';
        }
        $agoTime = (int)$agoTime;

        // 计算出当前日期时间到之前的日期时间的秒数，以便进行下一步的计算
        $time = time() - $agoTime;

        if ($time >= 31104000) { // N年前
            return date('Y-m-d',$agoTime);
        }
        if ($time >= 2592000) { // N月前
            $num = (int)($time / 2592000);
            return $num . '月前';
        }
        if ($time >= 86400) { // N天前
            $num = (int)($time / 86400);
            return $num . '天前';
        }
        if ($time >= 3600) { // N小时前
            $num = (int)($time / 3600);
            return $num . '小时前';
        }
        if ($time > 60) { // N分钟前
            $num = (int)($time / 60);
            return $num . '分钟前';
        }
        return '几秒钟前';
    }

    /**
     * html过滤
     * @param $data
     * @return string
     */
    static function purify_html($data){
        $cacheDir = RUNTIME_PATH.'/htmlPurifier';
        !is_dir($cacheDir) && mkdir($cacheDir, 0755, true);
        $purifier = new HTMLPurifier(['Cache.SerializerPath'=>$cacheDir]);
        return $purifier->purify($data);
    }
}