<?php
namespace Home\Controller;

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

	public function new_reg(){
		$moble = I('post.mobile');
		$password = I('post.password');
		$moble_verify = I('post.yzm');
		$invit = I('post.invit');
		if (!check($moble, 'moble')) {
			$this->error('手机号码格式错误！');
		}

		if (M('User')->where(array('moble' => $moble))->find()) {
			$this->error('手机号码已存在！');
		}

		if (!check($moble_verify, 'd')) {
			$this->error('短信验证码格式错误！');
		}

		/*if ($moble_verify != session('real_verify')) {
			$this->error('短信验证码错误！');
		}*/

		if (!check($password, 'password')) {
			$this->error('密码格式错误！');
		}
		
		if (!$invit) {
			$invit = session('invit');
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
		    if ($invituser && $invituser['addip']!=get_client_ip() && time()-$invituser['addtime']>24*3600) {
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
		$mo->execute('lock tables zhisucom_user write , zhisucom_user_coin write , zhisucom_coin , zhisucom_lever_coin');
		$rs = array();
		$login_token = md5($_POST['username'].$password);//app端平台用到
		$rs[] = $mo->table('zhisucom_user')->add(array('username' => $moble,'mobletime' => time(), 'password' => md5($password), 'invit' => $tradeno, 'tpwdsetting' => 3, 'invit_1' => $invit_1, 'invit_2' => $invit_2, 'invit_3' => $invit_3, 'addip' => get_client_ip(), 'addr' => get_city_ip(), 'addtime' => time(), 'status' => 1,'moble'=>$moble,'login_token'=>$login_token));
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
			$this->invit_jtc($invit);
			$this->success('注册成功！');
		}
		else {
			$mo->execute('rollback');
			$this->error('注册失败！');
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
					$data['remark'] = '邀请送JEFF';
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

	public function upregister($username='', $password='', $repassword='', $verify='', $invit='', $moble='', $moble_verify='')
	{
	    if (!check($username, 'username')) {
	        $this->error('用户名格式错误！');
	    }
		if(M_ONLY==0){
		
			if (!check_verify(strtoupper($moble_verify))) {
				$this->ajaxReturn(['status'=>0,'info'=>'图形验证码错误!']);
			}

			if (!check($password, 'password')) {
				$this->error('登录密码格式错误！');
			}

			if ($password != $repassword) {
				$this->error('确认登录密码错误！');
			}
		}else{
			//error_log('username='.$username,3,'./x.txt');
			if (!check($password, 'password')) {
				$this->error('登录密码格式错误！');
			}
			if (!check($_POST['fund_psd'], 'password')) {
			    $this->error('资金密码格式错误！');
			}
			//$username=$moble;
		}
		/*$email = trim(I('post.email'));
		if(!check($email,'email')){
		    $this->error('邮箱格式错误！');
		}*/
		
		if($password==$_POST['fund_psd']){
		    $this->error('资金密码不能与登陆密码相同！');
		}
		
		$status=check_verify(I('post.moble_verify'));
		if (!$status) {
				$this->ajaxReturn(['status'=>0,'info'=>'图形验证码错误!']);
			}
		
		/*if (!check($moble, 'moble')) {
			$this->error('手机号码格式错误！');
		}*/

		/*if (!check($moble_verify, 'd')) {
			$this->error('短信验证码格式错误！');
		}

		if ($moble_verify != session('real_verify')) {
			$this->error('短信验证码错误！');
		}*/
		
		/*if (M('User')->where(array('moble' => $moble))->find()) {
			$this->error('手机号码已存在！');
		}*/

		if (M('User')->where(array('username' => $username))->field('id')->find()) {
			$this->error('用户名已存在');
		}
		
		if (M('User')->where(array('email' => $email))->field('id')->find()) {
		    $this->error('邮箱已存在');
		}
		

		if (!$invit) {
			$invit = session('invit');
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
		    if ($invituser && $invituser['addip']!=get_client_ip() && time()-$invituser['addtime']>24*3600) {
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
		$mo->execute('lock tables zhisucom_user write , zhisucom_user_coin write ');
		$rs = array();
		$login_token = md5($_POST['username'].$password);//app端平台用到
		$rs[] = $mo->table('zhisucom_user')->add(array('username' => $_POST['username'],'mobletime' => time(), 'password' => md5($password), 'invit' => $tradeno, 'tpwdsetting' => 2, 'invit_1' => $invit_1, 'invit_2' => $invit_2, 'invit_3' => $invit_3, 'addip' => get_client_ip(), 'addr' => get_city_ip(), 'addtime' => time(), 'status' => 1,'paypassword'=>md5($_POST['fund_psd']),'email'=>$_POST['email'],'login_token'=>$login_token));
		$rs[] = $mo->table('zhisucom_user_coin')->add(array('userid' => $rs[0]));

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			session('reguserId', $rs[0]);
			$this->success('注册成功！');
			// $this->redirect('/Login/register');
		}
		else {
			$mo->execute('rollback');
			$this->error('注册失败！');
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
		/*if (!check_verify(strtoupper($verify))) {
			$this->error('图形验证码错误!');
		}*/

		if(strpos($moble,'@')){
		    //邮箱登陆
		    $where['email'] = $moble;
		    
		    if(isset($_POST['bj'])){
		        if (M('User')->where($where)->field('id')->find()) {
		            $this->error('该邮箱已存在！');
		        }
		    }
		    $user = M('user')->where($where)->field('id')->find();
		    //var_dump($user);die;
		    $code = rand(111111,999999);
		    session('email_code',$code);
		    $title = "邮箱绑定";
		    $content = "您的验证码是".$code."，请勿泄漏，谨防被骗。";
		    sendMail($moble,$title,$content);
		    $this->success('邮件验证码已发送');
		}else{
		    //手机号登陆
		    if (!check($moble, 'moble')) {
		        $this->error('手机号码格式错误！');
		    }
		    if(isset($_POST['bj'])){
		        if (M('User')->where(array('moble' => $moble))->find()) {
		            $this->error('手机号码已存在！');
		        }
		    }
		    
		    // echo 111;die;
		    $code = rand(111111, 999999);
		    session('real_verify', $code);
		   // $content = "您的验证码是".$code."，请勿泄漏，谨防被骗。";
		    $content = "您的验证码为".$code."请在5分钟内输入。感谢您对JEFFEX的支持，祝您生活愉快！";
		    if (send_mobile($moble, $content)) {
		        if(MOBILE_CODE ==0 ){
		            $this->success('目前是演示模式,请输入'.$code);
		        }else{
		            $this->success('验证码已发送');
		        }
		    }
		    else {
		        $this->error('验证码发送失败,请重发');
		    }
		}
	}

	public function dx_real(){
		$moble = $_POST['mobile'];
		if (!check($moble, 'moble')) {
		    $this->error('手机号码格式错误！');
		}
		if (M('User')->where(array('moble' => $moble))->find()) {
		    $this->error('手机号码已存在！');
		}
		    
		    
		    // echo 111;die;
		    $code = rand(111111, 999999);
		    session('new_real_verify', $code);
		    $content = "您的验证码是".$code."，请勿泄漏，谨防被骗。";
		    
		    if (send_mobile($moble, $content)) {
		        if(MOBILE_CODE ==0 ){
		            $this->success('目前是演示模式,请输入'.$code);
		        }else{
		            $this->success('验证码已发送');
		        }
		    }
		    else {
		        $this->error('验证码发送失败,请重发');
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
			$this->success('成功！');
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
	
	

	public function upregister3($truename, $idcard)
	{
		if (!check($truename, 'truename')) {
			$this->error('真实姓名格式错误！');
		}

		if (!check($idcard, 'idcard')) {
			$this->error('身份证号格式错误！');
		}

		if (!session('reguserId')) {
			$this->error('非法访问！');
		}

		if (M('User')->where(array('id' => session('reguserId')))->save(array('truename' => $truename, 'idcard' => $idcard))) {
			$this->success('成功！');
		}
		else {
			$this->error('失败！');
		}
	}

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
	public function submit()
	{
		/*if (C('login_verify')) {
			if (!check_verify(strtoupper($verify))) {
				$this->error('图形验证码错误!');
			}
		}*/
		$moble = I('post.moble');
		$password = I('post.login_password');
		if(M_ONLY==0){
			/*if (check($username, 'email')) {
				$user = M('User')->where(array('email' => $username))->find();
				$remark = '通过邮箱登录';
			}*/

			if (check($moble, 'moble')) {
				$user = M('User')->where(array('moble' => $moble))->find();
				$remark = '通过手机号登录';
			}

			/*if (!$user) {
				$user = M('User')->where(array('username' => $username))->find();
				$remark = '通过用户名登录';
			}*/
		}else{
		    if(strpos($moble, '@')){
		        //邮箱验证
		        $user = M('User')->where(array('email' => $moble))->find();
		        $remark = '通过邮箱登录';
				$phone=$user['moble'];
		    }elseif (check($moble, 'moble')) {
		        //手机号登陆
				$user = M('User')->where(array('moble' => $moble))->find();
				$remark = '通过手机号登录';
				$phone=$user['moble'];
			}/*else{
			    //用户名登陆
			    $user = M('User')->where(array('username' => $moble))->find();
			    $remark = '通过用户名登录';
				$phone=$user['moble'];
			}*/
			
		}

		if (!$user) {
			$this->error('用户不存在！');
		}

		if (!check($password, 'password')) {
			// $this->error('登录密码格式错误！');
		}

		if (md5($password) != $user['password']) {
			$this->error('登录密码错误！');
		}

		if ($user['status'] != 1) {
			$this->error('你的账号已冻结请联系管理员！');
		}

		
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
			$mobles = $user['moble'];
			
			//$login_times = date("Y-m-d H:i:s",time());
			$contents = "您的账户在PC端登录成功，如不是您本人操作，建议您立即修改密码。";
			//send_moble($mobles, $contents);
			$this->success('登录成功！');
		}
		else {
			session('zhisucom_already',0);
			$mo->execute('rollback');
			$this->error('登录失败！');
		}
	}

	public function login1(){
		// dump($_SESSION);
		$this->display();
	}
	//短信验证码
	public function loginac(){
	
		$sendcode = $_POST['code'];
		$moble=I('phone');
		if(strpos($moble,'@')){
		    //邮箱登陆
		    if($sendcode == $_SESSION['email_code']){
		        $res=M('user')->where(array('email'=>$moble))->field('id,username')->find();
		        session('userId', $res['id']);
		        $res['username'] = $res['username']?$res['username']:$moble;
		        session('userName', $res['username']);
		        	
		        $this->success('登录成功！');
		    }else{
		        $this->error('登录失败！');
		    }
		}else{
		    //手机号登陆
		    if($sendcode == $_SESSION['real_verify']){
		        $res=M('user')->where('moble='.$moble)->field('id,username')->find();
		        session('userId', $res['id']);
		        session('userName', $res['username']);
		        	
		        $this->success('登录成功！');
		    }else{
		        $this->error('登录失败！');
		    }
		}
		
	}
	//谷歌验证
	public function gugeverify(){		
		$code=I('code');
		$moble=I('phone');
		$res=M('user')->where('moble='.$moble)->field('secret,id,google,username')->find();		
		if($res['google']==1){			
			$info=check_google($code,$res['id']);

			if($info=="ok"){
				
				session('userId', $res['id']);
				session('userName', $res['username']);
				
				$this->ajaxReturn(array('status'=>1,'info'=>"验证成功"));
				exit;
			}else{
				
				$this->ajaxReturn(array('status'=>0,'info'=>"谷歌验证码错误"));
				exit;
			}	
		}else{
			
			$this->ajaxReturn(array('status'=>0,'info'=>"没有绑定谷歌验证，请使用短信登录"));
			exit;
		}
		
		
		
	
	}
	public function loginout()
	{
		session(null);
		// redirect('/');
		$this->success('退出成功');
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
				// session("findpwdmoble",$user['moble']);
				$this->success('验证成功');
			}
			
		}
		else {
			$this->display();
		}
	}
	
	
	public function findpwdconfirm(){
		
		/*if(empty(session('findpwdmoble'))){
			session(null);
			redirect('/');
		}*/
		
		$this->display();
	}
	
	/*public function password_up($password=""){
		
		
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
	}*/
   //忘记密码
  public function forget_pwd(){
  	$this->display();
  }
  //处理忘记密码
  public function forget(){
  	//var_dump($_POST);die;
    if($_POST){
        if(!check($_POST['new_pwd'],'password')){
            $this->error('新密码格式不正确');
        }
        
    	$where['moble'] = $_POST['mobile'];
     	$info = M('user')->where($where)->find();
      	if(!$info){
        	$this->error('绑定手机号不存在');
        }else{
			if(md5($_POST['new_pwd']) == $info['paypassword']){
            	$this->error('不能与资金密码相同');
            }
        	if($_POST['moble_verify_new'] != $_SESSION['real_verify']){
            	$this->error('短信验证码错误');
            }
          	if(md5($_POST['new_pwd']) == $info['password']){
				$this->error('不能与原密码相同');
			}
          	$data['password'] = md5($_POST['new_pwd']);
          	$save = M('user')->where(array('id'=>$info['id']))->save($data);
			//dump($save);die;
          	if($save){
            	$this->success('找回密码成功');
            }else{
            	$this->error('找回密码失败');
            }
        }
    }
  }
	

}

?>