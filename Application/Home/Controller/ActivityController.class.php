<?php
namespace Home\Controller;
use Com\Wechat;
use Com\WechatAuth;

class ActivityController extends WapController {

    public function index(){
		$list=D('Activity')->lists();
		$this->assign('list',$list);
		$this->display($this->tplpath."activity.html");
	}
	
	public function activity($id){
		$this->assign('ticket',$ticket);
		if(is_weixin() || is_mobile()){
			$this->activity_tpl($id,'wap_index_tpl');
		}else{
			$this->activity_tpl($id,'web_index_tpl');
		}
	}
	
	public function lists($id){
		if(is_weixin() || is_mobile()){
			$this->activity_tpl($id,'wap_list_tpl');
		}else{
			$this->activity_tpl($id,'web_index_tpl');
		}
	}
	
	public function content($id){
		if(is_weixin() || is_mobile()){
			$this->activity_tpl($id,'wap_content_tpl');
		}else{
			$this->activity_tpl($id,'web_content_tpl');
		}
	}
	
	public function ticket(){
		$webtoken = session("webtoken");
		if($webtoken){
            $this->auth = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'), $webtoken);
        } else {
            $this->auth  = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'));
            $webtoken = $this->auth->getAccessToken();
            session(array('expire' => $token['expires_in']));
            session("webtoken", $token['access_token']);
        }
		$data=$this->auth->qrcodeCreate(1,604800);
		if($data['errcode']==42001 || $data['errcode']==40001){
			session("webtoken",null);
			$this->ticket();
		}
		return $this->auth->showqrcode($data['ticket']);
	}
	
	protected function activity_tpl($id,$tpl){
		$info=D('Activity')->detail($id);
		$list_log=D('Activity')->list_log($id,UID);
		if(IS_AJAX){
			$this->ajaxReturn(array('info'=>$info,'list'=>$list_log,'uid'=>UID));
		}else{
			$this->assign('info',$info);
			$this->assign('list_log',$list_log);
			$this->display($this->tplpath.$info[$tpl]);
		}
	}

	public function sendhonbao(){
		if(M('user')->where('id='.UID.' and status=1')->getField('hongbao')<1){
			$this->auth  = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'));
			$data['mch_billno']=A('Pay')->create_sn();
			$data['mch_id']=C('WX_PAY_MCHID');
			$data['send_name']=C("WEB_SITE_TITLE");
			$data['re_openid']=session('openid');
			$data['total_amount']=100;
			$data['total_num']=1;
			$data['wishing']='感谢参与分享赢红包活动';
			$data['act_name']='分享赢红包活动';
			$data['remark']='分享好友一起来赢红包';
			$return=$this->auth->sendhonbao($data,C('WX_PAY_KEY'));
		}
		if($return['return_code']=='SUCCESS'){
			activity_log('hongbao_jl',1,UID);
		}
		$this->ajaxReturn($return);
	}

	public function apphongbao(){
		if(!defined('UID') || UID==0){
	    	$this->error('请先登录！');
	    }
	    $user=M('user')->field('hongbao,phone')->where('id='.UID.' and status=1 and ')->find();
	    if($user['hongbao']<1){
	    	if($user['phone']==0){
	    		$this->error('请先绑定手机！');
	    		return false;
	    	}
	    	M('user')->where('id='.UID.' and status=1')->setInc('black',1);
	    	activity_log('hongbao_jl',1,UID);
	    	$this->success('红包发放成功，请查看余额！');
	    }else{
	    	$this->error('该红包每用户只可以领一次！');
	    }
	}

	public function hongbao_list(){
		$hongbao=M('user')->field('nickname')->where('hongbao=1')->limit(10)->select();
		if(IS_AJAX){
			$this->ajaxReturn(array('hongbao' => $hongbao));
		}else{
			return $hongbao;
		}
	}

	public function turntable_num(){
		$turntable=M('user')->where('id='.UID)->getField('turntable');
		if(IS_AJAX){
			$this->ajaxReturn(array('turntable' => $turntable,'uid'=>UID));
		}else{
			return $turntable;
		}
	}

	public function turntable_list(){
		return M('activity_log')->where('activity_id=59')->limit(50)->order('price desc')->select();
	}

	public function getTurntable(){
		return activity_log('share_turntable',UID,UID);
	}

	public function getPrize(){
		$turntable=M('user')->where('id='.UID)->getField('turntable');
		if($turntable<=0){
			$this->ajaxReturn(array('v' =>1,'turntable' =>0));
			return false;
		}
		$prize_arr=array(
			array('num'=>100,'v'=>1),
			array('num'=>50,'v'=>2),
			array('num'=>0,'v'=>2700),
			array('num'=>10,'v'=>20),
			array('num'=>1,'v'=>1000),
			array('num'=>20,'v'=>7),
			array('num'=>0,'v'=>2700),
			array('num'=>1,'v'=>1000),
			array('num'=>3,'v'=>70),
			array('num'=>0,'v'=>2500));
		$proSum=10000;
		foreach($prize_arr as $k=>$v){
			$randNum=mt_rand(1,$proSum);//随机数
			if($randNum<=$v['v']){
				activity_log('turntable_jl',$v['num'],UID);
				M('user')->where('id='.UID.' and turntable>0')->setDec('turntable');
				$this->ajaxReturn(array('v' =>$k,'turntable' => $turntable));
				break;
			}else{
				$proSum-=$v['v'];
			}
		}	
	}
}