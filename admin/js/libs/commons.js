var pagecls = function(){};
pagecls.prototype = {
    perpage : 10,
    total : 100,
    itemlen : 10,
    pagecount : 0,
    prev : 1,
    next : 1,
    items : [],
    url : '',
    pagewrap : '.pagewrap',
    pagebtn : '.pagebtn',
    callback : function(curpage){},
    init : function(total, perpage, itemlen){
        if(typeof arguments[0] == 'object'){
            for(var key in arguments[0]){
                if(typeof this[key] != 'undefined'){
                    this[key] = arguments[0][key];
                }
            }
        }else{
            this.total = total;
            this.perpage = perpage;
            this.itemlen = itemlen;
        }
        this.pagecount = Math.ceil(this.total / this.perpage);
        return this;
    },
    calcpage : function(curpage){
        curpage = curpage || 1;
        this.prev = curpage - 1 || 1;
        this.next = curpage + 1 > this.pagecount ? this.pagecount : curpage + 1;
        var start = curpage - Math.floor(this.itemlen / 2);
        start = start < 1 ? 1 : start;
        this.items = [];
        for(var i = 0; i < this.itemlen && i + start <= this.pagecount; i ++ ){
            this.items.push(start + i);
        }
        if(this.items.length < this.itemlen && start > 1){
            i = start - 1;
            while(i > 0 && this.items.length < this.itemlen){
                this.items.unshift(i);
                i --;
            }
        }
        return this;
    },
    rewrite : function(curpage, type){
        curpage = parseInt(curpage) || 1;
        this.calcpage(curpage);
        type = type || 1;
        var html = '';
        var pagebtnclass = this.pagebtn.replace(".", "");
        switch(type){
            case 1:
                var firstpage = this.items[0] < 3 ? (this.items[0] == 2 ? '<a href="#" page="1" class="' + pagebtnclass + '" target="_self">1</a>' : '') : '<a href="#" page="1" class="' + pagebtnclass + '" target="_self">1</a><span class="ell">...</span>';
                var lastpage = this.items[this.items.length - 1] > this.pagecount - 2 ? (this.items[this.items.length - 1] == this.pagecount ? '' : '<a href="#" page="' + this.pagecount + '" class="' + pagebtnclass + '" target="_self">' + this.pagecount + '</a>') : '<span class="ell">...</span><a href="#" page="' + this.pagecount + '" class="' + pagebtnclass + '" target="_self">' + this.pagecount + '</a>';
                var prevpage = '<a title="上一页" href="#" page="' + this.prev + '" class="prev ' + pagebtnclass + '" target="_self">上一页</a>';
                var nextpage = '<a title="下一页" href="#" page="' + this.next + '" class="next ' + pagebtnclass + '" target="_self">下一页</a>';
                var totalpage = '<span class="p_nums">共<em>' + this.pagecount + '</em>页</span>';
                var itemspage = '';
                for(var i = 0; i < this.items.length; i++){
                    itemspage += this.items[i] == curpage ? '<span class="cur">' + curpage + '</span>' : '<a href="#" page="' + this.items[i] + '" class="' + pagebtnclass + '" target="_self">' + this.items[i] + '</a>';
                }
                html = prevpage + firstpage + itemspage + lastpage + nextpage + totalpage;
                break;
        }
        var self = this;
        $(this.pagewrap).html(html);
        $(this.pagewrap).find(this.pagebtn).click(function(){
            self.rewrite($(this).attr("page"));
            return false;
        });
        this.callback(curpage);
        return this;
    }
}

function moveTo(position){
    $(window).scrollTop(position);
}