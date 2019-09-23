<?php
namespace Home\Controller;

class C2cController extends HomeController {

    //c2c主页
    public function index()
    {
        if (userid()) {
            $user = M('User')->where(array('id' => userid()))->find();
            $stat = $user['isshop'];		
			$uid=userid();
					
			    $ucoinres=M('c2c_trade')->where("(bid=$uid or sid=$uid)and is_pay=1")->order("type,paytime DESC")->select();        //当前订单
            
           
                $ucoinres1=M('c2c_trade')->where("(bid=$uid or sid=$uid)and is_done=1")->order("type,paytime DESC")->select();       //已完成订单
            
            
                $ucoinres2=M('c2c_trade')->where("(bid=$uid or sid=$uid)and is_cancel=1")->order("type,paytime DESC")->select();     //取消订单
            
           
                $ucoinres3=M('c2c_trade')->where("(bid=$uid or sid=$uid)and is_ts=2")->order("type,paytime DESC")->select();         //已申诉订单
				
				$shop=M('c2c_trade')->where("(bid=$uid or sid=$uid)and mid>0")->select();
       
				
				$this->assign('shop',$shop);
				$this->assign('myucoin',$ucoinres);
				$this->assign('myucoin1',$ucoinres1);
				$this->assign('myucoin2',$ucoinres2);
				$this->assign('myucoin3',$ucoinres3);			
				$this->assign('uid',$uid);
				$this->assign('user',$user);
				$this->assign('stat', $stat);			
        }
        $ucoin=M('c2c_trade')->where('is_cancel=0 and is_pay=0 and residue>0')->order("type")->select();   
        $this->assign('ucoin',$ucoin);
        $this->display();
    }
    
    //C2C买卖中心 暂无使用
    public function shop(){
        if (!userid()) {
            redirect('/#login');
        }
        $trade=M('c2c_trade');
            $map['is_pay']=0;
            $map['residue']=array('gt',0);
        
        $s=1;
        if($s==1){
            $map['type']=1;
        }else{
            $map['type']=2;
        }
        $count=$trade->where($map)->select;
        $Page= new \Think\Page($count,10);
        $show= $Page->show(); 
        $list = $trade->where($map)->order('addtime DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
    
    //操作买入订单
    public function buyac(){

        if (!userid()) {
            redirect('/#login');
        }
        $number=I('post.num');                        //当前用户卖出数量获得
        $uid=userid();                                //当前用户ID
        $buyid=I('post.bid');                         //买家ID
        $id=I('post.id');                             //订单ID

        $trade=M('c2c_trade')->where("id=".$id)->find();
        $ucoin=M('user_coin')->where("userid=".$uid)->getField('usdt');


        if($number>$trade['residue']){
            $this->ajaxReturn(array("status"=>0,"msg"=>"卖出 USDT大于订单数量"));
            exit;
        }

        if($uid==$buyid){
            $this->ajaxReturn(array("status"=>0,"msg"=>"不能匹配自己的订单"));
            exit;
        }
            $interest=0;                            //调用C2C后台设置交易利息
        if($ucoin<($trade['residue']+$interest)){
            $this->ajaxReturn(array("status"=>0,"msg"=>"账号USDT数量不满足,请充值"));
            exit;
        }

        if($trade['minnum']<=$trade['residue']){            //minnum最小成交量   //residue订单剩余成交量

            if($number<$trade['minnum']){
                $this->ajaxReturn(array("status"=>0,"msg"=>"最低成交数量为".$trade['minnum']));
                exit;
            }

        }else{
            if($number!=$trade['residue']){
                $this->ajaxReturn(array("status"=>0,"msg"=>'订单成交数量为'.$trade['residue']));
                exit;
            }
        }
    /*    if($trade['is_lock']==1){
            $this->error("其他用户正在交易此订单，请稍后再试");
            exit;
        }*/
        //生成子订单
                $code_num="B".rand(10000,99999).time();
                $user=M('user')->where('id='.$uid)->find();
                $data['number']=$code_num;
                $data['market']='usdt';
                $data['sid']=$uid;
                $data['price']=$trade['price'];
                $data['cprice']=round($trade['price']*$number,8);
                $data['num']=$number;
                $data['type']=$trade['type'];
                $data['isshop']=$user['isshop'];
                $data['remark']="C2C交易-主动卖出-市场USDT";
                $data['addtime']=$trade['addtime'];
                $data['paytime']=time();
                $data['bid']=$trade['bid'];
                $data['is_pay']=1;
                $data['is_ts'] = 0;                    //0默认值  1确认收款 2投诉
                $data['is_cancel']=0;
                $data['match_id'] =$trade['id'];
                $data['wechat'] = $trade['wechat'];
                $data['alipay'] = $trade['alipay'];
                $data['bank'] = $trade['bank'];
                $result=M('c2c_trade')->add($data);
                if($result){
                    M("c2c_trade")->where('id='.$id)->setDec('residue',$number);        //计算剩余订单量
                    $marchid = M("c2c_trade")->where('id=' . $id)->getField('match_id');
                    $marchid = $marchid . $result . ",";
                    M("c2c_trade")->where('id=' . $id)->setField('match_id', $marchid);
                    $this->ajaxReturn(array("status"=>1,"msg"=>'卖出成功'));
                }else{
                    $this->ajaxReturn(array("status"=>0,"msg"=>'卖出失败'));
                }

    }
    //操作卖出订单
   public function sellac(){

    if (!userid()) {
        redirect('/#login');
    }
    $number =I("post.num");                 //当前用户买入数量获得
    $uid = userid();                        //当前用户ID
    $sellid = I('post.sid');                //卖家ID
    $id = I('post.id');                     //订单ID

    $trade = M('c2c_trade')->where("id=" . $id)->find();
    $ucoin = M('user_coin')->where("userid=" . $uid)->getField('usdt');


    if ($uid == $sellid) {
        $this->ajaxReturn(array("status"=>0,"msg"=>"不能抢购自己的订单"));
        exit;
    }


    if($number>$trade['residue']){
        $this->ajaxReturn(array("status"=>0,"msg"=>"买入USDT大于订单数量"));
        exit;
    }


    if ($trade['minnum'] <= $trade['residue']) {            //minnum最小成交量   //residue订单剩余成交量

        if ($number < $trade['minnum']) {
            $this->ajaxReturn(array('status'=>0,"msg"=>"最低成交数量为".$trade['minnum']));
            exit;
        }

    } else {
        if ($number != $trade['residue']) {
            $this->ajaxRetrun(array("status"=>0,"msg"=>'订单成交数量最低为'. $trade['residue']));
            exit;
        }
    }
    /*if($trade['is_pay']==1 or $trade['is_lock']==1){
        $this->error("其他用户正在交易此订单，请稍后再试");
            exit;
    }*/
    //生成子订单
     $code_num="S".rand(10000,99999).time();
    $user = M('user')->where('id=' . $uid)->find();
    $data['number'] = $code_num;
     $data['market']='usdt';
    $data['sid'] = $trade['sid'];
    $data['price'] = $trade['price'];
    $data['cprice'] = round($trade['price'] * $number,8);
    $data['num'] = $number;
    $data['type'] = $trade['type'];
    $data['isshop'] = $user['isshop'];
    $data['remark'] = "C2C交易-主动买入-市场USDT";
    $data['addtime'] = $trade['addtime'];
    $data['paytime'] = time();
    $data['bid'] = $uid;
    $data['is_pay'] = 1;
    $data['is_ts'] = 0;                    //0默认值  1确认收款 2投诉
    $data['is_cancel'] = 0;
    $data['match_id'] = $trade['id'];
    $data['wechat'] = $trade['wechat'];
    $data['alipay'] = $trade['alipay'];
    $data['bank'] = $trade['bank'];

    $result = M('c2c_trade')->add($data);
    if ($result) {
       
        M("c2c_trade")->where('id=' . $id)->setDec('residue', $number);        //计算剩余订单量
        $marchid = M("c2c_trade")->where('id=' . $id)->getField('match_id');
        $marchid = $marchid . $result . ",";
        M("c2c_trade")->where('id=' . $id)->setField('match_id', $marchid);
        $this->ajaxReturn(array("status"=>1,"msg"=>'卖出成功'));
    } else {
        $this->ajaxReturn(array("status"=>0,"msg"=>'卖出失败'));
    }
}
    //交易订单
    public function deal(){
         if (!userid()) {
            redirect('/#login');
        }
        $id = intval(I('get.id'));
        $uid=userid();

 //       $info = M('otc_log')->where(array('id' => $id))->find();
  //      $user = M('user')->where(array('id' => $info['matchid']))->find();
        $trade = M("c2c_trade")->where('id='.$id)->find();
        if (!$trade) {
            echo "<script> layer.msg('交易订单不存在',{icon : 2 });</script>";
        }
		$this->assign('c2c', $trade);
		$this->assign('uid', $uid);

        $this->display();  
        
    }
    //买家确认打款
    public function confirm_pay(){
        if (!userid()) {
            redirect('/#login');
        }
        if (IS_AJAX) {
            $id = I('post.id');
            if (!is_numeric($id)) {
                $this->error('无效订单');
                exit;

            }

            $message = I('post.message');

            $img = I('post.img');
            if (!$img) {
                $this->error('请上传打款凭证');
                exit;
            }

            $result = M('c2c_trade')->where("id=" . $id)->find();

            if ($result) {
                $data['img'] = $img;
                $data['message'] = $message;
                $data['is_get'] = 1;
                $data['gettime'] = time();
                $data['id'] = $id;
                $res = M('c2c_trade')->save($data);
                if ($res !== false) {
                    $this->success('打款成功,等待卖家确认收款');
                } else {
                    $this->success('确认提交失败');
                }
            }
        }
    }
    
    //上传交易凭证
    public function upimg()
    {
//        if (!userid()) {
//            $this->error('请先登录！');
//        }
//        $user = M('user')->where(array('userid' => userid()))->find();
//
//        if (!$user['idcardauth']) {
//            $this->error('请先实名认证');
//            $this->redirect('user/nameauth');
//        }


        $upload = new \Think\Upload();
        $upload->maxSize = 2048000;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath = './Upload/idcard/';
        $upload->autoSub = false;
        $info = $upload->upload();

        if (!$info) {// 上传错误提示错误信息
//            $this->error($upload->getError());
            $this->ajaxReturn(array("status"=>0,"msg"=>'上传打款凭证失败'));
        } else {// 上传成功
            foreach ($info as $file) {
                $path = $file['savePath'] . $file['savename'];
            }
            $this->ajaxReturn(array("status"=>1,"msg"=>"上传打款凭证成功","url"=>$path));
        }
        exit();
    }
    //C2C确认收款
    public function confirm_get(){
        $trade=M('c2c_trade');
        $ucoin=M('user_coin');
       if(IS_AJAX ){
                $id=I('post.id');
                $is_ts=I('post.is_ts');
                $where['id']=$id;
                $where['is_done']=0;
            //确认收款
              if($is_ts==1){
                  $data['is_done']=1;
                  $data['is_ts']=1;
                  $data['endtime']=time();
              }
             //未打款投诉
               if($is_ts==2){
                   $data['is_done']=0;
                   $data['is_ts']=2;
               }
            $res=$trade->where($where)->find();
            if($res){                                                                            //判断是否有这个订单
                $trade->where($where)->save($data);

                    $c2c=$trade->where("id=".$id." and is_pay=1 and is_done=1 and is_get=1 and is_cancel=0 and is_ts=1")->find();
                    if($c2c){
                             $matchid=$c2c['match_id'];                                               //主订单id
                             $ucoin->where("userid=".$c2c['bid'])->setInc('usdt',$c2c['num']);        //买家加款
                             $trade->where("id=".$matchid)->setInc('trade',$c2c['num']);              //更新主订单成交量trade
                             $statis=$trade->where('id='.$matchid)->find();
                                if($statis['num']==$statis['trade']){                                 //主订单交易完成
                                    $matchdata['is_done']=1;
                                    $trade->where('id='.$matchid)->save($matchdata);
                                }
                            //挂买单
                            if($c2c['type']==1){
                                $interest=0;//预留C2C交易手续费
                                $ucoin->where("userid=".$c2c['sid'])->setDec('usdt',$c2c['num']);      //卖家减款
                            }
                            $this->ajaxReturn(array("status"=>1,"msg"=>"交易完成","url"=>'/c2c/index'));
                    }else{

                        $this->ajaxReturn(array("status"=>0,"msg"=>"投诉信息已经提交等待管理员审核!"));
                    }
            }
            return;
       }
    }

    public function c2cpay()
    {
		if (!userid()) {
			redirect('/#login');
		}
		$pay=M('user')->where('id='.userid())->field(['id,alipay,bankname,ddpay,ddpayname,ddmobile'])->find();
	
		$this->assign('s1',$pay);
		$this->display();
	}
   
	  public function c2cshop($market = 'bb', $type = NULL, $status = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}

		$uid=userid();
		
		$uuu = M('User')->find($uid);
		if($uuu['isshop']<1)
		{
			$this->error('您目前不是商家，暂无法使用该功能');
		}
		

		if (!$market_list[$market]) {
			//$market = $Market[0]['name'];
		}
		
		

		$where['market'] = $market;

		if (($type == 1) || ($type == 2)) {
			$where['type'] = $type;
		}

		if ($status >0) {
			$where['cstatus'] = $status;
		}
		
		
		
		$where['shop_id'] = $uid;
		
		$c2c_log = M('c2c_trade')->where($where)->select();

	
		$this->assign('c2c_log', $c2c_log);

		$where['userid'] = userid();
		$this->assign('market', $market);
		$this->assign('type', $type);
		$this->assign('status', $status);
		$Moble = M('Trade');
		$count = $Moble->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$Page->parameter .= 'type=' . $type . '&status=' . $status . '&market=' . $market . '&';
		$show = $Page->show();
		$list = $Moble->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['num'] = $v['num'] * 1;
			$list[$k]['price'] = $v['price'] * 1;
			$list[$k]['deal'] = $v['deal'] * 1;
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	
	
	public function mmset($id = NULL)
	{
		$info=M('c2c_trade')->where('id='.$id)->find();
		
		$this->assign('info',$info);
		
		if($info['shop_id']){
			$user=M('user')->where('id='.$info['shop_id'])->find();
			$this->assign('user',$user);
		}
		
		
		$this->display();
	}
	
		public function mmsetshop($id = NULL)
	{
		$info=M('c2c_trade')->where('id='.$id)->find();
		
		$this->assign('info',$info);
		
		if($info['shop_id']){
			$user=M('user')->where('id='.$info['shop_id'])->find();
			$this->assign('user',$user);
		}
		
		$this->display();
	}
		
	public function Huikuan($id = NULL)
	{
		$cl=M('c2c_trade');
		$info=$cl->where("id=$id")->find();
		
		if(($info['cstatus']==1)&&($info['stat']=1)){
			$info=$cl->where('id='.$id)->save(['cstatus'=>2,'paytime'=>time()]);
			$this->success('提交成功');
			//echo 'success';
		}
		/*
		elseif(($info['cstatus']==1)&&($info['stat']=2)){
			$info=$cl->where('id='.$id)->save(['status'=>3,'endtime'=>time()]);
			$this->success('提交成功');
		}
		*/
		
		$this->display();
	}

    public function chexiao($id = null)    //撤销订单
    {
        
        $ol = M('c2c_trade');
        $info = $ol->where(['id' => $id])->find();
		$num=round($info['num']*$price,8);
        if (!$info) {
            $this->error('交易订单不存在！');
        }
//0未匹配，1已匹配，2已打款，3已发货，4已完成，-1取消
        if (($info['cstatus'] != 1) && ($info['cstatus'] != 4)) {
            $this->error('订单已经处理过！');
        }
		
		
		$hbb=M('user_coin')->where('id='.$info['shop_id'])->fetchSql(true)->setInc('bb',$num);dump($hbb);die;

        if ($info['status'] == 0) {
			
			
            
			$rs = $ol->where(array('id' => $id))->save(array('status' => -1));
        }

        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }
    public function ordersell()
    {
        if (!userid()) {
            redirect('/#login');
        }

        $id = intval(I('get.id'));

        $info = M('c2c_log')->where(array('id' => $id))->find();
        $user = M('user')->where(array('id' => $info['matchid']))->find();
        $remark = M('otc_trade')->where(array('id' => $info['tradeid']))->find();

        if (!$info) {
            $this->error('交易订单不存在！');
        }
        $types=$info['type']==1?'买入':'卖出';

        $this->assign('mobile', $user['moble']);
        $this->assign('types', $types);
        $this->assign('names', strtoupper($info['market']));
        $this->assign('info', $info);
        $this->assign('remark', $remark['remark']);
        $this->display();
    }

    //有问题
    public function mmset1()
    {
        $id = intval(I('get.id'));

        if ($id) {
            $info = M('c2c_log')->where(array('id' => $id))->find();
            if (!$info) {
                $this->error('交易订单不存在！');
            }

            $user=M('user')->where("id=".$info['matchid'])->find();
            $this->assign('names', strtoupper($info['market']));
            $this->assign('info', $info);
            $this->assign('s1', $user['userid']);
            $this->assign('s2', $user['moble']);
        }
        $this->display();
    }

    public function upshop(){
        $id=userid();
        $user = M('user')->where('id=' . $id)->find();
		
		if($user['alipay']==""){
			$this->error('请先设置C2C支付和收款方式','/otc/otcpay');
		}

        if ($user['isshop'] == 1){
            $this->error('已加入商户');
        }elseif ($user['isshop'] == 0) {
            $up = M('user')->where('id=' . $id)->save(['isshop' => -1]);
            $this->success('申请成功');
        }


    }
	//C2C挂单
	public function add1()
    {
        if (!userid()) {
			redirect('/#login');
		}
		
        $uid = userid();
        $user=M('user')->where("id=".$uid)->field('isshop,idcardauth')->find();
		
    if ($user) {

        $wechat1=intval(I('post.wechat'));
        $alipay1=intval(I('post.alipay'));
        $bank1=intval(I('post.bank'));


		if($user['isshop']!=1){
			$this->ajaxReturn(array("status"=>0,"msg"=>"只有商家才能进行挂单操作"));
			exit;
		}

        if($wechat1!=1 and $alipay1!=1 and $bank1!=1){
            $this->ajaxReturn(array("status"=>0,"msg"=>"请选择支付方式"));
            exit;
        }

        $bank=M('user_bank')->where('userid='.$uid)->find();
        if($user['idcardauth']==0){
			$this->ajaxReturn(array("status"=>0,"msg"=>'实名认证尚未通过，请认证后再来。',"url"=>'/user/nameauth'));
			exit;
        }

		if(!$bank){
			$this->ajaxReturn(array("status"=>0,"msg"=>'请先设置C2C支付和收款方式',"url"=>'/user/bank'));
			exit;
		}

        if (C('market')[$market]['begintrade']) {
            $begintrade = C('market')[$market]['begintrade'];
        } else {
            $begintrade = "00:00:00";
        }

        if (C('market')[$market]['endtrade']) {
            $endtrade = C('market')[$market]['endtrade'];
        } else {
            $endtrade = "23:59:59";
        }

        $trade_begin_time = strtotime(date("Y-m-d") . " " . $begintrade);
        $trade_end_time = strtotime(date("Y-m-d") . " " . $endtrade);
        $cur_time = time();

        if ($cur_time < $trade_begin_time || $cur_time > $trade_end_time) {
            $this->ajaxReturn(array("status"=>0,"msg"=>'当前市场禁止交易,交易时间为每日' . $begintrade . '-' . $endtrade));
            exit;
        }
        unset($cur_time);
        
            $num = I('post.num');
            
            if ($num == null) $this->ajaxReturn(array("status"=>0,"msg"=>"请填写数量"));
            if (!is_numeric($num) or $num<0) $this->ajaxReturn(array("status"=>0,"msg"=>'请填写正确的数量'));

            $price = I('post.price');
            if ($price == null ) $this->ajaxReturn(array("status"=>0,"msg"=>"请填写价格"));
            if (!is_numeric($price) or $price<0) $this->ajaxReturn(array("status"=>0,"msg"=>"请填写正确价格"));
            
            if($user['isshop']==1){            //商家自定义最小成交量
                $minnum=I('post.minnum');
                if($minnum==null ) $this->ajaxReturn(array("status"=>0,"msg"=>'请填写最小成交数量'));
                if(!is_numeric($minnum) or $minnum<0 or $minnum>$num) $this->ajaxReturn(array("status"=>0,"msg"=>'请填写正确的成交数量'));
            }
            $type = I('post.type');
			$total = round($num*$price,8);
            if ($type == 2) {
                //插入卖出记录trade
		    	$ucoin=M('user_coin')->where('userid='.$uid)->field('usdt')->find();
		    	$interest=0;                            //调用C2C后台设置交易利息
				if ($ucoin['usdt']<($total+$interest)) 
				{
					$this->ajaxReturn(array("status"=>0,"msg"=>'USDT余额不足，暂无法卖出'));
					exit;
				}
				unset($ucoin);
				$code_num="S".rand(10000,99999).time();
                $data=array(
				'sid' => $uid, 
				'number'=>$code_num,            //订单号
				'market' => 'usdt',
				'price' => $price,               
				'cprice' => $total,             //总价
				'num' => $num, 
				'type' => 2,
				'isshop'=>$user['isshop'],
				'minnum'=>$minnum,
				'residue'=>$num,                    //剩余订单量
			//	'stat' => 2,
			//	'status' => 1, 
				'addtime' =>time(),
				'remark' =>"C2C交易-委托卖出-市场USDT",
				'cstatus' => 0,                   //0未匹配，1已匹配，2已打款，3已发货，4已完成，-1取消
			    'match_id' =>",",                  
			    'mid'=>$uid,                   //主订单
				'is_pay' => 0,                    //是否交易
                'is_ts' => 0,                    //0默认值  1确认收款 2投诉
				'is_ship' => 0,                   //是否发货
				'is_done' => 0,                   //是否收货完成
		        'is_timeout'=>0,                  //是否超时
		        'is_lock'=>0,                       //交易锁定
		        'is_cancel'=>0,                   //是否撤销订单
                'wechat'=>$wechat1,
                'alipay'=>$alipay1,
                'bank'=>$bank1,
				);
			    $result=M('c2c_trade')->add($data);
                if ($result) {
					//扣除USDT余额
					M('user_coin')->where("userid=".$uid)->setDec('usdt',$total);
                    $this->ajaxReturn(array("status"=>1,"msg"=>'挂卖订单提交成功'));
                } else {
                    $this->ajaxReturn(array("status"=>0,"msg"=>'挂卖订单提交失败'));
                }
            } elseif ($type == 1) {
			    //插入买入记录表trade
			    $code_num="B".rand(10000,99999).time();
                $data=array(
				'market' => 'usdt',
				'number'=>$code_num,            //编号
				'price' => $price, 
				'cprice' => $total,
				'num' => $num, 
				'type' => 1,
				'isshop'=>$user['isshop'],
				'minnum'=>$minnum,
				'residue'=>$num,
			//	'stat' => 1,
			//	'status' => 2, 
				'addtime' => time(),
				'remark' =>"C2C交易-委托买入-市场USDT",
			    'cstatus' => 0,                   //0未匹配，1已匹配，2已打款，3已发货，4已完成，-1取消
			    'match_id' =>",",                  //主订单
			    'mid'=>$uid,
				'is_pay' => 0,                    //是否交易
                'is_ts' => 0,                    //0默认值  1确认收款 2投诉
 				'is_ship' => 0,                   //是否发货
				'is_done' => 0,                   //是否收货完成
	            'is_timeout'=>0,                  //是否超时
	            'is_cancel'=>0,                   //是否撤销订单
	            'is_lock'=>0,                     //交易锁定
				'bid' => $uid,                    //买家ID
                'wechat'=>$wechat1,
                'alipay'=>$alipay1,
                'bank'=>$bank1,
				);
				$result=M('c2c_trade')->add($data);
				if ($result) {
                    $this->ajaxReturn(array("status"=>1,"msg"=>'买入订单提交成功'));
                } else {
                    $this->ajaxReturn(array("status"=>0,"msg"=>'买入订单提交失败'));
                }
			}
			/*
			{
			
                $find = $clm->where("status=1 and type=2 and price<=$price and num>=$num and userid!=$uid and s1=0")->select();//找log订单


                if (!$find) $this->error('没有合适的订单');

                foreach ($find as $v) {
                
                    $fid[] = $v['id'];
                }
                $fids = $fid[array_rand($fid, 1)];

                $sell = $clm->where('id=' . $fids)->find();//匹配订单
                $sells1 = $clm->where('id=' . $sell['id'])->save(['s1' => 1]);

                $selltype = $sell['type'] == 1 ? '买入' : '卖出';

                if ($num == $sell['num']) {

                    //买委托订单
                    $buyt = $ctm->add(['userid' => userid(), 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'stat' => 1, 'status' => 2, 'addtime' => $cur_time,]);

                    if (!$buyt) $this->error('提交失败');

                    //买交易订单
                    $buyl = $clm->add(['userid' => userid(), 'matchid' => $sell['userid'], 'tradeid' => $buyt, 'ppid' => $sell['id'], 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'name' => 'c2c-bb商户' . $types . '交易', 'stat' => 1, 'status' => 2, 'addtime' => $cur_time, 'mptime' => time()]);

                    if ($buyl){
                        //修了卖交易记录
                        $sells = $clm->where('id=' . $sell['id'])->save(['matchid' => userid(), 'ppid' => $buyl,'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'status' => 2,'mptime' => time()]);
                        $sellts=$ctm->where('id=' . $sell['id'])->save(['status'=>2]);
                        if ($sells && $sellts) {
                            $this->success('提交成功');
                        }else{
                            $buylrollbak = $clm->where('id=' . $buyl)->save(['status' => -1]);
                            $buytrollbak = $ctm->where('id=' . $buyt)->save(['status' => -1]);
                            $selllsrollbak = $ctm->where('id=' . $sell['id'])->save(['status' => 1]);
                            $this->error('提交失败');
                        }
                    }else{
                        $buyts = $ctm->where('id='.$buyt)->save(['status'=>-1]);
                        $this->error('提交失败');
                    }

                } elseif ($num <= $sell['num']) {

                    $curnum = $sell['num'] - $num;

                    $buynewt = $ctm->add(['userid' => userid(), 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'stat' => 1, 'status' => 2, 'addtime' => $cur_time,]);

                    $buynewl = $clm->add(['userid' => userid(), 'tradeid' => $buynewt, 'ppid' => $sell['id'], 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'name' => 'c2c-usdt商户' . $types . '交易', 'stat' => 1, 'status' => 2, 'addtime' => $cur_time, 'mptime' => time()]);

                    if ($buynewt && $buynewl) {
                        $sellnew = $clm->add(['userid' => $sell['userid'], 'matchid' => userid(), 'tradeid' => $sell['tradeid'], 'ppid' => $buynewl, 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $sell['type'], 'name' => 'c2c-usdt商户' . $selltype . '交易', 'stat' => $sell['stat'], 'status' => 2, 'addtime' => $cur_time, 'mptime' => time()]);echo $sellnew;
                        $selllow=$clm->where('id='.$sell['id'])->save(['numd'=>$num,'num'=>$curnum,'s1'=>0]);
                        if ($sellnew && $selllow) {
                            $buynewls=$clm->where('id='.$buynewl)->fetchSql(true)->save(['matchid' => $sellnew]);
                            echo $buynewls;die;
                            $this->success('提交成功');
                        }else{
                            $buynewtrobk=$ctm->where('id='.$buynewt)->save(['status'=>-1]);
                            $buynewltrobk=$ctm->where('id='.$buynewl)->save(['status'=>-1]);
                            $selllowrobk=$ctm->where('id='.$sell['id'])->save(['stat'=>0]);
                            $this->error('提交失败');
                        }
                    } else {
                        $sellyrobk=$ctm->where('id='.$sell['id'])->save(['stat'=>0]);
                        $this->error('提交失败');
                    }

                }
            }
			*/
        }
    }
}
