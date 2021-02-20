<?php

namespace app\admin\model\bbs;

use think\Db;
use think\Loader;
use think\Model;

class Report extends Model
{
    // 表名
    protected $name = 'bbs_report';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public $type_relation=[
      'thread'=>1,
      'post'=>2,
    ];
    // 追加属性
    protected $append = [
        'type_text',
        'status_text'
    ];

    protected static function init()
    {
        self::event('after_update', function ($row) {
            if($row->origin['status'] != $row->status){
                if($info = $row['type']==1?Thread::get($row->value_id):Post::get($row->value_id)){
                    if($row->status == 1){
                        $info->save(['report_number'=>Db::raw('report_number + 1')]);
                    }elseif ($info->report_number > 0){
                        $info->save(['report_number'=>Db::raw('report_number - 1')]);
                    }
                }

            }
        });
    }


    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }

    public function getTypeList()
    {
        return ['1' => __('Type 1'),'2' => __('Type 2')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusTextAttr($value, $data){
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 被举报用户(主题/帖子创建用户)
     * @return \think\model\relation\BelongsTo
     */
    public function valueuser(){
        return $this->belongsTo('app\admin\model\User','value_user_id',"id")->setEagerlyType(0)->joinType('LEFT');

    }

    /**
     * 举报用户
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('app\admin\model\User','user_id',"id")->setEagerlyType(0)->joinType('LEFT');
    }


}
