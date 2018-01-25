
;(function(factory) {
    // CMD/SeaJS
    if(typeof define === "function") {
        define(factory);
    }
    // No module loader
    else {
        factory('', window['ue'] = window['ue'] || {}, '');
    }

}(function(require, exports, module) {
    function windowHeight(){
        return window.innerHeight || document.documentElement.clientHeight;
    }

    function scrollTop(){
        return document.body.scrollTop || document.documentElement.scrollTop;
    }

    var lazyimg = function(options){
        if(this.constructor !== lazyimg){
            return new lazyimg(options);
        }
        options = $.extend({}, {
            target : '',
            type : ''
        },options);
        this._timer = null;
        this.init(options);
    };


    var loadCount = 0;
    var scrollImgs = [];

    lazyimg.prototype = {
        constructor : lazyimg,

        init : function(options){
            var imgs = options.target.find('img[data-src]');
            var that = this;

            if(options.type === 'scroll'){
                scrollImgs = imgs;
                that.loadByScroll();
                $(window).unbind("scroll.lazyImg resize.lazyImg").bind("scroll.lazyImg resize.lazyImg", function(){
                    that._timer = setTimeout(function(){that.loadByScroll()},200);
                })
            } else {
                that.load(imgs);
            }
        },

        load : function(imgs){
            var img;
            for( var i = 0, len = imgs.length; i < len; i++ ){
                img = $(imgs[i]);
                if (!!img.attr('data-src')){
                    img.attr("src", img.attr('data-src')).removeAttr("data-src");
                }
            }
        },

        loadByScroll : function(){
            var img;
            var that = this;
            for( var i = 0, len = scrollImgs.length; i < len; i++ ){
                img = $(scrollImgs[i]);
                if (windowHeight() + scrollTop() >= img.offset().top){
                    if (!!img.attr('data-src')){
                        loadCount++;
                        img.attr("src", img.attr('data-src')).removeAttr("data-src");
                    }
                }
            }

            if (loadCount == scrollImgs.length){
                clearTimeout(that._timer);
                $(window).unbind("scroll.lazyImg resize.lazyImg");
            }
        }
    }

    if( {}.toString.call(module) == '[object Object]' ){
        module.exports = lazyimg;
    }else{
        exports.lazyimg = lazyimg;
    }
    
}));
