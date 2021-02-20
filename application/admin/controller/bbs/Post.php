<?php

namespace app\admin\controller\bbs;

use app\common\controller\Backend;
use PDOException;
use think\Db;
use think\Exception;

/**
 * 帖子管理
 *
 * @icon fa fa-circle-o
 */
class Post extends Backend
{

    /**
     * Post模型对象
     * @var \app\admin\model\bbs\Post
     */
    protected $model = null;

    /**
     * 是否是关联查询
     */
    protected $relationSearch = true;

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'id,brief';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\bbs\Post;

    }

    /**
     * 查看
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
            $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
            $total = $this->model->alias($name)->with([
                'forum' => function ($query) {
                    return $query->withField('id,name,createtime,updatetime');
                },
                'thread' => function ($query) {
                    return $query->withField('id,subject,createtime,updatetime');
                }, 'user'])->where($where)->order($sort, $order)
                ->count();
            $list = $this->model->alias($name)->with([
                'forum' => function ($query) {
                    return $query->withField('id,name,createtime,updatetime');
                },
                'thread' => function ($query) {
                    return $query->withField('id,title,createtime,updatetime');
                }, 'user'])->where($where)->order($sort, $order)
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
     * 详情
     */
    public function detail($ids = null)
    {
        $row = $this->model->withTrashed()->find($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);
        return $this->fetch();
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
            Db::startTrans();
            try {
                $list = $this->model->onlyTrashed()->select();
                foreach ($list as $index => $item) {
                    $count += $item->restore();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
        } else {
            $count = $this->model->whereNull($this->model->getDeleteTimeField())->update([$this->model->getDeleteTimeField() => NULL]);
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
        return $this->error('禁止删除');
//        $pk = $this->model->getPk();
//        $adminIds = $this->getDataLimitAdminIds();
//        if (is_array($adminIds)) {
//            $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
//        }
//        if ($ids) {
//            $this->model->where($pk, 'in', $ids);
//        }
//        $count = 0;
//        $list = $this->model->onlyTrashed()->select();
//        foreach ($list as $k => $v) {
//            $count += $v->delete(true);
//        }
//        if ($count) {
//            $this->success();
//        } else {
//            $this->error(__('No rows were deleted'));
//        }
//        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

}
