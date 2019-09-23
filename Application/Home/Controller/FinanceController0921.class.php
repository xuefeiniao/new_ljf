<?php
namespace Home\Controller;

class FinanceController extends HomeController
{
    
    public function test_sum(){
        get_vip_fee(112);
    }

	public function index()
	{
		if (!userid()) {
			redirect('/#login');
		}
		
	//	$this->upcoin('wfii');
	//	$this->upcoin('btc');
	//	$this->upcoin('ltc');
	//$this->upcoin('etc');
	//$this->upcoin('eth');

		$CoinList = M('Coin')->where(array('status' => 1))->select();
		$UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();
		$Market = M('Market')->where(array('status' => 1))->select();

		foreach ($Market as $k => $v) {
			$Market[$v['name']] = $v;
		}
		$cny['zj'] = 0;

/* 		foreach ($CoinList as $k => $v) {
			if ($v['name'] == 'cny') {
				$cny['ky'] = round($UserCoin[$v['name']], 2) * 1;
				$cny['dj'] = round($UserCoin[$v['name'] . 'd'], 2) * 1;
				$cny['zj'] = $cny['zj'] + $cny['ky'] + $cny['dj'];
			}
			else {
				if ($Market[$v['name'] . '_cny']['new_price']) {
					$jia = $Market[$v['name'] . '_cny']['new_price'];
				}
				else {
					$jia = 1;
				}

				$coinList[$v['name']] = array('name' => $v['name'], 'img' => $v['img'], 'title' => $v['title'] . '(' . strtoupper($v['name']) . ')', 'xnb' => round($UserCoin[$v['name']], 6) * 1, 'xnbd' => round($UserCoin[$v['name'] . 'd'], 6) * 1, 'xnbz' => round($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd'], 6), 'jia' => $jia * 1, 'zhehe' => round(($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia, 2));
				$cny['zj'] = round($cny['zj'] + (($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia), 2) * 1;
			}
		} */
		
		//20170514修改按类型统计
		
		foreach ($CoinList as $k => $v) {
			if ($v['name'] == 'bdb') {
				$cny['ky'] = round($UserCoin[$v['name']], 2) * 1;		//可用
				$cny['dj'] = round($UserCoin[$v['name'] . 'd'], 2) * 1;	//冻结
				$cny['zj'] = $cny['zj'] + $cny['ky'] + $cny['dj'];		//预估总资产
			}
			else {
				//if ($Market[C('market_type')[$v['name']]]['new_price']) {   //$v['name'] 币种
				 if ($Market[$v['name'].'_bdb']['new_price']) {
					$jia = $Market[$v['name'].'_bdb']['new_price'];//$Market[C('market_type')[$v['name']]]['new_price'];
					//echo $jia;
				}
				else {
					$jia = 1;
				}
				//开启市场时才显示对应的币
				if(in_array($v['name'],C('coin_on'))){
					$coinList[$v['name']] = array('name' => strtoupper($v['name']), 'img' => $v['img'], 'title' => $v['title'] . '(' . strtoupper($v['name']) . ')', 'xnb' => round($UserCoin[$v['name']], 6) * 1, 'xnbd' => round($UserCoin[$v['name'] . 'd'], 6) * 1, 'xnbc' => round($UserCoin[$v['name'] . 'c'], 6) * 1,'xnbz' => round($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']+$UserCoin[$v['name'] . 'c'], 6), 'jia' => $jia * 1, 'zhehe' => round(($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia, 2));
				}				
				$cny['zj'] = round($cny['zj'] + (($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia), 2) * 1;
			
			}
		}
		//$cny['zj'] = num_format($cny['zj']);
		//总bdb这个成btc
		$zhehe_btc = M('Market')->where(array('name'=>'btc_bdb'))->getField('new_price');
		$cny['bdb_btc'] = round($cny['zj']/$zhehe_btc,5);
		
      	
		$cny['uid'] = userid();
		$this->assign('cny', $cny);
		$this->assign('coinList', $coinList);
	//	var_dump($coinList);die;
		$this->assign('prompt_text', D('Text')->get_content('finance_index'));
		$this->display();
	}
    //地址管理
    public function mydz(){
        $this->display();
    }
	public function left()
	{
		if (!userid()) {
			redirect('/#login');
		}
		
		$user = M('User')->find(userid());
		$this->assign('u', $user);
		$this->display();
		
	}
	
	
	public function fhindex()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('game_fenhong'));
		$coin_list = D('Coin')->get_all_xnb_list_allow();

		foreach ($coin_list as $k => $v) {
			$list[$k]['img'] = D('Coin')->get_img($k);
			$list[$k]['title'] = $v;
			$list[$k]['quanbu'] = D('Coin')->get_sum_coin($k);
			$list[$k]['wodi'] = D('Coin')->get_sum_coin($k, userid());
			$list[$k]['bili'] = round(($list[$k]['wodi'] / $list[$k]['quanbu']) * 100, 2) . '%';
		}

		$this->assign('list', $list);
		$this->display();
	}
	
	public function upcoin($coin)
	{
		$uid = userid();
		//$u=M('user')->find($uid);
		//$uuu = M('User')->where(array('id' => $uid))->find();
		$dj_username = C('coin')[$coin]['dj_yh'];
		$dj_password = C('coin')[$coin]['dj_mm'];
		$dj_address = C('coin')[$coin]['dj_zj'];
		$dj_port = C('coin')[$coin]['dj_dk'];
		$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port);
		
		if (!$CoinClient) {
			$this->error('error');
		}
		
		if($CoinClient)
		{
		
		$user_coin = M('UserCoin')->where(array('userid' => $uid))->find();
		$old_user_coin = M('old_user_coin')->where(array('userid' => $uid))->find();
	
				$addr = $user_coin[$coin . 'b'];
			if($addr)
			{
				$oldmoney = $old_user_coin[$coin.'2'];
				$newmoney = $CoinClient->getreceivedbyaddress($addr);
				if($newmoney>0)
				{
				$cha = round($newmoney-$oldmoney,8);
				
				
				
				if($cha > 0)
				{
			
					$data[$coin] = $user_coin[$coin]+$cha;
					$datas[$coin.'2'] =  round($newmoney,8);
					$user_coins = M('UserCoin')->where(array('userid' => $uid))->save($data);
					$old_user_coins = M('old_user_coin')->where(array('userid' => $uid))->save($datas);
					
				}
				}
			}
	}
	}
	
	
	public function myfhzhisucom()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('game_fenhong_log'));
		$where['userid'] = userid();
		$Model = M('FenhongLog');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	
	
	
	
	public function bank(){
		if (!userid()) {
			redirect('/#login');
		}

		$UserBankType = M('UserBankType')->where(array('status' => 1))->order('id desc')->select();
		$this->assign('UserBankType', $UserBankType);
		$truename = M('User')->where(array('id' => userid()))->getField('truename');
		$this->assign('truename', $truename);
		//$UserBank = M('UserBank')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->limit(1)->select();
		$UserBank = M('UserBank')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();
		
		$this->assign('UserBank', $UserBank);
		$this->assign('prompt_text', D('Text')->get_content('user_bank'));
		$this->display();
	}
	
	
	public function upbank($name, $bank, $bankprov, $bankcity, $bankaddr, $bankuname, $bankcard, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($name, 'a')) {
			$this->error('备注名称格式错误！');
		}

		if (!check($bank, 'a')) {
			$this->error('开户银行格式错误！');
		} 
		
		if (!check($bankprov, 'c')) {
			$this->error('开户省市格式错误！');
		}

		if (!check($bankcity, 'c')) {
			$this->error('开户省市格式错误2！');
		}

		if (!check($bankaddr, 'a')) {
			$this->error('开户行地址格式错误！');
		}

		if (!check($bankcard, 'd')) {
			$this->error('银行账号格式错误！');
		}
		
		if(strlen($bankcard) < 16 || strlen($bankcard) > 19){
			
			$this->error('银行账号格式错误！');
			
		}
		

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

 		if (!M('UserBankType')->where(array('title' => $bank))->find()) {
			$this->error('开户银行错误！');
		} 

		$userBank = M('UserBank')->where(array('userid' => userid()))->select();

 		foreach ($userBank as $k => $v) {
			if ($v['name'] == $name) {
				$this->error('请不要使用相同的备注名称！');
			}

			if ($v['bankcard'] == $bankcard) {
				$this->error('银行卡号已存在！');
			}
		} 

		if (10 <= count($userBank)) {
			$this->error('每个用户最多只能添加10个银行卡账户！');
		}
		
		if($bankuname==""){
			
			$this->error('请输入开户行姓名！');
			
		}

		if (M('UserBank')->add(array('userid' => userid(), 'name' => $name, 'bank' => $bank, 'bankprov' => $bankprov, 'bankcity' => $bankcity, 'bankaddr' => $bankaddr,'bankuname' => $bankuname, 'bankcard' => $bankcard, 'addtime' => time(), 'status' => 1))) {
			$this->success('银行添加成功！');
		}
		else {
			$this->error('银行添加失败！');
		}
	}

	public function delbank($id, $paypassword)
	{

		if (!userid()) {
			redirect('/#login');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		if (!check($id, 'd')) {
			$this->error('参数错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		if (!M('UserBank')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error('非法访问！');
		}
		else if (M('UserBank')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success('删除成功！');
		}
		else {
			$this->error('删除失败！');
		}
	}
  //人民币充值
  	public function mycz1($status = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_mycz'));
		$myczType = M('MyczType')->where(array('status' => 1))->select();
		//var_dump($myczType);die;
		/*foreach ($myczType as $k => $v) {
			$myczTypeList[$v['name']] = $v['title'];
		}*/

		$this->assign('myczType', $myczType);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin['cny'] = round($user_coin['cny'], 2);
		$user_coin['cnyd'] = round($user_coin['cnyd'], 2);
		$this->assign('user_coin', $user_coin);

		if (($status == 1) || ($status == 2) || ($status == 3) || ($status == 4)) {
			$where['status'] = $status - 1;
		}

		$this->assign('status', $status);
		$where['userid'] = userid();
      	$list_info = M('Mycz')->where($where)->order('addtime desc')->limit(10)->select();
      $this->assign('list_info',$list_info);
		$count = M('Mycz')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Mycz')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['type'] = M('MyczType')->where(array('name' => $v['type']))->getField('title');
			$list[$k]['typeEn'] = $v['type'];
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
  public function mycz2(){
  	$this->display();
  }
	
	
	//我的挂单
  public function mygd(){

	    if (!userid()) {
          redirect('/Login/register');
        }

      $Moble=M('trade');
      $count = $Moble->where(['userid'=>userid(),'status'=>0])->count();
      $Page = new \Think\Page($count, 15);
      $show = $Page->show();
      $list = $Moble->where(['userid'=>userid(),'status'=>0])->order('addtime DESC')->limit($Page->firstRow.','.$Page->listRows)->select();

      foreach($list as $k=>$v){
          $n1 = explode('_', $v['market'])[0];
          $n2 = explode('_', $v['market'])[1];
          $list[$k]['ncoin'] = $n1.'/'.$n2;
          $list[$k]['coin'] = M('coin')->where(['name'=>$n1])->field('name,title,img')->find();

      }
      $this->assign('list', $list);
      $this->assign('page', $show);
      $this->display();

    }	
	
	
	
	
	public function mycz($status = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_mycz'));
		$myczType = M('MyczType')->where(array('status' => 1))->select();

		foreach ($myczType as $k => $v) {
			$myczTypeList[$v['name']] = $v['title'];
		}

		$this->assign('myczTypeList', $myczTypeList);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin['cny'] = round($user_coin['cny'], 2);
		$user_coin['cnyd'] = round($user_coin['cnyd'], 2);
		$this->assign('user_coin', $user_coin);

		if (($status == 1) || ($status == 2) || ($status == 3) || ($status == 4)) {
			$where['status'] = $status - 1;
		}

		$this->assign('status', $status);
		$where['userid'] = userid();
		$count = M('Mycz')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Mycz')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['type'] = M('MyczType')->where(array('name' => $v['type']))->getField('title');
			$list[$k]['typeEn'] = $v['type'];
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function myczHuikuan($id = NULL)
	{
      //var_dump($_POST);die;
		if (!userid()) {
			$this->error('请先登录！');
		}

		/*if (!check($id, 'd')) {
			$this->error('参数错误！');
		}*/

		$mycz = M('Mycz')->where(array('tradeno' => $_POST['id']))->find();

		if (!$mycz) {
			$this->error('充值订单不存在！');
		}

		if ($mycz['userid'] != userid()) {
			$this->error('非法操作！');
		}

		if ($mycz['status'] != 0) {
			$this->error('订单已经处理过！');
		}

		$rs = M('Mycz')->where(array('tradeno' => $_POST['id']))->save(array('status' => 3));

		if ($rs) {
			$this->success('操作成功');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function myczChakan($id = NULL)
	{
		if (!userid()) {
			$this->error('请先登录！');
		}

		if (!check($id, 'd')) {
			$this->error('参数错误！');
		}

		$mycz = M('Mycz')->where(array('id' => $id))->find();

		if (!$mycz) {
			$this->error('充值订单不存在！');
		}

		if ($mycz['userid'] != userid()) {
			$this->error('非法操作！');
		}

		if ($mycz['status'] != 0) {
			$this->error('订单已经处理过！');
		}

		$rs = M('Mycz')->where(array('id' => $id))->save(array('status' => 3));

		if ($rs) {
			$this->success('', array('id' => $id));
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function myczUp()
	{
		//var_dump($_POST);die;
      $num = $_POST['num'];
      $type = $_POST['type'];
		if (!userid()) {
			$this->error('请先登录！');
		}
	
		if (!check($type, 'n')) {
			$this->error('充值方式格式错误！');
		}

		if (!check($num, 'cny')) {
			$this->error('充值金额格式错误！');
		}

		$myczType = M('MyczType')->where(array('name' => $type))->find();

		if (!$myczType) {
			$this->error('充值方式不存在！');
		}

		if ($myczType['status'] != 1) {
			$this->error('充值方式没有开通！');
		}

		$mycz_min = ($myczType['min'] ? $myczType['min'] : 1);
		$mycz_max = ($myczType['max'] ? $myczType['max'] : 100000);

		if ($num < $mycz_min) {
			$this->error('充值金额不能小于' . $mycz_min . '元！');
		}

		if ($mycz_max < $num) {
			$this->error('充值金额不能大于' . $mycz_max . '元！');
		}

		for (; true; ) {
			$tradeno = tradeno();

			if (!M('Mycz')->where(array('tradeno' => $tradeno))->find()) {
				break;
			}
		}

		$mycz = M('Mycz')->add(array('userid' => userid(), 'num' => $num, 'type' => $type, 'tradeno' => $tradeno, 'addtime' => time(), 'status' => 0));

		if ($mycz) {
			$this->success('充值订单创建成功！',"/finance/mycz2/types/".$type."/id/".$tradeno);
         	// $this->success('充值订单创建成功！');
		}
		else {
			$this->error('提现订单创建失败！');
		}
	}

	
	
	public function outlog($status = NULL){
		
		if (!userid()) {
			redirect('/#login');
		}
		
		$this->assign('prompt_text', D('Text')->get_content('finance_mytx'));
		
		
		if (($status == 1) || ($status == 2) || ($status == 3) || ($status == 4)) {
			$where['status'] = $status - 1;
		}
		$where['userid'] = userid();
		$count = M('Mytx')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Mytx')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['fee'] = (Num($v['fee']) ? Num($v['fee']) : '');
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
		}
		$this->assign('status', $status);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
		
	}
	
	
	
	
	
	
	
	
	
	
	public function mytx($status = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_mytx'));
		$moble = M('User')->where(array('id' => userid()))->getField('moble');

		if ($moble) {
			$moble = substr_replace($moble, '****', 3, 4);
		}
		else {
			$this->error('请先认证手机！');
		}

		$this->assign('moble', $moble);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin['cny'] = round($user_coin['cny'], 2);
		$user_coin['cnyd'] = round($user_coin['cnyd'], 2);
		$this->assign('user_coin', $user_coin);
		$userBankList = M('UserBank')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->limit(10)->select();
		$this->assign('userBankList', $userBankList);

		if (($status == 1) || ($status == 2) || ($status == 3) || ($status == 4)) {
			$where['status'] = $status - 1;
		}

		$this->assign('status', $status);
		$where['userid'] = userid();
		$count = M('Mytx')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Mytx')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['fee'] = (Num($v['fee']) ? Num($v['fee']) : '');
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

public function mytxUp($moble_verify, $num, $paypassword, $type)
	{
		if (!userid()) {
			$this->error('请先登录！');
		}

		if (!check($moble_verify, 'd')) {
// 			$this->error('短信验证码格式错误！');
		}

		if (!check($num, 'd')) {
			$this->error('提现金额格式错误！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		if (!check($type, 'd')) {
			$this->error('提现方式格式错误！');
		}

		if ($moble_verify != session('mytx_verify')) {
// 			$this->error('短信验证码错误！');
		} 

		$userCoin = M('UserCoin')->where(array('userid' => userid()))->find();

		if ($userCoin['bb'] < $num) {
			$this->error('可用人民币余额不足！');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if (md5($paypassword) != $user['paypassword']) {
			$this->error('交易密码错误！');
		}

		$userBank = M('UserBank')->where(array('id' => $type))->find();

		if (!$userBank) {
			$this->error('提现地址错误！');
		}

		$mytx_min = (C('mytx_min') ? C('mytx_min') : 1);
		$mytx_max = (C('mytx_max') ? C('mytx_max') : 1000000);
		$mytx_bei = C('mytx_bei');
		$mytx_fee = C('mytx_fee');

		if ($num < $mytx_min) {
			$this->error('每次提现金额不能小于' . $mytx_min . '元！');
		}

		if ($mytx_max < $num) {
			$this->error('每次提现金额不能大于' . $mytx_max . '元！');
		}

		if ($mytx_bei) {
			if ($num % $mytx_bei != 0) {
				$this->error('每次提现金额必须是' . $mytx_bei . '的整倍数！');
			}
		}

		$fee = round(($num / 100) * $mytx_fee, 2);
		$mum = round(($num / 100) * (100 - $mytx_fee), 2);
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_mytx write , zhisucom_user_coin write ,zhisucom_finance write');
		$rs = array();
		$finance = $mo->table('zhisucom_finance')->where(array('userid' => userid()))->order('id desc')->find();
		$finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();
		$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec('bb', $num);
		$rs[] = $finance_nameid = $mo->table('zhisucom_mytx')->add(array('userid' => userid(), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'name' => $userBank['name'], 'truename' => $user['truename'], 'bank' => $userBank['bank'], 'bankprov' => $userBank['bankprov'], 'bankcity' => $userBank['bankcity'], 'bankaddr' => $userBank['bankaddr'], 'bankcard' => $userBank['bankcard'], 'addtime' => time(), 'status' => 0));
		$finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();
		$finance_hash = md5(userid() . $finance_num_user_coin['bb'] . $finance_num_user_coin['bbd'] . $mum . $finance_mum_user_coin['bb'] . $finance_mum_user_coin['bbd'] . MSCODE . 'auth.zhisucom.com');
		$finance_num = $finance_num_user_coin['bb'] + $finance_num_user_coin['bbd'];

		if ($finance['mum'] < $finance_num) {
			$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
		}
		else {
			$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
		}

		$rs[] = $mo->table('zhisucom_finance')->add(array('userid' => userid(), 'coinname' => 'bb', 'num_a' => $finance_num_user_coin['bb'], 'num_b' => $finance_num_user_coin['bbd'], 'num' => $finance_num_user_coin['bb'] + $finance_num_user_coin['bbd'], 'fee' => $num, 'type' => 2, 'name' => 'mytx', 'nameid' => $finance_nameid, 'remark' => '人民币提现-申请提现', 'mum_a' => $finance_mum_user_coin['bb'], 'mum_b' => $finance_mum_user_coin['bbd'], 'mum' => $finance_mum_user_coin['bb'] + $finance_mum_user_coin['bbd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));

		if (check_arr($rs)) {
			session('mytx_verify', null);
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('提现订单创建成功！');
		}
		else {
			$mo->execute('rollback');
			$this->error('提现订单创建失败！');
		}
	}


   public function mytxChexiao($id)
	{
		if (!userid()) {
			$this->error('请先登录！');
		}

		if (!check($id, 'd')) {
			$this->error('参数错误！');
		}

		$mytx = M('Mytx')->where(array('id' => $id))->find();

		if (!$mytx) {
			$this->error('提现订单不存在！');
		}

		if ($mytx['userid'] != userid()) {
			$this->error('非法操作！');
		}

		if ($mytx['status'] != 0) {
			$this->error('订单不能撤销！');
		}

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

	
	public function myzr($coin = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}
        //echo $coin;die;
		$this->assign('prompt_text', D('Text')->get_content('finance_myzr'));
        
        $coin=strtolower($_GET['name']);
        
        //$coin='ltc';
        //dump($coin);
        /*
		if (C('coin')[$coin]) {
			$coin = trim($coin);
		}
		else {
			$coin = C('xnb_mr');
		}*/
        //echo $coin;die;
        
		$this->assign('xnb', $coin);
		$Coin = M('Coin')->where(array(
			'status' => 1,
			'name'   => array('neq', 'cny')
			))->select();

		foreach ($Coin as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin[$coin] = round($user_coin[$coin], 6);
		$this->assign('user_coin', $user_coin);
		$Coin = M('Coin')->where(array('name' => $coin))->find();
		$this->assign('zr_jz', $Coin['zr_jz']);

		
		$zhisucom_getCoreConfig = zhisucom_getCoreConfig();
		if(!$zhisucom_getCoreConfig){
			$this->error('核心配置有误');
		}
		
		$this->assign("zhisucom_opencoin",$zhisucom_getCoreConfig['zhisucom_opencoin']);
		
		if($zhisucom_getCoreConfig['zhisucom_opencoin'] == 1)
		{		
		
				if (!$Coin['zr_jz']) {
					$qianbao = '当前币种禁止转入！';
					$enqianbao = 'Unable to transfer current currency！';
				}
				else {
					$qbdz = $coin . 'b';

						if (!$user_coin[$qbdz]) {
				if ($Coin['type'] == 'rgb') {
					$qianbao = md5(username() . $coin);
					$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($qbdz => $qianbao));

					if (!$rs) {
						$this->error('生成钱包地址出错！');
					}
				}

				if ($Coin['type'] == 'qbb') {
					$dj_username = $Coin['dj_yh'];
					$dj_password = $Coin['dj_mm'];
					$dj_address = $Coin['dj_zj'];
					$dj_port = $Coin['dj_dk'];
					
			if($coin=='eth'){
			    //echo 111;die;
						if($user_coin['ethb']){
							$cunb=$user_coin['ethb'];
						}
						$EthClient = EthCommon($dj_address, $dj_port);
								$result = $EthClient->web3_clientVersion();

						if (!$result) {
							$this->error(L('钱包链接失败eth！'));
							exit;
							}

						$qianbao= $EthClient->personal_newAccount('2018@0225#^^Ec1927#~!');//根据用户名生成账户
						if (!$qianbao) {
								$this->error(L('生成钱包地址出错！'));
								}else{
									$rs = M('UserCoin')->where(array('userid' => userid()))->save(array('ethb' => $qianbao));
									// $rs = M('UserCoin')->where(array('userid' => userid()))->save(array('tatcb' => $qianbao));
								}


					}elseif($coin=='eos' || $coin=='hpa'){
						if($user_coin['ethb']){
							$qianbao=$user_coin['ethb'];
							$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($coin.'b' => $qianbao));
						}else{
							$CoinClient = EthCommon($dj_address, $dj_port);
								 $result = $CoinClient->web3_clientVersion();
						if (!$result) {
							$this->error(L('钱包链接失败0！'));
							exit;
							}
						$qianbao= $CoinClient->personal_newAccount('2018@0225#^^Ec1927#~!'); //根据用户名生成账户
						if (!$qianbao) {
								$this->error(L('生成钱包地址出错！'));
								}else{
									$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($coin.'b' => $qianbao));
									// $rs = M('UserCoin')->where(array('userid' => userid()))->save(array('tatcb' => $qianbao));
								}
						}


					}
							
							
							
							
							else{
							$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
							
							$json = $CoinClient->getinfo();
							if (!isset($json['version']) || !$json['version']) {
								$this->error('钱包链接失败！');
							}
							$qianbao_addr = $CoinClient->getaddressesbyaccount(username());

							if (!is_array($qianbao_addr)) {
								$qianbao_ad = $CoinClient->getnewaddress(username());

								if (!$qianbao_ad) {
									$this->error('生成钱包地址出错1！');
								}
								else {
									$qianbao = $qianbao_ad;
								}
							}
							else {
								$qianbao = $qianbao_addr[0];
							}
								if (!$qianbao) {
						$this->error('生成钱包地址出错2！');
					}
					
				

					$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($qbdz => $qianbao));

					if (!$rs) {
						$this->error('钱包地址添加出错3！');
					}
							}

				
				}
			}
			else {
				$qianbao = $user_coin[$coin . 'b'];
				if($user_coin['ethb'] && $user_coin['ethb']!=$user_coin['eosb'])
				{
				M('UserCoin')->where(array('userid' => userid()))->save(array('eosb' => $user_coin['ethb']));
				}
				if($user_coin['ethb'] && $user_coin['ethb']!=$user_coin['hpab'])
				{
				M('UserCoin')->where(array('userid' => userid()))->save(array('hpab' => $user_coin['ethb']));
				}
			}
				}
		}else{
			
				if (!$Coin['zr_jz']) {
					$qianbao = '当前币种禁止转入！';
				}
				else {
					$qianbao = $Coin['zhisucom_coinaddress'];
					
					$moble = M('User')->where(array('id' => userid()))->getField('moble');

					if ($moble) {
						$moble = substr_replace($moble, '****', 3, 4);
					}
					else {
						redirect(U('Home/User/moble'));
						exit();
					}

					$this->assign('moble', $moble);
					
					
					
				}
			
		}
		
		
		
		
		
		

		$this->assign('qianbao', $qianbao);
		$this->assign('enqianbao', $enqianbao);
		$where['userid'] = userid();
		$where['coinname'] = $coin;
		$Moble = M('Myzr');
		$count = $Moble->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Moble->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	
	public function qianbao($coin = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}

		$Coin = M('Coin')->where(array(
			'status' => 1,
			'name'   => array('neq', 'cny')
			))->select();

		if (!$coin) {
			$coin = "";
		}

		$this->assign('xnb', $coin);

		foreach ($Coin as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		
		$where['userid'] = userid();
		$where['status'] = 1;
		if(!empty($coin)){
			$where['coinname'] = $coin;
		}
		
		
		$count = M('UserQianbao')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();

		$userQianbaoList = M('UserQianbao')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		$this->assign('page',$show);
		$this->assign('userQianbaoList', $userQianbaoList);
		$this->assign('prompt_text', D('Text')->get_content('user_qianbao'));
		$this->display();
	}

	public function upqianbao($coin, $name, $addr, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($name, 'a')) {
			$this->error('备注名称格式错误！');
		}

		if (!check($addr, 'dw')) {
			$this->error('钱包地址格式错误！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		if (!M('Coin')->where(array('name' => $coin))->find()) {
			$this->error('币种错误！');
		}

		$userQianbao = M('UserQianbao')->where(array('userid' => userid(), 'coinname' => $coin))->select();

		foreach ($userQianbao as $k => $v) {
			if ($v['name'] == $name) {
				$this->error('请不要使用相同的钱包标识！');
			}

			if ($v['addr'] == $addr) {
				$this->error('钱包地址已存在！');
			}
		}

		if (10 <= count($userQianbao)) {
			$this->error('每个人最多只能添加10个地址！');
		}

		if (M('UserQianbao')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'coinname' => $coin, 'addtime' => time(), 'status' => 1))) {
			$this->success('添加成功！');
		}
		else {
			$this->error('添加失败！');
		}
	}

	public function delqianbao($id, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		if (!check($id, 'd')) {
			$this->error('参数错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		if (!M('UserQianbao')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error('非法访问！');
		}
		else if (M('UserQianbao')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success('删除成功！');
		}
		else {
			$this->error('删除失败！');
		}
	}
	
	
	public function coinoutLog($coin = NULL){
		
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_myzc'));
		
		if (C('coin')[$coin]) {
			$coin = trim($coin);
		}
		else {
			$coin = C('xnb_mr');
		}

		$this->assign('xnb', $coin);
		$Coin = M('Coin')->where(array(
			'status' => 1,
			'name'   => array('neq', 'cny')
			))->select();

		foreach ($Coin as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		
		$where['userid'] = userid();
		$where['coinname'] = $coin;
		$Moble = M('Myzc');
		$count = $Moble->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Moble->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
		
		
	}
	
	//2018-08-14添加的手续费，实时计算用户提币手续费
	public function get_shouxufee($coin,$num){
	    $qbdz = $coin . 'b';
	    $Coin = M('Coin')->where(array(
	        'status' => 1,
	        'name'   => array('eq', $coin)
	    ))->field('zc_user,zc_fee')->find();
	    $fee_user = M('UserCoin')->where(array($qbdz => $Coin['zc_user']))->find();
	    
	    if ($fee_user) {
	        debug('手续费地址: ' . $Coin['zc_user'] . ' 存在,有手续费');
	        $fee = round(($num / 100) * $Coin['zc_fee'], 8);
	         
	        if ($fee < 0) {
	            $this->ajaxReturn(array('code'=>0,'msg'=>'转出手续费设置错误！'));
	        }
	    }
	    else {
	        debug('手续费地址: ' . $Coin['zc_user'] . ' 不存在,无手续费');
	        $fee = 0;
	    }
	    $this->ajaxReturn(array('code'=>1,'msg'=>'成功！','fee'=>$fee));
	}
	//

	public function myzc($coin = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_myzc'));

		if (C('coin')[$coin]) {
			$coin = trim($coin);
		}
		else {
			$coin = C('xnb_mr');
		}

		$this->assign('xnb', $coin);
		$Coin = M('Coin')->where(array(
			'status' => 1,
			'name'   => array('neq', 'cny')
			))->select();

		foreach ($Coin as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin[$coin] = round($user_coin[$coin], 6);
		$this->assign('user_coin', $user_coin);

		if (!$coin_list[$coin]['zc_jz']) {
			$this->assign('zc_jz', '当前币种禁止转出！');
		}
		else {
			$userQianbaoList = M('UserQianbao')->where(array('userid' => userid(), 'status' => 1, 'coinname' => $coin))->order('id desc')->select();
			$this->assign('userQianbaoList', $userQianbaoList);
			$moble = M('User')->where(array('id' => userid()))->getField('moble');

			if ($moble) {
				$moble = substr_replace($moble, '****', 3, 4);
			}
			else {
				redirect(U('Home/User/moble'));
				exit();
			}

			$this->assign('moble', $moble);
		}
		
		

		$where['userid'] = userid();
		$where['coinname'] = $coin;
		$Moble = M('Myzc');
		$count = $Moble->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Moble->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function upmyzc($coin, $num, $addr, $paypassword, $moble_verify)
	{
	    
	    $type=I('type');
		if (!userid()) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($moble_verify, 'd')) {
			$this->error('短信验证码格式错误！');
		}
		
		if($type==1){
			if ($moble_verify != session('real_verify')) {
				$this->error('短信验证码错误！');
				exit;
			}
		}else if($type==2){
			$id=userid();
			$res=check_google($moble_verify,$id);
			if ($res=='error') {
				$this->error('谷歌验证码错误！');
				exit;
			}			
		}
		$num = abs($num);

		if (!check($num, 'currency')) {
			$this->error('数量格式错误！');
		}

		if (!check($addr, 'dw')) {
			$this->error('钱包地址格式错误！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}
		//2018-08-14此处放开了
		/* if (!check($coin, 'xnb')) {
			$this->error('币种格式错误！');
		} */

		$coin = strtolower($coin);
		if (!C('coin')[$coin]) {
			$this->error('币种错误！');
		}
		
		$Coin = M('Coin')->where(array('name' => $coin))->find();

		if (!$Coin) {
			$this->error('币种错误！');
		}
//2018-08-14此处放开了
		$myzc_min = ($Coin['zc_min'] ? abs($Coin['zc_min']) : 0.0001);
		$myzc_max = ($Coin['zc_max'] ? abs($Coin['zc_max']) : 10000000);

		if ($num < $myzc_min) {
			$this->error('转出数量超过系统最小限制！');
		}

		if ($myzc_max < $num) {
			$this->error('转出数量超过系统最大限制！');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if (md5($paypassword) != $user['paypassword']) {
			$this->error('交易密码错误！');
		}

		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();

		//error_log('user_coin='.$user_coin['usdt'].'&&num='.$num,3,'./x.txt');
		if ($user_coin[$coin] < $num) {
			$this->error('可用余额不足');
		}

		$qbdz = $coin . 'b';
		$fee_user = M('UserCoin')->where(array($qbdz => $Coin['zc_user']))->find();

		if ($fee_user) {
			debug('手续费地址: ' . $Coin['zc_user'] . ' 存在,有手续费');
			$fee = round(($num / 100) * $Coin['zc_fee'], 8);
			$mum = round($num - $fee, 8);

			if ($mum < 0) {
				$this->error('转出手续费错误！');
			}

			if ($fee < 0) {
				$this->error('转出手续费设置错误！');
			}
		}
		else {
			debug('手续费地址: ' . $Coin['zc_user'] . ' 不存在,无手续费');
			$fee = 0;
			$mum = $num;
		}
		

		if ($Coin['type'] == 'rgb') {
			debug($Coin, '开始认购币转出');
			$peer = M('UserCoin')->where(array($qbdz => $addr))->find();

			if (!$peer) {
				$this->error('转出认购币地址不存在！');
			}

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables  zhisucom_status write  ,zhisucom_user_coin write  , zhisucom_myzc write  , zhisucom_myzr write , zhisucom_myzc_fee write');
			$rs = array();
			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec($coin, $num);
			
			//账单明细			
				$wallet=M('user_coin')->where("userid=".userid())->getField($coin);
				$mark['curr']=$coin;
				$mid=M('status')->where($mark)->getField('id');	
				parent::addCashhistory(userid(),$mid,7,"转出",-$num,0,$wallet.strtoupper($coin),"转出".strtoupper($coin));				
			//结束
			
			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $peer['userid']))->setInc($coin, $mum);
			
			
			//账单明细			
				$wallet=M('user_coin')->where("userid=".$peer['userid'])->getField($coin);
				parent::addCashhistory($peer['userid'],$mid,6,"转入",$mum,0,$wallet.strtoupper($coin),"转入".strtoupper($coin));				
			//结束
			
			
			if ($fee) {
				if ($mo->table('zhisucom_user_coin')->where(array($qbdz => $Coin['zc_user']))->find()) {
					$rs[] = $mo->table('zhisucom_user_coin')->where(array($qbdz => $Coin['zc_user']))->setInc($coin, $fee);
					debug(array('msg' => '转出收取手续费' . $fee), 'fee');
				}
				else {
					$rs[] = $mo->table('zhisucom_user_coin')->add(array($qbdz => $Coin['zc_user'], $coin => $fee));
					debug(array('msg' => '转出收取手续费' . $fee), 'fee');
				}
			}

			$rs[] = $mo->table('zhisucom_myzc')->add(array('userid' => userid(), 'username' => $addr, 'coinname' => $coin, 'txid' => md5($addr . $user_coin[$coin . 'b'] . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1));
			$rs[] = $mo->table('zhisucom_myzr')->add(array('userid' => $peer['userid'], 'username' => $user_coin[$coin . 'b'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $addr . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1));

			if ($fee_user) {
				$rs[] = $mo->table('zhisucom_myzc_fee')->add(array('userid' => $fee_user['userid'], 'username' => $Coin['zc_user'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $Coin['zc_user'] . time()), 'num' => $num, 'fee' => $fee, 'type' => 1, 'mum' => $mum, 'addtime' => time(), 'status' => 1));
			}

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				session('myzc_verify', null);
				$this->success('转账成功！');
			}
			else {
				$mo->execute('rollback');
				$this->error('转账失败!');
			}
		}

		if ($Coin['type'] == 'qbb') {
			$mo = M();

			if ($mo->table('zhisucom_user_coin')->where(array($qbdz => $addr))->find()) {
				$peer = M('UserCoin')->where(array($qbdz => $addr))->find();

				if (!$peer) {
					$this->error('转出地址不存在！');
				}

				$mo = M();
				$mo->execute('set autocommit=0');
				$mo->execute('lock tables  zhisucom_user_coin write  , zhisucom_myzc write  , zhisucom_myzr write , zhisucom_myzc_fee write');
				$rs = array();
				$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec($coin, $num);
				$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $peer['userid']))->setInc($coin, $mum);

				if ($fee) {
					if ($mo->table('zhisucom_user_coin')->where(array($qbdz => $Coin['zc_user']))->find()) {
						$rs[] = $mo->table('zhisucom_user_coin')->where(array($qbdz => $Coin['zc_user']))->setInc($coin, $fee);
					}
					else {
						$rs[] = $mo->table('zhisucom_user_coin')->add(array($qbdz => $Coin['zc_user'], $coin => $fee));
					}
				}

				$rs[] = $mo->table('zhisucom_myzc')->add(array('userid' => userid(), 'username' => $addr, 'coinname' => $coin, 'txid' => md5($addr . $user_coin[$coin . 'b'] . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1));
				$rs[] = $mo->table('zhisucom_myzr')->add(array('userid' => $peer['userid'], 'username' => $user_coin[$coin . 'b'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $addr . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1));

				if ($fee_user) {
					$rs[] = $mo->table('zhisucom_myzc_fee')->add(array('userid' => $fee_user['userid'], 'username' => $Coin['zc_user'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $Coin['zc_user'] . time()), 'num' => $num, 'fee' => $fee, 'type' => 1, 'mum' => $mum, 'addtime' => time(), 'status' => 1));
				}

				if (check_arr($rs)) {
					$mo->execute('commit');
					$mo->execute('unlock tables');
					session('myzc_verify', null);
					$this->success('转账成功！');
				}
				else {
					$mo->execute('rollback');
					$this->error('转账失败!');
				}
			}
			else {
				$dj_username = $Coin['dj_yh'];
				$dj_password = $Coin['dj_mm'];
				$dj_address = $Coin['dj_zj'];
				$dj_port = $Coin['dj_dk'];
				$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
				$json = $CoinClient->getinfo();

				if (!isset($json['version']) || !$json['version']) {
					$this->error('钱包链接失败！');
				}

				$valid_res = $CoinClient->validateaddress($addr);

				if (!$valid_res['isvalid']) {
					$this->error($addr . '不是一个有效的钱包地址！');
				}

				$auto_status = ($Coin['zc_zd'] && ($num < $Coin['zc_zd']) ? 1 : 0);

				if ($json['balance'] < $num) {
					$this->error('钱包余额不足');
				}

				$mo = M();
				$mo->execute('set autocommit=0');
				$mo->execute('lock tables  zhisucom_user_coin write  , zhisucom_myzc write ,zhisucom_myzr write, zhisucom_myzc_fee write');
				$rs = array();
				$rs[] = $r = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec($coin, $num);
				$rs[] = $aid = $mo->table('zhisucom_myzc')->add(array('userid' => userid(), 'username' => $addr, 'coinname' => $coin, 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => $auto_status));

				if ($fee && $auto_status) {
					$rs[] = $mo->table('zhisucom_myzc_fee')->add(array('userid' => $fee_user['userid'], 'username' => $Coin['zc_user'], 'coinname' => $coin, 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'type' => 2, 'addtime' => time(), 'status' => 1));

					if ($mo->table('zhisucom_user_coin')->where(array($qbdz => $Coin['zc_user']))->find()) {
						$rs[] = $r = $mo->table('zhisucom_user_coin')->where(array($qbdz => $Coin['zc_user']))->setInc($coin, $fee);
						debug(array('res' => $r, 'lastsql' => $mo->table('zhisucom_user_coin')->getLastSql()), '新增费用');
					}
					else {
						$rs[] = $r = $mo->table('zhisucom_user_coin')->add(array($qbdz => $Coin['zc_user'], $coin => $fee));
					}
				}

				if (check_arr($rs)) {
					if ($auto_status) {
						$mo->execute('commit');
						$mo->execute('unlock tables');
						$sendrs = $CoinClient->sendtoaddress($addr, floatval($mum));

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

						if (!$flag) {
							$this->error('钱包服务器转出币失败,请手动转出');
						}
						else {
							$this->success('转出成功!');
						}
					}

					if ($auto_status) {
						$mo->execute('commit');
						$mo->execute('unlock tables');
						session('myzc_verify', null);
						$this->success('转出成功!');
					}
					else {
						$mo->execute('commit');
						$mo->execute('unlock tables');
						session('myzc_verify', null);
						$this->success('转出申请成功,请等待审核！');
					}
				}
				else {
					$mo->execute('rollback');
					$this->error('转出失败!');
				}
			}
		}
	}

	public function mywt()
	{
		
	
		
		if (!userid()) {
			redirect('/#login');
		}
		
		$coin=M('status')->select();
		$this->assign("coin",$coin);
		$day1=time()+24*60*60;
		$day2=time()-24*60*60;
		$this->assign('nowday',date('Y-m-d',$day1));
		$this->assign('yesterday',date('Y-m-d',$day2));		
		$where['uid']= userid();			
		
		if($_REQUEST){
			$coin=I('request.base_curr');
			$type=I('request.curr_type');
			$begin=I('request.start_time');
			$end=I('request.end_time');
			$begin_time=strtotime(I('request.start_time'));
			$end_time=strtotime(I('request.end_time'));
			
			
			$this->assign('cointype',$coin);
			$this->assign("type",$type);	
			
			
			if ($coin!=0) {							
				$where['coin'] = $coin;
			}

			if ($type!=0 ) {
				$where['type'] = $type;
			}
			
			if($begin||$end){
				$where['time']=array(array('elt',$end_time),array('gt',$begin_time));				
			}
			
		}
		
		$story = M('chistory');
		$count = $story->where($where)->count();
		$Page = new \Think\Page($count, 15);
	//	$Page->parameter .= 'type=' . $type . '&status=' . $status . '&market=' . $market . '&';
		$Page->parameter .= 'curr_type=' . $type . '&base_curr=' . $coin . '&start_time='.$begin.'&end_time='.$end.'&';
		$show = $Page->show();
		$list = $story->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		if($end_time>0){
			$this->assign('nowday',date('Y-m-d',$end_time));
			$this->assign('yesterday',date('Y-m-d',$begin_time));	
		}		
		$this->assign('list', $list);
		$this->assign('page', $show);
		
		
		
		$this->display();
	}

	public function mycj($market = NULL, $type = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_mycj'));
		check_server();
		$Coin = M('Coin')->where(array('status' => 1))->select();

		foreach ($Coin as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		$Market = M('Market')->where(array('status' => 1))->select();

		foreach ($Market as $k => $v) {
			$v['xnb'] = explode('_', $v['name'])[0];
			$v['rmb'] = explode('_', $v['name'])[1];
			$market_list[$v['name']] = $v;
		}

		$this->assign('market_list', $market_list);

		if (!$market_list[$market]) {
			$market = $Market[0]['name'];
		}

		if ($type == 1) {
			$where = 'userid=' . userid() . ' && market=\'' . $market . '\'';
		}
		else if ($type == 2) {
			$where = 'peerid=' . userid() . ' && market=\'' . $market . '\'';
		}
		else {
			$where = '((userid=' . userid() . ') || (peerid=' . userid() . ')) && market=\'' . $market . '\'';
		}

		$this->assign('market', $market);
		$this->assign('type', $type);
		$this->assign('userid', userid());
		$Moble = M('TradeLog');
		$count = $Moble->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$Page->parameter .= 'type=' . $type . '&market=' . $market . '&';
		$show = $Page->show();
		$list = $Moble->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['num'] = $v['num'] * 1;
			$list[$k]['price'] = $v['price'] * 1;
			$list[$k]['mum'] = $v['mum'] * 1;
			$list[$k]['fee_buy'] = $v['fee_buy'] * 1;
			$list[$k]['fee_sell'] = $v['fee_sell'] * 1;
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function mytj()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_mytj'));
		check_server();
		$user = M('User')->where(array('id' => userid()))->find();

		if (!$user['invit']) {
			for (; true; ) {
				$tradeno = tradenoa();

				if (!M('User')->where(array('invit' => $tradeno))->find()) {
					break;
				}
			}

			M('User')->where(array('id' => userid()))->save(array('invit' => $tradeno));
			$user = M('User')->where(array('id' => userid()))->find();
		}
		//查找推荐人
		$username=M("User")->where("invit_1=".userid())->field('addtime,username')->order('addtime DESC')->select();
		$sum=M("User")->where("invit_1=".userid())->field('count(*) as a')->find();
		//var_dump($username);die;
		//最近30天佣金记录
		$nowday=time()-24*60*60*30;
		$where['userid']=userid();
		$where['addtime']=array('gt',$nowday);
		$yongjin=M('invit')->where($where)->order("addtime desc")->select();
		
		foreach($yongjin as $key=>$val){				
			$yongjin[$key]['username']=M('User')->where("id=".$val['invit'])->getField("username");								
		}
		//推广链接
		$link= PC_URL."/Login/register/?invit=".$user['invit'];
		//推广网站
		$link2=PC_URL."/?invit=".$user['invit'];
		
		//推广二维码没有生成去生成
		/*if(!$user['extend_qrcode']){
		    $qrcoder_imgurl = $this->getqrcode($user['id']);
		    if($qrcoder_imgurl){
		        M('user')->where(array('id' => $user['id']))->setField('extend_qrcode', $qrcoder_imgurl);
		    }
		}*/
		//
		
		
		$this->assign('link',$link);
		$this->assign('link2',$link2);
		$this->assign('yongjin',$yongjin);
		$this->assign('invituser',$username);
		$this->assign('sum',$sum['a']);		
		$this->assign('user', $user);
		$this->display();
	}

	public function mywd()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_mywd'));
		check_server();
		$where['invit_1'] = userid();
		$Model = M('User');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id asc')->field('id,username,moble,addtime,invit_1')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		
		foreach ($list as $k => $v) {
			$list[$k]['invits'] = M('User')->where(array('invit_1' => $v['id']))->order('id asc')->field('id,username,moble,addtime,invit_1')->select();
			$list[$k]['invitss'] = count($list[$k]['invits']);

			foreach ($list[$k]['invits'] as $kk => $vv) {
				$list[$k]['invits'][$kk]['invits'] = M('User')->where(array('invit_1' => $vv['id']))->order('id asc')->field('id,username,moble,addtime,invit_1')->select();
				$list[$k]['invits'][$kk]['invitss'] = count($list[$k]['invits'][$kk]['invits']);
			}
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function myjp()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_myjp'));
		check_server();
		$where['userid'] = userid();
		$Model = M('Invit');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['invit'] = M('User')->where(array('id' => $v['invit']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	public function myaward()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('finance_myaward'));
		//check_server();
		$where['userid'] = userid();
		$Model = M('UserAward');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
  //邮箱绑定发送邮件
  public function send_email(){
    
    //$a = 'wanghe379@163.com';
    $sender = $_POST['email'];
    $where['email'] = $sender;
    $user = M('user')->where($where)->find();
    	 $code = rand(111111,999999);
    	 session('email_code',$code);
    	 $title = "邮箱绑定";
    	 $content = "您正在进行邮箱绑定操作，验证码是".$code;
  		 sendMail($sender,$title,$content);
      $this->success('发送成功');
   
  }
  //成交记录
  public function mywc()
  {
    	
    if (!userid()) {
			redirect('/Login/register');
		}
    /**/
	$id=userid();
    $Moble=M('trade_log');
	$where="status=1 and ( userid=".$id." or peerid=".$id.")";
    $count = $Moble->where($where)->count();
    $Page = new \Think\Page($count, 15);
    $show = $Page->show();
    $list = $Moble->where($where)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
    
	foreach($list as $k=>$v){
		$n1 = explode('_', $v['market'])[0];
		$n2 = explode('_', $v['market'])[1];
		$list[$k]['ncoin'] = $n1.'/'.$n2;
		$list[$k]['coin'] = M('coin')->where(['name'=>$n1])->field('name,title,img')->find();	
		
	}

	$this->assign('uid',$id);
    $this->assign('list', $list);
	$this->assign('page', $show);
  	$this->display();
  }
  
  
  //充值记录
  public function rechargeRecord(){
	  $this->display();
  }
 
	

}

?>