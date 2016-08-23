<?php
namespace Admin\Controller;
use Com\Wechat;
use Com\WechatAuth;

class MenuController extends WebController {
	private $auth;
	
	public function _initialize(){
	 	parent::_initialize();
		S(array('prefix'=>'menu_'));
        $token = session("token");
		if($token){
            $this->auth = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'), $token);
        } else {
            $this->auth  = new WechatAuth(C('WX_APPID'), C('WX_APPSECRET'));
            $token = $this->auth->getAccessToken();
            session(array('expire' => $token['expires_in']));
            session("token", $token['access_token']);
        }
	}
	
	public function index(){
		if(IS_POST){
			$menu=$this->auth->menuCreate(json_decode(stripslashes($_POST['data']),true));
			if($menu['errcode'] == 42001 || $menu['errcode'] == 40001){
				session("token", null);
				$this->index();
				return false;
			}
			if($menu['errcode']){
				$this->error(weixinError($menu['errcode']));
			}else{
				S('menu',null);
				$this->success('保存成功！');
			}
		}else{
			if(!$menu=S('menu')){
				$menu=$this->auth->menuGet();
				S('menu',$menu);
			}
			if($menu['errcode'] == 42001 || $menu['errcode'] == 40001){
				session("token", null);
				$this->index();
				S('menu',null);
				return false;
			}
			if($menu['errcode'] && $menu['errcode']!=46003){
				$this->error(weixinError($menu['errcode']));
			}else{
				$this->assign('nav', $menu['menu']['button']);
				$this->meta_title = '自定义菜单';
				$this->display();
			}
		}
    }
}
