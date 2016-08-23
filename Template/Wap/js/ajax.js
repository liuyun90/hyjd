(function() {

//ajax get请求
    $('.ajax-get').click(function(){
        var target;
        var that = this;
        if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) {
            $.get(target).success(function(data){
				layer.open({content: data.info,time: 2});
				setTimeout(function(){
					if (data.url) {
						location.href=data.url;
					}else if( $(that).hasClass('no-refresh')){
						var callback = $(that).attr('callback');
						if(callback){
							eval(callback);
						}
					}else{
						location.reload();
					}
				},2000);
            });

        }
        return false;
    });
	$(document).on('click','.ajax-post',function(){
        var target,query,form;
        var target_form = $(this).attr('target-form');
        var that = this;
        var nead_confirm=false;
        var index=layer.open({type: 2});
        if( ($(this).attr('type')=='submit') || (target = $(this).attr('href')) || (target = $(this).attr('url')) ){
            form = $('.'+target_form);
            if ($(this).attr('hide-data') === 'true'){//无数据时也可以使用的功能
            	form = $('.hide-data');
            	query = form.serialize();
            }else if (form.get(0)==undefined){
            	return false;
            }else if ( form.get(0).nodeName=='FORM' ){
                if($(this).attr('url') !== undefined){
                	target = $(this).attr('url');
                }else{
                	target = form.get(0).action;
                }
                query = form.serialize();
            }else{
                query = form.find('input,select,textarea').serialize();
            }
            $(that).attr('autocomplete','off').prop('disabled',true);
            $.post(target,query).success(function(data){
                layer.close(index);
                layer.open({content: data.info,time: 2});
				setTimeout(function(){
					if (data.url) {
						location.href=data.url;
					}else if( $(that).hasClass('no-refresh')){
						var callback = $(that).attr('callback');
						if(callback){
							eval(callback);
						}
					}else{
						location.reload();
                        $(that).attr('autocomplete','on').prop('disabled',false);
					}
				},2000);
            });
        }
        return false;
    });
})();