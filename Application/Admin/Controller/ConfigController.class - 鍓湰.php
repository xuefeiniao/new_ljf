<?php
namespace Admin\Controller;

class ConfigController extends AdminController
{
	public function index()
	{
		//$this->checkUpdata();
		$this->data = M('Config')->where(array('id' => 1))->find();
		$this->display();
	}

	public function setvip()
	{
		//$this->checkUpdata();
		$this->data = M('Config')->where(array('id' => 1))->find();
		$this->display();
	}
	
	public function edit()
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}


		if (M('Config')->where(array('id' => 1))->save($_POST)) {
			$this->success('修改成功！');
		}
		else {
			$this->error('修改失败');
		} 
	}

	public function image3333()
	{
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/coin/';
		$upload->autoSub = false;
		$info = $upload->upload();

		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			echo $path;
			exit();
		}
	}

	public function image()
	{
		// dump($_FILES);die;
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

	public function moble()
	{
		$this->data = M('Config')->where(array('id' => 1))->find();
		$this->display();
	}

	public function mobleEdit()
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (M('Config')->where(array('id' => 1))->save($_POST)) {
			$this->success('修改成功！');
		}
		else {
			$this->error('修改失败');
		}
	}

	public function contact()
	{
		$this->data = M('Config')->where(array('id' => 1))->find();
		$this->display();
	}

	public function contactEdit()
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}
		
		
		if (M('Config')->where(array('id' => 1))->save($_POST)) {
			$this->success('修改成功！');
		}
		else {
			$this->error('修改失败');
		}
	}

	public function coin($name = NULL, $field = NULL, $status = NULL)
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

		$count = M('Coin')->where($where)->count();
		$Page = new \Think\Page($count, 50);
		$show = $Page->show();
		$list = M('Coin')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		// $count = M('Coin')->where(array('status'=>1))->count();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->assign('count',$count);
		$this->display();
	}

	public function coinEdit($id = NULL)
	{
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = array();
			}
			else {
				$this->data = M('Coin')->where(array('id' => trim($_GET['id'])))->find();
			}
			
			$zhisucom_getCoreConfig = zhisucom_getCoreConfig();
			if(!$zhisucom_getCoreConfig){
				$this->error('核心配置有误');
			}

			$this->assign("zhisucom_opencoin",$zhisucom_getCoreConfig['zhisucom_opencoin']);

			
			
			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$_POST['fee_bili'] = floatval($_POST['fee_bili']);

			if ($_POST['fee_bili'] && (($_POST['fee_bili'] < 0.01) || (100 < $_POST['fee_bili']))) {
				$this->error('挂单比例只能是0.01--100之间(不用填写%)！');
			}

			$_POST['zr_zs'] = floatval($_POST['zr_zs']);

			if ($_POST['zr_zs'] && (($_POST['zr_zs'] < 0.01) || (100 < $_POST['zr_zs']))) {
				$this->error('转入赠送只能是0.01--100之间(不用填写%)！');
			}

			$_POST['zr_dz'] = intval($_POST['zr_dz']);
			$_POST['zc_fee'] = floatval($_POST['zc_fee']);

			if ($_POST['zc_fee'] && (($_POST['zc_fee'] < 0.01) || (100 < $_POST['zc_fee']))) {
				$this->error('转出手续费只能是0.01--100之间(不用填写%)！');
			}

			if ($_POST['zc_user']) {
				if (!check($_POST['zc_user'], 'dw')) {
					$this->error('官方手续费地址格式不正确！');
				}

				$ZcUser = M('UserCoin')->where(array($_POST['name'] . 'b' => $_POST['zc_user']))->find();

				if (!$ZcUser) {
					$this->error('在系统中查询不到[官方手续费地址],请务必填写正确！');
				}
			}

			$_POST['zc_min'] = intval($_POST['zc_min']);
			$_POST['zc_max'] = intval($_POST['zc_max']);

			if ($_POST['id']) {
				
				$rs = M('Coin')->save($_POST);
			}
			else {
				if (!check($_POST['name'], 'n')) {
					$this->error('币种简称只能是小写字母！');
				}

				$_POST['name'] = strtolower($_POST['name']);

				if (check($_POST['name'], 'username')) {
					// $this->error('币种名称格式不正确！');
				}

				if (M('Coin')->where(array('name' => $_POST['name']))->find()) {
					$this->error('币种存在！');
				}

				$rea = M()->execute('ALTER TABLE  `zhisucom_user_coin` ADD  `' . $_POST['name'] . '` DECIMAL(20,8) UNSIGNED NOT NULL');
				$reb = M()->execute('ALTER TABLE  `zhisucom_user_coin` ADD  `' . $_POST['name'] . 'd` DECIMAL(20,8) UNSIGNED NOT NULL ');
				$rec = M()->execute('ALTER TABLE  `zhisucom_user_coin` ADD  `' . $_POST['name'] . 'b` VARCHAR(200) NOT NULL ');
				
				//对应的商品付款类型增加币种
				$rea = M()->execute('ALTER TABLE  `zhisucom_shop_coin` ADD  `' . $_POST['name'] . '` VARCHAR(200) NOT NULL');
				
				
				
				$rs = M('Coin')->add($_POST);
			}

			if ($rs) {
				$this->success('操作成功！');
			}
			else {
				$this->error('数据未修改！');
			}
		}
	}

	public function coinStatus()
	{
		//var_dump($_REQUEST);die;
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (IS_POST) {
			$id = array();
			$id = implode(',', $_POST['id']);
		}
		else {
			$id = $_GET['id'];
		}

		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		$where['id'] = array('in', $id);
		$method = $_GET['type'];
// echo $method;die; 
		switch (strtolower($method)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'delete':
			$rs = M('Coin')->where($where)->select();

			foreach ($rs as $k => $v) {
				$rs[] = M()->execute('ALTER TABLE  `zhisucom_user_coin` DROP COLUMN ' . $v['name']);
				$rs[] = M()->execute('ALTER TABLE  `zhisucom_user_coin` DROP COLUMN ' . $v['name'] . 'd');
				$rs[] = M()->execute('ALTER TABLE  `zhisucom_user_coin` DROP COLUMN ' . $v['name'] . 'b');
				
				$rs[] = M()->execute('ALTER TABLE  `zhisucom_shop_coin` DROP COLUMN ' . $v['name']);
			}

			if (M('Coin')->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('参数非法');
		}

		if (M('Coin')->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function coinInfo($coin)
	{
		$dj_username = C('coin')[$coin]['dj_yh'];
		$dj_password = C('coin')[$coin]['dj_mm'];
		$dj_address = C('coin')[$coin]['dj_zj'];
		$dj_port = C('coin')[$coin]['dj_dk'];
		$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port);
		//var_dump($CoinClient);die;
		if (!$CoinClient) {
			$this->error('钱包对接失败！');
		}

		
		//var_dump($CoinClient->getblockchaininfo());die;
		
		$info['b'] = $CoinClient->getinfo();
		//echo $CoinClient->getinfo();die;
		$info['num'] = M('UserCoin')->sum($coin) + M('UserCoin')->sum($coin . 'd');
		$info['coin'] = $coin;
		$this->assign('data', $info);
		$this->display();
	}

	public function coinUser($coin)
	{
		$dj_username = C('coin')[$coin]['dj_yh'];
		$dj_password = C('coin')[$coin]['dj_mm'];
		$dj_address = C('coin')[$coin]['dj_zj'];
		$dj_port = C('coin')[$coin]['dj_dk'];
		$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port);

		if (!$CoinClient) {
			$this->error('钱包对接失败！');
		}

		$arr = $CoinClient->listaccounts();

		foreach ($arr as $k => $v) {
			if ($k) {
				if ($v < 1.0000000000000001E-5) {
					$v = 0;
				}

				$list[$k]['num'] = $v;
				$str = '';
				$addr = $CoinClient->getaddressesbyaccount($k);

				foreach ($addr as $kk => $vv) {
					$str .= $vv . '<br>';
				}

				$list[$k]['addr'] = $str;
				$userid = M('User')->where(array('username' => $k))->getField('id');
				$user_coin = M('UserCoin')->where(array('userid' => $userid))->find();
				$list[$k]['xnb'] = $user_coin[$coin];
				$list[$k]['xnbd'] = $user_coin[$coin . 'd'];
				$list[$k]['zj'] = $list[$k]['xnb'] + $list[$k]['xnbd'];
				$list[$k]['xnbb'] = $user_coin[$coin . 'b'];
				unset($str);
			}
		}

		$this->assign('list', $list);
		$this->display();
	}

	public function coinQing($coin)
	{
		if (!C('coin')[$coin]) {
			$this->error('参数错误！');
		}

		$info = M()->execute('UPDATE `zhisucom_user_coin` SET `' . trim($coin) . 'b`=\'\' ;');

		if ($info) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function coinImage()
	{
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/coin/';
		$upload->autoSub = false;
		$info = $upload->upload();

		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			echo $path;
			exit();
		}
	}

	public function text($name = NULL, $field = NULL, $status = NULL)
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

		$count = M('Text')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Text')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function textEdit($id = NULL)
	{
		if (empty($_POST)) {
			if ($id) {
				$this->data = M('Text')->where(array('id' => trim($id)))->find();
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
				$rs = M('Text')->save($_POST);
			}
			else {
				$_POST['adminid'] = session('admin_id');
				$rs = M('Text')->add($_POST);
			}

			if ($rs) {
				$this->success('编辑成功！');
			}
			else {
				$this->error('编辑失败！');
			}
		}
	}

	public function qita()
	{
		$this->data = M('Config')->where(array('id' => 1))->find();
		$this->display();
	}

	public function qitaEdit()
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}
		
 		if (M('Config')->where(array('id' => 1))->save($_POST)) {
			$this->success('修改成功！');
		}
		else {
			$this->error('修改失败');
		} 
	}

	public function daohang($name = NULL, $field = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else if ($field == 'title') {
				$where['title'] = array('like', '%' . $name . '%');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}else{
			$where['status'] = array('neq',-1);
		}	
		
		
		
		$count = M('Daohang')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Daohang')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function daohangEdit($id = NULL)
	{
		if (empty($_POST)) {
			if ($id) {
				$this->data = M('Daohang')->where(array('id' => trim($id)))->find();
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
				$rs = M('Daohang')->save($_POST);
			}
			else {
				$_POST['addtime'] = time();
				$rs = M('Daohang')->add($_POST);
			}

			if ($rs) {
				$this->success('编辑成功！');
			}
			else {
				$this->error('编辑失败！');
			}
		}
	}

	public function daohangStatus($id = NULL, $type = NULL, $moble = 'Daohang')
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
				S('daohang',NULL);
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
			S('daohang',NULL);
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function checkUpdata()
	{
		
		if (!S(MODULE_NAME . CONTROLLER_NAME . 'checkUpdata')) {
			$DbFields = M('Config')->getDbFields();

			if (!in_array('footer_logo', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` ADD COLUMN `footer_logo` VARCHAR(200)  NOT NULL   COMMENT \' \' AFTER `id`;');
			}

			if (in_array('mycz_invit_3', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mycz_invit_3`;');
			}

			if (in_array('mycz_invit_2', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mycz_invit_2`;');
			}

			if (in_array('mycz_invit_1', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mycz_invit_1`;');
			}

			if (in_array('mycz_invit_coin', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mycz_invit_coin`;');
			}

			if (in_array('mycz_fee', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mycz_fee`;');
			}

			if (in_array('mycz_min', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mycz_min`;');
			}

			if (in_array('mycz_max', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mycz_max`;');
			}

			if (in_array('mycz_text_index', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mycz_text_index`;');
			}

			if (in_array('mycz_text_log', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mycz_text_log`;');
			}

			if (in_array('mytx_text_index', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mytx_text_index`;');
			}

			if (in_array('mytx_text_log', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `mytx_text_log`;');
			}

			if (in_array('trade_text_index', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `trade_text_index`;');
			}

			if (in_array('trade_text_entrust', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `trade_text_entrust`;');
			}

			if (in_array('issue_text_index', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `issue_text_index`;');
			}

			if (in_array('issue_text_log', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `issue_text_log`;');
			}

			if (in_array('issue_text_plan', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `issue_text_plan`;');
			}

			if (in_array('invit_text_index', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `invit_text_index`;');
			}

			if (in_array('invit_text_award', $DbFields)) {
				M()->execute('ALTER TABLE `zhisucom_config` DROP COLUMN `invit_text_award`;');
			}

			$tables = M()->query('show tables');
			$tableMap = array();

			foreach ($tables as $table) {
				$tableMap[reset($table)] = 1;
			}

			if (!isset($tableMap['zhisucom_daohang'])) {
				M()->execute("\r\n" . '                    CREATE TABLE `zhisucom_daohang` (' . "\r\n" . '                        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'自增id\',' . "\r\n" . '                        `name` VARCHAR(255) NOT NULL COMMENT \'名称\',' . "\r\n" . '                          `title` VARCHAR(255) NOT NULL COMMENT \'名称\',' . "\r\n" . '                        `url` VARCHAR(255) NOT NULL COMMENT \'url\',' . "\r\n" . '                        `sort` INT(11) UNSIGNED NOT NULL COMMENT \'排序\',' . "\r\n" . '                        `addtime` INT(11) UNSIGNED NOT NULL COMMENT \'添加时间\',' . "\r\n" . '                        `endtime` INT(11) UNSIGNED NOT NULL COMMENT \'编辑时间\',' . "\r\n" . '                        `status` TINYINT(4)  NOT NULL COMMENT \'状态\',' . "\r\n" . '                        PRIMARY KEY (`id`)' . "\r\n\r\n" . '                  )' . "\r\n" . 'COLLATE=\'gbk_chinese_ci\'' . "\r\n" . 'ENGINE=MyISAM' . "\r\n" . 'AUTO_INCREMENT=1' . "\r\n" . ';' . "\r\n\r\n\r\n" . 'INSERT INTO `zhisucom_daohang` (`name`,`title`, `url`, `sort`, `status`) VALUES (\'finance\',\'财务中心\', \'Finance/index\', 1, 1);' . "\r\n" . 'INSERT INTO `zhisucom_daohang` (`name`,`title`, `url`, `sort`, `status`) VALUES (\'user\',\'安全中心\', \'User/index\', 2, 1);' . "\r\n" . 'INSERT INTO `zhisucom_daohang` (`name`, `title`,`url`, `sort`, `status`) VALUES (\'game\',\'应用中心\', \'Game/index\', 3, 1);' . "\r\n" . 'INSERT INTO `zhisucom_daohang` (`name`, `title`,`url`, `sort`, `status`) VALUES (\'article\',\'帮助中心\', \'Article/index\', 4, 1);' . "\r\n\r\n\r\n" . '                ');
			}

			$list = M('Menu')->where(array(
				'url' => 'Config/index',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Config/index', 'title' => '基本配置', 'pid' => 7, 'sort' => 1, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Config/index',
					'pid' => array('neq', 0)
					))->save(array('title' => '基本配置', 'pid' => 7, 'sort' => 1, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Config/moble',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Config/moble', 'title' => '短信配置', 'pid' => 7, 'sort' => 2, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Config/moble',
					'pid' => array('neq', 0)
					))->save(array('title' => '短信配置', 'pid' => 7, 'sort' => 2, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Config/contact',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Config/contact', 'title' => '客服配置', 'pid' => 7, 'sort' => 3, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Config/contact',
					'pid' => array('neq', 0)
					))->save(array('title' => '客服配置', 'pid' => 7, 'sort' => 3, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Config/coin',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Config/coin', 'title' => '币种配置', 'pid' => 7, 'sort' => 4, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Config/coin',
					'pid' => array('neq', 0)
					))->save(array('title' => '币种配置', 'pid' => 7, 'sort' => 4, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Config/text',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Config/text', 'title' => '提示文字', 'pid' => 7, 'sort' => 5, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Config/text',
					'pid' => array('neq', 0)
					))->save(array('title' => '提示文字', 'pid' => 7, 'sort' => 5, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Config/qita',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Config/qita', 'title' => '其他配置', 'pid' => 7, 'sort' => 6, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Config/qita',
					'pid' => array('neq', 0)
					))->save(array('title' => '其他配置', 'pid' => 7, 'sort' => 6, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Config/daohang',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Config/daohang', 'title' => '导航配置', 'pid' => 7, 'sort' => 7, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Config/daohang',
					'pid' => array('neq', 0)
					))->save(array('title' => '导航配置', 'pid' => 7, 'sort' => 7, 'hide' => 0, 'group' => '设置', 'ico_name' => 'cog'));
			}

			if (M('Menu')->where(array('url' => 'Market/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Coin/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			S(MODULE_NAME . CONTROLLER_NAME . 'checkUpdata', 1);
		}
	}

	public function grade(){
		$this->data = M('Config')->where(array('id' => 1))->find();
		$this->display();
	}

	# 锁仓比例
	public function setlock()
	{
		$this->data = M('Config')->where(array('id' => 1))->find();
		$this->display();
	}
	//CCB私募价
	public function sm_price(){
		$sm_price = M('Config')->where('id=1')->getField('sm_price');
		$this->assign('sm_price',$sm_price);
		$this->display();
	}
	public function sm_priceac(){
		// dump($_POST);die;
		$sm_price = $_POST['sm_price'];
		$a = M('Config')->where('id=1')->save(array('sm_price'=>$sm_price));
		$b = U('Config/sm_price');
		if($a){
			echo "<script>alert('操作成功！');window.location='".$b."'</script>";
		}else{
			echo "<script>alert('操作失败！');history.back(-1);</script>";die;
		}
	}

	#手动锁仓配置
	public function setsc(){
		if(empty($_POST)){
			$this->data = M('Config')->where('id=1')->field("sdsm,sdts,sdcs,sdpz,sdpt,sdpc")->find();
			$this->display();
		}else{
			$res = M('Config')->where('id=1')->save($_POST);
			if($res){
				$this->success('操作成功！');
			}else{
				$this->error('操作失败！');
			}
		}
	}

	# 手动解锁比例
	public function setjs()
	{
		//$this->checkUpdata();
		$this->data = M('Config')->where(array('id' => 1))->find();
		$this->display();
	}
	
	//释放
	public function setjsac(){
		$bili = I('post.bili');
		$coin = 'jeff';
		$moble = I('post.user');
		if($bili > 0){
			
				if(empty($moble)){
					$user = M('User')->field('id')->select();
					foreach($user as $k=>$v){
						if($v['id'] != 1){
						$usercoin = M('UserCoin')->where(array('userid'=>$v['id']))->find();
						if($usercoin[$coin.'sc'] > 0){
							$release = $usercoin[$coin.'sc'] * ($bili/100);
							if($usercoin[$coin.'sc'] - $release >= 0){
								$info1 = M('UserCoin')->where(array('userid'=>$v['id']))->setInc($coin,$release);
								$info2 = M('UserCoin')->where(array('userid'=>$v['id']))->setDec($coin.'sc',$release);
								
								if($info1 && $info2){
									$data['userid'] = $v['id'];
									$data['coinname'] = strtoupper($coin);
									$data['num_a'] = $usercoin[$coin];
									$data['num_b'] = $release;
									$data['num'] = $usercoin[$coin]+$release;
									$data['fee'] = $release;
									$data['type'] = '4';
									$data['name'] = 'setjs';
									$data['nameid'] = '306';
									$data['remark'] = '手动解锁';
									$data['mum_a'] = $usercoin[$coin.'sc'];
									$data['mum_b'] = $release;
									$data['mum'] = $usercoin[$coin.'sc']-$release;
									$data['addtime'] = time();
									$data['status'] = '1';

									$result = M('Finance')->add($data);
								}
							}
						}
						}
					}
				
				
			}else{
				$user = M('User')->where(array('moble'=>$moble))->field('id')->find();
			    $usercoin = M('UserCoin')->where(array('userid'=>$user['id']))->find();
				if($user['id'] == 1) exit;
				if($usercoin[$coin.'sc'] > 0){
							$release = $usercoin[$coin.'sc'] * ($bili/100);
							if($usercoin[$coin.'sc'] - $release >= 0){
								$info1 = M('UserCoin')->where(array('userid'=>$user['id']))->setInc($coin,$release);
								$info2 = M('UserCoin')->where(array('userid'=>$user['id']))->setDec($coin.'sc',$release);
								
								if($info1 && $info2){
									$data['userid'] = $user['id'];
									$data['coinname'] = strtoupper($coin);
									$data['num_a'] = $usercoin[$coin];
									$data['num_b'] = $release;
									$data['num'] = $usercoin[$coin]+$release;
									$data['fee'] = $release;
									$data['type'] = '4';
									$data['name'] = 'setjs';
									$data['nameid'] = '306';
									$data['remark'] = '手动解锁';
									$data['mum_a'] = $usercoin[$coin.'sc'];
									$data['mum_b'] = $release;
									$data['mum'] = $usercoin[$coin.'sc']-$release;
									$data['addtime'] = time();
									$data['status'] = '1';

									$result = M('Finance')->add($data);
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
	
	

	
	
	
	
	
	
	//手动释放
public function setjsac222(){
		$coins = I('post.coins');
		$tuandui = I('post.tuandui');
		// $info = M('user')->select();
		if($tuandui <= 0){
			$info = M('user')->select();
		}else{
			// $where['re_path'] = array('like','%'.$tuandui.','.'%');
			$where['leaderid'] = $tuandui;
			$info = M('user')->where($where)->select();
		}
		$coin = M('Coin')->where('status=1')->select();
		switch ($coins) {
			case '0':#全部币种
					foreach ($info as $k=> $v) {
					
					foreach ($coin as $k1 => $v1) {
						$coinname = $v1['name'].'sc';
						$ltname = $v1['name'];
						if($v['lev'] == 0){
							$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
							if($_POST['pu']>0 && $user_coin[$coinname]>0){
								$money = $user_coin[$coinname]*($_POST['pu']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($coinname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($ltname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]+$money;
									$data['fee'] = $money;
									$data['type'] = '4';
									$data['name'] = 'setjs';
									$data['nameid'] = '306';
									$data['remark'] = '手动解锁';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]-$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif ($v['lev'] == 1) {
							$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
							if ($_POST['sm']>0 && $user_coin[$coinname]>0) {
								$money = $user_coin[$coinname]*($_POST['sm']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($coinname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($ltname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]+$money;
									$data['fee'] = $money;
									$data['type'] = '4';
									$data['name'] = 'setjs';
									$data['nameid'] = '306';
									$data['remark'] = '手动解锁';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]-$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif ($v['lev'] == 2) {
							$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
							if ($_POST['ts']>0 && $user_coin[$coinname]>0) {
								$money = $user_coin[$coinname]*($_POST['ts']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($coinname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($ltname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]+$money;
									$data['fee'] = $money;
									$data['type'] = '4';
									$data['name'] = 'setjs';
									$data['nameid'] = '306';
									$data['remark'] = '手动解锁';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]-$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif ($v['lev'] == 3) {
							$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
							if ($_POST['cs']>0 && $user_coin[$coinname]>0) {
								$money = $user_coin[$coinname]*($_POST['cs']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($coinname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($ltname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]+$money;
									$data['fee'] = $money;
									$data['type'] = '4';
									$data['name'] = 'setjs';
									$data['nameid'] = '306';
									$data['remark'] = '手动解锁';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]-$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif ($v['lev'] == 4) {
							$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
							if ($_POST['pz']>0 && $user_coin[$coinname]>0) {
								$money = $user_coin[$coinname]*($_POST['pz']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($coinname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($ltname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]+$money;
									$data['fee'] = $money;
									$data['type'] = '4';
									$data['name'] = 'setjs';
									$data['nameid'] = '306';
									$data['remark'] = '手动解锁';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]-$money;
									$data['addtime'] = time();
									$data['status'] = '1';
									$as = M('finance')->add($data);
								}
							}
						}elseif($v['lev'] == 5){
							$user_coin = M('user_coin')->where('userid='.$v['id'])->find();
							if ($_POST['pc']>0 && $user_coin[$coinname]>0) {
								$money = $user_coin[$coinname]*($_POST['pc']/100);
								$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec($coinname,$money);
								$save2 = M('user_coin')->where('userid='.$v['id'])->setInc($ltname,$money);
								if($save1 && $save2){
									$data['userid'] = $user_coin['userid'];
									$data['coinname'] = $ltname;
									$data['num_a'] = $user_coin[$ltname];
									$data['num_b'] = $money;
									$data['num'] = $user_coin[$ltname]+$money;
									$data['fee'] = $money;
									$data['type'] = '4';
									$data['name'] = 'setjs';
									$data['nameid'] = '306';
									$data['remark'] = '手动解锁';
									$data['mum_a'] = $user_coin[$coinname];
									$data['mum_b'] = $money;
									$data['mum'] = $user_coin[$coinname]-$money;
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
						if($_POST['pu']>0 && $user_coin['ccbsc']>0){
							$money = $user_coin['ccbsc']*($_POST['pu']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccbsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccb',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 1) {
						if ($_POST['sm']>0 && $user_coin['ccbsc']>0) {
							$money = $user_coin['ccbsc']*($_POST['sm']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccbsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccb',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 2) {
						if ($_POST['ts']>0 && $user_coin['ccbsc']>0) {
							$money = $user_coin['ccbsc']*($_POST['ts']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccbsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccb',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 3) {
						if ($_POST['cs']>0 && $user_coin['ccbsc']>0) {
							$money = $user_coin['ccbsc']*($_POST['cs']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccbsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccb',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 4) {
						if ($_POST['pz']>0 && $user_coin['ccbsc']>0) {
							$money = $user_coin['ccbsc']*($_POST['pz']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccbsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccb',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif($v['lev'] == 5){
						if ($_POST['pc']>0 && $user_coin['ccbsc']>0) {
							$money = $user_coin['ccbsc']*($_POST['pc']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ccbsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ccb',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ccb';
								$data['num_a'] = $user_coin['ccb'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ccb']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ccbsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ccbsc']-$money;
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
						if($_POST['pu']>0 && $user_coin['ltcsc']>0){
							$money = $user_coin['ltcsc']*($_POST['pu']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 1) {
						if ($_POST['sm']>0 && $user_coin['ltcsc']>0) {
							$money = $user_coin['ltcsc']*($_POST['sm']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 2) {
						if ($_POST['ts']>0 && $user_coin['ltcsc']>0) {
							$money = $user_coin['ltcsc']*($_POST['ts']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 3) {
						if ($_POST['cs']>0 && $user_coin['ltcsc']>0) {
							$money = $user_coin['ltcsc']*($_POST['cs']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 4) {
						if ($_POST['pz']>0 && $user_coin['ltcsc']>0) {
							$money = $user_coin['ltcsc']*($_POST['pz']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif($v['lev'] == 5){
						if ($_POST['pc']>0 && $user_coin['ltcsc']>0) {
							$money = $user_coin['ltcsc']*($_POST['pc']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ltcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('ltc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'ltc';
								$data['num_a'] = $user_coin['ltc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['ltc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ltcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ltcsc']-$money;
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
						if($_POST['pu']>0 && $user_coin['btcsc']>0){
							$money = $user_coin['btcsc']*($_POST['pu']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 1) {
						if ($_POST['sm']>0 && $user_coin['btcsc']>0) {
							$money = $user_coin['btcsc']*($_POST['sm']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 2) {
						if ($_POST['ts']>0 && $user_coin['btcsc']>0) {
							$money = $user_coin['btcsc']*($_POST['ts']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 3) {
						if ($_POST['cs']>0 && $user_coin['btcsc']>0) {
							$money = $user_coin['btcsc']*($_POST['cs']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 4) {
						if ($_POST['pz']>0 && $user_coin['btcsc']>0) {
							$money = $user_coin['btcsc']*($_POST['pz']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif($v['lev'] == 5){
						if ($_POST['pc']>0 && $user_coin['btcsc']>0) {
							$money = $user_coin['btcsc']*($_POST['pc']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('btcsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('btc',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'btc';
								$data['num_a'] = $user_coin['btc'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['btc']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['btcsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['btcsc']-$money;
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
						if($_POST['pu']>0 && $user_coin['ethsc']>0){
							$money = $user_coin['ethsc']*($_POST['pu']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ethsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('eth',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 1) {
						if ($_POST['sm']>0 && $user_coin['ethsc']>0) {
							$money = $user_coin['ethsc']*($_POST['sm']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ethsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('eth',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 2) {
						if ($_POST['ts']>0 && $user_coin['ethsc']>0) {
							$money = $user_coin['ethsc']*($_POST['ts']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ethsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('eth',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 3) {
						if ($_POST['cs']>0 && $user_coin['ethsc']>0) {
							$money = $user_coin['ethsc']*($_POST['cs']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ethsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('eth',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif ($v['lev'] == 4) {
						if ($_POST['pz']>0 && $user_coin['ethsc']>0) {
							$money = $user_coin['ethsc']*($_POST['pz']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ethsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('eth',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']-$money;
								$data['addtime'] = time();
								$data['status'] = '1';
								$as = M('finance')->add($data);
							}
						}
					}elseif($v['lev'] == 5){
						if ($_POST['pc']>0 && $user_coin['ethsc']>0) {
							$money = $user_coin['ethsc']*($_POST['pc']/100);
							$save1 = M('user_coin')->where(array('userid'=>$v['id']))->setDec('ethsc',$money);
							$save2 = M('user_coin')->where('userid='.$v['id'])->setInc('eth',$money);
							if($save1 && $save2){
								$data['userid'] = $user_coin['userid'];
								$data['coinname'] = 'eth';
								$data['num_a'] = $user_coin['eth'];
								$data['num_b'] = $money;
								$data['num'] = $user_coin['eth']+$money;
								$data['fee'] = $money;
								$data['type'] = '4';
								$data['name'] = 'setjs';
								$data['nameid'] = '306';
								$data['remark'] = '手动解锁';
								$data['mum_a'] = $user_coin['ethsc'];
								$data['mum_b'] = $money;
								$data['mum'] = $user_coin['ethsc']-$money;
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


	//杠杆管理
	public function lever(){
		if($_POST){
			$info = M('Config')->where('id=1')->save($_POST);
			if($info){
				$this->success('操作成功！');
			}else{
				$this->error('操作失败！');
			}
		}else{
		$this->display();
		}
	}
	
	public function invits(){
	    if(empty($_POST)){
	       // $invit_num = M('Config')->where(array('id'=>1))->getField('invit_num');
	       // $this->assign('num',$invit_num);
	        $this->display();
	    }else{
	        $info = M('Config')->where('id=1')->save($_POST);
	        if($info){
				$this->success('操作成功！');
			}else{
				$this->error('操作失败！');
			}
	    }
	}
}

?>