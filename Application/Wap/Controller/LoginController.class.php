<?php
namespace Wap\Controller;

class LoginController extends HomeController
{
	public function register()
	{
		$this->display();
	}

	public function webreg()
	{
		$this->display();
	}
	
	//jeff注册
	public function upregister()
	{
		$type = in_array(I('post.type'),array('1','2')) ? I('post.type') : 1;
		$username = I('post.username');
		$code = I('post.code');
		$password = I('post.password');
		$repassword = I('post.repassword');
		$invit = I('post.invit') ? I('post.invit') : '';
		
		/*$type = 2;
		$username="hehemail@qq.com";
		$code = '667290';
		$password = 'fff000';
		$repassword = 'fff000';*/
		
		if($type == 1)
		{
			if (!check($username, 'moble')) 
			{
    	        $this->error('手机号格式错误！');
    	    }
			$code_log = M('Code_log')->where(array('username'=>$username,'type'=>1))->order('id desc')->find();
			if($code_log)
			{
				if($code_log['addtime'] + 300 < time())
				{
					$this->error('验证码已过期');
				}
				if($code_log['code'] != $code)
				{
					$this->error('验证码错误');
				}
			}
			$where = "username=".$username." or moble=".$username;
			$user = M('User')->where($where)->field('id')->find();
			if($user)
			{
				$this->error('用户名已存在');
				//$this->ajaxReturn(array('code'=>'404','info'=>'用户名已存在'));
			}
			$moble = $username;
			$email = '';
		}
		elseif($type == 2)
		{
			if(!check($username,'eamil'))
			{
				$this->error('邮箱格式错误！');
			}
			$code_log = M('Code_log')->where(array('username'=>$username,'type'=>2))->order('id desc')->find();
			if($code_log)
			{
				if($code_log['addtime'] + 300 < time())
				{
					$this->error('验证码已过期');
				}
				if($code_log['code'] != $code)
				{
					$this->error('验证码错误');
				}
			}
			$where = "username=".$username." or email=".$username;
			$user = M('User')->where($where)->field('id')->find();
			if($user)
			{
				$this->error('用户名已存在');
				//$this->ajaxReturn(array('code'=>'404','info'=>'用户名已存在'));
			}
			$moble = '';
			$email = $username;
		}
		
		
		if (!check($password, 'password')) 
		{
			$this->error('登录密码格式错误！');
			//$this->ajaxReturn(array('code'=>'1019'));
		}

		if ($password != $repassword)
		{
			$this->error('确认登录密码错误！');
			//$this->ajaxReturn(array('code'=>'1025'));
		}
		
		if($invit){
		    //有推荐码时
		    $invituser = M('User')->where(array('invit' => $invit))->field('id,invit_1,invit_2,addip,addtime')->find();
		    
		    if (!$invituser) {
		        $invituser = M('User')->where(array('id' => $invit))->field('id,invit_1,invit_2,addip,addtime')->find();
		    }
		    
		    if (!$invituser) {
		        $invituser = M('User')->where(array('username' => $invit))->field('id,invit_1,invit_2,addip,addtime')->find();
		    }
		    
		    if (!$invituser) {
		        $invituser = M('User')->where(array('moble' => $invit))->field('id,invit_1,invit_2,addip,addtime')->find();
		    }
		    
		    //推荐用户跟本人账户IP地址相同的推荐无效，新注册1天内的帐号推荐别人无效
		    if ($invituser && time()-$invituser['addtime']>24*3600) {
		        $invit_1 = $invituser['id'];
		        $invit_2 = $invituser['invit_1'];
		        $invit_3 = $invituser['invit_2'];
		    }
		    else {
		        $invit_1 = 0;
		        $invit_2 = 0;
		        $invit_3 = 0;
		    }
		}else {
	        $invit_1 = 0;
	        $invit_2 = 0;
	        $invit_3 = 0;
	    }
		
		//生成推荐码
		for (; true; ) {
		    $tradeno = tradenoa();
		
		    if (!M('User')->where(array('invit' => $tradeno))->field('id')->find()) {
		        break;
		    }
		}
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_user write , zhisucom_user_coin write , zhisucom_lever_coin');
		$rs = array();
		$login_token = md5($username.$password);//app端平台用到
		$rs[] = $mo->table('zhisucom_user')->add(array('username' => $username,'moble' => $moble,'mobletime' => time(), 'password' => md5($password), 'invit' => $tradeno, 'tpwdsetting' => 3, 'invit_1' => $invit_1, 'invit_2' => $invit_2, 'invit_3' => $invit_3, 'addip' => get_client_ip(), 'addr' => get_city_ip(), 'addtime' => time(), 'status' => 1,'login_token'=>$login_token,'email'=>$email));
		$rs[] = $mo->table('zhisucom_user_coin')->add(array('userid' => $rs[0]));
		$coins = M('coin')->where(array('status'=>1))->field('name')->select();
		foreach($coins as $k=>$v){
			$datas['userid'] = $rs[0];
			$datas['name_en'] = $v['name'];
			$datas['update_time'] = time();
			$rs[] = $mo->table('zhisucom_lever_coin')->add($datas);
		}
		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			session('reguserId', $rs[0]);
			$this->success('注册成功！');
			$this->invit_jtc($invit);
			//$this->ajaxReturn(array('code'=>'1028'));
		}
		else {
			$mo->execute('rollback');
			 $this->error('注册失败！');
			//$this->ajaxReturn(array('code'=>'1029'));
		}
		
		
	}
	
	//注册验证码
	/**
	  *username,用户名
	  *type,1是手机号，2是邮箱
	  */
	public function sms_code()
	{
		$username = I('post.username');
		$type = I('post.type');
		$use_type = I('post.use_type')?I('post.use_type'):1;#默认是1,
		//$username = '15378733931';
		//$type = 1;
		if($type == 1)
		{
			if (!check($username, 'moble')) 
			{
				$this->error('手机号格式错误！');
				//$this->ajaxReturn(array('code'=>'1026','info'=>'手机号格式错误！'));
			}
		}
		elseif($type == 2)
		{
			if(!check($username,'eamil'))
			{
				$this->error('邮箱格式错误！');
			}
		}
		
		if($use_type == 1)
		{
			$user = M('User')->where(array('username'=>$username))->field('id')->find();
			if($user)
			{
				$this->error('用户名已存在');
				//$this->ajaxReturn(array('code'=>'404','info'=>'用户名已存在'));
			}
		}
		
		$code_log = M('Code_log')->where(array('username'=>$username,'type'=>$type))->order('id desc')->find();
		if($code_log)
		{
			if($code_log['addtime'] + 60 > time())
			{
				$this->error('请勿频繁发送');
				//$this->ajaxReturn(array('code'=>'404','info'=>'请勿频繁发送'));
			}
		}
		
		$code = rand(111111, 999999);
		$content = "您的验证码是".$code."，请勿泄漏，谨防被骗。";
		if($type == 1)
		{
			$res = send_mobile($username,$content);
		}
		elseif($type == 2)
		{
			$title = 'JEFF验证码';
			$res = sendMail($username,$title,$content);
		}
		
		if($res)
		{
			$codes = M('Code_log')->add(array('username'=>$username,'code'=>$code,'addtime'=>time(),'type'=>$type));
			
			if($codes)
			{
				$this->success('发送成功',$code);
				//$this->success('1045',$code);
			}
			else
			{
				$this->error('发送失败');
				//$this->error('1046');
			}
		}
		else
		{
			$this->error('发送失败');
			//$this->error('1046');
		}
		
	}
	
	//邮箱验证码(备用)
	public function sms_email()
	{
		$username = I('post.username');
		//$username = 'hehemail@qq.com';
		if(!check($username,'eamil'))
		{
			$this->error('邮箱格式错误！');
		}
		$user = M('User')->where(array('username'=>$username))->field('id')->find();
		if($user)
		{
			$this->ajaxReturn(array('code'=>'404','info'=>'用户名已存在'));
		}
		$code_log = M('Code_log')->where(array('username'=>$username,'type'=>2))->order('id desc')->find();
		if($code_log)
		{
			if($code_log['addtime'] + 60 > time())
			{
				$this->ajaxReturn(array('code'=>'404','info'=>'请勿频繁发送'));
			}
		}
		$code = rand(111111, 999999);
		$title = 'JEFF验证码';
		$content = "您的验证码是".$code."，请勿泄漏，谨防被骗。";
		$res = sendMail($username,$title,$content);
		if($res)
		{
			$codes = M('Code_log')->add(array('username'=>$username,'code'=>$code,'addtime'=>time(),'type'=>2));
			
			if($codes)
			{
				$this->success('1045',$code);
			}
			else
			{
				$this->error('1046');
			}
		}
		else
		{
			$this->error('1046');
		}
	}
	
	

	//邀请送JEFF
	public function invit_jtc($invit){
		
		$config = M('Config');
		$user = M('User');
		$usercoin = M('user_coin');
		$yaoqing = $user->where(array('invit'=>$invit))->field('id')->find();
		if($yaoqing){
			$con_invit = $config->where('id=1')->getField('invit_num');
			if($con_invit > 0){
				$usernum = $usercoin->where(array('userid'=>$yaoqing['id']))->field('jeff,jeffd')->find();
				$info = $usercoin->where(array('userid'=>$yaoqing['id']))->setInc('jeff',$con_invit);
				
				if($info){
					$data['userid'] = $yaoqing['id'];
					$data['coinname'] = 'JEFF';
					$data['num_a'] = $usernum['jeff'];
					$data['num_b'] = $usernum['jeffd'];
					$data['num'] = $usernum['jeff']+$usernum['jeffd'];
					$data['fee'] = $con_invit;
					$data['type'] = '104';
					$data['name'] = 'invit';
					$data['nameid'] = '406';
					$data['remark'] = '邀请送JTC';
					$data['mum_a'] = $usernum['jeff']+$con_invit;
					$data['mum_b'] = $usernum['jeffd'];
					$data['mum'] = $usernum['jeff']+$con_invit+$usernum['jeffd'];
					$data['addtime'] = time();
					$data['status'] = '1';

					$result = M('Finance')->add($data);
				}
			}
		}
		
	}
	//注册认证
	public function identity(){
		// dump($_SESSION);die;
		$this->display();
	}
	//处理注册认证信息
	public function identityac(){
		$moble = $_POST['moble'];
		$truename = $_POST['truename'];
		$moble_verify = $_POST['smscode'];
		if (M('User')->where(array('moble' => $moble))->find()) {
			$this->error('手机号码已存在！');
		}
		if ($moble_verify != session('real_verify')) {
			$this->error('短信验证码错误！');
		}
		$data['moble'] = $moble;
		$data['truename'] = $truename;
		$info = M('User')->where('id = '.$_SESSION['reguserId'])->save($data);
		if($info){
			$userinfo = M("User")->where('id = '.$_SESSION['reguserId'])->find();
			// $this->submit(0,$userinfo['password'],$userinfo['moble']);
			$username = $userinfo['moble'];
			if(M_ONLY==0){
			if (check($username, 'email')) {
				$user = M('User')->where(array('email' => $username))->find();
				$remark = '通过邮箱登录';
			}

			if (!$user && check($username, 'moble')) {
				$user = M('User')->where(array('moble' => $username))->find();
				$remark = '通过手机号登录';
			}

			if (!$user) {
				$user = M('User')->where(array('username' => $username))->find();
				$remark = '通过用户名登录';
			}
		}else{
			if (check($moble, 'moble')) {
				$user = M('User')->where(array('moble' => $moble))->find();
				$remark = '通过手机号登录';
			}
		}

		/*if (!$user) {
			$this->error('用户不存在！');
		}

		if (!check($password, 'password')) {
			$this->error('登录密码格式错误！');
		}

		if (md5($password) != $user['password']) {
			$this->error('登录密码错误！');
		}

		if ($user['status'] != 1) {
			$this->error('你的账号已冻结请联系管理员！');
		}*/

		
		$ip = get_client_ip();
		$logintime = time();
		$token_user = md5($user['id'].$logintime);
		session('token_user' , $token_user);
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_user write , zhisucom_user_log write ');
		$rs = array();
		$rs[] = $mo->table('zhisucom_user')->where(array('id' => $user['id']))->setInc('logins', 1);
		
		$rs[] = $mo->table('zhisucom_user')->where(array('id' => $user['id']))->save(array('token'=>$token_user));
		
		$rs[] = $mo->table('zhisucom_user_log')->add(array('userid' => $user['id'], 'type' => '登录', 'remark' => $remark, 'addtime' => $logintime, 'addip' => $ip, 'addr' => get_city_ip(), 'status' => 1));

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');

				if (!$user['invit']) {
					for (; true; ) {
						$tradeno = tradenoa();

						if (!M('User')->where(array('invit' => $tradeno))->find()) {
							break;
						}
					}

					M('User')->where(array('id' => $user['id']))->setField('invit', $tradeno);
				}

				session('userId', $user['id']);
				session('userName', $user['username']);

				if (!$user['paypassword']) {
					session('regpaypassword', $rs[0]);
					session('reguserId', $user['id']);
				}

				if (!$user['truename']) {
					session('regtruename', $rs[0]);
					session('reguserId', $user['id']);
				}
				session('zhisucom_already',0);
				$this->success('注册成功！');
			}
		}else{
			$this->error('注册失败！');
		}
	}
	
	
	public function check_moble($moble=0){
		
		if (!check($moble, 'moble')) {
			$this->error('手机号码格式错误！');
		}
		
		if (M('User')->where(array('moble' => $moble))->find()) {
			$this->error('手机号码已存在！');
		}
		
		$this->success('');
		
	}
	
	
	public function check_pwdmoble($moble=0){
		
		if (!check($moble, 'moble')) {
			$this->error('手机号码格式错误！');
		}
		
		if (!M('User')->where(array('moble' => $moble))->find()) {
			$this->error('手机号码不存在！');
		}
		
		$this->success('');
		
	}
	
	
	
	
	
	public function real()
	{
		// var_dump($_POST);die;
		$moble = $_POST['mobile'];
		// $moble = '13393733825';
		// $_POST['bj']=1;
		/*if (!check_verify(strtoupper($verify))) {
			$this->error('图形验证码错误!');
		}*/

		//error_log('mobile='.$moble.'&&bj='.$_POST['bj'],3,'./x.txt');
		if (!check($moble, 'moble')) {
			$this->error('1026');
		}
		
		if($_POST['bj']==1){
		    //注册
			if (M('User')->where(array('moble' => $moble))->field('id')->find()) {
				$this->error('1027');
			}
		}else{
		    //登陆状态中
		    $rel = M('User')->where(array('moble' => $moble))->field('id')->find();
		    if (!$rel) {
		        $this->error('1018');
		    }
		}
		
// echo 111;die;
		$code = rand(111111, 999999);
		session('real_verify', $code);
		$content = "您的验证码是".$code."，请勿泄漏，谨防被骗。";

		if (send_mobile($moble, $content)) {
			if(MOBILE_CODE ==0 ){
				$this->success('目前是演示模式,请输入'.$code,$code);
			}else{
				$this->success('1045',$code);
			}
		}
		else {
			$this->error('1046');
		}
	}
	
	
	
	
	public function register2()
	{
		if (!session('reguserId')) {
			redirect('/#login');
		}
		$this->display();
	}
	
	
	public function paypassword(){
		if (!session('reguserId')) {
			redirect('/#login');
		}
		$this->display();
	}
	
	

	public function upregister2($paypassword, $repaypassword)
	{
		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		if ($paypassword != $repaypassword) {
			$this->error('确认密码错误！');
		}

		if (!session('reguserId')) {
			$this->error('非法访问！');
		}

		if (M('User')->where(array('id' => session('reguserId'), 'password' => md5($paypassword)))->find()) {
			$this->error('交易密码不能和登录密码一样！');
		}

		if (M('User')->where(array('id' => session('reguserId')))->save(array('paypassword' => md5($paypassword)))) {
			//$this->success('成功！');
			return true;
		}
		else {
			$this->error('失败！');
		}
	}

	public function register3()
	{
		if (!session('reguserId')) {
			redirect('/#login');
		}
		$this->display();
	}
	
	public function truename()
	{
		if (!session('reguserId')) {
			redirect('/#login');
		}
		$this->display();
	}
	
	

	// public function upregister3($truename, $idcard)
	// {
		// if (!check($truename, 'truename')) {
			// $this->error('真实姓名格式错误！');
		// }

		// if (!check($idcard, 'idcard')) {
			// $this->error('身份证号格式错误！');
		// }

		// if (!$_POST['ltoken']) {
			// $this->error('非法访问！');
		// }
		// $info = M('User')->where(array('login_token' => $_POST['ltoken']))->save(array('truename' => $truename, 'idcard' => $idcard))

		// if ($info) {
		// //if (M('User')->where(array('id' => session('reguserId')))->save(array('truename' => $truename, 'idcard' => $idcard))) {
			// $this->success('认证成功！');
		// }
		// else {
			// $this->error('认证失败！');
		// }
	// }

	public function register4()
	{
		
		if (!session('reguserId')) {
			redirect('/#login');
		}
		
		$user = M('User')->where(array('id' => session('reguserId')))->find();
		
		
		if(!$user){
			$this->error('请先注册');
		}
		if($user['regaward']==0){
			if(C('reg_award')==1 && C('reg_award_num')>0){
				M('UserCoin')->where(array('userid' => session('reguserId')))->setInc(C('reg_award_coin'),C('reg_award_num'));
				M('User')->where(array('id' => session('reguserId')))->save(array('regaward'=>1));
			}
		}	

		session('userId', $user['id']);
		session('userName', $user['username']);
		$this->assign('user', $user);
		$this->display();
	}
	
	
	public function info()
	{
		
		if (!session('reguserId')) {
			redirect('/#login');
		}
		
		$user = M('User')->where(array('id' => session('reguserId')))->find();
		
		
		if(!$user){
			$this->error('请先注册');
		}
		if($user['regaward']==0){
			if(C('reg_award')==1 && C('reg_award_num')>0){
				M('UserCoin')->where(array('id' => session('reguserId')))->setInc(C('reg_award_coin'),C('reg_award_num'));
				M('User')->where(array('id' => session('reguserId')))->save(array('regaward'=>1));
			}
		}	

		session('userId', $user['id']);
		session('userName', $user['username']);
		$this->assign('user', $user);
		$this->display();
	}
	
	
	
	
	
	
	

	public function chkUser($username)
	{
		if (!check($username, 'username')) {
			$this->error('用户名格式错误！');
		}

		if (M('User')->where(array('username' => $username))->find()) {
			$this->error('用户名已存在');
		}

		$this->success('');
	}
	public function code()
	{
        ob_clean();
		$config['useNoise'] = false;
		$config['length'] = 4;
		$config['codeSet'] = '0123456789';
		$verify = new \Think\Verify($config);
		$verify->entry(1);
	}
	
	
	/**
	*username，用户名
	*password，密码
	*type,1是手机号，2是邮箱
	**/
	public function submit_login()
	{
		$username = trim(I('post.username'));
		$password = trim(I('post.password'));
		$type = I('post.type');
		
		//$username="15378733931";
		//$password = "fff000";
		//$type = '1';
		$code = trim(I('post.code'))>0 ? trim(I('post.code')) : 1;
		$remark = '通过用户名登录';
		
		$user = M('User')->where(array('username'=>$username))->find();
		if(!$user)
		{
			$this->error('用户不存在');
			//$this->ajaxReturn(array('code'=>'1018'));
		}
		
		if($type == 1)
		{
			if (!check($username, 'moble')) 
			{
				$this->error('手机号格式错误！');
    	        //$this->ajaxReturn(array('code'=>'1026'));
    	    }
		}
		elseif($type == 2)
		{
			if(!check($username,'eamil'))
			{
				$this->error('邮箱格式错误！');
			}
		}
		if (!check($password, 'password')) {
			$this->error('登录密码格式错误！');
			//$this->ajaxReturn(array('code'=>'1019'));
		}

		if (md5($password) != $user['password']) {
			$this->error('登录密码错误！');
			//$this->ajaxReturn(array('code'=>'1019'));
		}

		if ($user['status'] != 1) {
			$this->error('你的账号已冻结请联系管理员！');
			//$this->ajaxReturn(array('code'=>'1021'));
		}
		
		if($code != 1)
		{
			$code_log = M('Code_log')->where(array('username'=>$username,'type'=>$type))->order('id desc')->find();
			if($code_log)
			{
				if($code_log['addtime'] + 300 < time())
				{
					$this->error('验证码已过期');
				}
				if($code_log['code'] != $code)
				{
					$this->error('验证码错误');
				}
			}
			else
			{
				$this->error('请获取验证码');
			}
		}
		
		$ip = get_client_ip();
		$logintime = time();
		$token_user = md5($user['id'].$logintime);
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_user write , zhisucom_user_log write ');
		$rs = array();
		$rs[] = $mo->table('zhisucom_user')->where(array('id' => $user['id']))->setInc('logins', 1);
		
		$rs[] = $mo->table('zhisucom_user')->where(array('id' => $user['id']))->save(array('token'=>$token_user));
		
		$rs[] = $mo->table('zhisucom_user_log')->add(array('userid' => $user['id'], 'type' => '登录', 'remark' => $remark, 'addtime' => $logintime, 'addip' => $ip, 'addr' => get_city_ip(), 'status' => 1));
		
		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');

			if (!$user['invit']) {
				for (; true; ) {
					$tradeno = tradenoa();

					if (!M('User')->where(array('invit' => $tradeno))->find()) {
						break;
					}
				}

				M('User')->where(array('id' => $user['id']))->setField('invit', $tradeno);
			}

			$vip_return = get_vip_fee($user['id']);
			
			
			 $this->ajaxReturn(array(
			     'code'  =>  200,
			     'result'    =>  '登录成功！',
			     'body'    =>  array(
			         'ltoken'    =>  $user['login_token'],
			         'username'  =>  $user['username'],
			         'header_pic'  =>  $user['header_pic']?'/Upload/app/header/'.$user['header_pic']:'/Upload/app/5b0e17532b4b4.jpg',
			         // 'vip'  =>  $vip_return[0],
			         'id'=>$user['id'],
			         'truename'=>$user['truename']
			         )
			 ));
		}
		else {
			session('zhisucom_already',0);
			$mo->execute('rollback');
			$this->error('登录失败！');
			//$this->ajaxReturn(array('code'=>'1023'));
		}
	}
	public function submit($username="", $password="",$moble="", $verify = NULL)
	{
		/* if (C('login_verify')) {
			if (!check_verify(strtoupper($verify))) {
				$this->error('图形验证码错误!');
			}
		} */
		
	    $username = trim(I('post.moble'));
	    $moble = $username;
	    //$password = trim(I('post.password'));
	    //error_log('username='.$username.'&password='.$password,3,'./acb.txt');
		if(M_ONLY==0){
			if (check($username, 'email')) {
				$user = M('User')->where(array('email' => $username))->find();
				$remark = '通过邮箱登录';
			}

			if (!$user && check($username, 'moble')) {
				$user = M('User')->where(array('moble' => $username))->find();
				$remark = '通过手机号登录';
			}

			if (!$user) {
				$user = M('User')->where(array('username' => $username))->find();
				$remark = '通过用户名登录';
			}
		}else{
			if (check($moble, 'moble')) {
				$user = M('User')->where(array('moble' => $moble))->find();
				$remark = '通过手机号登录';
			}
		}

		if (!$user) {
			// $this->error('用户不存在！');
			$this->ajaxReturn(array('code'=>'1018'));
		}

		if (!check($password, 'password')) {
			// $this->error('登录密码格式错误！');
			$this->ajaxReturn(array('code'=>'1019'));
		}

		if (md5($password) != $user['password']) {
			// $this->error('登录密码错误！');
			$this->ajaxReturn(array('code'=>'1019'));
		}

		if ($user['status'] != 1) {
			// $this->error('你的账号已冻结请联系管理员！');
			$this->ajaxReturn(array('code'=>'1021'));
		}

		
		$ip = get_client_ip();
		$logintime = time();
		$token_user = md5($user['id'].$logintime);
		session('token_user_app' , $token_user);
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_user write , zhisucom_user_log write ');
		$rs = array();
		$rs[] = $mo->table('zhisucom_user')->where(array('id' => $user['id']))->setInc('logins', 1);
		
		$rs[] = $mo->table('zhisucom_user')->where(array('id' => $user['id']))->save(array('token'=>$token_user));
		
		$rs[] = $mo->table('zhisucom_user_log')->add(array('userid' => $user['id'], 'type' => '登录', 'remark' => $remark, 'addtime' => $logintime, 'addip' => $ip, 'addr' => get_city_ip(), 'status' => 1));

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');

			if (!$user['invit']) {
				for (; true; ) {
					$tradeno = tradenoa();

					if (!M('User')->where(array('invit' => $tradeno))->find()) {
						break;
					}
				}

				M('User')->where(array('id' => $user['id']))->setField('invit', $tradeno);
			}

			session('userId', $user['id']);
			session('userName', $user['username']);

			if (!$user['paypassword']) {
				session('regpaypassword', $rs[0]);
				session('reguserId', $user['id']);
			}

			if (!$user['truename']) {
				session('regtruename', $rs[0]);
				session('reguserId', $user['id']);
			}
			session('zhisucom_already',0);
			// echo "<script>location.href='__URL__/login1';</script>";
			//$this->success('登录成功！','/Login/login1?phone='.$moble);
			 //$this->success('登录成功！','/Finance/index');
			$vip_return = get_vip_fee($user['id']);
			$contents = "您的账户在APP端登录成功，如不是您本人操作，建议您立即修改密码。";
			//send_moble($moble, $contents);
			 $this->ajaxReturn(array(
			     'code'  =>  1022,
			     'result'    =>  '登录成功！',
			     'body'    =>  array(
			         'ltoken'    =>  $user['login_token'],
			         'username'  =>  $user['username'],
			         'header_pic'  =>  $user['header_pic']?'/Upload/app/header/'.$user['header_pic']:'/Upload/app/5b0e17532b4b4.jpg',
			         // 'vip'  =>  $vip_return[0],
			         'id'=>$user['id']
			         )
			 ));
		}
		else {
			session('zhisucom_already',0);
			$mo->execute('rollback');
			// $this->error('登录失败！');
			$this->ajaxReturn(array('code'=>'1023'));
		}
	}

	public function login1(){
		// dump($_SESSION);
		$this->display();
	}

	public function loginac(){
		// var_dump($_POST);die;
		$sendcode = $_POST['code'];
		if($sendcode == $_SESSION['real_verify']){
			$this->success('登录成功！');
		}else{
			$this->error('登录失败！');
		}
	}

	public function loginout()
	{
		session(null);
		redirect('/');
	}

	public function findpwd()
	{
		if (IS_POST) {
			$input = I('post.');
			if(M_ONLY==0){
				if (!check_verify(strtoupper($input['verify']))) {
					$this->error('图形验证码错误!');
				}

				if (!check($input['username'], 'username')) {
					$this->error('用户名格式错误！');
				}

				if (!check($input['moble'], 'moble')) {
					$this->error('手机号码格式错误！');
				}

				if (!check($input['moble_verify'], 'd')) {
					$this->error('短信验证码格式错误！');
				}

				if ($input['moble_verify'] != session('findpwd_verify')) {
					$this->error('短信验证码错误！');
				}

				$user = M('User')->where(array('username' => $input['username']))->find();
				
				
				
				if (!$user) {
					$this->error('用户名不存在！');
				}

				if ($user['moble'] != $input['moble']) {
					$this->error('用户名或手机号码错误！');
				}

				if (!check($input['password'], 'password')) {
					$this->error('新登录密码格式错误！');
				}
				

				if ($input['password'] != $input['repassword']) {
					$this->error('确认密码错误！');
				}


				$mo = M();
				$mo->execute('set autocommit=0');
				$mo->execute('lock tables zhisucom_user write , zhisucom_user_log write ');
				$rs = array();
				$rs[] = $mo->table('zhisucom_user')->where(array('id' => $user['id']))->save(array('password' => md5($input['password'])));

				if (check_arr($rs)) {
					$mo->execute('commit');
					$mo->execute('unlock tables');
					$this->success('修改成功');
				}
				else {
					$mo->execute('rollback');
					$this->error('修改失败');
				}
			
			}else{
				

				if (!check($input['moble'], 'moble')) {
					$this->error('手机号码格式错误！');
				}

				$user = M('User')->where(array('moble' => $input['moble']))->find();
				
				if(!$user){
					$this->error('不存在该手机号码');
				}
				
				if (!check($input['moble_verify'], 'd')) {
					$this->error('短信验证码格式错误！');
				}

				if ($input['moble_verify'] != session('findpwd_verify')) {
					$this->error('短信验证码错误！');
				}
				session("findpwdmoble",$user['moble']);
				$this->success('验证成功');
			}
			
		}
		else {
			$this->display();
		}
	}
	
	
	public function findpwdconfirm(){
		
		if(empty(session('findpwdmoble'))){
			session(null);
			redirect('/');
		}
		
		$this->display();
	}
	
	public function password_up($password=""){
		
		
		if(empty(session('findpwdmoble'))){
			$this->error('请返回第一步重新操作！');
		}
		
		if (!check($password, 'password')) {
			$this->error('新登录密码格式错误！');
		}
		$user = M('User')->where(array('moble' => session('findpwdmoble')))->find();
		
		if(!$user){
			$this->error('不存在该手机号码');
		}
		
		if($user['paypassword']==md5($password)){
			$this->error("登录密码不能和交易密码一样");
		}
		
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_user write , zhisucom_user_log write ');
		$rs = array();
		$rs[] = $mo->table('zhisucom_user')->where(array('moble' => $user['moble']))->save(array('password' => md5($password)));

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('操作成功');
		}
		else {
			$mo->execute('rollback');
			$this->error('操作失败');
		}
		
	}
	
	public function findpwdinfo(){
		
		if(empty(session('findpwdmoble'))){
			session(null);
			redirect('/');
		}
		session(null);
		$this->display();
	}
	
	
	public function findpaypwd()
	{
		if (IS_POST) {
			$input = I('post.');

			if (!check($input['username'], 'username')) {
				$this->error('用户名格式错误！');
			}

			if (!check($input['moble'], 'moble')) {
				$this->error('手机号码格式错误！');
			}

			if (!check($input['moble_verify'], 'd')) {
				$this->error('短信验证码格式错误！');
			}

			if ($input['moble_verify'] != session('findpaypwd_verify')) {
				$this->error('短信验证码错误！');
			}

			$user = M('User')->where(array('username' => $input['username']))->find();

			if (!$user) {
				$this->error('用户名不存在！');
			}

			if ($user['moble'] != $input['moble']) {
				$this->error('用户名或手机号码错误！');
			}

			if (!check($input['password'], 'password')) {
				$this->error('新交易密码格式错误！');
			}

			if ($input['password'] != $input['repassword']) {
				$this->error('确认密码错误！');
			}

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables zhisucom_user write , zhisucom_user_log write ');
			$rs = array();
			$rs[] = $mo->table('zhisucom_user')->where(array('id' => $user['id']))->save(array('paypassword' => md5($input['password'])));

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$this->success('修改成功');
			}
			else {
				$mo->execute('rollback');
				$this->error('修改失败' . $mo->table('zhisucom_user')->getLastSql());
			}
		}
		else {
			$this->display();
		}
	}
   //忘记密码
  public function forget_pwd(){
  	$this->display();
  }
  //处理忘记密码
  public function forget(){
  	//var_dump($_POST);die;
    if($_POST){
    	$where['moble'] = $_POST['mobile'];
     	$info = M('user')->where($where)->find();
      	if(!$info){
        	$this->error('1018');
        }else{
          if(!check($_POST['new_pwd'],'password')){
              $this->error('1043');
          }
          if(md5($_POST['new_pwd']) == $info['paypassword']){
            	$this->error('1044');
            }
        	/* if($_POST['moble_verify_new'] != $_SESSION['real_verify']){
            	$this->error('短信验证码错误');
            } */
          	
          	$data['password'] = md5($_POST['new_pwd']);
          	$save = M('user')->where($where)->save($data);
          	if($save!==FALSE){
            	$this->success('1012');
            }else{
            	$this->error('1013');
            }
        }
    }
  }
  
  
  //忘记 密码
  public function forget_password()
  {
	  $username = I('post.username');
	  $type = I('post.type');
	  $code = I('post.code')?I('post.code'):1;
	  $password = I('post.password');
	  $repassword = I('post.repassword');
	  if($type == 1)
	  {
		if (!check($username, 'moble')) 
		{
			$this->error('手机号格式错误！');
    	    //$this->ajaxReturn(array('code'=>'1026'));
    	}
		
		
	  }
	  elseif($type == 2)
	  {
		if(!check($username,'eamil'))
		{
			$this->error('邮箱格式错误！');
		}
		/*$where = "email=".$username." or username=".$username;
		$user = M('User')->where($where)->field('id')->find();
		if(!user)
		{
			$this->error('用户不存在');
		}*/
	  }

	  //$where = "moble=".$username." or username=".$username;
		$user = M('User')->where(array('username'=>$username))->field('id')->find();
		if(!$user)
		{
			$this->error('用户不存在');
		}
		if($user['password'] == md5($password))
		{
			$this->error('不能与原密码一样');
		}
	  if (!check($password, 'password')) 
	  {
			$this->error('登录密码格式错误！');
			//$this->ajaxReturn(array('code'=>'1019'));
	  }
	  if($code != 1)
		{
			$code_log = M('Code_log')->where(array('username'=>$username,'type'=>$type))->order('id desc')->find();
			if($code_log)
			{
				if($code_log['addtime'] + 300 < time())
				{
					$this->error('验证码已过期');
				}
				if($code_log['code'] != $code)
				{
					$this->error('验证码错误');
				}
			}
			else
			{
				$this->error('请获取验证码');
			}
		}
	  else
		{
			$this->error('请获取验证码');
		}
		 //var_dump($user);die;
		$save = M('User')->where(array('id'=>$user['id']))->save(array('password'=>md5($password)));
		//echo M('User')->getLastSql();
		//var_dump($save);die;
		if($save)
		{
			$this->success('操作成功');
		}
		else
		{
			$this->error('操作失败');
		}
	
	  
	  
	  
	  
  }
	

}

?>