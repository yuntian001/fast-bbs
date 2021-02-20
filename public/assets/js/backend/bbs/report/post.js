define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index:function (){
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bbs/report/post',
                    table: 'bbs_report',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'report_number',
                columns: [
                    [
                        {field: 'id', title: __('Id')},
                        {field: 'user.nickname', title: __('发布用户')},
                        {field: 'brief',title:__('Brief'),cellStyle:function () {return {css: {'max-width':'200px','overflow':'hidden'}};}},
                        {field: 'user_ip', title: __('User_ip')},
                        {field:'report_number',title:__('Report_number'),operate:'BETWEEN'},
                        {field: 'first_id', title: __('首层帖子'),cellStyle:function () {return {css: {'min-width':'65px'}};}},
                        {field: 'parent_id', title: __('父级帖子'),cellStyle:function () {return {css: {'min-width':'65px'}};}},
                        {field: 'forum.name', title: __('Forum_id'),operate: false},
                        {field:'forum_id',title:__('Forum_id'),visible:false,searchList: $.getJSON("bbs/forum/searchlist")},
                        {field: 'thread.title', title: __('Thread_id'),titleTooltip:'aa',cellStyle:function () {return {css: {'max-width':'200px','overflow':'hidden'}};}},
                        {field: 'last_time', title: __('Last_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'post_number', title: __('Post_number'),operate:'BETWEEN'},
                        {field: 'collect_number', title: __('Collect_number'),operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'deletetime', title: __('Deletetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'buttons', title: __('Operate'), operate:false, table: table, events: Table.api.events.operate,buttons: [
                                {
                                    name: 'detail',
                                    text: __('详情'),
                                    title: __('详情'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'bbs/report/post/detail',
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        return true;
                                    }
                                },
                                {
                                    name: 'reports',
                                    text: __('举报列表'),
                                    title: __('举报列表'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    url: 'bbs/report/post/reports',
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                                {
                                    name: 'del',
                                    text: __('删除'),
                                    title: __('删除'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    url: 'bbs/report/post/del',
                                    confirm: '确认删除被举报的主题？',
                                    refresh:true,
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        }
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        reports:function(){
            Table.api.init({
                extend: {
                    index_url: Config.index_url,
                    table: 'bbs_report',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user.nickname', title: __('User_id'),operate:'LIKE'},
                        {field: 'type', title: __('Type'), searchList: {"1":__('Type 1'),"2":__('Type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'valueuser.nickname',title:__('Value_user_id'),operate:'LIKE'},
                        {field: 'describe', title: __('Describe')},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'buttons', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.buttons, buttons: [
                                {
                                    name: 'ignore',
                                    text: __('忽略'),
                                    title: __('忽略'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    url: 'bbs/report/post/ignore/status/0',
                                    confirm: '确定忽略举报信息？',
                                    refresh:true,
                                    visible:(row)=>{return row.status == 1},
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                                {
                                    name: 'ignore',
                                    text: __('取消忽略'),
                                    title: __('取消忽略'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    url: 'bbs/report/post/ignore/status/1',
                                    confirm: '确定取消忽略举报信息？',
                                    refresh:true,
                                    visible:(row)=>{return row.status == 0},
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                }
                            ]}
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail:function (){
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});