<?php
namespace Home\Controller;

class UserController extends HomeController
{
	public function index()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();	
		
		$this->assign('user', $user);
		$this->assign('prompt_text', D('Text')->get_content('user_index'));
		$this->display();
	}

	public function numeauth1(){
		if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if ($user['idcard']) {
			$user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
		}
		
		$imgstr = "";
		$imgnum=0;
		if($user['idcardimg1']){
			$img_arr = array();
			$img_arr = explode("_",$user['idcardimg1']);

			foreach($img_arr as $k=>$v){
				$imgstr = $imgstr.'<li style="height:100px;float:left;"><img style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$v.'" /></li>';
				$imgnum++;
			}
			
			
			unset($img_arr);
		}
		$allowImg = false;
		if( ($user['idcardauth']==0 && $imgnum<3) || ($user['idcardauth']==0 && $imgnum==3 && !empty($user['idcardinfo']))){
			$allowImg = true;
		}

		$this->assign('user', $user);
		$this->assign('userimg', $imgstr);
		$this->assign('imgnum', $imgnum);
		$this->assign('allowImg', $allowImg);
		
		$this->assign('prompt_text', D('Text')->get_content('user_nameauth'));
		$this->display();
	}
	
	
	public function nameauth()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if ($user['idcard']) {
			$user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
		}
		
		$imgstr = "";
		$imgnum=0;
		if($user['idcardimg1']){
			$img_arr = array();
			$img_arr = explode("_",$user['idcardimg1']);

		/*	foreach($img_arr as $k=>$v){
				$imgstr = $imgstr.'<li style="height:100px;float:left;"><img style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$v.'" /></li>';
				$imgnum++;
			}*/
            $imgstr = '<li  style="height:100px;float:left;"><img class="img-all" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg1'].'" /></li>';
            $imgstr = $imgstr.'<li  style="height:100px;float:left;"><img class="img-all1" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg2'].'" /></li>';
            $imgstr = $imgstr.'<li style="height:100px;float:left;"><img class="img-all2" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg3'].'" /></li>';

			unset($img_arr);
		}
		$allowImg = false;
		if( ($user['idcardauth']==0 && $imgnum<3) || ($user['idcardauth']==0 && $imgnum==3 && !empty($user['idcardinfo']))){
			$allowImg = true;
		}

		$this->assign('user', $user);
		$this->assign('userimg', $imgstr);
		$this->assign('imgnum', $imgnum);
		$this->assign('allowImg', $allowImg);
		
		$this->assign('prompt_text', D('Text')->get_content('user_nameauth'));
		$this->display();
	}
	
	//注册实名认证
	public function realname(){
		// var_dump($_SESSION);die;
		$user = M('User')->where('id = '.$_SESSION['userId'])->find();
		// var_dump($user);die;
		$this->assign('user',$user);
		$this->display();
	}
	
	
	//高级认证
	public function senior(){
	    	if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();
// 		var_dump($user);die;
		$imgstr = "";
	/*	$senior_img = explode(',' , $user['senior_img']);
// 		var_dump($senior_img);die;
		foreach($senior_img as $k=>$v){
		    var_dump($v);
		    $imgstr = '<li  style="height:100px;float:left;"><img class="img-all" style="height:100px;margin-right:20px;" src="/Upload/senior/'.$v.'" /></li>';
		}*/
// 		echo $imgstr;die;

		if ($user['idcard']) {
			$user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
		}
		
		
		$imgnum=0;
		if($user['idcardimg1']){
			$img_arr = array();
			$img_arr = explode("_",$user['idcardimg1']);

		/*	foreach($img_arr as $k=>$v){
				$imgstr = $imgstr.'<li style="height:100px;float:left;"><img style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$v.'" /></li>';
				$imgnum++;
			}
            $imgstr = '<li  style="height:100px;float:left;"><img class="img-all" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg1'].'" /></li>';
            $imgstr = $imgstr.'<li  style="height:100px;float:left;"><img class="img-all1" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg2'].'" /></li>';
            $imgstr = $imgstr.'<li style="height:100px;float:left;"><img class="img-all2" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg3'].'" /></li>';*/

			unset($img_arr);
		}
		$allowImg = false;
		if( ($user['idcardauth']==0 && $imgnum<3) || ($user['idcardauth']==0 && $imgnum==3 && !empty($user['idcardinfo']))){
			$allowImg = true;
		}

		$this->assign('user', $user);
		$this->assign('userimg', $imgstr);
		$this->assign('imgnum', $imgnum);
		$this->assign('allowImg', $allowImg);
		
		$this->assign('prompt_text', D('Text')->get_content('user_nameauth'));
		$this->display();
	    
	}
	public function imgup(){
	   // var_dump($_FILES);die;
	  if($_FILES){
	      $upload = new \Think\Upload();
	      $upload->rootPath = './Upload/senior/';
	      foreach($_FILES as $k=>$v){
	          foreach($v['tmp_name'] as $k1=>$v1){
	              $name =  $upload->saveName = time().'_'.mt_rand();
	           //   var_dump($name);
	                $info = $upload->upload();
	               
	               if(!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                    }else{// 上传成功 获取上传文件信息
                     echo $info['savepath'].$info['savename'];
                      foreach($info as $k2=>$v2){
	                    $info_name .= $v2['savepath'].$v2['savename'].",";
	                   //$s =+  $v2['savename'].',';
	                   $time=time();
	                    M('User')->where(array('id' => userid()))->save(array('senior_img' => $info_name,'senior_past' => 0,'senior_time'=>$time));
	                    }
                    }
	          }
	           //     $name1 = $v['photo']['name'];
        		  //  $time = time().rand(100000, 999999);
        		  //  $n= $time;
	      }
	  }
	}
	//高级认证图片上传
	public function senior_upimg(){
		// var_dump($_FILES);die;
		 if($_FILES['inputfile1']["tmp_name"]){
		    $result=$this->upload_senior("inputfile1");
			// var_dump($result);die;
            if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('senior_img1' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
        if($_FILES['inputfile2']["tmp_name"]){
            $result=$this->upload_senior("inputfile2");
            if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('senior_img2' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
        if($_FILES['inputfile3']["tmp_name"]){
            $result=$this->upload_senior("inputfile3");
            if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('senior_img3' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
		 if($_FILES['inputfile4']["tmp_name"]){
            $result=$this->upload_senior("inputfile4");
            if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('senior_img4' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
	}
	
	protected function upload_senior($name){
        $upload = new \Think\Upload();
        $upload->maxSize = 2048000;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath = './Upload/idcard/';
        $upload->autoSub = false;
        $info = $upload->uploadOne($_FILES[$name]);
        if(!$info){
            return "error";
            exit();
        }else{
            return $info['savename'];
        }
    }
	//变更上传状态
	public function senior_s(){
		// var_dump($_POST);die;
		$data['senior_time'] = time();
		$data['senior_past'] = 0;
		$user_info = M('user')->where('id='.$_SESSION['userId'])->save($data);
		if($user_info){
			$this->success('提交成功');
		}else{
			$this->error('提交失败');
		}
	}
	//高级认证重新上传
	public function senior_cx(){
		if (!userid()) {
			redirect('/#login');
		}
		$url = M('user')->where('id='.$_SESSION['userId'])->find();
		$url1 = './Upload/idcard/'.$url['senior_img1'];
		$url2 = './Upload/idcard/'.$url['senior_img2'];
		$url3 = './Upload/idcard/'.$url['senior_img3'];
		$url4 = './Upload/idcard/'.$url['senior_img4'];
		
		if(file_exists($url1) && file_exists($url2) && file_exists($url3) && file_exists($url4)){
			// echo $url1;die;
			unlink($url1);
			unlink($url2);
			unlink($url3);
			unlink($url4);
			M('user')->where('id='.$_SESSION['userId'])->save(array('senior_img1'=>'','senior_img2'=>'','senior_img3'=>'','senior_img4'=>'', 'senior_time'=>'' ,'senior_past'=>''));
			$this->success('操作成功');
		}else{
			$this->success('未找到原图片路径');
		}
	}
	
	//身份认证重新上传
	public function realname_cx(){
		if (!userid()) {
			redirect('/#login');
		}
		$url = M('user')->where('id='.$_SESSION['userId'])->find();
		$url1 = './Upload/idcard/'.$url['idcardimg1'];
		$url2 = './Upload/idcard/'.$url['idcardimg2'];
		$url3 = './Upload/idcard/'.$url['idcardimg3'];
		
		if(file_exists($url1) && file_exists($url2) && file_exists($url3) ){
			// echo $url1;die;
			unlink($url1);
			unlink($url2);
			unlink($url3);
			M('user')->where('id='.$_SESSION['userId'])->save(array('idcardimg1'=>'','idcardimg2'=>'','idcardimg3'=>'' ,'idcardauth'=>'0'));
			$this->success('操作成功');
		}else{
			$this->success('未找到原图片路径');
		}
	}
	

	public function password()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('user_password'));
		$this->display();
	}

	public function uppassword($oldpassword, $newpassword, $repassword,$moble_verify)
	{
		if (!userid()) {
			$this->error('请先登录！');
		}
		
		if (!session('real_moble')) {
			$this->error('验证码已失效！');
		}

		if ($moble_verify != session('real_moble')) {
			$this->error('手机验证码错误！');
		}else{
			session('real_moble',null);
		}

		if (!check($oldpassword, 'password')) {
			$this->error('旧登录密码格式错误！');
		}

		if (!check($newpassword, 'password')) {
			$this->error('新登录密码格式错误！');
		}

		if ($newpassword != $repassword) {
			$this->error('确认新密码错误！');
		}

		$password = M('User')->where(array('id' => userid()))->getField('password');

		if (md5($oldpassword) != $password) {
			$this->error('旧登录密码错误！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('password' => md5($newpassword)));

		if ($rs) {
			$this->success('修改成功');
		}
		else {
			$this->error('修改失败');
		}
	}
	
	
	public function uppassword_zhisucom($oldpassword="", $newpassword="",$repassword="")
	{
		if (!userid()) {
			$this->error('请先登录！');
		}

		if ($oldpassword == $newpassword) {
			$this->error('新修改的密码和原密码一样！');
		}
		if (!check($oldpassword, 'password')) {
			$this->error('旧登录密码格式错误！');
		}

		if (!check($newpassword, 'password')) {
			$this->error('新登录密码格式错误！');
		}

		if ($newpassword != $repassword) {
			$this->error('确认新密码错误！');
		}

		$password = M('User')->where(array('id' => userid()))->getField('password');

		if (md5($oldpassword) != $password) {
			$this->error('旧登录密码错误！');
		}
		$paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if(md5($newpassword) == $paypassword){
			$this->error("新密码不能和交易密码一样");
		}
		
		
		
		$rs = M('User')->where(array('id' => userid()))->save(array('password' => md5($newpassword)));

		if (!($rs===false)) {
			$this->success('修改成功');
		}
		else {
			$this->error('修改失败');
		}
	}
	
	
	

	public function paypassword()
	{
		if (!userid()) {
			redirect('/#login');
		}
		
		
		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $user);
		
		$this->assign('prompt_text', D('Text')->get_content('user_paypassword'));
		$this->display();
	}

	
	
	public function uppaypassword_zhisucom($oldpaypassword, $newpaypassword, $repaypassword)
	{
		//var_dump($_POST);die;
		if (!userid()) {
			$this->error('请先登录！');
		}


		if (!check($oldpaypassword, 'password')) {
			$this->error('旧资金密码格式错误！');
		}

		if (!check($newpaypassword, 'password')) {
			$this->error('新资金密码格式错误！');
		}

		if ($newpaypassword != $repaypassword) {
			$this->error('确认新密码错误！');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if (md5($oldpaypassword) != $user['paypassword']) {
			$this->error('旧资金密码错误！');
		}

		if (md5($newpaypassword) == $user['password']) {
			$this->error('资金密码不能和登录密码相同！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('paypassword' => md5($newpaypassword)));

		if (!($rs===false)) {
			$this->success('修改成功');
		}
		else {
			$this->error('修改失败');
		}
	}
	
	
	
	
	
	
	
	
	public function uppaypassword($oldpaypassword, $newpaypassword, $repaypassword, $moble_verify)
	{
		if (!userid()) {
			$this->error('请先登录！');
		}

		if (!session('real_moble')) {
			$this->error('验证码已失效！');
		}

		if ($moble_verify != session('real_moble')) {
			$this->error('手机验证码错误！');
		}else{
			session('real_moble',null);
		}

		if (!check($oldpaypassword, 'password')) {
			$this->error('旧交易密码格式错误！');
		}

		if (!check($newpaypassword, 'password')) {
			$this->error('新交易密码格式错误！');
		}

		if ($newpaypassword != $repaypassword) {
			$this->error('确认新密码错误！');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if (md5($oldpaypassword) != $user['paypassword']) {
			$this->error('旧交易密码错误！');
		}

		if (md5($newpaypassword) == $user['password']) {
			$this->error('交易密码不能和登录密码相同！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('paypassword' => md5($newpaypassword)));

		if ($rs) {
			$this->success('修改成功');
		}
		else {
			$this->error('修改失败');
		}
	}

	public function ga()
	{
		if (empty($_POST)) {
			if (!userid()) {
				redirect('/#login');
			}

			$this->assign('prompt_text', D('Text')->get_content('user_ga'));
			$user = M('User')->where(array('id' => userid()))->find();
			$is_ga = ($user['ga'] ? 1 : 0);
			$this->assign('is_ga', $is_ga);

			if (!$is_ga) {
				$ga = new \Common\Ext\GoogleAuthenticator();
				$secret = $ga->createSecret();
				session('secret', $secret);
				$this->assign('Asecret', $secret);
				$qrCodeUrl = $ga->getQRCodeGoogleUrl($user['username'] . '%20-%20' . $_SERVER['HTTP_HOST'], $secret);
				$this->assign('qrCodeUrl', $qrCodeUrl);
				$this->display();
			}
			else {
				$arr = explode('|', $user['ga']);
				$this->assign('ga_login', $arr[1]);
				$this->assign('ga_transfer', $arr[2]);
				$this->display();
			}
		}
		else {
			if (!userid()) {
				$this->error('登录已经失效,请重新登录!');
			}

			$delete = '';
			$gacode = trim(I('ga'));
			$type = trim(I('type'));
			$ga_login = (I('ga_login') == false ? 0 : 1);
			$ga_transfer = (I('ga_transfer') == false ? 0 : 1);

			if (!$gacode) {
				$this->error('请输入验证码!');
			}

			if ($type == 'add') {
				$secret = session('secret');

				if (!$secret) {
					$this->error('验证码已经失效,请刷新网页!');
				}
			}
			else if (($type == 'update') || ($type == 'delete')) {
				$user = M('User')->where('id = ' . userid())->find();

				if (!$user['ga']) {
					$this->error('还未设置谷歌验证码!');
				}

				$arr = explode('|', $user['ga']);
				$secret = $arr[0];
				$delete = ($type == 'delete' ? 1 : 0);
			}
			else {
				$this->error('操作未定义');
			}

			$ga = new \Common\Ext\GoogleAuthenticator();

			if ($ga->verifyCode($secret, $gacode, 1)) {
				$ga_val = ($delete == '' ? $secret . '|' . $ga_login . '|' . $ga_transfer : '');
				M('User')->save(array('id' => userid(), 'ga' => $ga_val));
				$this->success('操作成功');
			}
			else {
				$this->error('验证失败');
			}
		}
	}

	public function moble()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		//if ($user['moble']) {
			//$user['moble'] = substr_replace($user['moble'], '****', 3, 4);
		//}

		$this->assign('user', $user);
		$this->assign('prompt_text', D('Text')->get_content('user_moble'));
		$this->display();
	}

	public function upmoble($moble, $moble_verify)
	{
		if (!userid()) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($moble, 'moble')) {
			$this->error('手机号码格式错误！');
		}

		if (!check($moble_verify, 'd')) {
			$this->error('短信验证码格式错误！');
		}

		if ($moble_verify != session('real_verify')) {
			$this->error('短信验证码错误！');
		}

		if (M('User')->where(array('moble' => $moble))->find()) {
			$this->error('手机号码已存在！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('moble' => $moble, 'mobletime' => time()));

		if ($rs) {
			$this->success('手机认证成功！');
		}
		else {
			$this->error('手机认证失败！');
		}
	}
	
	
	
	public function upmoble_zhisucom($moble_new="", $moble_verify_new="")
	{
		if (!userid()) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($moble_new, 'moble')) {
			$this->error('手机号码格式错误！');
		}

		if (!check($moble_verify_new, 'd')) {
			$this->error('短信验证码格式错误！');
		}

		if ($moble_verify_new != session('real_verify')) {
			$this->error('短信验证码错误！');
		}

		if (M('User')->where(array('moble' => $moble_new))->find()) {
			$this->error('手机号码已存在！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('moble' => $moble_new,'username'=>$moble_new, 'mobletime' => time()));

		if (!($rs===false)) {
			$this->success('手机绑定成功！');
		}
		else {
			$this->error('手机绑定失败！');
		}
	}
	
	
	
	
	
	
	
	

	public function alipay()
	{
		if (!userid()) {
			redirect('/#login');
		}

		D('User')->check_update();
		$this->assign('prompt_text', D('Text')->get_content('user_alipay'));
		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $user);
		$this->display();
	}

	public function upalipay($alipay = NULL, $paypassword = NULL)
	{
		if (!userid()) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($alipay, 'moble')) {
			if (!check($alipay, 'email')) {
				$this->error('支付宝账号格式错误！');
			}
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if (md5($paypassword) != $user['paypassword']) {
			$this->error('交易密码错误！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('alipay' => $alipay));

		if ($rs) {
			$this->success('支付宝认证成功！');
		}
		else {
			$this->error('支付宝认证失败！');
		}
	}

	public function tpwdset()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('prompt_text', D('Text')->get_content('user_tpwdset'));
		$this->assign('user', $user);
		$this->display();
	}

	public function tpwdsetting()
	{
		if (userid()) {
			$tpwdsetting = M('User')->where(array('id' => userid()))->getField('tpwdsetting');
			exit($tpwdsetting);
		}
	}

	public function uptpwdsetting($paypassword, $tpwdsetting)
	{
		if (!userid()) {
			$this->error('请先登录！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		if (($tpwdsetting != 1) && ($tpwdsetting != 2) && ($tpwdsetting != 3)) {
			$this->error('选项错误！' . $tpwdsetting);
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('tpwdsetting' => $tpwdsetting));

		if (!($rs===false)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function bank()
	{
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

	public function upbank($name, $bank, $bankprov, $bankcity, $bankaddr, $bankcard, $paypassword)
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

		if (M('UserBank')->add(array('userid' => userid(), 'name' => $name, 'bank' => $bank, 'bankprov' => $bankprov, 'bankcity' => $bankcity, 'bankaddr' => $bankaddr, 'bankcard' => $bankcard, 'addtime' => time(), 'status' => 1))) {
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
			$coin = $Coin[0]['name'];
		}

		$this->assign('xnb', $coin);

		foreach ($Coin as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		$userQianbaoList = M('UserQianbao')->where(array('userid' => userid(), 'status' => 1, 'coinname' => $coin))->order('id desc')->select();
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

	public function goods()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$userGoodsList = M('UserGoods')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();

		foreach ($userGoodsList as $k => $v) {
			$userGoodsList[$k]['moble'] = substr_replace($v['moble'], '****', 3, 4);
			$userGoodsList[$k]['idcard'] = substr_replace($v['idcard'], '********', 6, 8);
		}

		$this->assign('userGoodsList', $userGoodsList);
		$this->assign('prompt_text', D('Text')->get_content('user_goods'));
		$this->display();
	}

	public function upgoods($name, $truename, $idcard, $moble, $addr, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($name, 'a')) {
			$this->error('备注名称格式错误！');
		}

		if (!check($truename, 'truename')) {
			$this->error('联系姓名格式错误！');
		}

		if (!check($idcard, 'idcard')) {
			$this->error('身份证号格式错误！');
		}

		if (!check($moble, 'moble')) {
			$this->error('联系电话格式错误！');
		}

		if (!check($addr, 'a')) {
			$this->error('联系地址格式错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		$userGoods = M('UserGoods')->where(array('userid' => userid()))->select();

		foreach ($userGoods as $k => $v) {
			if ($v['name'] == $name) {
				$this->error('请不要使用相同的地址标识！');
			}
		}

		if (10 <= count($userGoods)) {
			$this->error('每个人最多只能添加10个地址！');
		}

		if (M('UserGoods')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'idcard' => $idcard, 'truename' => $truename, 'moble' => $moble, 'addtime' => time(), 'status' => 1))) {
			$this->success('添加成功！');
		}
		else {
			$this->error('添加失败！');
		}
	}
	
	
	
	
	public function upgoods_zhisucom($name="", $truename="", $idcard="", $moble="", $addr="", $paypassword="",$prov="",$city="")
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($name, 'a')) {
			$this->error('备注名称格式错误！');
		}

		if (!check($truename, 'truename')) {
			$this->error('联系姓名格式错误！');
		}

		if (!check($moble, 'moble')) {
			$this->error('联系电话格式错误！');
		}

		if (!check($addr, 'a')) {
			$this->error('联系地址格式错误！');
		}

		if (!check($prov, 'a')) {
			$this->error('省份填写错误！');
		}
		if (!check($city, 'a')) {
			$this->error('城市填写错误！');
		}		
		
		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		$userGoods = M('UserGoods')->where(array('userid' => userid()))->select();

		foreach ($userGoods as $k => $v) {
			if ($v['name'] == $name) {
				$this->error('请不要使用相同的地址标识！');
			}
		}

		if(10 <= count($userGoods)) {
			$this->error('每个人最多只能添加10个地址！');
		}

		if(M('UserGoods')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'prov' => $prov,'city'=>$city, 'truename' => $truename, 'moble' => $moble, 'addtime' => time(), 'status' => 1))) {
			$this->success('添加成功！');
		}
		else {
			$this->error('添加失败！');
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	public function delgoods($id, $paypassword)
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

		if (!M('UserGoods')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error('非法访问！');
		}
		else if (M('UserGoods')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success('删除成功！');
		}
		else {
			$this->error('删除失败！');
		}
	}

	public function log()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$where['status'] = array('egt', 0);
		$where['userid'] = userid();
		$Model = M('UserLog');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->assign('prompt_text', D('Text')->get_content('user_log'));
		$this->display();
	}

	public function install()
	{
	}
	
	//提交工单
	public function worklist(){
		 // var_dump($_SESSION);die;
		 $list = M('worklist')->where('close_id=0 and send_id='.$_SESSION['userId'])->order('send_time desc')->select();
		 // var_dump($list);die;
		 $this->assign('list',$list);
		$this->display();
	}
	
	//处理提交工单图片
	public function worklist_img(){
		 /* var_dump($_FILES);
		 var_dump($_POST);
		  die;*/
		 if (!userid()) {
			redirect('/#login');
		}
		 
		 if ($_FILES)
		{
		$name1 = $_FILES['filename1']['name'];
		$name2 = $_FILES['filename2']['name'];
		$name3 = $_FILES['filename3']['name'];
		// $tid=$_GET['tid'];
		$time = time();
		$n1 = $this->getRandCode().$name1;
		$n2 = $this->getRandCode().$name2;
		$n3 = $this->getRandCode().$name3;
		$URL1='./Upload/worklist_img/'.$n1;
		$URL2='./Upload/worklist_img/'.$n2;
		$URL3='./Upload/worklist_img/'.$n3;
		//上传图片
		$a=move_uploaded_file($_FILES['filename1']['tmp_name'], $URL1);
		$b=move_uploaded_file($_FILES['filename2']['tmp_name'], $URL2);
		$c=move_uploaded_file($_FILES['filename3']['tmp_name'], $URL3);
        //$this->success($URL1);
		}
		$user = M('User')->where('id = '.$_SESSION['userId'])->find();
		// var_dump($user);die;
		if($_POST){
			$data['send_id'] = $_SESSION['userId'];
			$data['send_time'] = time();
			$data['send_content'] = $_POST['ticket_text'];
			$data['send_user'] = $user['username'];
			$data['send_img1'] = '/Upload/worklist_img/'.$n1;
			$data['send_img2'] = '/Upload/worklist_img/'.$n2;
			$data['send_img3'] = '/Upload/worklist_img/'.$n3;
			$data['status'] = 0;
			$data['send_cate'] = $_POST['cate'];
			$data['currency'] = $_POST['currency'];
			$data['trans_id'] = $_POST['depo_txid'];
			$data['worklist_id'] = $this->worklist_code();
			$list = M('worklist')->add($data);
			if($list){
				$this->success('提交成功');
			}else{
				$this->error('提交失败');
			}
		}
     
	}
  public function imgUser_g(){
		// var_dump($_FILES);die;
		//上传用户身份证
		/*if (!userid()) {
			echo "nologin";
		}*/
		
        if($_FILES['inputfile1']["tmp_name"]){
		    $result=$this->upload2("inputfile1");
			// var_dump($result);die;
            if($result!="error"){
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
        if($_FILES['inputfile2']["tmp_name"]){
            $result=$this->upload2("inputfile2");
            if($result!="error"){
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
        if($_FILES['inputfile3']["tmp_name"]){
            $result=$this->upload2("inputfile3");
            if($result!="error"){
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
	}
	
	protected function upload2($name){
        $upload = new \Think\Upload();
        $upload->maxSize = 2048000;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath = './Upload/worklist_img/';
        $upload->autoSub = false;
        $info = $upload->uploadOne($_FILES[$name]);
        if(!$info){
            return "error";
            exit();
        }else{
            return $info['savename'];
        }
    }
	
	//关闭工单
	public function close_down(){
		// var_dump($_POST);die;
		$data['close_id'] = 1;
		$where['worklist_id'] = $_POST['dan'];
		$info = M('worklist')->where($where)->save($data);
		if($info){
			$this->success('关闭成功');
		}else{
			$this->error('关闭失败');
		}
	}
	
	
	//随机字符串(上传图片命名)
	public function getRandCode()
    {
        $charts = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz0123456789";
        $max = strlen($charts);
        $noncestr = "";
        for($i = 0; $i < 16; $i++)
        {
            $noncestr .= $charts[mt_rand(0, $max)];
        }


        return $noncestr;
    }
	//生成工单号
	public function worklist_code(){
		$yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J','K','M');
		$orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
		return $orderSn;
	}
	
	//邮箱绑定
  	public function email(){
      $user_info = M('user')->where('id='.$_SESSION['userId'])->find();
      //var_dump($_SESSION);die;
      $this->assign('user_info',$user_info);
    	$this->display();
    }
  //邮箱绑定提交
  public function email_bind(){
  	//var_dump($_POST);die;
    $where['email'] = $_POST['email'];
    $user_email = M('user')->where($where)->find();
    if($_SESSION['email_code'] != $_POST['email_verifyw']){
    	$this->error('验证码错误');
    }
    $data['email'] = $_POST['email'];
    $email_info = M('user')->where('id='.$_SESSION['userId'])->save($data);
    if($email_info){
    	$this->success('绑定成功');
    }else{
    	$this->error('绑定失败');
    }
    
  }
  
  //支付设置
  public function zhifushezhi(){
	  if (!userid()) {
			redirect('/#login');
		}
	  $info = M('user_bank')->where('userid='.$_SESSION['userId'])->select();
	  //var_dump($info);die;
	  $this->assign('info',$info);
	  $this->display();
  }
  //绑定银行卡
  public function bind_bank(){
	  // var_dump($_POST);die;
	   if (!userid()) {
			redirect('/#login');
		}
		$user = M('user')->where('id='.$_SESSION['userId'])->find();
	  if($_POST){
		$bank = M('user_bank')->where('bankcard='.$_POST['bank_addr'])->find();
		if($bank){
			$this->error('银行卡号已存在！');
		}
        
      	if (!check($_POST['bank_addr'], 'd')) {
			$this->error('银行账号格式错误！');
		}
        
		if(md5($_POST['fund_pwd_bank']) != $user['paypassword']){
			$this->error('资金密码错误');
		}
		$save = M('user_bank')->add(array('userid'=>$user['id'],'bank'=>$_POST['bank_name'],'bankuname'=>$_POST['bank_user'],'name'=>$user['truename'],'bankaddr'=>$_POST['bank_branch'],'bankcard'=>$_POST['bank_addr'],'addtime'=>time(),'sort'=>0,'status'=>1));
		if($save){
			$this->success('绑定成功');
		}else{
			$this->error('绑定失败');
		}
	  }else{
			$this->error('绑定失败');
		}
  }
  //绑定支付宝
  public function bind_ali(){
	  if (!userid()) {
			redirect('/#login');
		}
	  //var_dump($_POST);die;
	  $user = M('user')->where('id='.$_SESSION['userId'])->find();
	  if($_POST){
		if(md5($_POST['fund_pwd_ali'])!=$user['paypassword']){
			  $this->error('资金密码错误');
		}
		$ali = M('user')->where('id='.$_SESSION['userId'])->save(array('ddpay'=>$_POST['ali_name'],'ddpayname'=>$_POST['pay_name'],'ddmemo'=>$_POST['ali_memo']));
		if($ali){
			$this->success('绑定成功');
		}else{
			$this->error('绑定失败');
		}
	  }else{
			$this->error('绑定失败');
		}
  }
  //绑定微信
  public function weixin(){
	  //var_dump($_POST);die;
	   if (!userid()) {
			redirect('/#login');
		}
		$user = M('user')->where('id='.$_SESSION['userId'])->find();
		if($_POST){
			if(md5($_POST['fund_pwd_wei'])!=$user['paypassword']){
			  $this->error('资金密码错误');
			}
			$wei = M('user')->where('id='.$_SESSION['userId'])->save(array('weixin'=>$_POST['wei_nameid'],'weixin_name'=>$_POST['wei_name'],'weixin_memo'=>$_POST['wei_memo']));
			if($wei){
				$this->success('绑定成功');
			}else{
				$this->error('绑定失败');
			}
	  }else{
			$this->error('绑定失败');
		}
  }
 
    
}

?>