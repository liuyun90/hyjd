(function() {
	$('.paybut').parents('form').submit(function() {
		var paytype = $('input[name="pay_type"]:checked').val();
		$(this).find('.paybut').prop('disabled', true);
		switch (paytype) {
			case '2':
				callpay(this);
				break;
			case '3':
				aliPay(this);
				break;
			case '5':
				yunPay(this);
				break;
			case '6':
				payPal(this);
				break;
			case '7':
				iappPay(this);
				break;
			default:
				yuePay(this);
		}
		return false;
	})

	function yuePay(event) {
		$.post("/pay/yuepay", {
			pid: $('input[name="pid"]').val(),
			price: $('input[name="price"]').val()
		}).success(function(data){
			if (data.url) {
				location.href = data.url;
			} else {
				$(event).prop('disabled', false);
			}
		});
	}

	//调用微信JS api 支付
	function weixinPay() {
		$.get("/pay/pay_weixin/price/" + $('input[name="price"]').val()+"/pid/"+$('input[name="pid"]').val()).success(function(status){
			if(status.status){
				if(status.info.return_msg!="OK"){
					layer.open({content: status.info.return_msg,time: 2});
					return false;
				}
				var weixin=$('input[name="weixin"]').val();
				if(weixin!=1){
					var wxurl = 'weixin://wap/pay?appid='+status.info.parameters.appId+'&timestamp='+status.info.parameters.timeStamp+'&noncestr='+status.info.parameters.nonceStr+'&package=WAP&prepayid='+status.info.parameters.prepayid+'&sign='+status.info.parameters.paySign;
					layer.open({
					    content: '<p>1、请在微信内完成支付，支付成功页面自动跳转</p><p>2、如果您未支付，请点击“去支付”完成支付</p><p>3、如果您未安装微信6.0.2版本及以上版本客户端，请先安装并登陆微信完成支付</p>',
					    btn: ['<a style="text-decoration: none" href="'+wxurl+'">去支付</a>','关闭'],
					    shadeClose: false,
					    yes: function(){
					        location.href = wxurl;
					    }, no: function(){
							$('.paybut').prop('disabled', false);
    					}
					});
				}else{
					WeixinJSBridge.invoke(
						'getBrandWCPayRequest',status.info.parameters,function(res) {
							if(res.err_msg == "get_brand_wcpay_request:ok"){
								if (status.url){
									location.href = status.url;
								}
							}
						}
					);		
				}		
			}
		});
	}

	function aliPay(event){
		$('form').attr('action','/pay/pay_alipay').submit();
	}

	function yunPay(event){
		$('form').attr('action','/pay/pay_yun').submit();
	}

	function payPal(event){
		$('form').attr('action','/pay/pay_pal').submit();
	}

	function iappPay(event){
		$('form').attr('action','/pay/pay_iapp/type/h5').submit();
	}

	function callpay(event) {
		var weixin=$('input[name="weixin"]').val();
		if(!$('input[name="price"]').val()){
			layer.open({content: '请选择充值金额！',time: 2});
			$(event).prop('disabled', false);
			return false;
		}
		if (typeof WeixinJSBridge == "undefined" && weixin==1) {
			if (document.addEventListener) {
				document.addEventListener('WeixinJSBridgeReady', weixinPay, false);
			} else if (document.attachEvent) {
				document.attachEvent('WeixinJSBridgeReady', weixinPay);
				document.attachEvent('onWeixinJSBridgeReady', weixinPay);
			}
		} else {
			weixinPay();
		}
	}

	$('.dollar').click(function() {
		$('.dollar').removeClass('active');
		$(this).addClass('active');
		if(!$(this).hasClass('not')){
			$('[name="price"]').val($(this).text());
		}else{
			$('[name="price"]').val($(this).find('input').val());
		}
	});

	$('.dollar-more').click(function() {
		$('.dollar').removeClass('active');
	});

	$('input[name="price_ye"]').bind('input propertychange',function(event){
		$('input[name="price"]').val($(this).val());
	});
}());