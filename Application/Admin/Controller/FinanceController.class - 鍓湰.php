<?php
namespace Admin\Controller;

class FinanceController extends AdminController
{
	public function index($field = NULL, $name = NULL)
	{
		//$this->checkUpdata();
		$where = array();
		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}
		$where['isshop']=0;
		$count = M('Finance')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Finance')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$name_list = array('mycz' => '人民币充值', 'mytx' => '人民币提现', 'trade' => '委托交易', 'tradelog' => '成功交易', 'issue' => '用户认购');
		$nameid_list = array('mycz' => U('Mycz/index'), 'mytx' => U('Mytx/index'), 'trade' => U('Trade/index'), 'tradelog' => U('Tradelog/index'), 'issue' => U('Issue/index'));

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
			$list[$k]['num_a'] = Num($v['num_a']);
			$list[$k]['num_b'] = Num($v['num_b']);
			$list[$k]['num'] = Num($v['num']);
			$list[$k]['fee'] = Num($v['fee']);
			$list[$k]['type'] = ($v['fee'] == 1 ? '收入' : '支出');
			$list[$k]['name'] = ($name_list[$v['name']] ? $name_list[$v['name']] : $v['name']);
			$list[$k]['nameid'] = ($name_list[$v['name']] ? $nameid_list[$v['name']] . '?id=' . $v['nameid'] : '');
			$list[$k]['mum_a'] = Num($v['mum_a']);
			$list[$k]['mum_b'] = Num($v['mum_b']);
			$list[$k]['mum'] = Num($v['mum']);
			$list[$k]['addtime'] = addtime($v['addtime']);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	
	 
	
	
	public function mycz($field = NULL, $name = NULL, $status = NULL)
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

		$count = M('Mycz')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Mycz')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
			$list[$k]['type'] = M('MyczType')->where(array('name' => $v['type']))->getField('title');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function myczStatus($id = NULL, $type = NULL, $moble = 'Mycz')
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
			$this->error('操作失败1！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败2！');
		}
	}

	
	public function myczQueren()
	{
		$id = $_GET['id'];

		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		$mycz = M('Mycz')->where(array('id' => $id))->find();

		if (($mycz['status'] != 0) && ($mycz['status'] != 3)) {
			$this->error('已经处理，禁止再次操作！');
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_user_coin write,zhisucom_mycz write,zhisucom_finance write,zhisucom_invit write,zhisucom_user write');
		$rs = array();
		$finance = $mo->table('zhisucom_finance')->where(array('userid' => $mycz['userid']))->order('id desc')->find();
		$finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $mycz['userid']))->find();
		$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $mycz['userid']))->setInc('bb', $mycz['num']);
		$rs[] = $mo->table('zhisucom_mycz')->where(array('id' => $mycz['id']))->save(array('status' => 2, 'mum' => $mycz['num'], 'endtime' => time()));
		$finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $mycz['userid']))->find();
		$finance_hash = md5($mycz['userid'] . $finance_num_user_coin['bb'] . $finance_num_user_coin['bbd'] . $mycz['num'] . $finance_mum_user_coin['bb'] . $finance_mum_user_coin['bbd'] . MSCODE . 'auth.zhisucom.com');
		$finance_num = $finance_num_user_coin['bb'] + $finance_num_user_coin['bbd'];

		if ($finance['mum'] < $finance_num) {
			$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
		}
		else {
			$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
		}

		$rs[] = $mo->table('zhisucom_finance')->add(array('userid' => $mycz['userid'], 'coinname' => 'bb', 'num_a' => $finance_num_user_coin['bb'], 'num_b' => $finance_num_user_coin['bbd'], 'num' => $finance_num_user_coin['bb'] + $finance_num_user_coin['bbd'], 'fee' => $mycz['num'], 'type' => 1, 'name' => 'mycz', 'nameid' => $mycz['id'], 'remark' => '人民币充值-人工到账', 'mum_a' => $finance_mum_user_coin['bb'], 'mum_b' => $finance_mum_user_coin['bbd'], 'mum' => $finance_mum_user_coin['bb'] + $finance_mum_user_coin['bbd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
		
		$cz_mes="成功充值[".$mycz['num']."]元.";
		
		$cur_user_info = $mo->table('zhisucom_user')->where(array('id' => $mycz['userid']))->find();
		//invit_1  invit_2  invit_3  以mum为准  为到账金额
		//推广佣金，一次推广，终身拿佣金    奖励下线充值金额的0.6%三级分红。    一代0.3%      二代0.2%      三代0.1%
		$cz_jiner = $mycz['num'];
		if($cur_user_info['invit_1']&&$cur_user_info['invit_1']>0&&1==2){
			//存在一级推广人
			$invit_1_jiner = round(($cz_jiner/100)*0.3, 6);
			
			if ($invit_1_jiner) {
				//处理前信息
				$finance_1 = $mo->table('zhisucom_finance')->where(array('userid' => $cur_user_info['invit_1']))->order('id desc')->find();
		        $finance_num_user_coin_1 = $mo->table('zhisucom_user_coin')->where(array('userid' => $cur_user_info['invit_1']))->find();
				
				//开始处理
				$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $cur_user_info['invit_1']))->setInc('bb',$invit_1_jiner);
				$rs[] = $mo->table('zhisucom_invit')->add(array('userid' => $cur_user_info['invit_1'], 'invit' => $mycz['userid'], 'name' => 'bb', 'type' => '一代充值奖励', 'num' => $cz_jiner, 'mum' => $cz_jiner, 'fee' => $invit_1_jiner, 'addtime' => time(), 'status' => 1));
				
				//处理后
				$finance_mum_user_coin_1 = $mo->table('zhisucom_user_coin')->where(array('userid' => $cur_user_info['invit_1']))->find();
				$finance_hash_1 = md5($cur_user_info['invit_1'].$finance_num_user_coin_1['bb'] . $finance_num_user_coin_1['bbd'] . $invit_1_jiner . $finance_mum_user_coin_1['bb'] . $finance_mum_user_coin_1['bbd'] . MSCODE . 'auth.zhisucom.com');
				$finance_num_1 = $finance_num_user_coin_1['bb'] + $finance_num_user_coin_1['bbd'];

				if ($finance_1['mum'] < $finance_num_1) {
					$finance_status_1 = (1 < ($finance_num_1 - $finance_1['mum']) ? 0 : 1);
				}
				else {
					$finance_status_1 = (1 < ($finance_1['mum'] - $finance_num_1) ? 0 : 1);
				}

				$rs[] = $mo->table('zhisucom_finance')->add(array('userid' => $cur_user_info['invit_1'], 'coinname' => 'bb', 'num_a' => $finance_num_user_coin_1['bb'], 'num_b' => $finance_num_user_coin_1['bbd'], 'num' => $finance_num_user_coin_1['bb'] + $finance_num_user_coin_1['bbd'], 'fee' => $invit_1_jiner, 'type' => 1, 'name' => 'mycz', 'nameid' => $cur_user_info['invit_1'], 'remark' => '人民币充值-一代充值奖励-充值ID'.$mycz['userid'].',订单'.$mycz['tradeno'].',金额'.$cz_jiner.'元,奖励'.$invit_1_jiner.'元', 'mum_a' => $finance_mum_user_coin_1['bb'], 'mum_b' => $finance_mum_user_coin_1['bbd'], 'mum' => $finance_mum_user_coin_1['bb'] + $finance_mum_user_coin_1['bbd'], 'move' => $finance_hash_1, 'addtime' => time(), 'status' => $finance_status_1));
				
				//处理结束提示信息
				$cz_mes = $cz_mes."一代推荐奖励[".$invit_1_jiner."]元.";
			}
			

			
		}
		
		if($cur_user_info['invit_2']&&$cur_user_info['invit_2']>0&&1==2){
			//存在二级推广人
			$invit_2_jiner = round(($cz_jiner/100)*0.2, 6);
			if ($invit_2_jiner) {
				
				//处理前信息
				$finance_2 = $mo->table('zhisucom_finance')->where(array('userid' => $cur_user_info['invit_2']))->order('id desc')->find();
		        $finance_num_user_coin_2 = $mo->table('zhisucom_user_coin')->where(array('userid' => $cur_user_info['invit_2']))->find();
				
				//开始处理
				
				$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $cur_user_info['invit_2']))->setInc('bb',$invit_2_jiner);
				$rs[] = $mo->table('zhisucom_invit')->add(array('userid' => $cur_user_info['invit_2'], 'invit' => $mycz['userid'], 'name' => 'bb', 'type' => '二代充值奖励', 'num' => $cz_jiner, 'mum' => $cz_jiner, 'fee' => $invit_2_jiner, 'addtime' => time(), 'status' => 1));
			
				//处理后
				$finance_mum_user_coin_2 = $mo->table('zhisucom_user_coin')->where(array('userid' => $cur_user_info['invit_2']))->find();
				$finance_hash_2 = md5($cur_user_info['invit_2'].$finance_num_user_coin_2['bb'] . $finance_num_user_coin_2['bbd'] . $invit_2_jiner . $finance_mum_user_coin_2['bb'] . $finance_mum_user_coin_2['bbd'] . MSCODE . 'auth.zhisucom.com');
				$finance_num_2 = $finance_num_user_coin_2['bb'] + $finance_num_user_coin_2['bbd'];

				if ($finance_2['mum'] < $finance_num_2) {
					$finance_status_2 = (1 < ($finance_num_2 - $finance_2['mum']) ? 0 : 1);
				}
				else {
					$finance_status_2 = (1 < ($finance_2['mum'] - $finance_num_2) ? 0 : 1);
				}

				$rs[] = $mo->table('zhisucom_finance')->add(array('userid' => $cur_user_info['invit_2'], 'coinname' => 'bb', 'num_a' => $finance_num_user_coin_2['bb'], 'num_b' => $finance_num_user_coin_2['bbd'], 'num' => $finance_num_user_coin_2['bb'] + $finance_num_user_coin_2['bbd'], 'fee' => $invit_2_jiner, 'type' => 1, 'name' => 'mycz', 'nameid' => $cur_user_info['invit_2'], 'remark' => '人民币充值-二代充值奖励-充值ID'.$mycz['userid'].',订单'.$mycz['tradeno'].',金额'.$cz_jiner.'元,奖励'.$invit_2_jiner.'元', 'mum_a' => $finance_mum_user_coin_2['bb'], 'mum_b' => $finance_mum_user_coin_2['bbd'], 'mum' => $finance_mum_user_coin_2['bb'] + $finance_mum_user_coin_2['bbd'], 'move' => $finance_hash_2, 'addtime' => time(), 'status' => $finance_status_2));
				
				//处理结束提示信息
			
				$cz_mes = $cz_mes."二代推荐奖励[".$invit_2_jiner."]元.";
			
			}
			
		}
		
		if($cur_user_info['invit_3']&&$cur_user_info['invit_3']>0&&1==2){
			//存在三级推广人
			$invit_3_jiner = round(($cz_jiner/100)*0.1, 6);
			if ($invit_3_jiner) {
				
				//处理前信息
				$finance_3 = $mo->table('zhisucom_finance')->where(array('userid' => $cur_user_info['invit_3']))->order('id desc')->find();
		        $finance_num_user_coin_3 = $mo->table('zhisucom_user_coin')->where(array('userid' => $cur_user_info['invit_3']))->find();
				
				//开始处理

				$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $cur_user_info['invit_3']))->setInc('bb',$invit_3_jiner);
				$rs[] = $mo->table('zhisucom_invit')->add(array('userid' => $cur_user_info['invit_3'], 'invit' => $mycz['userid'], 'name' => 'bb', 'type' => '三代充值奖励', 'num' => $cz_jiner, 'mum' => $cz_jiner, 'fee' => $invit_3_jiner, 'addtime' => time(), 'status' => 1));
			
				//处理后
				$finance_mum_user_coin_3 = $mo->table('zhisucom_user_coin')->where(array('userid' => $cur_user_info['invit_3']))->find();
				$finance_hash_3 = md5($cur_user_info['invit_3'].$finance_num_user_coin_3['bb'] . $finance_num_user_coin_3['bbd'] . $invit_3_jiner . $finance_mum_user_coin_3['bb'] . $finance_mum_user_coin_3['bbd'] . MSCODE . 'auth.zhisucom.com');
				$finance_num_3 = $finance_num_user_coin_3['bb'] + $finance_num_user_coin_3['bbd'];

				if ($finance_3['mum'] < $finance_num_3) {
					$finance_status_3 = (1 < ($finance_num_3 - $finance_3['mum']) ? 0 : 1);
				}
				else {
					$finance_status_3 = (1 < ($finance_3['mum'] - $finance_num_3) ? 0 : 1);
				}

				$rs[] = $mo->table('zhisucom_finance')->add(array('userid' => $cur_user_info['invit_3'], 'coinname' => 'bb', 'num_a' => $finance_num_user_coin_3['bb'], 'num_b' => $finance_num_user_coin_3['bbd'], 'num' => $finance_num_user_coin_3['bb'] + $finance_num_user_coin_3['bbd'], 'fee' => $invit_3_jiner, 'type' => 1, 'name' => 'mycz', 'nameid' => $cur_user_info['invit_3'], 'remark' => '人民币充值-三代充值奖励-充值ID'.$mycz['userid'].',订单'.$mycz['tradeno'].',金额'.$cz_jiner.'元,奖励'.$invit_3_jiner.'元', 'mum_a' => $finance_mum_user_coin_3['bb'], 'mum_b' => $finance_mum_user_coin_3['bbd'], 'mum' => $finance_mum_user_coin_3['bb'] + $finance_mum_user_coin_3['bbd'], 'move' => $finance_hash_3, 'addtime' => time(), 'status' => $finance_status_3));
				
				//处理结束提示信息
				$cz_mes = $cz_mes."三代推荐奖励[".$invit_3_jiner."]元.";
			}
			
		}
		
		
		
		
		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success($cz_mes);
		}
		else {
			$mo->execute('rollback');
			$this->error('操作失败！');
		}
	}

	
	public function myczType()
	{
		$where = array();
		$count = M('MyczType')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('MyczType')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function myczTypeEdit()
	{
		// dump($_POST['id']);die;
		if(empty($_GET['id'])){
			$id = NULL;
		}else{
			$id = $_GET['id'];
		}
		if (empty($_POST)) {
			if ($id) {
				$this->data = M('MyczType')->where(array('id' => trim($id)))->find();
			}
			else {
				$this->data = null;
			}

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			if ($_POST['id']) {
				$rs = M('MyczType')->save($_POST);
			}
			else {
				$rs = M('MyczType')->add($_POST);
			}

			if ($rs) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}
		}
	}

	public function myczTypeImage()
	{
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/public/';
		$upload->autoSub = false;
		$info = $upload->upload();

		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			echo $path;
			exit();
		}
	}

	public function myczTypeStatus($id = NULL, $type = NULL, $moble = 'MyczType')
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
			$this->error('操作失败1！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败2！');
		}
	}

	
	

	
	public function mytx($field = NULL, $name = NULL, $status = NULL)
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

		$count = M('Mytx')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Mytx')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}


	public function mytxStatus($id = NULL, $type = NULL, $moble = 'Mytx')
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
			$this->error('操作失败1！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败2！');
		}
	}

	public function mytxChuli()
	{
		$id = $_GET['id'];

		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		if (M('Mytx')->where(array('id' => $id))->save(array('status' => 3))) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function mytxChexiao()
	{
		$id = $_GET['id'];

		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		$mytx = M('Mytx')->where(array('id' => trim($_GET['id'])))->find();
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_user_coin write,zhisucom_mytx write,zhisucom_finance write');
		$rs = array();
		$finance = $mo->table('zhisucom_finance')->where(array('userid' => $mytx['userid']))->order('id desc')->find();
		$finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $mytx['userid']))->find();
		$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $mytx['userid']))->setInc('bb', $mytx['num']);
		$rs[] = $mo->table('zhisucom_mytx')->where(array('id' => $mytx['id']))->setField('status', 2);
		$finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $mytx['userid']))->find();
		$finance_hash = md5($mytx['userid'] . $finance_num_user_coin['bb'] . $finance_num_user_coin['bbd'] . $mytx['num'] . $finance_mum_user_coin['bb'] . $finance_mum_user_coin['bbd'] . MSCODE . 'auth.zhisucom.com');
		$finance_num = $finance_num_user_coin['bb'] + $finance_num_user_coin['bbd'];

		if ($finance['mum'] < $finance_num) {
			$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
		}
		else {
			$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
		}

		$rs[] = $mo->table('zhisucom_finance')->add(array('userid' => $mytx['userid'], 'coinname' => 'bb', 'num_a' => $finance_num_user_coin['bb'], 'num_b' => $finance_num_user_coin['bbd'], 'num' => $finance_num_user_coin['bb'] + $finance_num_user_coin['bbd'], 'fee' => $mytx['num'], 'type' => 1, 'name' => 'mytx', 'nameid' => $mytx['id'], 'remark' => '人民币提现-撤销提现', 'mum_a' => $finance_mum_user_coin['bb'], 'mum_b' => $finance_mum_user_coin['bbd'], 'mum' => $finance_mum_user_coin['bb'] + $finance_mum_user_coin['bbd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('操作成功！');
		}
		else {
			$mo->execute('rollback');
			$this->error('操作失败！');
		}
	}

	public function mytxQueren()
	{
		$id = $_GET['id'];

		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		if (M('Mytx')->where(array('id' => $id))->save(array('status' => 1))) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function mytxExcel()
	{
		if (IS_POST) {
			$id = implode(',', $_POST['id']);
		}
		else {
			$id = $_GET['id'];
		}

		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		$where['id'] = array('in', $id);
		$list = M('Mytx')->where($where)->select();

		foreach ($list as $k => $v) {
			$list[$k]['userid'] = M('User')->where(array('id' => $v['userid']))->getField('username');
			$list[$k]['addtime'] = addtime($v['addtime']);

			if ($list[$k]['status'] == 0) {
				$list[$k]['status'] = '未处理';
			}
			else if ($list[$k]['status'] == 1) {
				$list[$k]['status'] = '已划款';
			}
			else if ($list[$k]['status'] == 2) {
				$list[$k]['status'] = '已撤销';
			}
			else {
				$list[$k]['status'] = '错误';
			}

			$list[$k]['bankcard'] = ' ' . $v['bankcard'] . ' ';
		}

		$zd = M('Mytx')->getDbFields();
		$xlsName = 'cade';
		$xls = array();

		foreach ($zd as $k => $v) {
			$xls[$k][0] = $v;
			$xls[$k][1] = $v;
		}

		$xls[0][2] = '编号';
		$xls[1][2] = '用户名';
		$xls[2][2] = '提现金额';
		$xls[3][2] = '手续费';
		$xls[4][2] = '到账金额';
		$xls[5][2] = '姓名';
		$xls[6][2] = '银行备注';
		$xls[7][2] = '银行名称';
		$xls[8][2] = '开户省份';
		$xls[9][2] = '开户城市';
		$xls[10][2] = '开户地址';
		$xls[11][2] = '银行卡号';
		$xls[12][2] = ' ';
		$xls[13][2] = '提现时间';
		$xls[14][2] = '导出时间';
		$xls[15][2] = '提现状态';
		$this->exportExcel($xlsName, $xls, $list);
	}

	public function mytxConfig()
	{
		if (empty($_POST)) {
			$this->display();
		}
		else if (M('Config')->where(array('id' => 1))->save($_POST)) {
			$this->success('修改成功！');
		}
		else {
			$this->error('修改失败');
		}
	}

	public function myzr($field = NULL, $name = NULL, $coinname = NULL)
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

		if ($coinname) {
			$where['coinname'] = $coinname;
		}

		$count = M('Myzr')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Myzr')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['usernamea'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function myzc($field = NULL, $name = NULL, $coinname = NULL)
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

		if ($coinname) {
			$where['coinname'] = $coinname;
		}

		$count = M('Myzc')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Myzc')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['usernamea'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function myzcQueren($id = NULL)
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		$myzc = M('Myzc')->where(array('id' => trim($id)))->find();

		if (!$myzc) {
			$this->error('转出错误！');
		}

		if ($myzc['status']) {
			$this->error('已经处理过！');
		}

		$username = M('User')->where(array('id' => $myzc['userid']))->getField('username');
		$coin = $myzc['coinname'];
		$dj_username = C('coin')[$coin]['dj_yh'];
		$dj_password = C('coin')[$coin]['dj_mm'];
		$dj_address = C('coin')[$coin]['dj_zj'];
		$dj_port = C('coin')[$coin]['dj_dk'];
		//dump($dj_username);die;
		$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
		
		$json = $CoinClient->getinfo();
//dump($CoinClient->help());die;
		/*if (!isset($json['version']) || !$json['version']) {
			$this->error('钱包链接失败！');
		}*/

		$Coin = M('Coin')->where(array('name' => $myzc['coinname']))->find();
		$fee_user = M('UserCoin')->where(array($coin . 'b' => $Coin['zc_user']))->find();
		$user_coin = M('UserCoin')->where(array('userid' => $myzc['userid']))->find();
		$zhannei = M('UserCoin')->where(array($coin . 'b' => $myzc['username']))->find();
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables  zhisucom_user_coin write  , zhisucom_myzc write  , zhisucom_myzr write , zhisucom_myzc_fee write');
		$rs = array();

		if ($zhannei) {
			$rs[] = $mo->table('zhisucom_myzr')->add(array('userid' => $zhannei['userid'], 'username' => $myzc['username'], 'coinname' => $coin, 'txid' => md5($myzc['username'] . $user_coin[$coin . 'b'] . time()), 'num' => $myzc['num'], 'fee' => $myzc['fee'], 'mum' => $myzc['mum'], 'addtime' => time(), 'status' => 1));
			$rs[] = $r = $mo->table('zhisucom_user_coin')->where(array('userid' => $zhannei['userid']))->setInc($coin, $myzc['mum']);
		}

		if (!$fee_user['userid']) {
			$fee_user['userid'] = 0;
		}

		if (0 < $myzc['fee']) {
			$rs[] = $mo->table('zhisucom_myzc_fee')->add(array('userid' => $fee_user['userid'], 'username' => $Coin['zc_user'], 'coinname' => $coin, 'num' => $myzc['num'], 'fee' => $myzc['fee'], 'mum' => $myzc['mum'], 'type' => 2, 'addtime' => time(), 'status' => 1));

			if ($mo->table('zhisucom_user_coin')->where(array($coin . 'b' => $Coin['zc_user']))->find()) {
				$rs[] = $mo->table('zhisucom_user_coin')->where(array($coin . 'b' => $Coin['zc_user']))->setInc($coin, $myzc['fee']);
				debug(array('lastsql' => $mo->table('zhisucom_user_coin')->getLastSql()), '新增费用');
			}
			else {
				$rs[] = $mo->table('zhisucom_user_coin')->add(array($coin . 'b' => $Coin['zc_user'], $coin => $myzc['fee']));
			}
		}

		//$rs[] = M('Myzc')->where(array('id' => trim($id)))->save(array('status' => 1));
//dump($rs);die;
		if (check_arr($rs)) {
			
			$mo->execute('commit');
			$mo->execute('unlock tables');
			
			if($coin == 'eth' || $coin == 'ETH')
			{
					/*$mum=$CoinClient->toWei($myzc['mum']);
					 $mum=$myzc['mum'];
					 $CoinClient = EthCommon($dj_address, $dj_port);
					 //$sendrs = $CoinClient->eth_sendTransaction($dj_username,$myzc['username'],$dj_password,$mum);
					 $sendrs = $CoinClient->eth_sendTransaction($dj_username,$myzc['username'],'Test@126.com',$mum);
					 */
					 $sendrs=true;
				}else if($coin == 'jtc' || $coin == 'JTC')
				{
					/*
					$EthClient = EthCommon($dj_address, $dj_port);
					//dump($EthClient);die;
					$mum=$myzc['mum'];
					//dump($mum);
					$mum=dechex($mum*100000000);//代币的位数18
					$mum = number_format($mum, 0, '', '');
					$mum =  base_convert($mum, 10, 16);
					$amounthex=sprintf("%064s",$mum); //转换为64位
					
					$addr2=explode('0x',  $myzc['username'])[1];//接受地址
					//dump($addr2);die;
					$dataraw='0xa9059cbb000000000000000000000000'.addr2.$amounthex;//拼接data
					//dump($dataraw);die;
					$constadd='0xb3d239238c659a5ffe569b153b60a3e1fcf8f003';//合约地址
					$sendrs = $EthClient->eth_sendTransactionraw($dj_username,$constadd,'Test@126.com',$dataraw);//转出账户,合约地址,转出账户解锁密码,data值
					//dump($sendrs);die;
					//0xa9059cbb00000000000000000000000040b2920E78E2E974D99F192bB12Be1Ae3668FE3400000000000000000000000000000000000000000000000000000000a817c800
					//0xa9059cbb00000000000000000000000053ae985e2f61557bd9349506d71fda05fec407270000000000000000000000000000000000000000000000000000000ba43b7400
				*/
					$sendrs=true;
				}
				else if($coin == 'hpa' || $coin == 'HPA')
				{
					$EthClient = EthCommon($dj_address, $dj_port);
					$mum=$myzc['mum'];
					$mum=dechex($mum*1000000000000000000);//代币的位数18
					$amounthex=sprintf("%064s",$mum); //转换为64位
					$addr2=explode('0x',  $myzc['username'])[1];//接受地址
					$dataraw='0xa9059cbb000000000000000000000000'.$addr2.$amounthex;//拼接data
					$constadd='0x9e4cd73B10bc2e393565C08C0d08dF9dfd79E537';//合约地址
					$sendrs = $EthClient->eth_sendTransactionraw($dj_username,$constadd,'HPATug89kcy@VH99999',$dataraw);//转出账户,合约地址,转出账户解锁密码,data值
				}
				else if($coin=='usdt' || $coin=='USDT'){
					$sendrs = $CoinClient->omni_send($user_coin['usdtb'],$myzc['username'],31,$myzc['mum']);
				}
				else{
					$sendrs = $CoinClient->sendtoaddress($myzc['username'], (double) $myzc['mum']);
				}
//dump($sendrs);die;
			if ($sendrs) {
				$flag = 1;
				$arr = json_decode($sendrs, true);

				if (isset($arr['status']) && ($arr['status'] == 0)) {
					$flag = 0;
				}
			}
			else {
				$flag = 0;
			}
			
			if(strtolower($coin)=="eth" || strtolower($coin)=="jtc"){
				$flag=1;
			}
			
			if (!$flag) {
				$mo->execute('rollback');
				$mo->execute('unlock tables');
				$this->error('钱包服务器转出币失败!');
			}else {
				//echo 111;
				$ress = M('Myzc')->where(array('id' => trim($id)))->save(array('status'=>1));
				//echo $ress;die;
				$mobles = M('User')->where(array('id' => $myzc['userid']))->getField('moble');
				$contents = '您的提现操作已被确认，请您注意查收！' . $code;
				send_moble($mobles, $contents);
				$this->success('转账成功！');
			}
		}
		else {
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			$this->error('转出失败!' . implode('|', $rs) . $myzc['fee']);
		}
	}

	
	
	
	
	
	public function myzcbohui($id = NULL)
	{
		$myzc = M('Myzc')->where(array('id' => trim($id)))->find();

		if (!$myzc) {
			$this->error('转出错误！');
		}

		if ($myzc['status']) {
			$this->error('已经处理！');
		}

		$username = M('User')->where(array('id' => $myzc['userid']))->getField('username');
		$coin = $myzc['coinname'];
		$num = $myzc['num'] + $myzc['fee'];
		$res = M('user_coin')->where(array('userid'=>$myzc['userid']))->setInc($coin,$num);
		if($res){
			$status = M('myzc')->where(array('id' => trim($id)))->save(array('status'=>3));
			$this->success('驳回成功');
		}else{
			$this->error('驳回失败');
		}
	}
	public function checkUpdata()
	{
		if (!S(MODULE_NAME . CONTROLLER_NAME . 'checkUpdata')) {
			$tables = M()->query('show tables');
			$tableMap = array();

			foreach ($tables as $table) {
				$tableMap[reset($table)] = 1;
			}

			if (!isset($tableMap['zhisucom_finance'])) {
				M()->execute("\r\n" . '                   CREATE TABLE `zhisucom_finance` (' . "\r\n\t" . '`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'自增id\',' . "\r\n\t" . '`userid` INT(11) UNSIGNED NOT NULL COMMENT \'用户id\',' . "\r\n\t" . '`coinname` VARCHAR(50) NOT NULL COMMENT \'币种\',' . "\r\n\t" . '`num_a` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'之前正常\',' . "\r\n\t" . '`num_b` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'之前冻结\',' . "\r\n\t" . '`num` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'之前总计\',' . "\r\n\t" . '`fee` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'操作数量\',' . "\r\n\t" . '`type` VARCHAR(50) NOT NULL COMMENT \'操作类型\',' . "\r\n\t" . '`name` VARCHAR(50) NOT NULL COMMENT \'操作名称\',' . "\r\n\t" . '`nameid` INT(11) NOT NULL COMMENT \'操作详细\',' . "\r\n\t" . '`remark` VARCHAR(50) NOT NULL COMMENT \'操作备注\',' . "\r\n\t" . '`mum_a` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'剩余正常\',' . "\r\n\t" . '`mum_b` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'剩余冻结\',' . "\r\n\t" . '`mum` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'剩余总计\',' . "\r\n\t" . '`move` VARCHAR(50) NOT NULL COMMENT \'附加\',' . "\r\n\t" . '`addtime` INT(11) UNSIGNED NOT NULL COMMENT \'添加时间\',' . "\r\n\t" . '`status` TINYINT(4) UNSIGNED NOT NULL COMMENT \'状态\',' . "\r\n\t" . 'PRIMARY KEY (`id`),' . "\r\n\t" . 'INDEX `userid` (`userid`),' . "\r\n\t" . 'INDEX `coinname` (`coinname`),' . "\r\n\t" . 'INDEX `status` (`status`)' . "\r\n" . ')' . "\r\n" . 'COLLATE=\'utf8_general_ci\'' . "\r\n" . 'ENGINE=InnoDB' . "\r\n" . ';' . "\r\n\r\n" . '                ');
			}

			$Config_DbFields = M('MyczType')->getDbFields();

			if (!in_array('truename', $Config_DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_mycz_type` ADD COLUMN `truename` VARCHAR(200)  NOT NULL   COMMENT \'名称\' AFTER `id`;');
			}

			if (!in_array('kaihu', $Config_DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_mycz_type` ADD COLUMN `kaihu` VARCHAR(200)  NOT NULL   COMMENT \'名称\' AFTER `id`;');
			}

			if (!in_array('img', $Config_DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_mycz_type` ADD COLUMN `img` VARCHAR(200)  NOT NULL   COMMENT \'名称\' AFTER `id`;');
			}

			if (!in_array('min', $Config_DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_mycz_type` ADD COLUMN `min` VARCHAR(200)  NOT NULL   COMMENT \'名称\' AFTER `id`;');
			}

			if (!in_array('max', $Config_DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_mycz_type` ADD COLUMN `max` VARCHAR(200)  NOT NULL   COMMENT \'名称\' AFTER `id`;');
			}

			if (!in_array('username', $Config_DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_mycz_type` ADD COLUMN `username` VARCHAR(200)  NOT NULL   COMMENT \'名称\' AFTER `id`;');
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/index',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Finance/index', 'title' => '财务明细', 'pid' => 4, 'sort' => 1, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Finance/index',
					'pid' => array('neq', 0)
					))->save(array('title' => '财务明细', 'pid' => 4, 'sort' => 1, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/mycz',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Finance/mycz', 'title' => '人民币充值', 'pid' => 4, 'sort' => 2, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Finance/mycz',
					'pid' => array('neq', 0)
					))->save(array('title' => '人民币充值', 'pid' => 4, 'sort' => 2, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/myczType',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Finance/myczType', 'title' => '人民币充值方式', 'pid' => 4, 'sort' => 3, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Finance/myczType',
					'pid' => array('neq', 0)
					))->save(array('title' => '人民币充值方式', 'pid' => 4, 'sort' => 3, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/mytx',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Finance/mytx', 'title' => '人民币提现', 'pid' => 4, 'sort' => 4, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Finance/mytx',
					'pid' => array('neq', 0)
					))->save(array('title' => '人民币提现', 'pid' => 4, 'sort' => 4, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/mytxConfig',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Finance/mytxConfig', 'title' => '人民币提现配置', 'pid' => 4, 'sort' => 5, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Finance/mytxConfig',
					'pid' => array('neq', 0)
					))->save(array('title' => '人民币提现配置', 'pid' => 4, 'sort' => 5, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/myzr',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Finance/myzr', 'title' => '虚拟币转入', 'pid' => 4, 'sort' => 6, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Finance/myzr',
					'pid' => array('neq', 0)
					))->save(array('title' => '虚拟币转入', 'pid' => 4, 'sort' => 6, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/myzc',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Finance/myzc', 'title' => '虚拟币转出', 'pid' => 4, 'sort' => 7, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Finance/myzc',
					'pid' => array('neq', 0)
					))->save(array('title' => '虚拟币转出', 'pid' => 4, 'sort' => 7, 'hide' => 0, 'group' => '财务', 'ico_name' => 'th-list'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/myczStatus',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/mycz',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/myczStatus', 'title' => '修改状态', 'pid' => $pid, 'sort' => 100, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/myczStatus',
						'pid' => array('neq', 0)
						))->save(array('title' => '修改状态', 'pid' => $pid, 'sort' => 100, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/myczQueren',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/mycz',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/myczQueren', 'title' => '确认到账', 'pid' => $pid, 'sort' => 100, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/myczQueren',
						'pid' => array('neq', 0)
						))->save(array('title' => '确认到账', 'pid' => $pid, 'sort' => 100, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/myczTypeEdit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/myczType',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/myczTypeEdit', 'title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/myczTypeEdit',
						'pid' => array('neq', 0)
						))->save(array('title' => '编辑添加', 'pid' => $pid, 'sort' => 1, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/myczTypeStatus',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/myczType',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/myczTypeStatus', 'title' => '状态修改', 'pid' => $pid, 'sort' => 2, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/myczTypeStatus',
						'pid' => array('neq', 0)
						))->save(array('title' => '状态修改', 'pid' => $pid, 'sort' => 2, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/myczTypeImage',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/myczType',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/myczTypeImage', 'title' => '上传图片', 'pid' => $pid, 'sort' => 2, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/myczTypeImage',
						'pid' => array('neq', 0)
						))->save(array('title' => '上传图片', 'pid' => $pid, 'sort' => 2, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/mytxStatus',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/mytx',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/mytxStatus', 'title' => '修改状态', 'pid' => $pid, 'sort' => 2, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/mytxStatus',
						'pid' => array('neq', 0)
						))->save(array('title' => '修改状态', 'pid' => $pid, 'sort' => 2, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/mytxExcel',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/mytx',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/mytxExcel', 'title' => '导出选中', 'pid' => $pid, 'sort' => 3, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/mytxExcel',
						'pid' => array('neq', 0)
						))->save(array('title' => '导出选中', 'pid' => $pid, 'sort' => 3, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/mytxChuli',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/mytx',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/mytxChuli', 'title' => '正在处理', 'pid' => $pid, 'sort' => 4, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/mytxChuli',
						'pid' => array('neq', 0)
						))->save(array('title' => '正在处理', 'pid' => $pid, 'sort' => 4, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/mytxChexiao',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/mytx',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/mytxChexiao', 'title' => '撤销提现', 'pid' => $pid, 'sort' => 5, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/mytxChexiao',
						'pid' => array('neq', 0)
						))->save(array('title' => '撤销提现', 'pid' => $pid, 'sort' => 5, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/mytxQueren',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/mytx',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/mytxQueren', 'title' => '确认提现', 'pid' => $pid, 'sort' => 6, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/mytxQueren',
						'pid' => array('neq', 0)
						))->save(array('title' => '确认提现', 'pid' => $pid, 'sort' => 6, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			$list = M('Menu')->where(array(
				'url' => 'Finance/myzcQueren',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else {
				$pid = M('Menu')->where(array(
					'url' => 'Finance/myzc',
					'pid' => array('neq', 0)
					))->getField('id');

				if (!$list) {
					M('Menu')->add(array('url' => 'Finance/myzcQueren', 'title' => '确认转出', 'pid' => $pid, 'sort' => 6, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
				else {
					M('Menu')->where(array(
						'url' => 'Finance/myzcQueren',
						'pid' => array('neq', 0)
						))->save(array('title' => '确认转出', 'pid' => $pid, 'sort' => 6, 'hide' => 1, 'group' => '财务', 'ico_name' => 'home'));
				}
			}

			if (M('Menu')->where(array('url' => 'Mycz/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Mycztype/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Mycz/type'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Mycz/invit'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Mycz/config'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Mytx/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Config/mytx'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Myzc/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Myzr/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Config/mycz'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			S(MODULE_NAME . CONTROLLER_NAME . 'checkUpdata', 1);
		}
	}

	//手动锁仓
	public function sdsc(){
		$this->display();
	}
	
	public function lock_position(){
		$bili = I('post.bili');
		$coin = 'jeff';
		$moble = I('post.user');
		//$xcoin = strtolower($coin);
		if($bili >0){
			
			$is_coin = M('Coin')->where(array('name'=>$coin,'status'=>1))->field('name')->find();
			if($is_coin){
			    if(empty($moble)){
			        $user = M('User')->field('id')->select();
    				foreach($user as $k=>$v){
    					$usercoin = M('UserCoin')->where(array('userid'=>$v['id']))->getField($coin);
    					if($usercoin>0){
    						$lock = $usercoin * ($bili/100);
    						if($usercoin-$lock >= 0){
    							$info1 = M('UserCoin')->where(array('userid'=>$v['id']))->setInc($coin.'sc',$lock);
    							$info2 = M('UserCoin')->where(array('userid'=>$v['id']))->setDec($coin,$lock);
    							
    							if($info1 && $info2){
    								$data['userid'] = $v['id'];
    								$data['coinname'] = strtoupper($coin);
    								$data['num_a'] = $usercoin;
    								$data['num_b'] = $lock;
    								$data['num'] = $usercoin - $lock;
    								$data['fee'] = $lock;
    								$data['type'] = '3';
    								$data['name'] = 'sdsc';
    								$data['nameid'] = '305';
    								$data['remark'] = '手动锁仓';
    								$data['mum_a'] = $usercoin.'sc';
    								$data['mum_b'] = $lock;
    								$data['mum'] = $usercoin.'sc' + $lock;
    								$data['addtime'] = time();
    								$data['status'] = '1';
    								
    								$result = M('Finance')->add($data);
    							}
    						}
    					}
    				}
			    }else{
			        $user = M('User')->where(array('moble'=>$moble))->field('id')->find();
			        $usercoin = M('UserCoin')->where(array('userid'=>$user['id']))->getField($coin);
			        if($usercoin>0){
			            $lock = $usercoin * ($bili/100);
			            if($usercoin-$lock >= 0){
			                $info1 = M('UserCoin')->where(array('userid'=>$user['id']))->setInc($coin.'sc',$lock);
    						$info2 = M('UserCoin')->where(array('userid'=>$user['id']))->setDec($coin,$lock);
    						if($info1 && $info2){
    						    $data['userid'] = $user['id'];
    								$data['coinname'] = strtoupper($coin);
    								$data['num_a'] = $usercoin;
    								$data['num_b'] = $lock;
    								$data['num'] = $usercoin - $lock;
    								$data['fee'] = $lock;
    								$data['type'] = '3';
    								$data['name'] = 'sdsc';
    								$data['nameid'] = '305';
    								$data['remark'] = '手动锁仓';
    								$data['mum_a'] = $usercoin.'sc';
    								$data['mum_b'] = $lock;
    								$data['mum'] = $usercoin.'sc' + $lock;
    								$data['addtime'] = time();
    								$data['status'] = '1';
    								
    								$result = M('Finance')->add($data);
    								$a = U('Finance/index');
                    				if($result){
                    					echo "<script>alert('操作成功');window.location='".$a."';</script>";
                    				}else{
                    					echo "<script>alert('操作失败');history.back(-1);</script>";die;
                    				}
    						}
			            }
			        }
			    }
			    
				$a = U('Finance/index');
				if($result){
					echo "<script>alert('操作成功');window.location='".$a."';</script>";
				}else{
					echo "<script>alert('操作失败');history.back(-1);</script>";die;
				}
				
			}
		}
	}
	
	//手动锁仓提交
	public function lock_position11(){
		$bili = I('post.bili');
		$coin = I('post.coins');

		if($bili > 0){
			$user = M('User')->field('id')->select();
			if($coin == 0){#全部币种
				$coinname = M('Coin')->where("status=1 and name != 'cny'")->field('name')->select();
				foreach ($user as $k => $v) {
					$usercoin = M("UserCoin")->where(array('userid'=>$v['id']))->find();
					foreach ($coinname as $k1 => $v1) {
						$name = $v1['name'];
						if($usercoin[$name] > 0){
							$lock = $usercoin[$name] * ($bili/100);
							if($usercoin[$name] - $lock >= 0){
								$info1 = M('UserCoin')->where(array('userid'=>$v['id']))->setInc($name.'sc',$lock);
								$info2 = M('UserCoin')->where(array('userid'=>$v['id']))->setDec($name,$lock);

								if($info1 && $info2){
									$data['userid'] = $v['id'];
									$data['coinname'] = strtoupper($name);
									$data['num_a'] = $usercoin[$name];
									$data['num_b'] = $lock;
									$data['num'] = $usercoin[$name] - $lock;
									$data['fee'] = $lock;
									$data['type'] = '3';
									$data['name'] = 'sdsc';
									$data['nameid'] = '305';
									$data['remark'] = '手动锁仓';
									$data['mum_a'] = $usercoin[$name.'sc'];
									$data['mum_b'] = $lock;
									$data['mum'] = $usercoin[$name.'sc'] + $lock;
									$data['addtime'] = time();
									$data['status'] = '1';
									$result = M('Finance')->add($data);
								}
							}
						}
					}
				}
			}else{
				$res = M('Coin')->where(array('status'=>1,'name'=>$coin))->field('name')->find();
				if($res){
					foreach ($user as $k2 => $v2) {
						$usercoin = M("UserCoin")->where(array('userid'=>$v2['id']))->find();
						if($usercoin[$coin] > 0){
							$lock = $usercoin[$coin] * ($bili/100);

							if($usercoin[$coin] - $lock >=0){
								$info1 = M('UserCoin')->where(array('userid'=>$v2['id']))->setInc($coin.'sc',$lock);
								$info2 = M('UserCoin')->where(array('userid'=>$v2['id']))->setDec($coin,$lock);

								if($info1 && $info2){
									$data['userid'] = $v2['id'];
									$data['coinname'] = strtoupper($coin);
									$data['num_a'] = $usercoin[$coin];
									$data['num_b'] = $lock;
									$data['num'] = $usercoin[$coin] - $lock;
									$data['fee'] = $lock;
									$data['type'] = '3';
									$data['name'] = 'sdsc';
									$data['nameid'] = '305';
									$data['remark'] = '手动锁仓';
									$data['mum_a'] = $usercoin[$coin.'sc'];
									$data['mum_b'] = $lock;
									$data['mum'] = $usercoin[$coin.'sc'] + $lock;
									$data['addtime'] = time();
									$data['status'] = '1';
									$result = M('Finance')->add($data);
								}
							}
						}
					}
				}
			}
			$a = U('Finance/index');
			if($result){
				echo "<script>alert('操作成功');window.location='".$a."';</script>";
			}else{
				echo "<script>alert('操作失败');history.back(-1);</script>";die;
			}
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
public function suocang(){
		$tuandui = I('post.tuandui');
		$coins = I('post.coins');
		
		if($tuandui <= 0){
			$info = M('user')->select();
		}else{
			// $where['re_path'] = array('like','%'.$tuandui.','.'%');
			$where['leaderid'] = $tuandui;
			$info = M('user')->where($where)->select();
		}
		// dump($info);die;
		$coin = M('Coin')->where('status=1')->select();
		switch ($coins) {
			case '0':#全部币种
					foreach ($info as $k=> $v) {
					$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
					foreach ($coin as $k1 => $v1) {
						$coinname = $v1['name'].'sc';
						$ltname = $v1['name'];
						if($v['lev'] == 0){
							if($_POST['pu']>0 && $user_coin[$ltname]>0){
								$money = $user_coin[$ltname]*($_POST['pu']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($ltname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($coinname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]-$money;
									$data['fee'] = $money;
									$data['type'] = '3';
									$data['name'] = 'sdsc';
									$data['nameid'] = '305';
									$data['remark'] = '手动锁仓';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]+$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif ($v['lev'] == 1) {
							if ($_POST['sm']>0 && $user_coin[$ltname]>0) {
								$money = $user_coin[$ltname]*($_POST['sm']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($ltname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($coinname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]-$money;
									$data['fee'] = $money;
									$data['type'] = '3';
									$data['name'] = 'sdsc';
									$data['nameid'] = '305';
									$data['remark'] = '手动锁仓';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]+$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif ($v['lev'] == 2) {
							if ($_POST['ts']>0 && $user_coin[$ltname]>0) {
								$money = $user_coin[$ltname]*($_POST['ts']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($ltname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($coinname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]-$money;
									$data['fee'] = $money;
									$data['type'] = '3';
									$data['name'] = 'sdsc';
									$data['nameid'] = '305';
									$data['remark'] = '手动锁仓';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]+$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif ($v['lev'] == 3) {
							if ($_POST['cs']>0 && $user_coin[$ltname]>0) {
								$money = $user_coin[$ltname]*($_POST['cs']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($ltname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($coinname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]-$money;
									$data['fee'] = $money;
									$data['type'] = '3';
									$data['name'] = 'sdsc';
									$data['nameid'] = '305';
									$data['remark'] = '手动锁仓';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]+$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif ($v['lev'] == 4) {
							if ($_POST['pz']>0 && $user_coin[$ltname]>0) {
								$money = $user_coin[$ltname]*($_POST['pz']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($ltname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($coinname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]-$money;
									$data['fee'] = $money;
									$data['type'] = '3';
									$data['name'] = 'sdsc';
									$data['nameid'] = '305';
									$data['remark'] = '手动锁仓';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]+$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif($v['lev'] == 5){
							if ($_POST['pc']>0 && $user_coin[$ltname]>0) {
								$money = $user_coin[$ltname]*($_POST['pc']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($ltname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($coinname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]-$money;
									$data['fee'] = $money;
									$data['type'] = '3';
									$data['name'] = 'sdsc';
									$data['nameid'] = '305';
									$data['remark'] = '手动锁仓';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]+$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}
					}
					
				}
				$a = U('Finance/index');
				if($as){
					echo "<script>alert('操作成功');window.location='".$a."';</script>";
				}else{
					echo "<script>alert('操作失败');history.back(-1);</script>";die;
				}
				break;
			case '2':#CCB
				foreach ($info as $k=> $v) {
					$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
					if($v['lev'] == 0){
						if($_POST['pu']>0 && $user_coin['ccb']>0){
							$money = $user_coin['ccb']*($_POST['pu']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccb',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccbsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 1) {
						if ($_POST['sm']>0 && $user_coin['ccb']>0) {
							$money = $user_coin['ccb']*($_POST['sm']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccb',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccbsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 2) {
						if ($_POST['ts']>0 && $user_coin['ccb']>0) {
							$money = $user_coin['ccb']*($_POST['ts']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccb',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccbsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 3) {
						if ($_POST['cs']>0 && $user_coin['ccb']>0) {
							$money = $user_coin['ccb']*($_POST['cs']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccb',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccbsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 4) {
						if ($_POST['pz']>0 && $user_coin['ccb']>0) {
							$money = $user_coin['ccb']*($_POST['pz']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccb',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccbsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif($v['lev'] == 5){
						if ($_POST['pc']>0 && $user_coin['ccb']>0) {
							$money = $user_coin['ccb']*($_POST['pc']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccb',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccbsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}
					
				}
				$a = U('Finance/index');
				if($as){
					echo "<script>alert('操作成功');window.location='".$a."';</script>";
				}else{
					echo "<script>alert('操作失败');history.back(-1);</script>";die;
				}
				break;
				case 3:#LTC
					foreach ($info as $k=> $v) {
					$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
					if($v['lev'] == 0){
						if($_POST['pu']>0 && $user_coin['ltc']>0){
							$money = $user_coin['ltc']*($_POST['pu']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 1) {
						if ($_POST['sm']>0 && $user_coin['ltc']>0) {
							$money = $user_coin['ltc']*($_POST['sm']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 2) {
						if ($_POST['ts']>0 && $user_coin['ltc']>0) {
							$money = $user_coin['ltc']*($_POST['ts']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 3) {
						if ($_POST['cs']>0 && $user_coin['ltc']>0) {
							$money = $user_coin['ltc']*($_POST['cs']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 4) {
						if ($_POST['pz']>0 && $user_coin['ltc']>0) {
							$money = $user_coin['ltc']*($_POST['pz']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif($v['lev'] == 5){
						if ($_POST['pc']>0 && $user_coin['ltc']>0) {
							$money = $user_coin['ltc']*($_POST['pc']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}
					
				}
				$a = U('Finance/index');
				if($as){
					echo "<script>alert('操作成功');window.location='".$a."';</script>";
				}else{
					echo "<script>alert('操作失败');history.back(-1);</script>";die;
				}
				break;
				case 4:#BTC
					foreach ($info as $k=> $v) {
					$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
					if($v['lev'] == 0){
						if($_POST['pu']>0 && $user_coin['btc']>0){
							$money = $user_coin['btc']*($_POST['pu']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 1) {
						if ($_POST['sm']>0 && $user_coin['btc']>0) {
							$money = $user_coin['btc']*($_POST['sm']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 2) {
						if ($_POST['ts']>0 && $user_coin['btc']>0) {
							$money = $user_coin['btc']*($_POST['ts']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 3) {
						if ($_POST['cs']>0 && $user_coin['btc']>0) {
							$money = $user_coin['btc']*($_POST['cs']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 4) {
						if ($_POST['pz']>0 && $user_coin['btc']>0) {
							$money = $user_coin['btc']*($_POST['pz']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif($v['lev'] == 5){
						if ($_POST['pc']>0 && $user_coin['btc']>0) {
							$money = $user_coin['btc']*($_POST['pc']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btcsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}
					
				}
				$a = U('Finance/index');
				if($as){
					echo "<script>alert('操作成功');window.location='".$a."';</script>";
				}else{
					echo "<script>alert('操作失败');history.back(-1);</script>";die;
				}
				break;
				case 5:#ETH
					foreach ($info as $k=> $v) {
					$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
					if($v['lev'] == 0){
						if($_POST['pu']>0 && $user_coin['eth']>0){
							$money = $user_coin['eth']*($_POST['pu']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('eth',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ethsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 1) {
						if ($_POST['sm']>0 && $user_coin['eth']>0) {
							$money = $user_coin['eth']*($_POST['sm']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('eth',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ethsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 2) {
						if ($_POST['ts']>0 && $user_coin['eth']>0) {
							$money = $user_coin['eth']*($_POST['ts']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('eth',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ethsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 3) {
						if ($_POST['cs']>0 && $user_coin['eth']>0) {
							$money = $user_coin['eth']*($_POST['cs']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('eth',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ethsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 4) {
						if ($_POST['pz']>0 && $user_coin['eth']>0) {
							$money = $user_coin['eth']*($_POST['pz']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('eth',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ethsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif($v['lev'] == 5){
						if ($_POST['pc']>0 && $user_coin['eth']>0) {
							$money = $user_coin['eth']*($_POST['pc']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('eth',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ethsc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']-$money;
								$data['fee'] = $money;
								$data['type'] = '3';
								$data['name'] = 'sdsc';
								$data['nameid'] = '305';
								$data['remark'] = '手动锁仓';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']+$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}
					
				}
				$a = U('Finance/index');
				if($as){
					echo "<script>alert('操作成功');window.location='".$a."';</script>";
				}else{
					echo "<script>alert('操作失败');history.back(-1);</script>";die;
				}
				break;
				
			
		}
	
	}


	
}

?>