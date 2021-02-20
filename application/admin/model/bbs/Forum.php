<?php

namespace app\admin\model\bbs;

use think\Model;

class Forum extends Model
{
    // 表名
    protected $name = 'bbs_forum';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'status_text',
        'thread_status_text'
    ];

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getThreadStatusList()
    {
        return ['0' => __('Thread_status 0'), '1' => __('Thread_status 1'), '2' => __('Thread_status 2')];
    }

    public function getThreadStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['thread_status']) ? $data['thread_status'] : '');
        $list = $this->getThreadStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }





}
