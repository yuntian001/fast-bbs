<?php


namespace addons\bbs\controller;

use think\addons\Controller;
use think\Config;
use think\Db;
use think\Hook;
use \addons\bbs\model\Forum;
use \addons\bbs\model\Thread;
use addons\bbs\model\Post;

class Base extends Controller
{
    const LIST_ROWS=[
        'thread'=>5,
        'thread_comment'=>5,
        'post_comment'=>5
    ];

    protected $layout = 'default';
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        \config('paginate.type','\addons\bbs\library\Paginator');
        parent::_initialize();
        $last_clear_time = cache('bbs_last_clear_time');
        if(!$last_clear_time){
            Db::startTrans();
            $forums = Forum::lock(true)->select();
            $time_start = strtotime(date('Y-m-d'));
            foreach ($forums as $forum){
                $forum->today_threads = Thread::where('forum_id',$forum->id)->where('createtime','>=',$time_start)->count();
                $forum->today_posts = Post::where('forum_id',$forum->id)->where('createtime','>=',$time_start)->count();
                $forum->save();
            }
            cache('bbs_last_clear_time',$time_start);
            Db::commit();
        }else{
            if($last_clear_time < strtotime(date('Y-m-d'))){
                Forum::update([
                   'today_posts' => 0,
                    'today_threads'=> 0,
                    'updatetime'=>time(),
                ],['id'=>['>',0]]);
                cache('bbs_last_clear_time',time());
            }
        }
        $this->config = get_addon_config('bbs');
        // 设定主题模板目录
        $this->view->engine->config('view_path', $this->view->engine->config('view_path') . $this->config ['theme'] . DS);


        $forums = Forum::where('status',1)->order('weigh','desc')->select();
        $menu = explode("\n",trim($this->config['menu']));
        if(count($menu) == 0){
            $menu = explode("\r",trim($this->config['menu']));//兼容macos系统
        }
        foreach ($menu as &$value){
            $value = explode('|',$value);
        }
        $menu2 = [];
        //添加前五个板块作为菜单
        foreach ($forums as $k=>$v){
            if($k >4){
                break;
            }
            $menu2[]=[$v['name'],addon_url('bbs/index/index',['id'=>$v['id']])];
        }
        $this->config['menu']=array_merge($menu2,$menu);
        $url = explode("\n",trim($this->config['url']));
        foreach ($url as &$value){
            $value = explode('|',$value);
        }
        $this->config['url'] = $url;
        \think\Config::set('bbs', $this->config);
        $this->assign('config', $this->config);
        $upload = \app\common\model\Config::upload();
        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);
        $this->assign('upload',json_encode($upload));
        $this->assign('forums',$forums);
        $this->assign('auth',$this->auth);
    }
}