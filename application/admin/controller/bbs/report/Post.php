<?php

namespace app\admin\controller\bbs\report;

use app\admin\model\User;
use app\common\controller\Backend;
use Exception;
use PDOException;
use think\Db;

/**
 * 举报管理
 *
 * @icon fa fa-circle-o
 */
class Post extends Backend
{

    /**
     * Report模型对象
     * @var \app\admin\model\bbs\Report
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\bbs\Report;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->assignconfig('type_relation', $this->model->type_relation);

    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 帖子被举报记录
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $this->model = new \app\admin\model\bbs\Post();
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
            $total = $this->model->alias($name)->with([
                'forum' => function ($query) {
                    return $query->withField('id,name,createtime,updatetime');
                },
                'thread' => function ($query) {
                    return $query->withField('id,title,createtime,updatetime');
                }, 'user'])->where($where)->where($name.'.report_number','>',0)->order($name.'.report_number', 'DESC')
                ->count();
            $list = $this->model->alias($name)->with([
                'forum' => function ($query) {
                    return $query->withField('id,name,createtime,updatetime');
                },
                'thread' => function ($query) {
                    return $query->withField('id,title,createtime,updatetime');
                }, 'user'])->where($where)->where($name.'.report_number','>',0)->order($name.'.report_number', 'DESC')
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        return false;
    }

    /**
     * 帖子详情
     * @param $ids
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail($ids){
        $m_post = new \app\admin\model\bbs\Post();
        $info = $m_post::withTrashed()->find($ids);
        if(!$info){
            $this->error('错误的数据');
        }
        if($info->report_number == 0){
            $this->error('该信息未被举报过');
        }
        $this->assign('info',$info);
        return $this->fetch();
    }


    /**
     * 帖子举报列表
     * @return mixed|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function reports(){
        $type = 2;
        $value_id = input('param.ids/d',0);
        if(!$value_id){
            $this->error('错误的参数');
        }
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $this->relationSearch = true;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
            $total = $this->model->alias($name)->with(['user','valueuser'])->where([$name.'.type'=>$type,$name.'.value_id'=>$value_id])->where($where)->count();
            $list = $this->model->alias($name)->with(['user','valueuser'])
                ->where([$name.'.type'=>$type,$name.'.value_id'=>$value_id])->where($where)->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->assignconfig('index_url',url('/admin/bbs/report/post/reports',['ids'=>$value_id]));
        return $this->fetch();
    }



    /**
     * 删除举报的帖子
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del($ids=''){
        if(!$ids){
            $this->error('错误的参数');
        }
        $m_post = new \app\admin\model\bbs\Post();
        $post = $m_post->find($ids);
        if(!$post){
            $this->error('此回复已被删除');
        }
        if($post->delete()){
            $this->success('删除成功');
        }
        $this->error('操作失败,请稍后再试');
    }

    /**
     * 设置举报忽略状态（帖子）
     * @param $ids
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function ignore($ids){
        $ids = $ids ? $ids : $this->request->param("ids");
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where('type',2)->where($pk, 'in', $ids)->select();
            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->save(['status'=>$this->request->param('status',0)]);
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were updated'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }
}
