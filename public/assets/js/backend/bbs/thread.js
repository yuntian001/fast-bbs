define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bbs/thread/index',
                    del_url: 'bbs/thread/del',
                    table: 'bbs_thread',
                }
            });
            //弹出输入框监听
            $(document).on("click",".btn-prompt",function () {
                var that = this;
                var rule = $(that).data("rule");
                Layer.prompt({
                    value: '1', //初始时的值，默认空字符
                    title:$(this).data("prompt-title"),
                }, function(value, index){
                    var checked_message = true;
                    $.each(rule.split(";;"),function(index,item){
                        console.log(item);
                        if(checked_message === true){
                            var regu =  item.split("|");
                            if(!RegExp(regu[0]).test(value)){
                                checked_message = regu[1];
                                return false;
                            }
                        }
                    });
                    if(checked_message === true){
                        var options = $.extend({}, $(that).data() || {});
                        options.data=$.extend(options.data,{value:value});
                        if (typeof options.url === 'undefined' && $(that).attr("href")) {
                            options.url = $(that).attr("href");
                        }
                        options.url = Backend.api.replaceids(this, options.url);
                        var success = typeof options.success === 'function' ? options.success : null;
                        var error = typeof options.error === 'function' ? options.error : null;
                        delete options.success;
                        delete options.error;
                        var button = Backend.api.gettablecolumnbutton(options);
                        if (button) {
                            if (typeof button.success === 'function') {
                                success = button.success;
                            }
                            if (typeof button.error === 'function') {
                                error = button.error;
                            }
                        }
                        //如果未设备成功的回调,设定了自动刷新的情况下自动进行刷新
                        if (!success && typeof options.tableId !== 'undefined' && typeof options.refresh !== 'undefined' && options.refresh) {
                            success = function () {
                                $("#" + options.tableId).bootstrapTable('refresh');
                            }
                        }
                        Backend.api.ajax(options, success, error);
                    }else {
                        Toastr.error(checked_message);
                    }
                    layer.close(index);
                });
                return false;
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
                        {field: 'id', title: __('Id'),sortable:true,width: 60},
                        {field:'forum_id',title:__('Forum_id'),visible:false,searchList: $.getJSON("bbs/forum/searchlist")},
                        {field: 'forum.name', title: __('Forum_id'),operate: false},
                        {field: 'user.nickname', title: __('User_id')},
                        {field: 'user_ip', title: __('User_ip')},
                        {field: 'all_top', title: __('All_top'),operate:'BETWEEN',sortable:true,width: 95,operate: false},
                        {field: 'top', title: __('Top'),operate:'BETWEEN',sortable:true,width: 95,operate: false},
                        {field: 'is_elite', title: __('Is_elite'), searchList: {"1":__('Is_elite 1'),"0":__('Is_elite 0')}, formatter: Table.api.formatter.normal},
                        {field: 'title', title: __('Title'),width:150,cellStyle:{css:{'white-space':'normal'}}},
                        {field: 'brief', title: __('Brief'),width:250,cellStyle:{css:{'white-space':'normal'}}},
                        {field:'url',operate: false,title:__('Url'),formatter: function (value){
                                return `<a target="_top" href="${value}">查看</a>`
                            }},
                        {field: 'post_number', title: __('Post_number'),operate:'BETWEEN'},
                        {field: 'view_number', title: __('View_number'),operate:'BETWEEN'},
                        {field: 'praise_number', title: __('Praise_number'),operate:'BETWEEN'},
                        {field: 'collect_number', title: __('Collect_number'),operate:'BETWEEN'},
                        {field:'report_number',title:__('Report_number'),operate:'BETWEEN'},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,buttons:[
                                {
                                    name:'detail',
                                    text:'详情',
                                    title:'详情',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    url: 'bbs/thread/detail',
                                },
                                {
                                    name: 'setAllTop',
                                    text: __('全局置顶'),
                                    title: __('全局置顶'),
                                    classname: 'btn btn-xs btn-success btn-prompt',
                                    icon: 'fa',
                                    url: 'bbs/thread/set_all_top',
                                    extend:' data-prompt-title="请输入置顶排序(降序)" data-rule="^[1-9][0-9]*$|请输入大于0的数字;;"',
                                    refresh:true,
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(parseInt(row.all_top)>0){
                                            return false;
                                        }
                                        return true;
                                    }
                                },
                                {
                                    name: 'setAllTop',
                                    text: __('取消全局置顶'),
                                    title: __('取消全局置顶'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    confirm: '确定取消全局置顶?',
                                    icon: 'fa',
                                    url: 'bbs/thread/set_all_top',
                                    refresh:true,
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(parseInt(row.all_top)>0){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'setTop',
                                    text: __('板块置顶'),
                                    title: __('板块置顶'),
                                    classname: 'btn btn-xs btn-success btn-prompt',
                                    icon: 'fa',
                                    url: 'bbs/thread/set_top',
                                    extend:' data-prompt-title="请输入置顶排序(降序)" data-rule="^[1-9][0-9]*$|请输入大于0的数字;;"',
                                    refresh:true,
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(parseInt(row.top)>0){
                                            return false;
                                        }
                                        return true;
                                    }
                                },
                                {
                                    name: 'setTop',
                                    text: __('取消板块置顶'),
                                    title: __('取消板块置顶'),
                                    confirm: '确定取消板块置顶?',
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa',
                                    url: 'bbs/thread/set_top',
                                    refresh:true,
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(parseInt(row.top)>0){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'setElite',
                                    text: __('添加精华'),
                                    title: __('添加精华'),
                                    confirm: '确定设置主题精华?',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa',
                                    url: 'bbs/thread/set_elite',
                                    refresh:true,
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(parseInt(row.is_elite) == 0){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'delElite',
                                    text: __('取消精华'),
                                    title: __('取消精华'),
                                    confirm: '确定取消主题精华?',
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa',
                                    url: 'bbs/thread/del_elite',
                                    refresh:true,
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(parseInt(row.is_elite)>0){
                                            return true;
                                        }
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
                url: 'bbs/thread/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),sortable:true,width: 60},
                        {field:'forum_id',title:__('Forum_id'),visible:false,searchList: $.getJSON("bbs/forum/searchlist")},
                        {field: 'title', title: __('Title'),width:150,cellStyle:{css:{'white-space':'normal'}}},
                        {field: 'brief', title: __('Brief'),width:250,cellStyle:{css:{'white-space':'normal'}}},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'createtime',sortable:true, title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime',sortable:true,title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name:'detail',
                                    text:'详情',
                                    title:'详情',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    url: 'bbs/thread/detail',
                                },
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'bbs/thread/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'bbs/thread/destroy',
                                    refresh: true
                                }
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