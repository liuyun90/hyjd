<?php
namespace Admin\Model;
use Think\Model;

class ReplyModel extends Model{

    protected $_validate = array(
		array('rule_name', 'require', '规则名不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('keyword', 'require', '关键字不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
		array('create_time', 'getCreateTime', self::MODEL_BOTH,'callback'),
    );
	
	public function info($id, $field = true){
        $map = array();
        if(is_numeric($id)){
            $map['id'] = $id;
        }
		$info=$this->field($field)->where($map)->find();
		if($info['msgtype']=="mpnews"){
			$info['data_list']=json_decode(stripslashes(stripslashes(htmlspecialchars_decode($info['content']))),true);
		}elseif($info['msgtype']!="text"){
			$info['media_id']=$info['content'];
		}
        return $info;
    }
	
    public function update(){
        $data = $this->create();
        if(!$data){
            return false;
        }
		if($data['msgtype']=="text"){
			$this->content=I('content');
		}elseif($data['msgtype']=="mpnews"){
			$this->content=$_POST['data_list'];
		}else{
			$this->content=I('media_id');
		}
        if(empty($data['id'])){
            $res = $this->add();
        }else{
            $res = $this->save();
        }
        return $res;
    }
		
    public function remove($id = null){
		$map = array('id' => array('in', $id));
		$res=$this->where($map)->delete();
		return $res;
	}

    protected function getCreateTime(){
        $create_time    =   I('post.create_time');
        return $create_time?strtotime($create_time):NOW_TIME;
    }
}
