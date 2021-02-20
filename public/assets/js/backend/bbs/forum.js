define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bbs/forum/index',
                    add_url: 'bbs/forum/add',
                    edit_url: 'bbs/forum/edit',
                    del_url: 'bbs/forum/del',
                    multi_url: 'bbs/forum/multi',
                    table: 'bbs_forum',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                commonSearch: true, //是否启用通用搜索
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                sort:'DESC',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'thread_number', title: __('Thread_number'),operate:'BETWEEN'},
                        {field: 'today_posts', title: __('Today_posts'),operate:'BETWEEN'},
                        {field: 'today_threads', title: __('Today_threads'),operate:'BETWEEN'},
                        {field: 'thread_status', title: __('Thread_status'), searchList: {"0":__('Thread_status 0'),"1":__('Thread_status 1'),"2":__('Thread_status 2')}, formatter: Table.api.formatter.status},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'mod_users_name', title: __('Mod_user_ids'),operate: false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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