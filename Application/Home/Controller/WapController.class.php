<?php
namespace Home\Controller;
use Think\Controller;
use Com\WechatAuth;

class WapController extends Controller {

	public function _empty(){
		$this->redirect("./Web/index.html");
	}

    protected function _initialize(){
		$config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =  config_lists();
            S('DB_CONFIG_DATA',$config);
        }
        C($config);
		$this->web_path=__ROOT__."/";
		$this->web_title=C("WEB_SITE_TITLE");
		$this->web_logo="/".C('TMPL_PATH')."/Web/images/".C("WEB_LOGO");
		$this->web_keywords=C("WEB_SITE_KEYWORD");
		$this->web_description=C("WEB_SITE_DESCRIPTION");
		$this->web_icp=C("WEB_SITE_ICP");
		$this->web_url=C("WEB_URL");
		$this->web_currency=C("WEB_CURRENCY");
		$this->wx_pay=C('WX_PAY_MCHID');
        $this->ali_pay=C('ALI_PAY_PARTNER');
        $this->band_pay=C('BAND_PAY_MID');
        $this->yun_pay=C('YUN_PAY_ID');
        $this->pay_pal=C('PAY_PAL');
        $this->iapp_pay=C('IAPP_PAY_APPID');
		$this->web_time=NOW_TIME;
		activity(3,'',UID);
		if(is_weixin()){
			$this->tplpath="./".C('TMPL_PATH')."/Wap/";
			$this->web_tplpath=$this->web_path.C('TMPL_PATH')."/Wap/";
			C('CACHE_PATH',RUNTIME_PATH."/Cache/".MODULE_NAME."/Wap/");
			if(!session('unionid')){
				$this->auth  = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'));
				$token = session("token");
				if(!$token){
					if(!I('get.code')){
						header("Location: ".$this->auth->getRequestCodeURL(get_url()));
						return false;
					}
					$token=$this->auth->getAccessToken('code',I('get.code'));
					session(array('expire' => $token['expires_in']));
					session("token", $token['access_token']);
					session("openid", $token['openid']);
					session("unionid", $token['unionid']);
					$this->weixin_login($token['openid']);
					$this->Subscribe=$this->isSubscribe($token['openid']);
				}
			}
		}elseif(is_mobile()){
			$this->tplpath="./".C('TMPL_PATH')."/Wap/";
			$this->web_tplpath=$this->web_path.C('TMPL_PATH')."/Wap/";
			C('CACHE_PATH',RUNTIME_PATH."/Cache/".MODULE_NAME."/Wap/");
		}else{
			$this->tplpath="./".C('TMPL_PATH')."/Web/";
			$this->web_tplpath=$this->web_path.C('TMPL_PATH')."/Web/";
			C('CACHE_PATH',RUNTIME_PATH."/Cache/".MODULE_NAME."/Web/");
		}
		define('UID',is_login());
    }

    protected function isSubscribe($unionid){
    	$usertoken = session("usertoken");
		if($usertoken){
            $auth = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'), $usertoken);
        } else {
            $auth  = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'));
            $token = $auth->getAccessToken();
            session(array('expire' => $token['expires_in']));
            session("usertoken", $token['access_token']);
        }
        $data=$auth->userInfo($unionid);
        if($data['errcode']==42001 || $data['errcode']==40001){
			session("token",null);
			$this->isSubscribe($unionid);
		}
		return $data['subscribe'];
    }
	
	protected function weixin_login($unionid){
		if($unionid){
			$user=M('User')->where(array('openid'=>$unionid,'status'=>1))->field('id,nickname,openid')->find();
			if($user){
		        if(!$user['openid']){
		        	M('User')->where(array("id"=>$user['id']))->setField('openid',session("openid"));
		        }
		        D('Public')->autoLogin($user);
				activity(2,$user['id'],$user['id']);
				define('UID',$user['id']);
			}else{
				$this->auth  = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'),session('token'));
				$data = $this->auth->getUserInfo($unionid);
				if($data['errcode']==42001 || $data['errcode']==40001){
					session("token",null);
					$this->weixin_login($unionid);
				}
				if(session('uid')){
					$data['tid']=session('uid');
				}
				$data['password']=think_ucenter_md5($data['openid']);
				$data['login_ip']=ip2long(get_client_ip());
				$data['status']=1;
				$data['create_time']=NOW_TIME;
				$data['activation']=1;
				unset($data['language'],$data['privilege']);
				$uid=M('User')->add($data);
				$auth = array(
		            'uid'             => $uid,
		            'username'        => $data['nickname']
		        );
				session('user_auth', $auth);
		        session('user_auth_sign', data_auth_sign($auth));
				if(session('uid')){
	                activity(7,$uid,session('uid'));
	                session('uid',null);
	            }
				activity(1,$uid,$uid);
				define('UID',$uid);
			}
		}
	}

	protected function getSignPackage($url) {
		$webtoken = session("webtoken");
		if($webtoken){
            $auth = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'), $webtoken);
        } else {
            $auth  = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'));
            $webtoken = $auth->getAccessToken();
            session(array('expire' => $webtoken['expires_in']));
            session("webtoken", $webtoken['access_token']);
        }
        if(!session("jsapiTicket")){
        	$jsapiTicket = $auth->getJsApiTicket();
			if($jsapiTicket['errcode']==42001 || $jsapiTicket['errcode']==40001){
				session("jsapiTicket",null);
				$this->getSignPackage();
			}
			session(array('expire' => $jsapiTicket['expires_in']));
	        session("jsapiTicket", $jsapiTicket['ticket']);
        }
		$timestamp=NOW_TIME;
		$nonceStr=$auth->getNonceStr();
		$string = "jsapi_ticket=".session("jsapiTicket")."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;
		$signature = sha1($string);
		$signPackage = array(
		  "debug"     => false,
		  "appId"     => C('WX_APPID'),
		  "nonceStr"  => $nonceStr,
		  "timestamp" => $timestamp,
		  "signature" => $signature,
		  "jsApiList" => ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone','chooseImage','uploadImage']
		);
		return $signPackage;
	}
}
