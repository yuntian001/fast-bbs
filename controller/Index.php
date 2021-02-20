<?php

namespace addons\bbs\controller;

class Index extends Base
{

    protected $noNeedLogin = ['*'];


    public function index()
    {
        $m_thread = new \addons\bbs\model\Thread();
        $id = input('id', 0);
        $type = input('type', 1);
        $sort_type = input('sort_type', 0);
        $keywords = input('keywords/s','');
        $where['all_top'] = 0;
        if ($type == 2) {
            $where['is_elite'] = 1;
        } elseif ($type == 3) {
            $where['post_number'] = 0;
        }
        if ($id != 0) {
            $where['forum_id'] = $id;
            $where['top'] = 0;
        }
        if($keywords){
            $where['title'] = ['LIKE','%'.$keywords.'%'];
        }
        if($sort_type == 1){
            $order['last_time'] = 'DESC';
        }else{
            $order['createtime'] = 'DESC';

        }
        $top_list = [];
        $all_top_list = $m_thread->where('all_top', '>', '0')->with([
            'user' => function ($query) {
                return $query->withField('nickname,id,avatar,username');
            },
            'forum' => function ($query) {
                return $query->withField('id,name');
            },
            'praise','collect'
        ])->order('all_top', 'DESC')->select();
        if ($id) {
            $top_list = $m_thread
                ->where(['top' => ['>', 0], 'all_top' => 0])
                ->with([
                    'user' => function ($query) {
                        return $query->withField('nickname,id,avatar,username');
                    },
                    'forum' => function ($query) {
                        return $query->withField('id,name');
                    },
                    'praise','collect'
                ])->order('top', 'DESC')->select();
        }

        foreach ($all_top_list as &$value) {
            $value ['is_top'] = 1;
        }
        foreach ($top_list as &$value) {
            $value ['is_top'] = 1;
        }

        $list = $m_thread->where($where)->with([
            'user' => function ($query) {
                return $query->withField('nickname,id,avatar,username');
            },
            'forum' => function ($query) {
                return $query->withField('id,name');
            },
            'praise','collect'
        ])->order($order)->paginate(self::LIST_ROWS['thread'],'',[
            'query'=>['id'=>$id,'type'=>$type,'sort_type'=>$sort_type,'keywords'=>$keywords]
        ]);
        return $this->fetch('index',[
            'threads' => array_merge($all_top_list, $top_list, $list->getCollection()->toArray()),
            'page'=>$list->render(),
            'menu_forum_id'=>$id,
            'type'=>$type,
            'sort_type'=>$sort_type,
            'keywords'=>$keywords]);
    }

}
