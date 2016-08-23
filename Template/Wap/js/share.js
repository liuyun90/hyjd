$.get("/user/wxshare/").success(function(status){
  if(status.status){
    wx.config(status.info);
  }
});
wx.ready(function () {
	var imgUrl=$('.product-topimg img').attr('src');
	if(imgUrl){
		imgUrl='http://www.ilhuo.cn'+imgUrl;
	}else{
		imgUrl='http://www.ilhuo.cn/wx-logo.png';
	}
	var desc=['1元就买了个腰子6，不信你就往前走，别回头。只要1元，进来了就不后悔，进来了就不上当。大到家用电器，小到针头线脑一律只要1元。1元就能实现梦想，走向人生巅峰迎娶百富美。',
		'微信零钱除了发红包好可以做这些，你绝对想不到！',
		'微信零钱除了发红包好可以做这些，免费攻略来了。',
		'让万能的朋友圈来告诉你，1块钱究竟都可以做些神马。',
		'大鹏都做了煎饼侠，你还在等神马？赶快来加入逆袭行列吧',
		'一瞬间从屌丝变身高富帅，迎娶白富美，仅仅只是1元钱的距离。',
		'万一买不起自己想要的东西怎么办？来看看吧。都只要1块钱。1块钱啊~',
		'梦想总是辣么大。自己却是辣么小。NOW，现实与梦想的距离仅差1元'
	];
	var title=desc[Math.floor(Math.random()*desc.length)];
	var wxshare={
		title: title, // 分享标题
		desc: title, // 分享描述
		link: 'http://www.ilhuo.cn/activity/activity/id/59', // 分享链接
		imgUrl: imgUrl, // 分享图标
		success: function () { 
		   $('.backdrops').hide();
		},
		cancel: function () { 
			$('.backdrops').hide();
		}
	}
  wx.onMenuShareTimeline(wxshare);
  wx.onMenuShareAppMessage(wxshare);
  wx.onMenuShareQQ(wxshare);
  wx.onMenuShareWeibo(wxshare);
  wx.onMenuShareQZone(wxshare);
});

$(".navigate-shared").click(function(){
    $(".backdrops").show();
});
$(".backdrops").click(function(){
    $(".backdrops").hide();
});