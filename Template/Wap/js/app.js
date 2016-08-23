!(function() {
  var page = 2; 
  $('.scroll').dropload({
    scrollArea: window,
    loadDownFn: function(me) {
      switch (htmltype) {
        case 'shop':
          shop(me);
          break;
        case 'record':
          record(me);
          break;
        case 'history':
          history(me);
          break;
        case 'lottery':
          lottery(me);
          break;
        case 'records':
          records(me);
          break;
        case 'displays':
          displays(me);
          break;
        case 'announced':
          announced(me);
          break;
        case 'invite':
          invite(me);
          break;
        case 'commission':
          commission(me);
          break;
        default:
      }
    }
  });

  function tabs(tabTit, on, tabCon){
    invite('');
    $(tabCon).each(function() {
      $(this).children().eq(0).show();
    });
    $(tabTit).each(function() {
      $(this).children().eq(0).addClass(on);
    });
    $(tabTit).children().click(function() {
      page=1;
      $(this).addClass(on).siblings().removeClass(on);
      var index = $(tabTit).children().index(this);
      $(tabCon).children().eq(index).show().siblings().hide();
      $(tabCon).children().eq(index).find('#invite').is(function(){
        htmltype='invite';
        $(this).empty();
        invite('');
      });
      $(tabCon).children().eq(index).find('#commission').is(function(){
        htmltype='commission';
        $(this).empty();
        commission('');
      });
      $(tabCon).children().eq(index).find('#mention_list').is(function(){
        htmltype='mention_list';
        $(this).empty();
        mention_list('');
      });
    });
  }
    
  function shop(me) {
    $.getJSON($('#shop').attr('url'), {
      p: page
    }, function(json) {
      if (json) {
        var str = "";
        for (var i = 0; i < json.length; i++) {
          str += '<div class="col-sm-6 top-border rg-padding">';
          if(json[i].ten_name){
            str += '<div class="zhuanq">'+json[i].ten_name+'</div>';
          }
          if(json[i].ten_restrictions){
            str += '<span class="flag">限购'+json[i].ten_restrictions_num+'人次</span>';
          }
          str += '<a href="' + json[i].url + '"><div class="col-sm-12 prod-padding img-hidden">';
          str += '<img src="' + json[i].pic + '" class="img-circle"></div>';
          str += '<div class="col-sm-12 prod-padding pro-name">' + json[i].name + '</div>';
          str += '<div class="col-sm-7 prod-padding">';
          str += '<small>进度<span class="blue">' + json[i].jd + '%</span></small>';
          str += '<div class="progress"><span class="orange" style="width: ' + json[i].jd + '%;"></span>';
          str += '</div></div></a><div class="col-sm-5">';
          str += '<a href="' + json[i].url + '" class="btn btn-negative btn-outlined btn-index">马上参与</a></div></div>';
        }
        $("#shop").append(str);
        page++;
        me.resetload();
      } else {
        me.nodata();
      }
    });
  }

  function record(me) {
    $.getJSON($('#record').attr('url'), {
      p: page
    }, function(json) {
      if(json){
        var str = "";
        for (var i = 0; i < json['list'].length; i++) {
          str += '<li class="table-view-cell media right-no-padding"><a href="user/user/id/' + json['list'][i].id + '">';
          str += '<img class="media-object pull-left user-img" src="' + json['list'][i].img + '">';
          str += '<div class="media-body">' + json['list'][i].name + ' <small>（' + json['list'][i].address + '）</small>';
          str += '<p>参与了<span class="red">' + json['list'][i].count + '</span>人次 <small>' + json['list'][i].time + '</small></p>';
          str += '</div></a></li>';
        }
        $("#record").append(str);
        page++;
        me.resetload();
      } else {
        me.nodata();
      }
    });
  }

  function history(me) {
    $.getJSON($('#history').attr('url'), {
      p: page
    }, function(json) {
      if (json) {
        var str = "";
        for (var i = 0; i < json.length; i++) {
          str += '<ul class="table-view">';
          str += '<li class="table-view-cell"><a href="/user/over/no/' + json[i].no + '" class="navigate-right">第' + json[i].no + '期 <small>揭晓时间：' + json[i].time + '</small></a></li>';
          str += '<li class="table-view-divider history-ls">';
          str += '<a href="' + json[i].user_url + '"><img class="media-object pull-left user-img" src="' + json[i].img + '">';
          str += '<div class="media-body">获 奖 者：' + json[i].name + ' <small class="green">（' + json[i].address + '）</small>';
          str += '<p>幸运号码：<span class="red">' + json[i].kaijang_num + '</span><br>本期参与：<span class="red">' + json[i].number + '</span>人次</p>';
          str += '</div></a></li></ul>';
        }
        $("#history").append(str);
        page++;
        me.resetload();
      } else {
        me.nodata();
      }
    });
  }

  function lottery(me) {
    $.getJSON($('#lottery').attr('url'), {
      p: page
    }, function(json) {
      if (json) {
        var str = "";
        for (var i = 0; i < json.length; i++) {
          str += '<ul class="table-view">';
          str += '<li class="table-view-cell right-no-padding">';
          str += '<img class="media-object pull-left cart-img" src="' + json[i].pic + '">';
          if(json[i].express_no){
            str += '<div class="yfahuo active"></div>';
          }
          str += '<div class="media-body">(第' + json[i].no + '期) ' + json[i].name + ' <p><small>总需 ' + json[i].price + ' 人次</small><br>';
          str += '本期参与：<span class="red">' + json[i].count + '</span>人次<br><span class="red">幸运号码：' + json[i].kaijang_num + '</span><br>';
          str += '揭晓时间：' + json[i].kaijang_time;
          if(json[i].express_no){
            str += '<br>物流名称：'+json[i].express_name+'<br>货运单号：'+json[i].express_no;
          }
          str += '</p>';
          if (json[i].shared == 1) {
            str += '<a href="shared.html" class="btn btn-positive"><span class="demo-icon icon-camera-1"></span> 晒单炫耀</a>';
          }
          str += '</div></li></ul>';
        }
        $("#lottery").append(str);
        page++;
        me.resetload();
      } else {
        me.nodata();
      }
    });
  }

  function records(me) {
    $.getJSON($('#records').attr('url'), {
      p: page
    }, function(json) {
      if (json) {
        var str = "";
        for (var i = 0; i < json.length; i++) {
          if (json[i].state == 2) {
            str += '<ul class="table-view"><li class="table-view-cell"><a href="/shop/over/id/'+json[i].pid+'" class="navigate-right"><small>(第' + json[i].no + '期)</small> ' + json[i].name + '</a></li><li class="table-view-divider history-ls">';
            str += '<img class="media-object pull-left cart-img" src="' + json[i].pic + '"><div class="media-body">';
            str += '<p><small>总需 ' + json[i].price + ' 人次</small><br>';
            str += '本期参与：<span class="red">' + json[i].count + '</span>人次<small> <a href="' + json[i].lookurl + '">查看号码</a></small><br>揭晓时间：' + json[i].kaijang_time + '';
            str += '</p><span class="label label-fullb">获得者：<a href="' + json[i].userurl + '">' + json[i].user.name + '</a>';
            str += '本期参与：<span class="red">' + json[i].user.count + '</span>人次<br>幸运号码：<span class="red">' + json[i].kaijang_num + '</span><br>';
            str += '</span></div></li></ul>';
          } else {
            str += '<ul class="table-view"><li class="table-view-cell"><a href="/shop/index/id/'+json[i].pid+'" class="navigate-right"><small>(第' + json[i].no + '期)</small> ' + json[i].name + '</a></li><li class="table-view-divider history-ls">';
            str += '<img class="media-object pull-left cart-img" src="' + json[i].pic + '"><div class="media-body">';
            str += '<p>本期参与：<span class="red">' + json[i].count + '</span>人次 ';
            str += '<a href="' + json[i].lookurl + '">查看号码</a></p><div class="col-sm-12"><div class="col-sm-12 prod-padding">';
            str += '<div class="progress"><span class="orange" style="width: ' + json[i].jd + '%;"></span></div></div>';
            str += '<div class="col-sm-6 prod-padding">总需' + json[i].price + '人次</div><div class="col-sm-6 prod-padding text-right">剩余 <span class="blue">' + (json[i].price - json[i].number) + '</span></div>';
            str += '</div></div></li></ul>';
          }
        }
        $("#records").append(str);
        page++;
        me.resetload();
      } else {
        me.nodata();
      }
    });
  }

  function displays(me) {
    $.getJSON($('#displays').attr('url'), {
      p: page
    }, function(json) {
      if (json) {
        var str = "";
        for (var i = 0; i < json.length; i++) {
          str += '<ul class="table-view"><li class="table-view-divider history-ls bottom-no-border">';
          str += '<a href="' + json[i].user_url + '"><img class="media-object pull-left user-img" src="' + json[i].img + '"></a><div class="media-body">';
          str += '获 奖 者：<a href="' + json[i].user_url + '">' + json[i].name + '</a><small class="green">（' + json[i].address + '）</small><a href="' + json[i].url + '"><p>';
          str += '幸运号码：<span class="red">' + json[i].no + '</span><br>本期参与：<span class="red">' + json[i].count + '</span>人次</p>';
          str += '<p>' + json[i].content + '</p>';
          for (var x = 0; x < json[i].thumbpic.length; x++) {
            str += '<div class="col-sm-3 prod-padding"><div class="up-img-zy">';
            str += '<img src="' + json[i].thumbpic[x] + '" class="img-circle"></div></div>';
          }
          str += '</a></div></li></ul>';
        }
        $("#displays").append(str);
        page++;
        me.resetload();
      } else {
        me.nodata();
      }
    });
  }

  function announced(me) {
    $.getJSON($('#announced').attr('url'), {
      p: page
    }, function(json) {
      if (json) {
        var str = "";
        for (var i = 0; i < json.length; i++) {
          if (json[i].state == 1) {
            str += '<div class="col-sm-6 top-border rg-padding" pid="' + json[i].pid + '">';
            str += '<a href="/shop/over/id/' + json[i].pid + '">';
            str += '<div class="col-sm-12 prod-padding img-hidden"><img src="' + json[i].pic + '" class="img-circle"></div>';
            str += '<div class="col-sm-12 prod-padding pro-name">' + json[i].name + '</div>';
            str += '<div class="col-sm-5 ann-text"><small>期号：</small></div>';
            str += '<div class="col-sm-7 ann-text"><small>' + json[i].no + '</small></div>';
            str += '<div class="col-sm-12 ann-text"><small class="red"><span class="demo-icon icon-clock-1"></span> 即将揭晓：</small></div>';
            str += '<div class="col-sm-12 ann-time"><b id="clock" class="times red settime" diffe="' + json[i].kaijang_diffe + '"></b></div>';
            str += '</a></div>';
          } else {
            str += '<div class="col-sm-6 top-border rg-padding">';
            str += '<a href="/shop/over/id/' + json[i].pid + '">';
            str += '<div class="col-sm-12 prod-padding img-hidden"><img src="' + json[i].pic + '" class="img-circle"></div>';
            str += '<div class="col-sm-12 prod-padding pro-name">(第' + json[i].no + '期) ' + json[i].name + '</div>';
            str += '<div class="col-sm-5 ann-text"><small>获 得 者：</small></div>';
            str += '<div class="col-sm-7 ann-text"><small><a href="/user/user/id/'+ json[i].uid +'">' + json[i].user + '</a></small></div>';
            str += '<div class="col-sm-5 ann-text"><small>参与人次：</small></div>';
            str += '<div class="col-sm-7 ann-text"><small>' + json[i].count + '</small></div>';
            str += '<div class="col-sm-5 ann-text"><small>幸运号码：</small></div>';
            str += '<div class="col-sm-7 ann-text"><small class="red">' + json[i].kaijang_num + '</small></div>';
            str += '<div class="col-sm-5 ann-text"><small>揭晓时间：</small></div>';
            str += '<div class="col-sm-7 ann-text"><small>' + json[i].kaijang_time + '</small></div></a></div>';
          }
        }
        $("#announced").append(str);
        page++;
        if(me){
          me.resetload();
        }
        $("#announced").find('.settime:not(:has(span))').each(function(){
          var xthis=$(this);
          xthis.downCount({difference:xthis.attr('diffe'),now:servertime*1000},function(){
            announced_ajax(xthis);
          });
        });
      } else {
        me.nodata();
      }
    });
  }

  function invite(me) {
    $.getJSON($('#invite').attr('url'), {
      p: page
    }, function(result) {
      var str = "";
      if(page==1){
        str += '<li class="table-view-divider">成功邀请 <span class="red">'+result['count']+'</span> 位会员注册</li>';
      }
      if(result['list']){
        $.each(result['list'], function(num, list){
          str += '<li class="table-view-cell right-no-padding">';
          str += '<div class="ls-left"><img src="' + list.user_pic + '">';
          str += '<div class="ls-left-centont">' + list.user + '<br><small>' + list.time + '</small></div></div></li>';
        });
        $("#invite").append(str);
        page++;
        if(me){me.resetload();}
      } else {
        if(me){me.nodata();}
      }
    });
  }

  function commission(me) {
    $.getJSON($('#commission').attr('url'), {
      p: page
    }, function(result) {
      var str = "";
      if(result){
        $.each(result, function(num, list){
          str += '<li class="table-view-cell right-no-padding"><div class="ls-left">';
          str += '<img src="' + list.img + '">';
          str += '<div class="ls-left-centont">' + list.name + '<br><small>' + list.create_time + '</small></div></div>';
          str += '<p>' + list.shop_name + '(第' + list.shop_no + '期)</p>';
          str += '<div class="row padding-r"><div class="col-sm-6">购买金额：<span class="red">' + list.number + '元</span></div>';
          str += '<div class="col-sm-6 text-right">佣金：<span class="red">' + list.money + '元</span></div></div></li>';
        });
        $("#commission").append(str);
        page++;
        if(me){me.resetload();}
      } else {
        if(me){me.nodata();}
      }
    });
  }

  function mention_list(me) {
    $.getJSON($('#mention_list').attr('url'), {
      p: page
    }, function(result) {
      var str = "";
      if(result){
        $.each(result, function(num, list){
          str += '<li class="table-view-cell right-no-padding">';
          str += '<div class="ls-left"><span class="red">' + list.money + ' 元</span></div>';
          str += '<div class="ls-center"><span>' + list.type + '</span></div>';
          str += '<div class="ls-right"><span class="red">' + list.pay_state + '</span></div>';
          str += '<p><small>' + list.time + '</small></p></li>';
        });
        $("#mention_list").append(str);
        page++;
        if(me){me.resetload();}
      } else {
        if(me){me.nodata();}
      }
    });
  }

  function invitex(){

  }

  $('#announced').is(function(){
    if(htmltype=='announced'){
      page=1;
      $('.dropload-down').html('<div class="dropload-load"><span class="loading"></span>加载中...</div>');
      announced();
    }
  });
  tabs(".segmented-control","active",".scroll");

}());

function pinboard(obg) {
  $('[name="pinboard"]').children(".radio-btn").removeClass('active');
  $.get($(obg).attr('url'), function(data) {
    $(obg).children(".radio-btn").addClass('active');
  });
}

$('.settime').each(function(){
  var xthis=$(this);
  xthis.downCount({difference:xthis.attr('diffe'),now:servertime*1000},function(){
      if(product_over){
        location.reload();
      }else{
        announced_ajax(xthis);
      }
  });
});

function announced_ajax(obg){
  $.getJSON($('#announced').attr('url'), {
      pid: $(obg).parents('.top-border').attr('pid')
    }, function(json) {
      if (json) {
        var str = '<a href="/shop/over/id/' + json[0].pid + '">';
        str += '<div class="col-sm-12 prod-padding img-hidden"><img src="' + json[0].pic + '" class="img-circle"></div>';
        str += '<div class="col-sm-12 prod-padding pro-name">(第' + json[0].no + '期) ' + json[0].name + '</div>';
        str += '<div class="col-sm-5 ann-text"><small>获 得 者：</small></div>';
        str += '<div class="col-sm-7 ann-text"><small><a href="/user/user/id/'+ json[0].uid +'">' + json[0].user + '</a></small></div>';
        str += '<div class="col-sm-5 ann-text"><small>参与人次：</small></div>';
        str += '<div class="col-sm-7 ann-text"><small>' + json[0].count + '</small></div>';
        str += '<div class="col-sm-5 ann-text"><small>幸运号码：</small></div>';
        str += '<div class="col-sm-7 ann-text"><small class="red">' + json[0].kaijang_num + '</small></div>';
        str += '<div class="col-sm-5 ann-text"><small>揭晓时间：</small></div>';
        str += '<div class="col-sm-7 ann-text"><small>' + json[0].kaijang_time + '</small></div></a>';
      }
      $(obg).parents('.top-border').html(str);
  })  
}