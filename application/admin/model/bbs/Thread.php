<?php

namespace app\admin\model\bbs;

use think\Db;
use think\Model;
use traits\model\SoftDelete;

class Thread extends \addons\bbs\model\Thread
{
    use SoftDelete;

    // 表名
    protected $name = 'bbs_thread';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    public $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'is_elite_text',
        'last_time_text',
        'status_text',
        'url'
    ];


    public function getIsEliteList()
    {
        return ['1' => __('Is_elite 1'), '0' => __('Is_elite 0')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getIsEliteTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_elite']) ? $data['is_elite'] : '');
        $list = $this->getIsEliteList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getLastTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['last_time']) ? $data['last_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setLastTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    /**
     * 关联板块
     * @return \think\model\relation\BelongsTo
     */
    public function forum()
    {
        return $this->belongsTo('Forum')->setEagerlyType(0)->joinType('LEFT');
    }

    /**
     * 关联创建用户
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('\app\admin\model\User','user_id','id')->setEagerlyType(0);
    }

    protected static function init()
    {
        self::event('after_update', function ($row) {
            if(is_null($row->origin[$row->deleteTime]) && $row->{$row->deleteTime} > 0){
                $where = ['id'=>$row->forum_id,'thread_number'=>['>',0]];
                //软删除
                $up_forum = ['thread_number'=>Db::raw('thread_number - 1')];
                if($row->getData($row->createTime) >= strtotime(date('Y-m-d'))){
                    $up_forum['today_threads'] = Db::raw('today_threads - 1');
                }
                \app\admin\model\bbs\Forum::update($up_forum,$where);
            }elseif ($row->origin[$row->deleteTime] > 0 && is_null($row->{$row->deleteTime})){
                //还原
                $up_forum = ['thread_number'=>Db::raw('thread_number + 1')];
                if($row->getData($row->createTime) >= strtotime(date('Y-m-d'))){
                    $up_forum['today_threads'] = Db::raw('today_threads + 1');
                }
                \app\admin\model\bbs\Forum::update($up_forum,['id'=>$row->forum_id]);
            }
        });
    }

}
