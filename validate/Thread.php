<?php

namespace addons\bbs\validate;

use think\Validate;

class Thread extends Validate
{

    /**
     * 正则
     */
    protected $regex = ['format' => '[a-z0-9_\/]+'];

    /**
     * 验证规则
     */
    protected $rule = [
        'title|主题'  => 'require|length:1,50',
        'forum_id|板块' => 'require|number',
        'content_html|内容'=>'require',
    ];

    /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'create'=>[],
        'update'=>[],
    ];


}
