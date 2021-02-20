//最大文件限制转换成byte
var getSizeByte = function (maxsize) {
    var matches = maxsize.toString().match(/^([0-9\.]+)(\w+)$/);
    var size = matches ? parseFloat(matches[1]) : parseFloat(maxsize),
        unit = matches ? matches[2].toLowerCase() : 'b';
    var unitDict = {'b': 0, 'k': 1, 'kb': 1, 'm': 2, 'mb': 2, 'gb': 3, 'g': 3, 'tb': 4, 't': 4};
    var y = typeof unitDict[unit] !== 'undefined' ? unitDict[unit] : 0;
    return size * Math.pow(1024, y);
};
var bbsEditor = {
    editor: undefined,
    init: function (divDomName, textDom, config) {
        if(!$(divDomName)){
            return false;
        }
        this.editor = new window.wangEditor(divDomName);
        if (textDom) {
            this.editor.config.onchange = (html) => {
                // 第二步，监控变化，同步更新到 textarea
                textDom.val(html);
            }
        }
        this.editor.config.height = 500;
        this.editor.config.zIndex = 0;
        this.editor.config.pasteFilterStyle = false;//关闭粘贴样式过滤。
        this.editor.config.pasteIgnoreImg = true;//忽略粘贴的网络图片
        // 挂载highlight插件
        this.editor.highlight = hljs;
        this.editor.config.menus = [
            'head',
            'bold',
            'fontSize',
            'fontName',
            'italic',
            'underline',
            'strikeThrough',
            'indent',
            'lineHeight',
            'foreColor',
            'backColor',
            'link',
            'list',
            'justify',
            'quote',
            'emoticon',
            'image',
            // 'video',
            'table',
            'code',
            'splitLine',
            'undo',
            'redo',
        ];
        this.editor.config.uploadImgServer = upload.uploadurl;
        this.editor.config.uploadImgMaxSize = getSizeByte(upload.maxsize);
        this.editor.config.uploadFileName = 'file';
        this.editor.config.uploadImgMaxLength = 1; // 一次最多上传 1 个图片
        this.editor.config.showLinkImg = false;
        if(upload.multipart > 0){
            this.editor.config.uploadImgParams = upload.multipart;
        }
        this.editor.config.uploadImgHooks = {
            customInsert: function(insertImgFn, result) {
                if(result.code != 1){
                    return layer.msg(result.msg || '上传出错',{icon:2});
                }
                insertImgFn(upload.cdnurl+result.data.url);
            }
        };
        if (config) {
            $.each(config, (key, val) => {
                this.editor.config[key] = val;
            });
        }
        this.editor.create();
        if (textDom) {
            // 第一步，初始化 textarea 的值
            textDom.val(this.editor.txt.html());
        }
        return this;
    }
};
window.bbsEditor = bbsEditor;
var apiAjax = function (config){
    let index = undefined;
    if(!config.noLoading){
        index = layer.load();
    }
    let ajaxConfig = {
        success :(response)=>{
            if(response.code == 0){
                if(typeof config.error == 'function'){
                    config.error(response);
                    index && layer.close(index);
                    return ;
                }
                index && layer.close(index);
                layer.msg(response.msg||'出错', {icon: 2},response.url?function (){window.location.href=response.url}:undefined);
                return ;
            }
            if(!config.noSuccessMsg){
                layer.msg(response.msg||'操作成功',{icon:1},function (){
                    index && layer.close(index);
                });
            }
            if(typeof config.success == 'function'){
                config.success(response.data);
            }
            index && layer.close(index);
        },
        error : (response,error)=>{
            index && layer.close(index);
            console.log(response);
            console.log(error);
            return  layer.msg('请求异常', {icon: 2});
        }};
    $.ajax($.extend({},config,ajaxConfig));
};
var init = function (){
    $('.show-content').each(function (index, item) {
        let threadDom = $(item).closest('.thread');
        if(threadDom){
            let bodyHeight = threadDom.find('.thread-body').height();
            let contentHeight = threadDom.find('.thread-body .content').height();
            if (contentHeight > 0 && contentHeight > bodyHeight) {
                $(item).show();
                threadDom.find('.hide-content').data('height', bodyHeight + 'px');
            }
            if(threadDom.find('.thread-body .content img').length){
                threadDom.find('.thread-body .content img').load(function (){
                    let bodyHeight = threadDom.find('.thread-body').height();
                    let contentHeight = threadDom.find('.thread-body .content').height();
                    if (contentHeight > 0 && contentHeight > bodyHeight) {
                        $(item).show();
                        threadDom.find('.hide-content').data('height', bodyHeight + 'px');
                    }
                });
            }
        }
    });
};
$(document).on('click', '.show-content', function () {
    let threadDom = $(this).closest('.thread');
    threadDom.find('.hide-content').data('targetTop', $('html,body').scrollTop());
    threadDom.find('.thread-body').css('maxHeight', 'max-content');
    $(this).hide();
    threadDom.find('.hide-content').show();
});
$(document).on('click', '.hide-content', function () {
    let threadDom = $(this).closest('.thread');
    threadDom.find('.thread-body').css('maxHeight', $(this).data('height'));
    $(this).hide();
    threadDom.find('.show-content').show();
    // var _targetTop = threadDom.find('.show-content').offset().top-300;//获取位置
    $("html,body").animate({scrollTop: $(this).data('targetTop')}, 300);//跳转
});
$(document).on('click', '.show-hide', function () {
    if ($(this).data('changeDom')) {
        let Dom = $($(this).data('changeDom'));
        if (Dom.is(':hidden')) {
            Dom.show();
            Dom.data('targetTop', $('html,body').scrollTop());
            var _targetTop = Dom.offset().top - 200;//获取位置
            $("html,body").animate({scrollTop: _targetTop}, 300);
        } else {
            Dom.hide();
            $("html,body").animate({scrollTop: Dom.data('targetTop')}, 300);//跳转
        }
    }
});
$(document).on('click', '.sub-form', function () {
    let formDom = $(this).closest('form');
    if (formDom) {
        layer.load();
        $.ajax(formDom.attr('action'), {
            type: formDom.attr('method') || 'GET',
            data: (new FormData(formDom[0])),
            processData:false,
            contentType:false,
            dataType: 'json',
            success: (response)=>{
                layer.closeAll('loading');
                if(response.code && response.code == 1){
                    layer.msg(response.msg, {icon: 1});
                    if($(this).data('callback')){
                        if(typeof(eval($(this).data('callback'))) === "function"){
                            eval($(this).data('callback')+"();");
                        }
                    }
                }else{
                    return  layer.msg(response.msg||'出错', {icon: 2},response.url?function (){window.location.href=response.url}:undefined);
                }
            },
            error: function (response,error,exception) {
                layer.closeAll('loading');
                console.log(response);
                console.log(error);
                console.log(exception);
                layer.msg('请求异常', {icon: 2});
            }
        });

    }
});

var ajaxPage = function (url,params,containerDom,callBack){
    let render = template.compile($(containerDom.data('tpl')).html());
    containerDom.html(render({loading:true}));
    apiAjax({
        url: url,
        dataType: 'json',
        data:params,
        noLoading:true,
        noSuccessMsg:true,
        success: (data)=>{
            containerDom.html(render(data.list));
            if(containerDom.data('num-id')){
                $('#'+containerDom.data('num-id')).html(data.list.total);
                if(typeof callBack === "function"){
                    callBack();
                }
            }
            init();
        },error:function (){

        }
    });
};
$(document).on('click','.ajax-page a',function (e){
    let containerDom = $(this).closest('.page-container');
    if(containerDom){
        //取消默认事件
        e.preventDefault();
        ajaxPage($(this).attr('href'),undefined,containerDom);
    }
});