function category_submit_check(){
    var name = $('input[name=name]').val();
    if(!name){
        alert('分类名称不能为空');
        return false;
    }
    return true;
}