<?php

namespace app\admin\controller\bbs;

use app\admin\model\User;
use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;

/**
 * 板块管理
 *
 * @icon fa fa-circle-o
 */
class Forum extends Backend
{

    /**
     * Forum模型对象
     * @var \app\admin\model\bbs\Forum
     */
    protected $model = null;
    protected $noNeedRight = ['searchlist'];


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\bbs\Forum;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("threadStatusList", $this->model->getThreadStatusList());

    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 搜索下拉列表
     */
    public function searchlist()
    {
        $result = $this->model->limit(10)->select();
        $searchlist = [];
        foreach ($result as $key => $value) {
            $searchlist[] = ['id' => $value['id'], 'name' => $value['name']];
        }
        $data = ['searchlist' => $searchlist];
        $this->success('', null, $data);
    }

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            $rows = $list->getCollection()->toArray();
            $user_ids = [];
            foreach ($rows as $key=> $value){
                if($value['mod_user_ids']){
                    $rows[$key]['mod_user_ids'] = $value['mod_user_ids'] = explode(',',$value['mod_user_ids']);
                    $user_ids = array_merge($user_ids,$value['mod_user_ids']);
                }else{
                    $rows[$key]['mod_user_ids'] = [];
                }
                $rows[$key]['mod_users_name'] = [];
            }
            if($user_ids){
                $users = User::whereIn('id',$user_ids)->column('nickname','id');
                foreach ($rows as $key=> $value){
                    foreach ($rows[$key]['mod_user_ids'] as $v){
                        $rows[$key]['mod_users_name'][]=$users[$v];
                    }
                }
            }

            $result = array("total" => $list->total(), "rows" => $rows);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            $no_ids = [];
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    if(\app\admin\model\bbs\Thread::withTrashed()->where('forum_id',$v->id)->count()){
                        $no_ids[]=$v->id;
                    }else{
                        $count += $v->delete();
                    }
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $message ='';
            if($no_ids){
                $message = 'id为'.implode('、',$no_ids).'的板块下有主题存在不可删除';
            }
            if ($count) {
                $this->success($message);
            } else {
                $this->error(__('No rows were deleted').$message);
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


}
