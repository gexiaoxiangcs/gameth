
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

	function ctor(options){
		if(this.constructor !== ctor){
			return new ctor(options);
		}

		var defaults = {
				hovertarget : "",//鼠标hover停止切换的对象
				target : "",//滚动对象 一般为 ul
				items : "", //滚动的详细列表
				gotobtn : "",//指定滚动位置的按钮
				prevbtn : "",//向前或者 向上按钮
				nextbtn : "",//向后或者 向下按钮

				trigger : "click",//事件触发方式
				delay : 3000,//切换间隔时间
				speed : 300,//切换速度
		
				autoplay : true,//是否自动播放
				currentclass : "",
				maxzIndex : 2,
				
				afterSlide : function(){},//每滚动一个完的回调函数
				beforeSlide : function(){}//每滚动一个之前的回调函数
			};
			
		options = this.options = $.extend(defaults, options);
		
		this.hovertarget = $(options.hovertarget);
		this.target = $(options.target);
		this.prevbtn = $(options.prevbtn);
		this.nextbtn = $(options.nextbtn);
		this.gotobtn = $(options.gotobtn);
		this.items = $(options.items);
		
		if (this.items.length < 2) return;
		
		this.current = 0;
		this.items.eq(0).css("z-index", options.maxzIndex);
		this.bind();
		this.start();
	}
	
	ctor.prototype = {
		constructor : ctor,

		bind : function(){
			var options = this.options,
				_this = this;
			
			this.prevbtn.bind("click", function(){
				_this.prev();
				return false;
			});
			
			this.nextbtn.bind("click", function(){
				_this.next();
				return false;
			});
			
			this.gotobtn.bind(options.trigger, function(){
				var index = $(this).index();
				
				_this.goto(index);
				if (options.trigger == "click"){
					return false;
				}
			});
			
			this.hovertarget.bind("mouseover mouseout", function(evt){
				if (evt.type == "mouseover"){
					_this.hoverstatus = true;
					setTimeout(function(){
						_this.checkHover();
					},300);
				} else if (evt.type == "mouseout"){
					_this.hoverstatus = false;
					setTimeout(function(){
						_this.checkHover();
					},300);
				}
			});
		},
		
		next : function(){
			//if (this.animate) return false;
			var next = this.current + 1;
			this.goto(next);
		},
		
		prev : function(){
			//if (this.animate) return false;
			var prev = this.current - 1;
			this.goto(prev);
		},
		
		goto : function(index){
			var _this = this,
				options = this.options;

			if (index == _this.current) return false;
			//this.animate = true;
			if (index >= this.items.length) index = 0;
			if (index < 0) index = this.items.length - 1;
			
			_this.preIndex = _this.current;
			_this.current = index;

			_this.options.beforeSlide.call(_this, _this.preIndex, _this.current);
			
			_this.items.stop(true, true);

			_this.items.eq(index).css({
				"z-index" : options.maxzIndex - 1,
				"opacity" : 0
			});

			_this.gotobtn.removeClass(options.currentclass);
			_this.gotobtn.eq(_this.current).addClass(options.currentclass);

			_this.items.eq(_this.preIndex).animate({opacity : 0}, options.speed, function(){
				_this.items.eq(_this.preIndex).css("z-index", 0);
			});

			_this.items.eq(_this.current).animate({opacity : 1}, options.speed, function(){
				_this.items.eq(index).css("z-index", options.maxzIndex).siblings().css("z-index", 0);
				_this.options.afterSlide.call(_this, _this.preIndex, _this.current);
			});
			
		},
		
		stop : function(){
			clearInterval(this.timer);
		},
		
		start : function(){
			var options = this.options,
				_this = this;
				
			if (!options.autoplay){
				return;
			}
			this.stop();
			this.timer = setInterval(function(){
				_this.next();
			}, options.delay + options.speed);
		},
		
		checkHover : function(){
			if (this.hoverstatus){
				this.stop();
			} else {
				this.start();
			}
		}
	}

	if( {}.toString.call(module) == '[object Object]' ){
    	module.exports = ctor;
	}else{
		exports.slide = ctor;
	}
		
}));