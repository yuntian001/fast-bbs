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
use think\Model;
use traits\model\SoftDelete;

class Post extends Model
{
    use SoftDelete;

    protected $name = 'bbs_post';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    public $deleteTime = 'deletetime';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 追加属性
    protected $append = [
      'url'
    ];

    public function getUrlAttr()
    {
        return addon_url('bbs/thread/info',['id'=>$this->thread_id,'post_id'=>$this->id]);
    }

    public function user()
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function parent(){
        return $this->belongsTo(Post::class, 'parent_id', 'id');
    }

    public function thread(){
        return $this->belongsTo(Thread::class, 'thread_id', 'id');
    }

    /**
     * 关联当前登录用户的点赞记录
     * @return \think\model\relation\HasOne
     */
    public function praise(){
        $result = $this->hasOne(PraisePost::class,'post_id','id');
        if(Auth::instance()->isLogin()){
            $result = $result->where('user_id',Auth::instance()->getUser()['id']);
        }else{
            $result = $result->where('user_id',0);
        }
        return $result;
    }

    /**
     * 关联当前登录用户的手册记录
     * @return \think\model\relation\HasOne
     */
    public function collect(){
        $result = $this->hasOne(CollectPost::class,'post_id','id');
        if(Auth::instance()->isLogin()){
            $result = $result->where('user_id',Auth::instance()->getUser()['id']);
        }else{
            $result = $result->where('user_id',0);
        }
        return $result;
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