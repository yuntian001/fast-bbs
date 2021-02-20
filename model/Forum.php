<?php
/**
 * Created by PhpStorm.
 * User: yun
 * Date: 2019-01-26
 * Time: 08:45
 */

namespace addons\bbs\model;

use think\Model;

class Forum extends Model
{
    protected $name = 'bbs_forum';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    protected $append =[
        'mod_user_id_arr',
    ];

    public function getModUserIdArrAttr($value, $data){
        return isset($data['mod_user_ids'])?explode(',',$data['mod_user_ids']):[];
    }

    protected function getCreatetimeAttr($value)
    {
        return $value && is_numeric($value) ? date('Y-m-d H:i', $value) : $value;
    }

    protected function getUpdatetimeAttr($value)
    {
        return $value && is_numeric($value) ? date('Y-m-d H:i', $value) : $value;
    }
}