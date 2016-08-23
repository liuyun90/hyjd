<?php
namespace Admin\Controller;
use Com\WechatAuth;

class ReplyController extends WebController {
	private $auth;
	
	public function _initialize(){
	 	parent::_initialize();
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

    public function beadded(){
		if(IS_POST){
			if(I('msgtype')=="text"){
				$content=I('content');
			}else{
				$content=I('media_id');
			}
			F('SUBSCRIBE_DATA',array("msgtype"=>I('msgtype'),"content"=>$content));
			$this->success('编辑成功！');
		}else{
			$info = F('SUBSCRIBE_DATA');
			if($info['msgtype']!="text"){
				$info['media_id']=$info['content'];
			}
			$this->meta_title = '消息设置';
			$this->assign('info', $info);
			$this->display();
		}
    }
	
	public function keyword(){
        $list   =   $this->lists('reply', $map);
		$this->assign('list', $list);
        $this->meta_title = '关键字回复';
        $this->display();
    }
	
	public function editkeyword($id = null){
        $Reply = D('Reply');
        if(IS_POST){
            if(false !== $Reply->update()){
                $this->success('编辑成功！', U('keyword'));
            } else {
                $error = $Reply->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
			$info=$Reply->info($id);
            $this->assign('info', $info);
            $this->meta_title = '编辑关键字回复';
            $this->display();
        }
    }

    public function addkeyword(){
        $Reply = D('Reply');
        if(IS_POST){
            if(false !== $Reply->update()){
                $this->success('新增成功！', U('keyword'));
            } else {
                $error = $Reply->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $this->meta_title = '添加关键字回复';
            $this->display('editkeyword');
        }
    }

	 public function delkeyword(){
		$id = array_unique((array)I('id',0));
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $Reply = D('Reply')->remove($id);
        if($res !== false){
            $this->success('删除关键字回复成功！');
        }else{
            $this->error('删除关键字回复失败！');
        }
    }
	
	public function group(){
		if(IS_POST){
			$filter=array("is_to_all"=>I('is_to_all')=="true"?true:false,"group_id"=>I('group_id'));
			if(I('msgtype')=="text"){
				$content=array("content"=>I('content'));
			}else{
				$content=array("media_id"=>I('media_id'));
			}
			$msg=$this->auth->messageSendall($filter,$content,I('msgtype'));
			if($msg['errcode'] == 42001  || $msg['errcode'] == 40001){
				session("token", null);
				$this->group();
				return false;
			}
			if($msg['errcode'] == 0){
				msgData($msg['msg_id'],array('msg_data_id'=>$msg['msg_data_id'],'media_id'=>$content,'msgtype'=>I('msgtype'),"group_id"=>I('group_id'),"send_timt"=>NOW_TIME));
				$this->success('发送成功！', U('reply/record'));
			}else{
				$this->error(weixinError($msg['errcode']));
			}
		}else{
			$group=$this->auth->groupsGet();
			$errcode=$group["errcode"];
			if($_GET['media_id']){
				$news=json_decode($this->auth->mediaGet(I('media_id')), true);
				$errcode=$news["errcode"];
			}
			if($errcode == 42001  || $errcode==40001){
				session("token", null);
				$this->group();
				return false;
			}
			if($errcode){
				$this->error(weixinError($errcode));
			}else{
				$this->meta_title = '消息群发';
				$this->assign('news',$news['news_item']);
				$this->assign('group',$group['groups']);
				$this->display();
			}
		}
    }
	
	 public function record(){
        $this->meta_title = '群发记录';
        $this->display();
    }

    public function edit($id = null){
        $Config = D('Config');
        if(IS_POST){
            if(false !== $Config->update()){
                $this->success('编辑成功！', $_SERVER['HTTP_REFERER']);
            } else {
                $error = $Config->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        }
    }
}
