
function showmsg(data){
    if(data.success){
        alert(data.msg || '更新成功');
    }else{
        alert(data.msg || '更新失败');
    }
    return false;
}

$(function(){
    $('.questionmark').click(function(){
        var $this = $(this), $intro = $this.parents('.dataitem').find('.itemtip');
        if($intro.css('display') == 'block'){
            $intro.hide();
            return false;
        }
        var offset = $this.offset();
        if($intro.width() < 358){
            $intro.css({width : '358px'});
        }
        if($intro.height() < 109){
            $intro.css({height : '109px'});
        }
        $intro.css({top : 20 + 'px', left : offset.left + 5 + 'px'}).show();
        return false;
    });
    $('form').submit(function(){
        var $this = $(this), $titleinput = $this.find('input[name=title]');
        if($titleinput.length > 0 && $.trim($titleinput.val()) == ''){
            alert('标题不能为空。');
            return false;
        }
    });
});