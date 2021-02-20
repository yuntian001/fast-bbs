<?php
namespace app\index\controller\bbs;

use addons\bbs\model\CollectPost;
use addons\bbs\model\CollectThread;
use addons\bbs\model\Forum;
use addons\bbs\model\Post;
use app\admin\model\bbs\Thread;
use app\common\controller\Frontend;
use app\common\model\User;
use think\Loader;

class My extends Frontend{

    protected $layout = 'default';
    protected $noNeedRight='*';

    /**
     * 我的收藏
     * @return mixed
     */
    public function collect($type = 0){
        switch ($type){
            case 0://主题
                $model = new CollectThread();
                $table =  $model->getTable();
                $list = $model->field("{$table}.*,user.nickname as user_nickname,user.avatar as user_avatar,thread.createtime as thread_createtime,thread.brief as thread_brief,thread.title as thread_title,thread.id as thread_id")
                    ->join(Thread::getTable().' thread',$table.'.thread_id = thread.id')->whereNull('thread.deletetime')
                    ->join(User::getTable().' user','thread.user_id = user.id')
                    ->where($table.'.user_id',$this->auth->id)
                    ->order($table.'.createtime','DESC')->paginate(10);
                break;
            case 1://回帖
                $model = new CollectPost();
                $table =  $model->getTable();
                $list = $model->field("{$table}.*,user.nickname as user_nickname,user.avatar as user_avatar,post.createtime as post_createtime,post.brief as post_brief,thread.title,thread.id as thread_id")
                    ->join(Post::getTable().' post',$table.'.post_id = post.id')->whereNull('post.deletetime')
                    ->join(User::getTable().' user','post.user_id = user.id')
                    ->join(Thread::getTable().' thread','post.thread_id = thread.id')->whereNull('thread.deletetime')
                    ->where($table.'.user_id',$this->auth->id)
                    ->order($table.'.createtime','DESC')->paginate(10);
                break;
        }
        return $this->fetch('',['type'=>$type,'list'=>$list]);
    }

    /**
     * 我的发布
     * @param int $type
     */
    public function createList($type = 0){
        switch ($type) {
            case 0://主题
                $list = Thread::withTrashed()->where('user_id', $this->auth->id)
                    ->order('id', 'DESC')->paginate(10);
                break;
            case 1:
                $list = Post::withTrashed()->with('thread')->where('user_id', $this->auth->id)
                    ->order('id', 'DESC')->paginate(10);
                break;
        }
        return $this->fetch('',['type'=>$type,'list'=>$list]);
    }

    public function forum(){
        $list = Forum::where('find_in_set(:id,mod_user_ids)',['id'=>$this->auth->id])->order('id', 'DESC')->paginate(10);
        return $this->fetch('',['list'=>$list]);

    }
}