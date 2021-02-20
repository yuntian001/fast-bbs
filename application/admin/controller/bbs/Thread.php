<?php

namespace app\admin\controller\bbs;

use app\common\controller\Backend;
use think\Db;

/**
 * 主题管理
 *
 * @icon fa fa-circle-o
 */
class Thread extends Backend
{

    /**
     * Thread模型对象
     * @var \app\admin\model\bbs\Thread
     */
    protected $model = null;

    /**
     * 是否是关联查询
     */
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\bbs\Thread;
        $this->view->assign("isEliteList", $this->model->getIsEliteList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
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
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
//            $order1['all_top'] = 'DESC';
//            $filter = $this->request->get("filter", '');
//            $filter = (array)json_decode($filter, TRUE);
//            if (isset($filter['forum_id'])) {
//                $order1['top'] = 'DESC';
//            }
            $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
            $total = $this->model->alias($name)->with([
                'forum' => function ($query) {
                    return $query->withField('id,name,createtime,updatetime');
                }, 'user'])->where($where)->order($sort, $order)
                ->count();
            $list = $this->model->alias($name)->with([
                'forum' => function ($query) {
                    return $query->withField('id,name,createtime,updatetime');
                },
                'user'])->where($where)->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        return false;
    }

    /**
     * 详情
     */
    public function detail($ids = NULL)
    {
        $row = $this->model->withTrashed()->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("info", $row);
        return $this->view->fetch();
    }

    /**
     * 设置/取消全局置顶排序
     * @param null $ids
     * @throws \think\exception\DbException
     */
    public function set_all_top($ids = null)
    {
        $value = input('post.value/d', 0);
        if ($value < 0) {
            $this->error('请输入大于0的数字');
        }
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        $row->all_top = $value;
        $row->save();
        $this->success();
    }

    /**
     * 设置/取消板块置顶排序
     * @param null $ids
     * @throws \think\exception\DbException
     */
    public function set_top($ids = null)
    {
        $value = input('post.value/d', 0);
        if ($value < 0) {
            $this->error('请输入大于0的数字');
        }
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        $row->top = $value;
        $row->save();
        $this->success();
    }

    /**
     * 设为精华
     * @param $ids
     * @throws \think\exception\DbException
     */
    public function set_elite($ids)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($row->setElite()) {
            $this->success();
        }
        $this->error();
    }

    /**
     * 取消精华
     * @param $ids
     * @throws \think\exception\DbException
     */
    public function del_elite($ids)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($row->delElite()) {
            $this->success();
        }
        $this->error();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 回收站
     */
    public function recyclebin()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
            $total = $this->model
                ->onlyTrashed()->alias($name)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->onlyTrashed()->alias($name)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 还原
     */
    public function restore($ids = "")
    {
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $count = 0;
        if ($ids) {
            $this->model->where($pk, 'in', $ids);
            $list = $this->model->onlyTrashed()->select();
            foreach ($list as $index => $item) {
                $count += $item->restore();
            }

        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }


    /**
     * 真实删除
     */
    public function destroy($ids = "")
    {
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $count = $this->model->where($this->dataLimitField, 'in', $adminIds);

        }
        if ($ids) {
            $this->model->where($pk, 'in', $ids);
        }
        $count = 0;
        $list = $this->model->onlyTrashed()->select();
        foreach ($list as $k => $v) {
            $count += $v->delete(true);
        }
        if ($count) {
            $this->success();
        } else {
            $this->error(__('No rows were deleted'));
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }
}
