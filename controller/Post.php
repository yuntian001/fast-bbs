<?php


namespace addons\bbs\controller;


use addons\bbs\library\Common;
use addons\bbs\model\CollectPost;
use addons\bbs\model\Forum;
use addons\bbs\model\PraisePost;
use addons\bbs\model\Report;
use think\Db;

class Post extends Base
{

    protected $noNeedLogin = ['index','info','commentList'];

    /**
     * 回复帖子
     * @throws \think\exception\DbException
     */
    public function comment()
    {
        if(\request()->isPost()){
            if(!$this->auth->mobile){
                return $this->error('绑定手机号后才可回复');
            }
            $id = input('id', 0);
            $m_post = new \addons\bbs\model\Post();
            $post = $m_post->get($id);
            if (!$post) {
                return $this->error('错误的参数');
            }
            $m_thread = new \addons\bbs\model\Thread();
            $thread = $m_thread->findOrFail($post->thread_id);
            if ($thread->status == 1) {
                return $this->error('该主题禁止回复');
            }
            $data['content_html'] = input('post.content', '', 'trim');
            if (empty($data['content_html'])) {
                return $this->error('内容不能为空');
            }
            $data['brief'] = mb_substr(strip_tags($data['content_html']), 0, 100);
            $user_id = $this->auth->getUser()['id'];
            $data = [
                'parent_floor' => $post->first_id?$post->floor:0,
                'parent_id' => $id,
                'id_path' => $post->id_path.$id.',',
                'forum_id' => $thread->forum_id,
                'thread_id' => $thread->id,
                'user_id' => $user_id,
                'user_ip' => $this->request->ip(),
                'brief' => mb_substr(strip_tags($data['content_html']), 0, 80),
                'content_html' => $data['content_html'],
                'content_fmt' => Common::purify_html($data['content_html']),
                'first_id'=>(int)$post->first_id?:$post->id,
            ];
            Db::startTrans();
            $data['floor'] = $m_post->lock(true)->where('first_id',$data['first_id'])->order('id', 'desc')->value('floor') + 1;
            $m_post = \addons\bbs\model\Post::create($data);
            Db::commit();
            $m_thread = new \addons\bbs\model\Thread();
            $m_thread->save(['post_number' => Db::raw('post_number + 1'),'last_time'=>time(),'last_user_id'=>$user_id,'last_post_id'=>$m_post->id], ['id' => $thread->id]);

//所有父级楼层都会增加回帖数            \addons\bbs\model\Post::whereIn('id',explode(',',trim($data['id_path'],',')))->update(['post_number' => Db::raw('post_number + 1'),'last_time'=>time(),'last_user_id'=>$user_id,'last_post_id'=>$m_post->id]);
//          仅首层和父层会受到更新的影响
            \addons\bbs\model\Post::whereIn('id',[$post->first_id,$id])->update(['post_number' => Db::raw('post_number + 1'),'last_time'=>time(),'last_user_id'=>$user_id,'last_post_id'=>$m_post->id]);
            Forum::update(['post_number' => Db::raw('post_number + 1'), 'today_posts' => Db::raw('today_posts + 1')], ['id' => $thread->forum_id]);
            return $this->success('回复成功');
        }
        return $this->error('访问异常');
    }

    /**
     * 回复列表
     * @throws \think\exception\DbException
     */
    public function commentList(){
        if(!$this->request->isAjax()){
            return $this->error('请求异常');
        }
        $id = input('id', 0);
        $where = [];
        $where['first_id'] = $id;
        $list = \addons\bbs\model\Post::withTrashed()->with('praise,collect')->where($where)->with([
            'user' => function ($query) {
                return $query->withField('nickname,id,avatar,username');
            },
            'parent.user',
        ])->order('id', 'ASC')->paginate(self::LIST_ROWS['post_comment'],false)->appends('id',$id);
        foreach ($list as $value) {
            if ($value->trashed()) {
                $value['content_html'] = $value['content_fmt'] = '<em>该楼层因违规已被删除</em>';
            }
        }
        $data = $list->toArray();
        $data['page'] = $list->render();
        return $this->success('success', '', ['list' => $data]);
    }


    /**
     * 点赞帖子
     * @throws \think\Exception
     */
    public function praise()
    {
        if(!$this->request->isAjax()){
            return $this->error('请求异常');
        }
        $id = input('param.id/d', 0);
        if (!$id) {
            return $this->error('错误的参数');
        }
        $m_post = new \addons\bbs\model\Post();
        $post = $m_post->findOrFail($id);
        $praise_number = $post->praise_number;
        Db::startTrans();
        $m_praise = new PraisePost();
        if ($m_praise->where('user_id', $this->auth->getUser()['id'])->where('post_id', $id)->lock(true)->count()) {
            Db::rollback();
            return $this->success('您已经赞过了',null,$praise_number);
        }
        $m_praise->data(['user_id' => $this->auth->getUser()['id'], 'post_id' => $id])->save();
        $post->save(['praise_number'=>Db::raw('praise_number + 1')]);
        Db::commit();
        return $this->success('点赞成功',null,$praise_number+1);
    }


    /**
     * 举报
     */
    public function report()
    {
        $data['value_id'] = input('post.id/d', 0);
        $data['type'] = 2;
        $data['describe'] = input('post.describe', '', 'trim,htmlspecialchars');
        foreach ($data as $value) {
            if (!$value) {
                return $this->error('错误的参数');
            }
        }
        if (mb_strlen($data['describe']) > 50) {
            return $this->error('最多50个字');
        }
        $value_model = new \addons\bbs\model\Post();
        $info = $value_model->find($data['value_id']);
        if (!$info) {
            return $this->error('错误的参数');
        }
        $data['value_user_id'] = $info->user_id;
        $data['user_id'] = $this->auth->getUser()['id'];
        $m_report = new Report();
        $report = $m_report->where(['type' => $data['type'], 'value_id' => $data['value_id'], 'value_user_id' => $data['value_user_id'], 'user_id' => $data['user_id']])->find();
        if ($report) {
            $report->describe = $data['describe'];
            $report->save();
        } else {
            $m_report->data($data)->save();
            $info->save(['report_number'=>Db::raw('report_number + 1')]);
        }
        return $this->success('举报成功');
    }

    /**
     * 取消点赞
     */
    public function noPraise()
    {
        if(!$this->request->isAjax()){
            return $this->error('请求异常');
        }
        $id = input('post.id/d', 0);
        if (!$id) {
            return $this->error('错误的参数');
        }
        $m_praise = new PraisePost();
        $m_post = new \addons\bbs\model\Post();
        $post = $m_post->findOrFail($id);
        $praise_number = $post->praise_number;
        if ($m_praise->where('user_id', $this->auth->getUser()['id'])->where('post_id', $id)->delete()) {
            $post->save(['praise_number'=>Db::raw('praise_number - 1')]);
            return $this->success('取消点赞成功',null,$praise_number-1);
        }
        return $this->success('您还没有赞过',null,$praise_number);
    }

    /**
     * 收藏帖子
     * @throws \think\Exception
     */
    public function collect()
    {
        if(!$this->request->isAjax()){
            return $this->error('请求异常');
        }
        $id = input('post.id/d', 0);
        if (!$id) {
            return $this->error('错误的参数');
        }
        $m_post = new \addons\bbs\model\Post();
        $post = $m_post->findOrFail($id);
        $collect_number = $post->collect_number;
        $m_collect = new CollectPost();
        Db::startTrans();
        if ($m_collect->where('user_id', $this->auth->getUser()['id'])->where('post_id', $id)->lock(true)->count()) {
            Db::rollback();
            return $this->success('您已经收藏过了',$collect_number);
        }
        $m_collect->data(['user_id' => $this->auth->getUser()['id'], 'post_id' => $id])->save();
        $post->save(['collect_number'=>Db::raw('collect_number + 1')]);
        Db::commit();
        return $this->success('收藏成功',null,$collect_number+1);
    }

    /**
     * 取消收藏
     */
    public function noCollect()
    {
        if(!$this->request->isAjax()){
            return $this->error('请求异常');
        }
        $id = input('post.id/d', 0);
        if (!$id) {
            return $this->error('错误的参数');
        }
        $m_post = new \addons\bbs\model\Post();
        $post = $m_post->findOrFail($id);
        $collect_number = $post->collect_number;
        $m_collect = new CollectPost();
        if ($m_collect->where('user_id', $this->auth->getUser()['id'])->where('post_id', $id)->delete()) {
            $post->save(['collect_number'=>Db::raw('collect_number - 1')]);
            return $this->success('取消收藏成功',null,$collect_number-1);
        }
        return $this->success('您还没有收藏过',null,$collect_number);
    }
}