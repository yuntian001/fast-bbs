<?php

namespace addons\bbs;

use app\common\library\Menu;
use think\Addons;
use think\Request;

/**
 * 插件
 */
class Bbs extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name' => 'fa_bbs',
                'title' => '论坛管理',
                'sublist' => [
                    [
                        'name' => 'bbs/forum',
                        'title' => '板块管理',
                        'icon' => 'fa fa-th-large',
                        'sublist' => [
                            ['name' => 'bbs/forum/index', 'title' => '查看'],
                            ['name' => 'bbs/forum/add', 'title' => '添加'],
                            ['name' => 'bbs/forum/edit', 'title' => '修改'],
                            ['name' => 'bbs/forum/del', 'title' => '删除'],
                        ],
                        'remark' => '论坛板块'
                    ],
                    [
                        'name' => 'bbs/thread',
                        'title' => '主题管理',
                        'icon' => 'fa fa-book',
                        'sublist' => [
                            ['name' => 'bbs/thread/index', 'title' => '查看'],
                            ['name' => 'bbs/thread/detail', 'title' => '详情',],
                            ['name' => 'bbs/thread/set_all_top', 'title' => '全局置顶'],
                            ['name' => 'bbs/thread/set_top', 'title' => '板块置顶'],
                            ['name' => 'bbs/thread/set_eliet', 'title' => '设置精华'],
                            ['name' => 'bbs/thread/del_eliet', 'title' => '取消精华'],
                            ['name' => 'bbs/thread/del', 'title' => '删除'],
                            ['name' => 'bbs/thread/recyclebin', 'title' => '回收站'],
                            ['name' => 'bbs/thread/restore', 'title' => '回收站还原'],
                            ['name' => 'bbs/thread/destroy', 'title' => '真实删除'],

                        ],
                        'remark' => '设置主题相关信息。 全局置顶和板块置顶均为降序，0代表不置顶。'
                    ],
                    [
                        'name' => 'bbs/post',
                        'title' => '帖子管理',
                        'icon' => 'fa fa-file-text-o',
                        'sublist' => [
                            ['name' => 'bbs/post/index', 'title' => '查看'],
                            ['name' => 'bbs/post/detail', 'title' => '详情'],
                            ['name' => 'bbs/post/del', 'title' => '删除'],
                            ['name' => 'bbs/post/recyclebin', 'title' => '回收站'],
                            ['name' => 'bbs/post/restore', 'title' => '回收站还原'],
//                            ['name' => 'bbs/post/destroy', 'title' => '真实删除'],
                        ],
                    ],
                    [
                        'name' => 'bbs/report',
                        'title' => '举报管理',
                        'icon' => 'fa fa-exclamation-triangle',
                        'sublist' => [
                            [
                                'name' => 'bbs/report/thread',
                                'title' => '主题',
                                'icon' => 'fa fa-book',
                                'sublist' => [
                                    ['name' => 'bbs/report/thread/index', 'title' => '查看'],
                                    ['name' => 'bbs/report/thread/detail', 'title' => '详情'],
                                    ['name' => 'bbs/report/thread/del', 'title' => '删除'],
                                    ['name' => 'bbs/report/thread/reports', 'title' => '举报列表'],
                                    ['name' => 'bbs/report/thread/ignore', 'title' => '忽略举报'],
                                ],
                                'remark' => '查看和处理被举报的主题'
                            ],
                            [
                                'name' => 'bbs/report/post',
                                'title' => '帖子',
                                'icon' => 'fa fa-file-text-o',
                                'sublist' => [
                                    ['name' => 'bbs/report/post/index', 'title' => '查看'],
                                    ['name' => 'bbs/report/post/detail', 'title' => '详情'],
                                    ['name' => 'bbs/report/post/del', 'title' => '删除'],
                                    ['name' => 'bbs/report/post/reports', 'title' => '举报列表'],
                                    ['name' => 'bbs/report/post/ignore', 'title' => '忽略举报'],
                                ],
                                'remark' => '查看和处理被举报的帖子'
                            ],
                        ],
                    ]
                ]
            ]
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('bbs');
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {

        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {

        return true;
    }


    /**
     * 会员中心边栏后
     * @return mixed
     * @throws \Exception
     */
    public function userSidenavAfter()
    {
        return $this->fetch('view/hook/user_sidenav_after');
    }

}
