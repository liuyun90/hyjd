/**
 * dropload
 * 西门
 * 0.4.0(150927)
 */

;(function($){
    'use strict';
    var $win = $(window);
    var $doc = $(document);
    $.fn.dropload = function(options){
        return new MyDropLoad(this, options);
    };
    var MyDropLoad = function(element, options){
        var me = this;
        me.$element = $(element);
        me.loading = false;
        me.isLock = false;
        me.isData = true;
        me.init(options);
    };

    // 初始化
    MyDropLoad.prototype.init = function(options){
        var me = this;
        me.opts = $.extend({}, {
            scrollArea : me.$element,                                            // 滑动区域
            domDown : {                                                          // 下方DOM
                domClass   : 'dropload-down',
                domRefresh : '<div class="dropload-refresh">↑上拉加载更多</div>',
                domLoad    : '<div class="dropload-load"><span class="loading"></span>加载中...</div>',
                domNoData  : '<div class="dropload-noData">神马都没有了</div>'
            },
            distance : 50,                                                       // 拉动距离
            threshold : '',                                                      // 提前加载距离
            loadDownFn : ''                                                      // 下方function
        }, options);

        // 如果加载下方，事先在下方插入DOM
        if(me.opts.loadDownFn != ''){
            me.$element.append('<div class="'+me.opts.domDown.domClass+'">'+me.opts.domDown.domRefresh+'</div>');
            me.$domDown = $('.'+me.opts.domDown.domClass);
        }

        // 判断滚动区域
        if(me.opts.scrollArea == window){
            me.$scrollArea = $win;
        }else{
            me.$scrollArea = me.opts.scrollArea;
        }

        // 绑定触摸
        me.$element.on('touchstart',function(e){
            if(!me.loading && !me.isLock){
                fnTouches(e);
                fnTouchstart(e, me);
            }
        });
        me.$element.on('touchmove',function(e){
            if(!me.loading && !me.isLock){
                fnTouches(e);
                fnTouchmove(e, me);
            }
        });
    };

    // touches
    function fnTouches(e){
        if(!e.touches){
            e.touches = e.originalEvent.touches;
        }
    }

    // touchstart
    function fnTouchstart(e, me){
        me._startY = e.touches[0].pageY;
        // 判断滚动区域
        if(me.opts.scrollArea == window){
            me._meHeight = $win.height();
            me._childrenHeight = $doc.height();
        }else{
            me._meHeight = me.$element.height();
            me._childrenHeight = me.$element.children().height();
        }
        me._scrollTop = me.$scrollArea.scrollTop();
    }

    // touchmove
    function fnTouchmove(e, me){
        // 加载下方
        if(me.opts.threshold === ''){
            // 默认滑到加载区2/3处时加载
            me._threshold = Math.floor(me._childrenHeight*1/5);
        }else{
            me._threshold = me.opts.threshold;
        }
        if(me.opts.loadDownFn != '' && !me.loading && (me._childrenHeight - me._threshold) <= (me._meHeight+me._scrollTop)){
            // alert(me._childrenHeight +'|'+(me._meHeight+me._scrollTop));
            fnLoadDown();
        }

        // 加载下方方法
        function fnLoadDown(){
            me.$domDown.html(me.opts.domDown.domLoad);
            me.loading = true;
            me.opts.loadDownFn(me);
        }

    }

    // 锁定
    MyDropLoad.prototype.lock = function(){
        var me = this;
        me.isLock = true;
    };

    // 解锁
    MyDropLoad.prototype.unlock = function(){
        var me = this;
        me.isLock = false;
    };

    // 无数据
    MyDropLoad.prototype.nodata = function(){
        var me = this;
        me.$domDown.html(me.opts.domDown.domNoData);
        me.isData = false;
    };

    // 重置
    MyDropLoad.prototype.resetload = function(){
        var me = this;
        me.loading = false;
        // 如果有数据
        if(me.isData){
            // 加载区修改样式
            me.$domDown.html(me.opts.domDown.domRefresh);
        }else{
            // 如果没数据
            me.$domDown.html(me.opts.domDown.domNoData);
        }
    };
})(window.Zepto || window.jQuery);