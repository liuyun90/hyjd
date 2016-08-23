<?php
namespace Home\Controller;
use Think\Controller;

class UpdataController extends Controller {

	public function index(){
		$conf=M('config');
		$data=array(
			'id'=>27,
			'name'=>'USER_INVITEL',
			'type'=>0,
			'title'=>'用户佣金比例',
			'group'=>3,
			'extra'=>'',
			'remark'=>'',
			'create_time'=>NOW_TIME,
			'update_time'=>NOW_TIME,
			'value'=>6,
			'sort'=>0,
			'display'=>1
		);
		$conf->add($data);
		$data=array(
			'id'=>117,
			'name'=>'AlidayuTplReg',
			'type'=>0,
			'title'=>'大鱼注册模板ID',
			'group'=>7,
			'extra'=>'',
			'remark'=>'',
			'create_time'=>NOW_TIME,
			'update_time'=>NOW_TIME,
			'value'=>'SMS_5235593',
			'sort'=>0,
			'display'=>1
		);
		$conf->add($data);
		$data=array(
			'id'=>118,
			'name'=>'AlidayuTplId',
			'type'=>0,
			'title'=>'大鱼身份验证模版ID',
			'group'=>7,
			'extra'=>'',
			'remark'=>'',
			'create_time'=>NOW_TIME,
			'update_time'=>NOW_TIME,
			'value'=>'SMS_5235596',
			'sort'=>0,
			'display'=>1
		);
		$conf->add($data);
		$data=array(
			'id'=>119,
			'name'=>'AlidayuTplPw',
			'type'=>0,
			'title'=>'大鱼密码修改模板ID',
			'group'=>7,
			'extra'=>'',
			'remark'=>'',
			'create_time'=>NOW_TIME,
			'update_time'=>NOW_TIME,
			'value'=>'SMS_5235591',
			'sort'=>0,
			'display'=>1
		);
		$conf->add($data);
		$data=array(
			'id'=>172,
			'title'=>'佣金发放',
			'pid'=>36,
			'sort'=>46,
			'url'=>'Invite/pay',
			'hide'=>0,
			'tip'=>'',
			'group'=>'用户',
			'icon'=>''
		);
		M('web_menu')->add($data);

		$data=array(
			'id'=>57,
			'rule'=>'',
			'log'=>''
		);
		M('activity')->save($data);

		$sql="CREATE TABLE `".C('DB_PREFIX')."commission_log` (`id` int(11) NOT NULL AUTO_INCREMENT,`create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',`money` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '金额',`uid` int(11) NOT NULL DEFAULT '0' COMMENT '对应用户ID',`pid` int(11) NOT NULL DEFAULT '0' COMMENT '对应产品期id',`tid` int(11) NOT NULL DEFAULT '0' COMMENT '对应上线id',`number` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '购买人次',PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC";
		M()->execute($sql,true);

		$sql="CREATE TABLE `".C('DB_PREFIX')."cash_log` (`id` int(11) NOT NULL AUTO_INCREMENT,`create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',`money` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '金额',`status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',`pay_state` tinyint(2) DEFAULT '0' COMMENT '支付状态',`uid` int(11) NOT NULL DEFAULT '0' COMMENT '对应联盟ID',`account` varchar(50) DEFAULT NULL COMMENT '开户人',`bank` varchar(100) DEFAULT NULL COMMENT '银行',`bank_number` varchar(100) DEFAULT NULL COMMENT '银行卡号',`phone` varchar(20) DEFAULT NULL COMMENT '手机号',`branch` varchar(100) DEFAULT NULL COMMENT '支行名称',`type` tinyint(3) DEFAULT '0',PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC";
		M()->execute($sql,true);

		$sql = "ALTER TABLE " .C('DB_PREFIX')."user ADD `brokerage` double(10,2) NOT NULL DEFAULT '0.00', ADD `withdraw_brokerage` double(10,2) NOT NULL DEFAULT '0.00' ,ADD `tid` int(11) DEFAULT '0'";
		M()->execute($sql,true);
	}
}