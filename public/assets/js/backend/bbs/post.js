define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bbs/post/index',
                    del_url: 'bbs/post/del',
                    table: 'bbs_post',
                }
            });

            var table = $("#table");
            //在普通搜索渲染后
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form", table.$commonsearch);
                $("input[name='thread_id']", form).addClass("selectpage").data("source", "bbs/thread/index").data("primaryKey", "id").data("field", "title").data("orderBy", "id desc");
                Form.events.cxselect(form);
                Form.events.selectpage(form);
            });
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder:'ASC',
                dblClickToEdit: false, //是否启用双击编辑
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),sortable:true,width:'100px'},
                        {field: 'forum.name', title: __('Forum_id'),operate: false},
                        {field:'forum_id',title:__('Forum_id'),visible:false,searchList: $.getJSON("bbs/forum/searchlist")},
                        {field: 'thread.title', operate: false,title: __('Thread_id'),width:150,cellStyle:{css:{'white-space':'normal'}}},
                        {field: 'thread_id', visible:false,title: __('Thread_id')},
                        {field: 'user.nickname', operate: false,title: __('User_id')},
                        {field: 'first_id', title: __('首层帖子')},
                        {field: 'parent_id', title: __('父级帖子')},
                        {field: 'user_ip', title: __('User_ip')},
                        {field: 'brief',title:__('Brief'),width:250,cellStyle:{css:{'white-space':'normal'}}},
                        {field:'url',operate: false,title:__('Url'),formatter: function (value){
                            return `<a target="_top" href="${value}">查看</a>`
                            }},
                        {field: 'last_time', title: __('Last_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'post_number', title: __('Post_number'),operate:'BETWEEN'},
                        {field: 'praise_number', title: __('Praise_number'),operate:'BETWEEN'},
                        {field: 'collect_number', title: __('Collect_number'),operate:'BETWEEN'},
                        {field:'report_number',title:'被举报次数',operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,buttons:[
                                {
                                    name: 'detail',
                                    text: __('详情'),
                                    title: __('帖子详情'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa',
                                    url: 'bbs/post/detail',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        return true;
                                    }
                                },
                        ]}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'bbs/post/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),sortable:true},
                        {field: 'brief',title:__('Brief'),width:250,cellStyle:{css:{'white-space':'normal'}}},
                        {field: 'createtime', title: __('Createtime'), sortable:true, operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), sortable:true, operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('详情'),
                                    title: __('帖子详情'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa',
                                    url: 'bbs/post/detail',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        return true;
                                    }
                                },
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'bbs/post/restore',
                                    refresh: true
                                },
                                // {
                                //     name: 'Destroy',
                                //     text: __('Destroy'),
                                //     classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                //     icon: 'fa fa-times',
                                //     url: 'bbs/post/destroy',
                                //     refresh: true
                                // }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});