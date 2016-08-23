<?php
namespace Admin\Controller;
use Think\Controller;
use Com\WechatAuth;

class MaterialController extends WebController {
	private $auth;
	
	public function _initialize(){
	 	parent::_initialize();
		S(array('prefix'=>'media_'));
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

    public function iframe(){
		$REQUEST    =   (array)I('request.');
		$p=$REQUEST['p']>0?($REQUEST['p']-1):$REQUEST['p'];
		$listRows = 12;
		if(!$list=S('mediaList'.$REQUEST['type'].$p)){
			$list=$this->auth->mediaListGet($REQUEST['type'],$p*$listRows,$listRows);
			S('mediaList'.$REQUEST['type'].$p,$list);
		}
		if($list["errcode"] == 42001  || $menu['errcode'] == 40001){
            session("token", null);
			S('mediaList'.$REQUEST['type'].$p,null);
            $this->iframe();
        }
		$page = new \Think\Page($list['total_count'], $listRows, $REQUEST);
        if($list['total_count']>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
		if($list['errcode']){
			S('mediaList'.$REQUEST['type'].$p,null);
			$this->error(weixinError($list['errcode']));
		}else{
			$this->assign('typetext',$this->materialType($REQUEST['type']));
			$this->assign('type',$REQUEST['type']);
			$this->assign('list',$list['item']);
			$this->assign('_page', $page->show());
			$this->display();
		}
    }
	
	public function wechatUpload($filename,$type,$discription=''){
		$media = $this->auth->materialAddMaterial($filename, $type, $discription);
		if($media["errcode"] == 42001  || $media['errcode'] == 40001){
            session("token", null);
            $this->wechatUpload($filename,$type,$discription);
        }
		
        return $media;
	}
	
	public function uploadweixin(){
        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '', 'type' => I('get.type'));
        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('WEIXIN_UPLOAD_DRIVER');
		$pic_upload = C('WEIXIN_UPLOAD');
        $info = $Picture->wxupload(
            $_FILES,
            $pic_upload,
            C('WEIXIN_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        );
		$media=$this->wechatUpload($info['download']['path'],I('get.type'));
        if($info){
            $return['status'] = 1;
            $return = array_merge($info['download'],$return,$media);
			if(I('get.type')=='news'){
				imgMedia($return['media_id'],array('path'=>$return['path'],'url'=>$return['url']));
			}else{
				unlink($return['path']);
			}
			S('media_','clear');
        } else {
            $return['status'] = 0;
            $return['info']   = $Picture->getError();
        }
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    } 
	
	public function materialType($type){
		switch ($type) {
		case 'image':
			return '上传图片';
			break;
		case 'video':
			return '上传视频';
			break;
		case 'voice':
			return '上传语音';
			break;
		case 'news':
			return '新建图文消息';
			break;
		}
	
	}
	
	public function addmsg(){
        if(IS_POST){
			$data = json_decode(stripslashes($_POST['content']),true);
			$media = $this->auth->materialAddNews($data);
			if($media["errcode"] == 42001  || $media['errcode'] == 40001){
            	session("token", null);
            	$this->addmsg();
        	}
			if($media['media_id']){
				S('media_','clear');
				if($_GET['msg']){
					$this->success('新增成功！', U('Reply/group',array('media_id'=>$media['media_id'])));
				}else{
					$this->success('新增成功！', U('index'));
				}
			}else{
				$this->error(weixinError($media['errcode']));
			}
        } else {
            $this->meta_title = '添加图文消息';
            $this->display('editmsg');
        }
	}
	
	public function editmsg(){
		if(IS_POST){
			$data = json_decode(stripslashes($_POST['content']),true);
			foreach ($data as $k=>$v){
				$dataArr=array("media_id"=>I('media_id'),"index"=>$k,"articles"=>$v);
				$media = $this->auth->updateNews($dataArr);
				if($media['errcode']!=0){
					$this->error(weixinError($media['errcode']));
					return;
				}
			}				
			if($media["errcode"] == 42001  || $media['errcode'] == 40001){
            	session("token", null);
            	$this->editmsg();
        	}
			S('media_','clear');
			if($_GET['msg']){
				$this->success('修改完成正在跳转发送页面！', U('Reply/group',array('media_id'=>I('media_id'))));
			}else{
				$this->success('修改成功！', U('index'));
			}
        }else{
			$media = $this->auth->mediaGet(I('media_id'));
			$list=json_decode($media, true);
			if($list["errcode"] == 42001  || $list['errcode'] == 40001){
            	session("token", null);
            	$this->editmsg();
        	}
			$this->assign('media',addslashes($media));
			$this->assign('list',$list['news_item']);
            $this->meta_title = '修改图文消息';
            $this->display();
        }
	}
	
	public function index(){
		$REQUEST    =   (array)I('request.');
		$type=$REQUEST['type']?$REQUEST['type']:"news";
		$p=$REQUEST['p']>0?($REQUEST['p']-1):$REQUEST['p'];
		$listRows = 12;
		if(!$list=S('mediaList'.$type.$p)){
			$list=$this->auth->mediaListGet($type,$p*$listRows,$listRows);
			S('mediaList'.$type.$p,$list);
		}
		if($list["errcode"] == 42001  || $list['errcode'] == 40001){
            session("token", null);
			S('mediaList'.$type.$p,null);
            $this->index();
        }
		$page = new \Think\Page($list['total_count'], $listRows, $REQUEST);
        if($list['total_count']>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
		if($list['errcode']){
			S('mediaList'.$type.$p,null);
			$this->error(weixinError($list['errcode']));
		}else{
			$this->meta_title = '素材管理';
			$this->assign('typetext',$this->materialType($type));
			$this->assign('type',$type);
			$this->assign('list',$list['item']);
			$this->assign('_page', $page->show());
			$this->display();
		}
	}
	
	public function del($media_id){
		$access=$this->auth->materialDel($media_id);
		if($access["errcode"] == 42001  || $access['errcode'] == 40001){
            session("token", null);
            $this->del(media_id);
        }
		if($access['errcode']!=0){
			$this->error(weixinError($access['errcode']));
		}else{
			S('media_','clear');
			$this->success('删除成功！');
		}
	}
}