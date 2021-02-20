<?php
/**
 * Created by PhpStorm.
 * User: yun
 * Date: 2019-01-26
 * Time: 11:08
 */

namespace addons\bbs\model;

use addons\bbs\library\Common;
use app\common\library\Auth;
use think\Db;
use think\Model;
use traits\model\SoftDelete;

class Thread extends Model
{
    use SoftDelete;

    protected $name = 'bbs_thread';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    public $deleteTime = 'deletetime';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    protected $append=[
        'createtime_text',
        'url',
        'last_time_text',
    ];

    public function getCreatetimeTextAttr(){
        return Common::time_ago($this->getData('createtime'));
    }

    public function getLastTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['last_time']) ? $data['last_time'] : '');
        return Common::time_ago($value);
    }

    protected function getLastTimeAttr($value)
    {
        return $value && is_numeric($value) ? date('Y-m-d H:i', $value) : '--';
    }

    public function getUrlAttr()
    {
        return addon_url('bbs/thread/info',['id'=>$this->id]);
    }

    /**
     * 关联当前登录用户的点赞记录
     * @return \think\model\relation\HasOne
     */
    public function praise(){
        $result = $this->hasOne(PraiseThread::class,'thread_id','id');
        if(Auth::instance()->isLogin()){
            $result = $result->where('user_id',Auth::instance()->getUser()['id']);
        }else{
            $result = $result->where('user_id',0);
        }
        return $result;
    }

    /**
     * 关联当前登录用户的收藏记录
     * @return \think\model\relation\HasOne
     */
    public function collect(){
        $result = $this->hasOne(CollectThread::class,'thread_id','id');
        if(Auth::instance()->isLogin()){
            $result = $result->where('user_id',Auth::instance()->getUser()['id']);
        }else{
            $result = $result->where('user_id',0);
        }
        return $result;
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class, 'forum_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }


    protected function getCreatetimeAttr($value)
    {
        return $value && is_numeric($value) ? date('Y-m-d H:i', $value) : $value;
    }

    protected function getUpdatetimeAttr($value)
    {
        return $value && is_numeric($value) ? date('Y-m-d H:i', $value) : $value;
    }


    /**
     * 设为精华
     */
    public function setElite()
    {
        if (!$this->id) {
            throw new \Exception(__('请获取单个实例后调用'));
        }
        if ($this->is_elite) {
            return false;
        }
        $this->is_elite = 1;
        \db()->startTrans();
        if ($this->save() && Forum::update(['elite_number' => Db::raw('elite_number + 1')], ['id' => $this->forum_id])) {
            \db()->commit();
            return true;
        }
        \db()->rollback();
        return false;
    }

    /**
     * 取消精华
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function delElite()
    {
        if (!$this->id) {
            throw new \Exception(__('请获取单个实例后调用'));
        }
        if ($this->is_elite == 0) {
            return false;
        }
        $this->is_elite = 0;
        \db()->startTrans();
        if ($this->save() && Forum::update(['elite_number' => Db::raw('elite_number - 1')], ['id' => $this->forum_id])) {
            \db()->commit();
            return true;
        }
        \db()->rollback();
        return false;
    }
}