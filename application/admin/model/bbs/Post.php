<?php

namespace app\admin\model\bbs;

use think\Model;
use traits\model\SoftDelete;

class Post extends \addons\bbs\model\Post
{
    use SoftDelete;

    // 表名
    protected $name = 'bbs_post';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    public $deleteTime = 'deletetime';




    /**
     * 关联板块
     * @return \think\model\relation\BelongsTo
     */
    public function forum()
    {
        return $this->belongsTo('Forum')->setEagerlyType(0);
    }

    /**
     * 关联板块
     * @return \think\model\relation\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo('Thread')->setEagerlyType(0);
    }

    /**
     * 关联创建用户
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('\app\admin\model\User','user_id','id')->setEagerlyType(0);
    }



}
