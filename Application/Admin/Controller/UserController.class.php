<?php
namespace Admin\Controller;

class UserController extends AdminController
{
    
    //测试用户总资产
    public function totalUser(){
        sumCoin_new(75);
    }
    
	public function t1()
	{
		echo $this->tt(5);
		
		
		
		
		
		
		
		
	}
	
	public function tt()
	{
		$coin=C('coin');//dump($coin);
		
		$userc=M('user_coin')->where('userid=5')->find();
		
		
		foreach($coin as $k=>$v)
		{
			$d=$k.'d';
			$data[$k]=$userc[$k]+($userc[$k.'d']);
			$list[$k]['lt']+=$userc[$k];
			$list[$k]['dj']+=$userc[$k.'d'];
			
		}
		$list['zj']=array_sum($data);
		dump($list);
    }
	
	
	
	public function index($name = NULL, $field = NULL, $status = NULL)
	{
		//$this->checkUpdata();
		//$where = array();
		$where=" 1 ";
		if ($field && $name) {
			//$where[$field] = $name;
			if($field=="awardid" &&($name==7 || $name==9)){
				$where = " (`awardid`=7 or `awardid`=9) ";
			}else{
				$where = "`".$field."`='".$name."'";
			}

		}

		if ($status) {
			if($status>2){
				switch($status){
					case "3":
						$where = $where." and `awardstatus`=1 ";
						break;
					case "4":
						$where = $where." and `awardstatus`=0 ";
						break;
					case "5":
						$where = $where." and `idcardauth`=1 ";
						break;
					case "6":
						$where = $where." and `idcardauth`=0 ";
						break;
				}

			}else{

				$where = $where." and `status`=".($status-1);
			}
		}

		$count = M('User')->where($where)->count();
		$Page = new \Think\Page($count, 10000);
		$show = $Page->show();
		$list = M('User')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$shoper = M('User_shop')->where(array('userid' => $vo['userid']))->find();
			if($shoper)
			{
				$list[$k]['shoper'] = $shoper['status'];
			}
			else{
				$list[$k]['shoper'] = 3;
			}
			$list[$k]['invit_1'] = M('User')->where(array('id' => $v['invit_1']))->getField('username');
			$list[$k]['invit_2'] = M('User')->where(array('id' => $v['invit_2']))->getField('username');
			$list[$k]['invit_3'] = M('User')->where(array('id' => $v['invit_3']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->assign('count', $count);
		$this->display();
	}

	public function edit($id = NULL)
	{
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = null;
			}
			else {
				$user = M('User')->where(array('id' => trim($id)))->find();
				$this->data = $user;
			}
			
			$imgstr = "";
			if($user['idcardimg1']){
				$img_arr = array();
				$img_arr = explode("_",$user['idcardimg1']);

	/*			foreach($img_arr as $k=>$v){
					$imgstr = $imgstr.'<img src="/Upload/idcard/'.$v.'"  style="width:200px;height:100px;" />';
				}
*/
             $imgstr = '<li style="height:100px;float:left;"><img style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg1'].'" /></li>';
            $imgstr = $imgstr.'<li style="height:100px;float:left;"><img style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg2'].'" /></li>';
            $imgstr = $imgstr.'<li style="height:100px;float:left;"><img style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg3'].'" /></li>';
    

				unset($img_arr);
			}
			
			$this->assign('userimg', $imgstr);

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			switch($_POST['awardid']){
				case 0:
					$_POST['awardname']="无奖品";
					break;
				case 1:
					$_POST['awardname']="苹果电脑";
					break;
				case 2:
					$_POST['awardname']="华为手机";
					break;
				case 3:
					$_POST['awardname']="1000元现金";
					break;
				case 4:
					$_POST['awardname']="小米手环";
					break;
				case 5:
					$_POST['awardname']="100元现金";
					break;
				case 6:
					$_POST['awardname']="10元现金";
					break;
				case 7:
					$_POST['awardname']="1元现金";
					break;
				case 8:
					$_POST['awardname']="无奖品";
					break;
				case 9:
					$_POST['awardname']="1元现金";
					break;
				case 10:
					$_POST['awardname']="无奖品";
					break;
				default:
					$_POST['awardid']=0;
					$_POST['awardname']="无奖品";
			}
			
			
// 			dump($_POST);die;
			if ($_POST['password']) {
				$_POST['password'] = md5($_POST['password']);
			}
			else {
				unset($_POST['password']);
			}

			if ($_POST['paypassword']) {
				$_POST['paypassword'] = md5($_POST['paypassword']);
			}
			else {
				unset($_POST['paypassword']);
			}

			$_POST['mobletime'] = strtotime($_POST['mobletime']);

            $flag = false;
            if (isset($_POST['id'])) {
                $rs = M('User')->save($_POST);
            } else {
                $mo = M();
                $mo->execute('set autocommit=0');
                $mo->execute('lock tables zhisucom_user write , zhisucom_user_coin write ');
                $rs[] = $mo->table('zhisucom_user')->add($_POST);
                $rs[] = $mo->table('zhisucom_user_coin')->add(array('userid' => $rs[0]));
                $flag = true;
            }

			if ($rs) {
                if ($flag) {
                    $mo->execute('commit');
                    $mo->execute('unlock tables');
                }
                session('reguserId', $rs);
				$this->success('编辑成功！');
			}
			else {
                if ($flag) {
                    $mo->execute('rollback');
                }
				$this->error('编辑失败！');
			}
		}
	}

	public function status($id = NULL, $type = NULL, $moble = 'User',$awardid=0)
	{
		if (APP_DEMO) {
			//$this->error('测试站暂时不能修改！');
		}

		$id=$_REQUEST['id'];

		if (empty($id)) {
			$this->error('请选择会员！');
		}

		if (empty($type)) {
			$this->error('参数错误！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		
		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
                $_where = array(
                    'userid' => $where['id'],
                );
                M('UserCoin')->where($_where)->delete();
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;
			
		case 'sm':#私募
			$data = array('lev'=>1);
			break;
		
		case 'ts':#天使
			$data = array('lev'=>2);
			break;
			
		case 'cs':#创始
			$data = array('lev'=>3);
			break;
		
		case 'pz':#平准
			$data = array('lev'=>4);
			break;

		case 'pc':#平仓
			$data = array('lev'=>5);
			break;
			
	    case 'senior_p':
	        //余额超过10BTC即可通过高级认证成为商户
	        /*if($id){
	            
	            $new_id = '';
	            
	            foreach ($id as $v){
	                $zong_zc = 0;
	                $zong_zc = sumCoin($v);
	                
	                $new_price = M('market')->where(array('name'=>'btc_bdb'))->getField('new_price');
	                
	                $price = round($zong_zc/$new_price,2);
	                error_log('id='.$v.'&zong_zc='.$zong_zc.'&price='.$price,3,'./aa.txt');
	                //echo $price;exit;
	                if($price>10){
	                    //$new_id[] = $new_price;
	                      //$new_id[] = $price;
	                     $new_id .= $v.',';
	                }
	            }
	            
	            if($new_id){
	                $where['id'] = array('in', rtrim($new_id,','));
	            }else{
	                $this->error('余额超过10BTC即可通过高级认证，操作失败！');
					exit;
	            }
	            
	        }else{
	            $this->error('没有勾选用户，操作失败！');
				exit;
	        }*/
	        //
			
			//赠送糖果	
			$candy=M('config')->field('reg_award_coin,reg_award_num')->where('id=1')->find();		
	
				
				$status=M('user')->where($where)->field('candy,id')->select();
				foreach($status as $k=>$v){
					if($v['candy'] == 0){
						$candyadd=M("user_coin")->where(array('userid'=>$v['id']))->setInc($candy['reg_award_coin'],$candy['reg_award_num']);
						if($candyadd){
							M('user')->where(array('id'=>$v['id']))->setField('candy',1);
							//账单明细			
								$wallet=M('user_coin')->where(array('userid'=>$v['id']))->getField($candy['reg_award_coin']);
								$mark['curr']=$candy['reg_award_coin'];
								$mid=M('status')->where($mark)->getField('id');	
								parent::addCashhistory($v['id'],$mid,8,"赠送",$candy['reg_award_num'],0,$wallet.strtoupper($candy['reg_award_coin']),"注册赠送".strtoupper($candy['reg_award_coin']));				
							//结束	
						}
					}
				}
			
	        $data = array('senior_past' =>2, 'senior_time'=>time());
	        break;
	    case 'senior_d':
	        $data = array('senior_past' =>0,'senior_time'=>time(),'senior_img1'=>'','senior_img2'=>'','senior_img3'=>'','senior_img4'=>'');

	        break;
		case 'idauth': 
			$data = array('idcardauth' => 2, 'addtime' => time());
			
				
			
			break;
			
		case 'notidauth': 
			$data = array('idcardauth' => 0);
			break;
			
		case 'award';
		
			switch($awardid){
				case 0:
					$awardname="无奖品";
					break;
				case 1:
					$awardname="苹果电脑";
					break;
				case 2:
					$awardname="华为手机";
					break;
				case 3:
					$awardname="1000元现金";
					break;
				case 4:
					$awardname="小米手环";
					break;
				case 5:
					$awardname="100元现金";
					break;
				case 6:
					$awardname="10元现金";
					break;
				case 7:
					$awardname="1元现金";
					break;
				case 8:
					$awardname="无奖品";
					break;
				case 9:
					$awardname="1元现金";
					break;
				case 10:
					$awardname="无奖品";
					break;
				default:
					$awardid=0;
					$awardname="无奖品";
			}
			$data = array('awardstatus' => 0, 'awardid' => $awardid,'awardname'=>$awardname);
			
			break;
		
		default:
			$this->error('操作失败！');
		}
		
		
		if (M($moble)->where($where)->save($data)) {
			
			
			//结束		
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function admin($name = NULL, $field = NULL, $status = NULL)
	{
		$DbFields = M('Admin')->getDbFields();

		if (!in_array('email', $DbFields)) {
			M()->execute('ALTER TABLE `zhisucom_admin` ADD COLUMN `email` VARCHAR(200)  NOT NULL   COMMENT \'\' AFTER `id`;');
		}

		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		$count = M('Admin')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Admin')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function adminEdit()
	{
		if (empty($_POST)) {
			if (empty($_GET['id'])) {
				$this->data = null;
			}
			else {
				$this->data = M('Admin')->where(array('id' => trim($_GET['id'])))->find();
			}

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$input = I('post.');

			if (!check($input['username'], 'username')) {
				$this->error('用户名格式错误！');
			}

			if ($input['nickname'] && !check($input['nickname'], 'A')) {
				$this->error('昵称格式错误！');
			}

			if ($input['password'] && !check($input['password'], 'password')) {
				$this->error('登录密码格式错误！');
			}

			if ($input['moble'] && !check($input['moble'], 'moble')) {
				$this->error('手机号码格式错误！');
			}

			if ($input['email'] && !check($input['email'], 'email')) {
				$this->error('邮箱格式错误！');
			}

			if ($input['password']) {
				$input['password'] = md5($input['password']);
			}
			else {
				unset($input['password']);
			}

			if ($_POST['id']) {
				$rs = M('Admin')->save($input);
			}
			else {
				$_POST['addtime'] = time();
				$rs = M('Admin')->add($input);
			}

			if ($rs) {
				$this->success('编辑成功！');
			}
			else {
				$this->error('编辑失败！');
			}
		}
	}

	public function adminStatus($id = NULL, $type = NULL, $moble = 'Admin')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function auth()
	{
		//$list = $this->lists('AuthGroup', array('module' => 'admin'), 'id asc');
		$authGroup = M('AuthGroup');
		$condition['module'] = 'admin';
		$list = $authGroup->order('id asc')->where($condition)->select();
		$list = int_to_string($list);
		$this->assign('_list', $list);
		$this->assign('_use_tip', true);
		$this->meta_title = '权限管理';
		$this->display();
	}

	public function authEdit()
	{
		if (empty($_POST)) {
			if (empty($_GET['id'])) {
				$this->data = null;
			}
			else {
				$this->data = M('AuthGroup')->where(array(
                    'module' => 'admin',
                    'type' => 1,//Common\Model\AuthGroupModel::TYPE_ADMIN
                ))->find((int) $_GET['id']);
			}

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			if (isset($_POST['rules'])) {
				sort($_POST['rules']);
				$_POST['rules'] = implode(',', array_unique($_POST['rules']));
			}

			$_POST['module'] = 'admin';
			$_POST['type'] = 1;//Common\Model\AuthGroupModel::TYPE_ADMIN;
			$AuthGroup = D('AuthGroup');
			$data = $AuthGroup->create();

			if ($data) {
				if (empty($data['id'])) {
					$r = $AuthGroup->add();
				}
				else {
					$r = $AuthGroup->save();
				}

				if ($r === false) {
					$this->error('操作失败' . $AuthGroup->getError());
				}
				else {
					$this->success('操作成功!');
				}
			}
			else {
				$this->error('操作失败' . $AuthGroup->getError());
			}
		}
	}

	public function authStatus($id = NULL, $type = NULL, $moble = 'AuthGroup')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function authStart()
	{
		if (M('AuthRule')->where(array('status' => 1))->delete()) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function authAccess()
	{
		$this->updateRules();
		$auth_group = M('AuthGroup')->where(array(
			'status' => array('egt', '0'),
			'module' => 'admin',
			'type'   => 1,//Common\Model\AuthGroupModel::TYPE_ADMIN
			))->getfield('id,id,title,rules');
		$node_list = $this->returnNodes();
		$map = array(
            'module' => 'admin',
            'type' => 2,//Common\Model\AuthRuleModel::RULE_MAIN,
            'status' => 1
        );
		$main_rules = M('AuthRule')->where($map)->getField('name,id');
		$map = array(
            'module' => 'admin',
            'type' => 1,//Common\Model\AuthRuleModel::RULE_URL,
            'status' => 1
        );
		$child_rules = M('AuthRule')->where($map)->getField('name,id');
		$this->assign('main_rules', $main_rules);
		$this->assign('auth_rules', $child_rules);
		$this->assign('node_list', $node_list);
		$this->assign('auth_group', $auth_group);
		$this->assign('this_group', $auth_group[(int) $_GET['group_id']]);
		$this->meta_title = '访问授权';
		$this->display();
	}

	protected function updateRules()
	{
		$nodes = $this->returnNodes(false);
		$AuthRule = M('AuthRule');
		$map = array(
			'module' => 'admin',
			'type'   => array('in', '1,2')
			);
		$rules = $AuthRule->where($map)->order('name')->select();
		$data = array();

		foreach ($nodes as $value) {
			$temp['name'] = $value['url'];
			$temp['title'] = $value['title'];
			$temp['module'] = 'admin';

			if (0 < $value['pid']) {
				$temp['type'] = 1;//Common\Model\AuthRuleModel::RULE_URL;
			}
			else {
				$temp['type'] = 2;//Common\Model\AuthRuleModel::RULE_MAIN;
			}

			$temp['status'] = 1;
			$data[strtolower($temp['name'] . $temp['module'] . $temp['type'])] = $temp;
		}

		$update = array();
		$ids = array();

		foreach ($rules as $index => $rule) {
			$key = strtolower($rule['name'] . $rule['module'] . $rule['type']);

			if (isset($data[$key])) {
				$data[$key]['id'] = $rule['id'];
				$update[] = $data[$key];
				unset($data[$key]);
				unset($rules[$index]);
				unset($rule['condition']);
				$diff[$rule['id']] = $rule;
			}
			else if ($rule['status'] == 1) {
				$ids[] = $rule['id'];
			}
		}

		if (count($update)) {
			foreach ($update as $k => $row) {
				if ($row != $diff[$row['id']]) {
					$AuthRule->where(array('id' => $row['id']))->save($row);
				}
			}
		}

		if (count($ids)) {
			$AuthRule->where(array(
				'id' => array('IN', implode(',', $ids))
				))->save(array('status' => -1));
		}

		if (count($data)) {
			$AuthRule->addAll(array_values($data));
		}

		if ($AuthRule->getDbError()) {
			trace('[' . 'Admin\\Controller\\UserController::updateRules' . ']:' . $AuthRule->getDbError());
			return false;
		}
		else {
			return true;
		}
	}

	public function authAccessUp()
	{
		if (isset($_POST['rules'])) {
			sort($_POST['rules']);
			$_POST['rules'] = implode(',', array_unique($_POST['rules']));
		}

		$_POST['module'] = 'admin';
		$_POST['type'] = 1;//Common\Model\AuthGroupModel::TYPE_ADMIN;
		$AuthGroup = D('AuthGroup');
		$data = $AuthGroup->create();

		if ($data) {
			if (empty($data['id'])) {
				$r = $AuthGroup->add();
			}
			else {
				$r = $AuthGroup->save();
			}

			if ($r === false) {
				$this->error('操作失败' . $AuthGroup->getError());
			}
			else {
				$this->success('操作成功!');
			}
		}
		else {
			$this->error('操作失败' . $AuthGroup->getError());
		}
	}

	public function authUser($group_id)
	{
		if (empty($group_id)) {
			$this->error('参数错误');
		}

		$auth_group = M('AuthGroup')->where(array(
			'status' => array('egt', '0'),
			'module' => 'admin',
			'type'   => 1,//Common\Model\AuthGroupModel::TYPE_ADMIN
			))->getfield('id,id,title,rules');
		$prefix = C('DB_PREFIX');
/* 		$l_table = $prefix . 'ucenter_member';//Common\Model\AuthGroupModel::MEMBER;
		$r_table = $prefix . 'auth_group_access';//Common\Model\AuthGroupModel::AUTH_GROUP_ACCESS;
		$model = M()->table($l_table . ' m')->join($r_table . ' a ON m.id=a.uid');
		$_REQUEST = array();
		$list = $this->lists($model, array(
			'a.group_id' => $group_id,
			'm.status'   => array('egt', 0)
			), 'm.id asc', null, 'm.id,m.username,m.nickname,m.last_login_time,m.last_login_ip,m.status'); */

			
		$l_table = $prefix . 'auth_group_access';//Common\Model\AuthGroupModel::MEMBER;
		$r_table = $prefix . 'admin';//Common\Model\AuthGroupModel::AUTH_GROUP_ACCESS;
		$model = M()->table($l_table . ' a')->join($r_table . ' m ON m.id=a.uid');
		$_REQUEST = array();
		$list = $this->lists($model, array(
			'a.group_id' => $group_id,
			//'m.status'   => array('egt', 0)
			), 'a.uid desc', null, 'm.id,m.username,m.nickname,m.last_login_time,m.last_login_ip,m.status');
			
			
        int_to_string($list);
		
		//var_dump($list);
		
		$this->assign('_list', $list);
		$this->assign('auth_group', $auth_group);
		$this->assign('this_group', $auth_group[(int) $_GET['group_id']]);
		$this->meta_title = '成员授权';
		$this->display();
	}

	public function authUserAdd()
	{
		$uid = I('uid');

		if (empty($uid)) {
			$this->error('请输入后台成员信息');
		}

		if (!check($uid, 'd')) {
			$user = M('Admin')->where(array('username' => $uid))->find();

			if (!$user) {
				$user = M('Admin')->where(array('nickname' => $uid))->find();
			}

			if (!$user) {
				$user = M('Admin')->where(array('moble' => $uid))->find();
			}

			if (!$user) {
				$this->error('用户不存在(id 用户名 昵称 手机号均可)');
			}

			$uid = $user['id'];
		}

		$gid = I('group_id');

		if ($res = M('AuthGroupAccess')->where(array('uid' => $uid))->find()) {
			if ($res['group_id'] == $gid) {
				$this->error('已经存在,请勿重复添加');
			} else {
				$res = M('AuthGroup')->where(array('id' => $gid))->find();

				if (!$res) {
					$this->error('当前组不存在');
				}

				$this->error('已经存在[' . $res['title'] . ']组,不可重复添加');
			}
		}

		$AuthGroup = D('AuthGroup');

		if (is_numeric($uid)) {
			if (is_administrator($uid)) {
				$this->error('该用户为超级管理员');
			}

			if (!M('Admin')->where(array('id' => $uid))->find()) {
				$this->error('管理员用户不存在');
			}
		}

		if ($gid && !$AuthGroup->checkGroupId($gid)) {
			$this->error($AuthGroup->error);
		}

		if ($AuthGroup->addToGroup($uid, $gid)) {
			$this->success('操作成功');
		}
		else {
			$this->error($AuthGroup->getError());
		}
	}

	public function authUserRemove()
	{
		$uid = I('uid');
		$gid = I('group_id');

		if ($uid == UID) {
			$this->error('不允许解除自身授权');
		}

		if (empty($uid) || empty($gid)) {
			$this->error('参数有误');
		}

		$AuthGroup = D('AuthGroup');

		if (!$AuthGroup->find($gid)) {
			$this->error('用户组不存在');
		}

		if ($AuthGroup->removeFromGroup($uid, $gid)) {
			$this->success('操作成功');
		}
		else {
			$this->error('操作失败');
		}
	}

	public function log($name = NULL, $field = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		$count = M('UserLog')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('UserLog')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function logEdit($id = NULL)
	{
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = null;
			}
			else {
				$this->data = M('UserLog')->where(array('id' => trim($id)))->find();
			}

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$_POST['addtime'] = strtotime($_POST['addtime']);

            if ($id) {
                unset($_POST['id']);
                if (M()->table('zhisucom_user_log')->where(array('id'=>$id))->save($_POST)) {
                    $this->success('编辑成功！');
                } else {
                    $this->error('编辑失败！');
                }
            } else {
                if (M()->table('zhisucom_user_log')->add($_POST)) {
                    $this->success('添加成功！');
                } else {
                    $this->error('添加失败！');
                }
            }

		}
	}

	public function logStatus($id = NULL, $type = NULL, $moble = 'UserLog')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function qianbao($name = NULL, $field = NULL, $coinname = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		if ($coinname) {
			$where['coinname'] = trim($coinname);
		}

		$count = M('UserQianbao')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('UserQianbao')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
      	$this->assign('count', $count);
		$this->display();
	}

	public function qianbaoEdit($id = NULL)
	{
		//print_r($_POST);
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = null;
			}
			else {
				$this->data = M('UserQianbao')->where(array('id' => trim($id)))->find();
			}

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$_POST['addtime'] = strtotime($_POST['addtime']);

            if ($id) {
                unset($_POST['id']);
                if (M()->table('zhisucom_user_qianbao')->where(array('id' => $id))->save($_POST)) {
                    $this->success('编辑成功！');
                } else {
                    $this->error('编辑失败！');
                }
            } else {
                if (M()->table('zhisucom_user_qianbao')->add($_POST)) {
                    $this->success('添加成功！');
                } else {
                    $this->error('添加失败！');
                }
            }
		}
	}

	public function qianbaoStatus($id = NULL, $type = NULL, $moble = 'UserQianbao')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function bank($name = NULL, $field = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		$count = M('UserBank')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('UserBank')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function bankEdit($id = NULL)
	{
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = null;
			}
			else {
				$this->data = M('UserBank')->where(array('id' => trim($id)))->find();
			}

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$_POST['addtime'] = strtotime($_POST['addtime']);

            if ($id) {
                if (M()->table('zhisucom_user_bank')->where(array('id' => $id))->save($_POST)) {
                    $this->success('编辑成功！');
                }
                else {
                    $this->error('编辑失败！');
                }
            } else {
                if (M()->table('zhisucom_user_bank')->add($_POST)) {
                    $this->success('添加成功！');
                }
                else {
                    $this->error('添加失败！');
                }
            }
		}
	}

	public function bankStatus($id = NULL, $type = NULL, $moble = 'UserBank')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function coin($name = NULL, $field = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		$count = M('UserCoin')->where($where)->count();
		$Page = new \Think\Page($count, 10000);
		$show = $Page->show();
		$list = M('UserCoin')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function coinEdit($id = NULL)
	{
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = null;
			}
			else {
				$this->data = M('UserCoin')->where(array('id' => trim($id)))->find();
			}

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}
			
            if ($id) {
                if (M('UserCoin')->save($_POST)) {
                    $this->success('编辑成功！');
                }
                else {
                    $this->error('编辑失败！');
                }
            } else {
                if (M()->table('zhisucom_user_coin')->add($_POST)) {
                    $this->success('添加成功！');
                }
                else {
                    $this->error('添加失败！');
                }
            }
		}
	}

	public function coinLog($userid = NULL, $coinname = NULL)
	{
		$data['userid'] = $userid;
		$data['username'] = M('User')->where(array('id' => $userid))->getField('username');
		$data['coinname'] = $coinname;
		$data['zhengcheng'] = M('UserCoin')->where(array('userid' => $userid))->getField($coinname);
		$data['dongjie'] = M('UserCoin')->where(array('userid' => $userid))->getField($coinname . 'd');
		$data['zongji'] = $data['zhengcheng'] + $data['dongjie'];
		$data['chongzhicny'] = M('Mycz')->where(array(
			'userid' => $userid,
			'status' => array('neq', '0')
			))->sum('num');
		$data['tixiancny'] = M('Mytx')->where(array('userid' => $userid, 'status' => 1))->sum('num');
		$data['tixiancnyd'] = M('Mytx')->where(array('userid' => $userid, 'status' => 0))->sum('num');

		if ($coinname != 'cny') {
			$data['chongzhi'] = M('Myzr')->where(array(
				'userid' => $userid,
				'status' => array('neq', '0')
				))->sum('num');
			$data['tixian'] = M('Myzc')->where(array('userid' => $userid, 'status' => 1))->sum('num');
		}

		$this->assign('data', $data);
		$this->display();
	}

	public function goods($name = NULL, $field = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		$count = M('UserGoods')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('UserGoods')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function goodsEdit($id = NULL)
	{
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = null;
			}
			else {
				$this->data = M('UserGoods')->where(array('id' => trim($id)))->find();
			}

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$_POST['addtime'] = strtotime($_POST['addtime']);

            if ($id) {
                unset($_POST['id']);
                if (M()->table('zhisucom_user_goods')->where(array('id'=>$id))->save($_POST)) {
                    $this->success('编辑成功！');
                }
                else {
                    $this->error('编辑失败！');
                }
            } else {
                if (M()->table('zhisucom_user_goods')->add($_POST)) {
                    $this->success('添加成功！');
                }
                else {
                    $this->error('添加失败！');
                }
            }
		}
	}

	public function goodsStatus($id = NULL, $type = NULL, $moble = 'UserGoods')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function setpwd()
	{
	    
		if (IS_POST) {
			defined('APP_DEMO') || define('APP_DEMO', 0);

			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$oldpassword = $_POST['oldpassword'];
			$newpassword = $_POST['newpassword'];
			$repassword = $_POST['repassword'];
            $admin_id = session('admin_id');
			if (!check($oldpassword, 'password')) {
				// $this->error('旧密码格式错误！');
			}

			if (md5($oldpassword) != session('admin_password')) {
				$this->error('旧密码错误！');
			}

			if (!check($newpassword, 'password')) {
				// $this->error('新密码格式错误！');
			}

			if ($newpassword != $repassword) {
				$this->error('确认密码错误！');
			}

			if (D('Admin')->where(array('id' => $admin_id))->save(array('password' => md5($newpassword)))) {
				$this->success('登陆密码修改成功！');
			}
			else {
				$this->error('登陆密码修改失败！');
			}
		}

		$this->display();
	}

	
	public function award($name = NULL, $field = NULL, $status = NULL)
	{
/* 		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		} */

		$where="";
		if ($field && $name) {
			//$where[$field] = $name;
			if($field=="awardid" &&($name==7 || $name==9)){
				$where = " (`awardid`=7 or `awardid`=9) ";
			}else{
				$where = "`".$field."`='".$name."'";
			}
		}

		if($status){
			$where = $where." and `status`=".($status-1);
		}
		
		
		
		
		
		
		$count = M('UserAward')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('UserAward')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function awardStatus($id = NULL, $type = NULL,$status=NUll, $moble = 'UserAward')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('请选择要操作的记录！');
		}

		if (empty($type)) {
			$this->error('参数错误！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'dealaward':
			if(empty($status)){
				$this->error("参数错误！");
			}
			$data=array('status' => $status,'dealtime'=>time());
			break;
		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败');
		}
		
		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
		
		
	}
	
	public function checkUpdata()
	{
		if (!S(MODULE_NAME . CONTROLLER_NAME . 'checkUpdata')) {
			$list = M('Menu')->where(array(
				'url' => 'User/index',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'User/index', 'title' => '用户管理', 'pid' => 3, 'sort' => 1, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'User/index',
					'pid' => array('neq', 0)
					))->save(array('title' => '用户管理', 'pid' => 3, 'sort' => 1, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}

			$list = M('Menu')->where(array(
				'url' => 'User/admin',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'User/admin', 'title' => '管理员管理', 'pid' => 3, 'sort' => 2, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'User/admin',
					'pid' => array('neq', 0)
					))->save(array('title' => '管理员管理', 'pid' => 3, 'sort' => 2, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}

			$list = M('Menu')->where(array(
				'url' => 'User/auth',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'User/auth', 'title' => '权限列表', 'pid' => 3, 'sort' => 3, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'User/auth',
					'pid' => array('neq', 0)
					))->save(array('title' => '权限列表', 'pid' => 3, 'sort' => 3, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}

			$list = M('Menu')->where(array(
				'url' => 'User/log',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'User/log', 'title' => '登陆日志', 'pid' => 3, 'sort' => 4, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'User/log',
					'pid' => array('neq', 0)
					))->save(array('title' => '登陆日志', 'pid' => 3, 'sort' => 4, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}

			$list = M('Menu')->where(array(
				'url' => 'User/qianbao',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'User/qianbao', 'title' => '用户钱包', 'pid' => 3, 'sort' => 5, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'User/qianbao',
					'pid' => array('neq', 0)
					))->save(array('title' => '用户钱包', 'pid' => 3, 'sort' => 5, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}

			$list = M('Menu')->where(array(
				'url' => 'User/bank',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'User/bank', 'title' => '提现地址', 'pid' => 3, 'sort' => 6, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'User/bank',
					'pid' => array('neq', 0)
					))->save(array('title' => '提现地址', 'pid' => 3, 'sort' => 6, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}

			$list = M('Menu')->where(array(
				'url' => 'User/coin',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'User/coin', 'title' => '用户财产', 'pid' => 3, 'sort' => 7, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'User/coin',
					'pid' => array('neq', 0)
					))->save(array('title' => '用户财产', 'pid' => 3, 'sort' => 7, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}

			$list = M('Menu')->where(array(
				'url' => 'User/goods',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'User/goods', 'title' => '联系地址', 'pid' => 3, 'sort' => 8, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'User/goods',
					'pid' => array('neq', 0)
					))->save(array('title' => '联系地址', 'pid' => 3, 'sort' => 8, 'hide' => 0, 'group' => '用户', 'ico_name' => 'user'));
			}

			$list = M('Menu')->where(array(
				'url' => 'User/edit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/index',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/edit', 'title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/edit',
						'pid' => array('neq', 0)
						))->save(array('title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/status',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/index',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/status', 'title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/status',
						'pid' => array('neq', 0)
						))->save(array('title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/adminEdit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/admin',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/adminEdit', 'title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/adminEdit',
						'pid' => array('neq', 0)
						))->save(array('title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/adminStatus',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/admin',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/adminStatus', 'title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/adminStatus',
						'pid' => array('neq', 0)
						))->save(array('title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/authEdit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/auth',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/authEdit', 'title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/authEdit',
						'pid' => array('neq', 0)
						))->save(array('title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/authStatus',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/auth',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/authStatus', 'title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/authStatus',
						'pid' => array('neq', 0)
						))->save(array('title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/authStart',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/auth',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/authStart', 'title' => '重新初始化权限', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/authStart',
						'pid' => array('neq', 0)
						))->save(array('title' => '重新初始化权限', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/authAccess',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/auth',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/authAccess', 'title' => '访问授权', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/authAccess',
						'pid' => array('neq', 0)
						))->save(array('title' => '访问授权', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/authAccessUp',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/auth',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/authAccessUp', 'title' => '访问授权修改', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/authAccessUp',
						'pid' => array('neq', 0)
						))->save(array('title' => '访问授权修改', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/authUser',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/auth',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/authUser', 'title' => '成员授权', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/authUser',
						'pid' => array('neq', 0)
						))->save(array('title' => '成员授权', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/authUserAdd',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/auth',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/authUserAdd', 'title' => '成员授权增加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/authUserAdd',
						'pid' => array('neq', 0)
						))->save(array('title' => '成员授权增加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/authUserRemove',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/auth',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/authUserRemove', 'title' => '成员授权解除', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/authUserRemove',
						'pid' => array('neq', 0)
						))->save(array('title' => '成员授权解除', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/logEdit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/log',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/logEdit', 'title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/logEdit',
						'pid' => array('neq', 0)
						))->save(array('title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/logStatus',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/log',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/logStatus', 'title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/logStatus',
						'pid' => array('neq', 0)
						))->save(array('title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/qianbaoEdit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/qianbao',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/qianbaoEdit', 'title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/qianbaoEdit',
						'pid' => array('neq', 0)
						))->save(array('title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/qianbaoStatus',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/qianbao',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/qianbaoStatus', 'title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/qianbaoStatus',
						'pid' => array('neq', 0)
						))->save(array('title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/bankEdit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/bank',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/bankEdit', 'title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/bankEdit',
						'pid' => array('neq', 0)
						))->save(array('title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/bankStatus',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/bank',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/bankStatus', 'title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/bankStatus',
						'pid' => array('neq', 0)
						))->save(array('title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/coinEdit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/coin',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/coinEdit', 'title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/coinEdit',
						'pid' => array('neq', 0)
						))->save(array('title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/coinLog',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/coin',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/coinLog', 'title' => '财产统计', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/coinLog',
						'pid' => array('neq', 0)
						))->save(array('title' => '财产统计', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/goodsEdit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/goods',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/goodsEdit', 'title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/goodsEdit',
						'pid' => array('neq', 0)
						))->save(array('title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/goodsStatus',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'User/goods',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'User/goodsStatus', 'title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'User/goodsStatus',
						'pid' => array('neq', 0)
						))->save(array('title' => '修改状态', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'User/setpwd',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'User/setpwd', 'title' => '修改管理员密码', 'pid' => 3, 'sort' => 0, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'User/setpwd',
					'pid' => array('neq', 0)
					))->save(array('title' => '修改管理员密码', 'pid' => 3, 'sort' => 0, 'hide' => 1, 'group' => '用户', 'ico_name' => 'home'));
			}

			if (M('Menu')->where(array('url' => 'AuthManager/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'User/adminUser'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'AdminUser/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Userlog/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Userqianbao/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Userbank/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Usercoin/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Usergoods/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			S(MODULE_NAME . CONTROLLER_NAME . 'checkUpdata', 1);
		}
	}
	
	//工单管理
	public function worklist(){
		$list = M('worklist')->where('close_id=0')->order('send_time desc')->select();
		$count = M('worklist')->where('close_id=0')->count();
		// var_dump($list);die;
		$this->assign('list',$list);
		$this->assign('count',$count);
		$this->display();
	}
	//工单回复页面
	public function workedit(){
		$where['id'] = $_GET['id'];
		$user = M('worklist')->where($where)->find();
		// var_dump($user);die;
		$this->assign('user',$user);
		$this->display();
	}
	//工单回复
	public function workrepaly($type = NULL){
		// var_dump($_POST);die;
		$where['id'] = $_POST['id'];
		$info = M('worklist')->where($where)->find();
      	if ($type == 'images') {
				$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
				$upload = new \Think\Upload();
				$upload->maxSize = 3145728;
				$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
				$upload->rootPath = './Upload/article/';
				$upload->autoSub = false;
				$info = $upload->uploadOne($_FILES['imgFile']);

				if ($info) {
					$data = array('url' => str_replace('./', 'http://www.jeffex.io/', $upload->rootPath) . $info['savename'], 'error' => 0);
					
					exit(json_encode($data));
				}
				else {
					$error['error'] = 1;
					$error['message'] = $upload->getError();
					exit(json_encode($error));
				}
			}
		// var_dump($info);die;
      	
		$data['reply_time'] = time();
		$data['reply_content'] = $_POST['content'];
		$data['reply_id'] = $info['worklist_id'];
		$data['status'] = 1;
		$data['reply_code'] = $this->worklist_code();
		$info_data = M('worklist')->where($where)->save($data);
		if($info_data){
			$this->success('回复成功');
		}else{
			$this->error('回复失败');
		}
	}
	//生成工单号
	public function worklist_code(){
		$yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J','K','M');
		$orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
		return $orderSn;
	}
  
  public function articleimage()
	{
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/article/';
		$upload->autoSub = false;
		$info = $upload->upload();

		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			echo $path;
			exit();
		}
	}


	//商家管理
	public function shoper()
	{
		$list = M('User_shop')->select();
		$this->assign('list',$list);
		$this->display();
	}

	public function shop_detail()
	{
		$list = M('User_shop')->where(array('id'=>$_GET['id']))->find();
		$list['username'] = M('User')->where(array('id'=>$list['userid']))->getField('username');
		$this->assign('data',$list);
		$this->display();
	}


	public function shop_tongguo()
	{
		$id  = $_POST['id'];
		if($id>0)
		{
			$info = M('user_shop')->where(array('id'=>$id))->find();
			$res = M('user_shop')->where(array('id'=>$id))->save(array('status'=>2));
			$ress = M('User')->where(array('id'=>$info['userid']))->save(array('shoper'=>2));
			if($res && $ress)
			{
				$this->success('审核成功');
			}
			else
			{
				$this->error('审核失败');
			}
		}
		else
		{
			$this->error('审核失败');
		}
	}


	public function shop_remove()
	{
		$id  = $_POST['id'];
      $bohui = $_POST['bohui'];
		if($id>0)
		{
			$info = M('user_shop')->where(array('id'=>$id))->find();
			$ress = M('User')->where(array('id'=>$info['userid']))->save(array('shoper'=>0));
			$res =  M('user_shop')->where(array('id'=>$id))->delete();

			if($res)
			{
				$this->success('驳回成功');
			}
			else
			{
				$this->error('驳回失败1');
			}
		}
		else
		{
			$this->error('驳回失败2');
		}
	}
  
  public function chongzhi_admin()
  {
    $coin = M('Coin')->where(array('status'=>1))->select();
    $this->assign('coin',$coin);
  	$this->display();
  }
  
  public function chongzhi_ac()
  {
  	$username = I('post.username');
    $coinname = I('post.coinname');
    $num = I('post.num');
  	//dump($_POST);die;
    $user = M('User')->where(array('username'=>$username))->find();
    if(!$user)
    {
    	$this->error('用户不存在');
    }
    
    $coin = M('Coin')->where(array('name'=>$coinname,'status'=>1))->find();
    if(!$coin)
    {
    	$this->error('币种错误');
    }
    
    if($num>0)
    {
    	$res = M('User_coin')->where(array('userid'=>$user['id']))->setInc($coinname,$num);
      	if($res)
        {
          	$data = array('userid'=>$user['id'],'username'=>'admin','coinname'=>$coinname,'txid'=>1,'num'=>$num,'mum'=>$num,'fee'=>0,'addtime'=>time(),'status'=>1);
        	$myzr = M('Myzr')->add($data);
          if($myzr)
          {
          	$this->success('充值成功');
          }
          else
          {
          	$this->error('充值失败');
          }
        }
    }else{
    	$this->error('数量错误');
    }
  }
  
  
  //用户钱包导出
 	public function qianbao_out()
    {
		$column = ["ID","用户名","币种","钱包标签","钱包地址","时间","状态"];

		$data = M('UserQianbao')->order('id desc')->select();

		foreach ($data as $k => $v) {
          	$list[$k]['id'] = $v['id'];
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
          	$list[$k]['coinname'] = $v['coinname'];
          	$list[$k]['name'] = $v['name'];
          	$list[$k]['addr'] = $v['addr'];
          	$list[$k]['addtime'] = date("Y-m-d H:i:s",$v['addtime']);
          	$list[$k]['status'] = $v['status'] == 1 ? '可用':'禁用';
		}
		excelOutput($list,$column,"用户钱包[".date("Y-m-d H:i:s")."]","用户钱包-".date("Y-m-d H:i:s"));
		
    }


}

?>