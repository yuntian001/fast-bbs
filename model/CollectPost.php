<?php
/**
 * Created by PhpStorm.
 * User: yun
 * Date: 2019-01-26
 * Time: 11:08
 */

namespace addons\bbs\model;

use think\Db;
use think\Model;

class CollectPost extends Model
{
    protected $name = 'bbs_collect_post';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    public function user()
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function post(){
        return $this->belongsTo(Post::class,'post_id','id')->setEagerlyType(0);
    }
}