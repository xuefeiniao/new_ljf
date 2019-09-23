<?php
namespace Wap\Controller;

class C2cController extends HomeController {

    //c2c主页
    public function index()
    {
		date_default_timezone_set('PRC');
			
        if (userid()) {
            $user = M('User')->where(array('id' => userid()))->find();
			//判断支付方式			
			$bank=M('user_bank')->where('userid='.userid())->find();							
			
			if($bank){			
				$where .= ' bank=1';
			}
			if($user['weixin_img']){				
				$where .= ' OR wechat=1';
			}
			if($user['ali_img']){
			
				$where .= ' OR alipay=1';
			}			
			if(strpos($where,'O')==1){
				 $str = substr($where,3);
				 $where = '('.$str;
			}else{
				$where = '('.$where;
			}	
			
			$where .= ') and (is_cancel=0 and is_pay=0 and residue>0)';
	
			//$where = 'is_cancel=0 and is_pay=0 and residue>0';
            $stat = $user['senior_past'];		
			$uid=userid();
			    /* $ucoinres=M('c2c_trade')->where("(bid=$uid or sid=$uid)and is_pay=1")->order("type,paytime DESC")->select();        //当前订单
                
                $ucoinres1=M('c2c_trade')->where("(bid=$uid or sid=$uid)and is_done=1")->order("type,paytime DESC")->select();       //已完成订单   
                $ucoinres2=M('c2c_trade')->where("(bid=$uid or sid=$uid)and is_cancel=1")->order("type,paytime DESC")->select();     //取消订单   
                $ucoinres3=M('c2c_trade')->where("(bid=$uid or sid=$uid)and is_ts=2")->order("type,paytime DESC")->select();         //已申诉订单
				*/
                $ucoinres1=M('c2c_trade')->where("(bid=$uid or sid=$uid) and (is_done=1 OR is_cancel=1 OR is_ts=2)")->order("type,paytime DESC")->select();       //已完成订单
                			 
                //$shop=M('c2c_trade')->where("(bid=$uid or sid=$uid)and mid>0 and (is_cancel=0 OR is_pay=1)")->select();
                $shop=M('c2c_trade')->where("(bid=$uid or sid=$uid) and (is_cancel=0 OR (is_pay=1 And (is_done=0 OR is_ts=0 OR is_get=0))")->order('id desc')->select();
				//$this->assign('myucoin',$ucoinres);
				//$this->assign('myucoin1',$ucoinres1);
				//$this->assign('myucoin2',$ucoinres2);
				//$this->assign('myucoin3',$ucoinres3);	
          		$this->assign('shop',$shop);
				$this->assign('uid',$uid);
				$this->assign('user',$user);
				$this->assign('stat', $stat);	
				$ucoin=M('c2c_trade')->where($where)->order("type,price")->select();								
        }else{
			
				$ucoin=M('c2c_trade')->where('is_cancel=0 and is_pay=0 and residue>0')->order("type,price")->select();			
		}
		$min_price=M('c2c_trade')->where('is_done=1 and is_pay=1 and is_get=1 and is_ts=1')->field('min(price)as min')->find();	//c2c最低价
		$max_price=M('c2c_trade')->where('is_done=1 and is_pay=1 and is_get=1 and is_ts=1')->field('max(price)as max')->find();	//c2c最高价
		$time_price=M('c2c_trade')->where('is_done=1 and is_pay=1 and is_get=1 and is_ts=1')->order('endtime desc')->find();	//最新价
		//$buy=M('c2c_trade')->where('type=1 and is_done=0 and is_pay=0 and is_get=0 and mid>0')->field('max(number) as a,price')->find();			//买入价格
		//$sell=M('c2c_trade')->where('type=2 and is_done=0 and is_pay=0 and is_get=0 and mid>0')->field('max(number) as a,price')->find();								//卖出价格

		
		$buy=M('c2c_trade')->where('is_cancel=0 and type=2 and is_done=0 and is_pay=0 and is_get=0 and mid>0 and residue>0')->field('min(price) as a,price')->find();			//买入价格
		$sell=M('c2c_trade')->where('is_cancel=0 and type=1 and is_done=0 and is_pay=0 and is_get=0 and mid>0 and residue>0')->field('max(price) as a,price')->find();								//卖出价
				
		//计算日涨跌
		$data1=date('Y-m-d',time());
		$data2=strtotime($data1);
		$ztend=$data2-1;
		
		$data=date("Y-m-d",strtotime("-1 day"));
		$ztbend=strtotime($data);
		
		$shoupan=M('c2c_trade')->where('is_done=1 AND gettime>='.$ztbend.' AND gettime<='.$ztend)->order('gettime DESC')->find();
		
		if($shoupan){
		    $zhangdie=($time_price['price']-$shoupan['price'])/$shoupan['price']*100;
		}else{
		    $zhangdie=0;
		}
		
		
		$myusdt = M('user_coin')->where('userid='.userid())->getField('usdt');
		$data = array('myusdt'=>$myusdt,'maxprice'=>$max_price['max'],'minprice'=>$min_price['min'],'newprice'=>$time_price['price'],'buy'=>$buy['a'],'sell'=>$sell['a'],'zhangdie'=>$zhangdie,'isshop'=>$user['senior_past']);
        
		if($ucoin){
		    foreach ($ucoin as &$v){
		        if($v['bid']){
		            $v['shop_name'] = M("user")->where('id='.$v['bid'])->getField('truename');
		        }else{
		            $v['shop_name'] = M("user")->where('id='.$v['sid'])->getField('truename');
		        }
		    }
		}
		
		if($ucoinres1){
		    foreach ($ucoinres1 as &$v){
		        if($v['bid']){
		            $v['shop_name'] = M("user")->where('id='.$v['bid'])->getField('truename');
		        }
		    }
		}
		
		if($shop){
		    foreach ($shop as &$v){
		        if($v['bid']){
		            $v['shop_name'] = M("user")->where('id='.$v['bid'])->getField('truename');
		        }else{
		            $v['shop_name'] = M("user")->where('id='.$v['sid'])->getField('truename');
		        }
		    }
		}
		
		$this->ajaxReturn(
		    array(
		        'code'    =>  200,
		        'result'  =>  '获取成功',
		        'body'=>array(
		            'myucoin'   =>  $ucoin,//市场挂单
		            
		            //'myucoin' =>  $ucoinres,
		            'myucoin1' =>  $ucoinres1,//历史挂单
		            //'myucoin2' =>  $ucoinres2,
		            //'myucoin3' =>  $ucoinres3,
		            'shop' =>  $shop,//最下面我的挂单，当前订单
		            /* 'newprice'=>$time_price['price'],//实时价
		            'buy'=>$buy['price'],//买入估价
		            'sell'=>$sell['price'],//卖出估价
		            'myusdt'  =>  $myusdt,//我的可用usdt余额 */
		            /* 'user' =>  $user,
		            'stat' =>  $stat,
		            'data' =>  $data, */
		            'data' =>  $data,
		
		        )
		    ));
		$this->display();
    }
	
	
    
    
    //c2c操作买入订单-->当前用户卖出
    public function buyac(){

        if (!userid()) {
			$this->ajaxReturn(array("status"=>0,"msg"=>"请先登录帐号"));
            redirect('/#login');
        }
        $number=I('post.num');                        //当前用户卖出数量获得
        $uid=userid();                                //当前用户ID
        $buyid=I('post.bid');                         //买家ID
        $id=I('post.id');                             //订单ID

        $trade=M('c2c_trade')->where("id=".$id)->find();
        $ucoin=M('user_coin')->where("userid=".$uid)->getField('usdt');


       
        if($uid==$buyid){
            $this->ajaxReturn(array("status"=>0,"msg"=>"不能匹配自己的订单"));
            exit;
        }
		
		if($number<=0){			
			$this->ajaxReturn(array("status"=>0,"msg"=>"请输入正确的数量"));
            exit;
		}
		
		 if($number>$trade['residue']){
            $this->ajaxReturn(array("status"=>0,"msg"=>"卖出USDT大于订单数量"));
            exit;
        }

		
		$interest=0;                            //调用C2C后台设置交易利息
        if($ucoin<($number+$interest)){
            $this->ajaxReturn(array("status"=>0,"msg"=>"账号USDT数量不足,请充值"));
            exit;
        }

        if($trade['residue']>=$trade['minnum']){            //minnum最小成交量   //residue订单剩余成交量

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
                $code_num="B".rand(100000,999999);
                $user=M('user')->where('id='.$uid)->find();
                $data['number']=$code_num;
                $data['market']='USDT';
                $data['sid']=$uid;
                $data['price']=$trade['price'];
                $data['cprice']=round($trade['price']*$number,8);
                $data['num']=$number;
				$data['minnum']=$trade['minnum'];
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
					M('user_coin')->where("userid=".$uid)->setDec('usdt', $number);		//存入冻结金额
					M('user_coin')->where("userid=".$uid)->setInc('usdtc', $number);		//存入冻结金额
                    $marchid = M("c2c_trade")->where('id=' . $id)->getField('match_id');
                    $marchid = $marchid . $result . ",";
                    M("c2c_trade")->where('id=' . $id)->setField('match_id', $marchid);
                    $this->ajaxReturn(array("status"=>1,"msg"=>'卖出USDT成功','url'=>$result));
                }else{
                    $this->ajaxReturn(array("status"=>0,"msg"=>'卖出USDT失败'));
                }

    }
    //操作卖出订单
   public function sellac(){

    if (!userid()) {
		$this->ajaxReturn(array("status"=>0,"msg"=>"请先登录帐号"));
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
	
	
		if($number<=0){			
			$this->ajaxReturn(array("status"=>0,"msg"=>"请输入正确的数量"));
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
    $code_num="S".rand(100000,999999);
    $user = M('user')->where('id=' . $uid)->find();
    $data['number'] = $code_num;
    $data['market']='USDT';
    $data['sid'] = $trade['sid'];
    $data['price'] = $trade['price'];
    $data['cprice'] = round($trade['price'] * $number,8);
    $data['num'] = $number;
	$data['minnum']=$trade['minnum'];
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
        $this->ajaxReturn(array("status"=>1,"msg"=>'买入USDT成功','url'=>$result));
    } else {
        $this->ajaxReturn(array("status"=>0,"msg"=>'买入USDT失败'));
    }
}
    //C2C交易订单详情
    public function deal(){

         if (!userid()) {
            //redirect('/#login');
            $this->error('请先登陆');
            
        }
        $id = intval(I('get.id'));
        $uid=userid();
        $trade = M("c2c_trade")->where('id='.$id)->find();
        if (!$trade) {
            $this->ajaxReturn(array("status"=>204,"msg"=>"交易订单不存在"));
            //echo "<script> layer.msg('交易订单不存在',{icon : 2 });</script>";
			//exit;
        }
		//$timeout=($trade['paytime']+(1*60))*1000;
        $timeout=($trade['paytime']+((40+30)*60));//30分钟未付款订单取消（服务器时间慢40分钟）
		$res=M("user")->where("id=".$trade['bid'])->field('truename,moble')->find();//卖方界面-买方信息
		$res1=M("user")->where("id=".$trade['sid'])->field('truename,moble,weixin_img,ali_img')->find();//买家界面-卖方信息
		
		$bank=M("user_bank")->where("userid=".$trade['sid'])->field('bankuname,bank,bankaddr,bankcard')->order("addtime DESC")->find();//卖方卡信息
		$this->ajaxReturn(
		    array(
		        'code'    =>  200,
		        'result'  =>  '获取成功',
		        'body'=>array(
		            'timeout'   =>  $timeout,
		            'uid' =>  $uid,
		            'c2c' =>  $trade,
		            'res' =>  $res,//付款方信息
		            'res1' =>  $res1,//付款方信息
		            'bank' => $bank
		        )
		    ));
    }
    //C2C买家确认打款
    public function confirm_pay(){
        if (!userid()) {
			$this->ajaxReturn(array("status"=>0,"msg"=>"请先登录帐号"));
            redirect('/#login');
        }
        if (IS_POST) {
            $id = I('post.id');
            if (!is_numeric($id)) {
                $this->ajaxReturn(array('status'=>0,'msg'=>'无效订单'));               
				exit;
            }
			
			$pay = I('post.pay');
            if ($pay==0) {
               $this->ajaxReturn(array('status'=>0,'msg'=>'请选择打款方式'));
                exit;
            }				
            $message = I('post.message');
            $img = I('post.img');
            if (!$img) {
                $this->ajaxReturn(array('status'=>0,'msg'=>'请上传打款凭证'));
                exit;
            }
            $result = M('c2c_trade')->where("id=" . $id)->find();

            //var_dump($result);exit;
            if ($result) {
                $data['img'] = $img;
                $data['message'] = $message;
                $data['is_get'] = 1;
                $data['gettime'] = time();
                $data['id'] = $id;
				$data['pay']=$pay;
                $res = M('c2c_trade')->save($data);
                
                if ($res !== false) {
                    $this->ajaxReturn(array('status'=>1,'msg'=>'打款成功,等待卖家确认收款'));
                } else {
                    $this->ajaxReturn(array('status'=>0,'msg'=>'确认提交失败'));
                }
            }
			return;
        }
    }
    
    //c2c上传交易凭证
    public function upimg()
    {

        $upload = new \Think\Upload();
        $upload->maxSize = 2048000;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath = './Upload/idcard/';
        //$upload->rootPath = 'http://lhpc.finsys.net/Upload/idcard/';
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
		
		if (!userid()) {
			$this->ajaxReturn(array("status"=>0,"msg"=>"请先登录帐号"));
			redirect('/#login');
		}   

        $trade=M('c2c_trade');
        $ucoin=M('user_coin');
       if(IS_POST ){
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
            if($res){  
			//判断是否有这个订单
                $trade->where($where)->save($data);
                    $c2c=$trade->where("id=".$id." and is_pay=1 and is_done=1 and is_get=1 and is_cancel=0 and is_ts=1")->find();
                    if($c2c){
							 $interest=0;//预留C2C交易手续费						
                             $matchid=$c2c['match_id'];                                               //主订单id
							 $ucoin->where("userid=".$c2c['sid'])->setDec('usdtc',$c2c['num']);      //卖家冻结减款	
                             $ucoin->where("userid=".$c2c['bid'])->setInc('usdt',$c2c['num']);        //买家加款
								
							//写入记录
                           					
								$wallet=M('user_coin')->where("userid=".$c2c['sid'])->getField('usdt');
								parent::addCashhistory($c2c['sid'],1,2,"卖出",$c2c['num']."USDT",$c2c['price'],$wallet."USDT","C2C卖出".$c2c['number']);
                          
								$wallet=M('user_coin')->where("userid=".$c2c['bid'])->getField('usdt');
								parent::addCashhistory($c2c['bid'],1,1,"买入",$c2c['num']."USDT",$c2c['price'],$wallet."USDT","C2C买入".$c2c['number']);
							
							
							 
								 $trade->where("id=".$matchid)->setInc('trade',$c2c['num']);              	//更新主订单成交量trade
								 $statis=$trade->where('id='.$matchid)->find();
                                if($statis['num']==$statis['trade']){                                 		//主订单交易完成
                                    $matchdata['is_done']=1;
									$matchdata['endtime']=time();
                                    $trade->where('id='.$matchid)->save($matchdata);
                                }
							
                            $this->ajaxReturn(array("status"=>1,"msg"=>"交易完成","url"=>'/c2c/index'));
                    }else{
						
						 $c2clog=$trade->where("id=".$id." and is_done=0 and is_cancel=0 and is_ts=2")->find();
											 
								$data['uid']=$c2clog['id'];
								$data['number']=$c2clog['number'];
								$data['sid']=$c2clog['sid'];
								$data['market']=$c2clog['market'];
								$data['price']=$c2clog['price'];
								$data['cprice']=$c2clog['cprice'];
								$data['num']=$c2clog['num'];
								$data['minnum']=$c2clog['minnum'];
								$data['type']=$c2clog['type'];	
								$data['ts_time']=time();
								$data['bid']=$c2clog['bid'];
								$data['is_ts']=$c2clog['is_ts'];
								$data['img']=$c2clog['img'];
								$data['pay']=$c2clog['pay'];
								$res=M('c2c_record')->add($data);	
								if($res){									
									$this->ajaxReturn(array("status"=>1,"msg"=>"投诉信息已经提交等待管理员审核!"));
								}
							 }
											                   
                    }
            }
            return;
       }
  
  //C2C后台成交记录
    public function mywcc2c(){

		   
      if (!userid()) {
		//	$this->ajaxReturn(array("status"=>0,"msg"=>"请先登录帐号"));
			redirect('/#login');
		}      		
			$user = M('User')->where(array('id' => userid()))->find();
			$stat = $user['isshop'];		
			$uid=userid();


			$static=I('curr_static');
			$type=I('curr_type');
			$where['is_pay']=1;
			$str="bid=".$uid." or sid=".$uid;
			if($type==1){$str="bid=".$uid;}					//买入
			if($type==2){$str="sid=".$uid;}					//卖出
			if($static==1){$where['is_pay']=1;$where['is_done']=0;}				//当前订单
			if($static==2){$where['is_done']=1;}			//已完成
			if($static==3){$where['is_cancel']=1;}			//已取消
			if($static==4){$where['is_ts']=2;}			    //投诉订单					


			$Moble=M('c2c_trade');
			$count = $Moble->where($where)->where($str)->count();
			$Page = new \Think\Page($count, 15);
			$show = $Page->show();
			$list = $Moble->where($where)->where($str)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('list', $list);
			$this->assign('page', $show);
			$this->assign('uid',$uid);
			$this->assign('status',$static);
			$this->assign('type',$type);
			$this->display();  
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
			$this->ajaxReturn(array("status"=>0,"msg"=>"请先登录帐号"));
			redirect('/#login');
		}
		
        $uid = userid();
        $user=M('user')->where("id=".$uid)->field('isshop,idcardauth,weixin_img,ali_img')->find();
		
    if ($user) {

        $wechat1=intval(I('post.wechat'));
        $alipay1=intval(I('post.alipay'));
        $bank1=intval(I('post.bank'));
		
        
        $bank=M('user_bank')->where('userid='.$uid)->find();
		$bank=M('user_bank')->where('userid='.$uid)->find();
		
		if(!$bank){
			if(!$user['ali_img']){
				if(!$user['weixin_img']){
					$this->ajaxReturn(array("status"=>0,"msg"=>'请先进行支付设置',"url"=>'/user/zhifushezhi'));
					exit;
				}
			}	
		}
		
		
		if($wechat1==1 and !$user['weixin_img']){
			$this->ajaxReturn(array("status"=>0,"msg"=>'请设置微信支付方式',"url"=>'/user/zhifushezhi'));
			exit;
		}
		if($alipay1==1 and !$user['ali_img']){
			$this->ajaxReturn(array("status"=>0,"msg"=>'请设置支付宝支付方式',"url"=>'/user/zhifushezhi'));
			exit;
		}
		if($bank1==1 and !$bank){
			$this->ajaxReturn(array("status"=>0,"msg"=>'请设置银行卡支付方式',"url"=>'/user/zhifushezhi'));
			exit;
		}
		
		
		
        if($wechat1!=1 and $alipay1!=1 and $bank1!=1){
            $this->ajaxReturn(array("status"=>0,"msg"=>"请选择支付方式"));
            exit;
        }
      
        if($user['idcardauth']==0){
			$this->ajaxReturn(array("status"=>0,"msg"=>'实名认证尚未通过，请认证后再来。',"url"=>'/user/realname'));
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
            
            if ($num == null
			) $this->ajaxReturn(array("status"=>0,"msg"=>"请填写数量"));
            if (!is_numeric($num) or $num<0) $this->ajaxReturn(array("status"=>0,"msg"=>'请填写正确的数量'));

            $price = I('post.price');
            if ($price == null ) $this->ajaxReturn(array("status"=>0,"msg"=>"请填写价格"));
            if (!is_numeric($price) or $price<0) $this->ajaxReturn(array("status"=>0,"msg"=>"请填写正确价格"));
            
           //if($user['isshop']==1){
               $minnum=I('post.minnum');
               if($minnum==null ) $this->ajaxReturn(array("status"=>0,"msg"=>'请填写最小成交数量'));
               if(!is_numeric($minnum) or $minnum<0 or $minnum>$num) $this->ajaxReturn(array("status"=>0,"msg"=>'请填写正确的成交数量'));
           /*}else{
               $minnum=1;
           }*/
			
            $type = I('post.type');
			$total = round($num*$price,8);
            if ($type == 2) {
                //插入卖出记录trade
		    	$ucoin=M('user_coin')->where('userid='.$uid)->field('usdt')->find();
		    	$interest=0;                            //调用C2C后台设置交易利息
				if ($ucoin['usdt']<($num+$interest)) 
				{
					$this->ajaxReturn(array("status"=>0,"msg"=>'USDT余额不足，暂无法卖出'));
					exit;
				}
				unset($ucoin);
				
				//自动匹配订单
				$sign=$this->autodeal($num,$price,2,$wechat1,$alipay1,$bank1);
				if($sign>1){
					$this->ajaxReturn(array("status"=>1,"msg"=>'卖自动匹配成功','id'=>$sign));
					exit;
				}
				
				$code_num="S".rand(100000,999999);
                $data=array(
				'sid' => $uid, 
				'number'=>$code_num,            //订单号
				'market' => 'USDT',
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
				'remark' =>"C2C交易-挂单卖出-市场USDT",				
			    'match_id' =>",",              //子订单ID序列    
			    'mid'=>$uid,                   //主订单
				'is_pay' => 0,                    //是否交易
                'is_ts' => 0,                    //0默认值  1确认收款 2投诉
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
					M('user_coin')->where("userid=".$uid)->setDec('usdt',$num);  //卖出手续费预留
					M('user_coin')->where("userid=".$uid)->setInc('usdtc', $num);//存入冻结金额
					$wallet=M('user_coin')->where("userid=".$uid)->getField('usdt');
					parent::addCashhistory($uid,1,2,"卖出",-$num."USDT",$price,$wallet."USDT","C2C挂单卖出".$code_num);
                    $this->ajaxReturn(array("status"=>1,"msg"=>'挂卖订单提交成功'));	
                } else {
                    $this->ajaxReturn(array("status"=>0,"msg"=>'挂卖订单提交失败'));
                }
            } elseif ($type == 1) {
			    
				//自动匹配订单
				$sign=$this->autodeal($num,$price,1,$wechat1,$alipay1,$bank1);
				if($sign>1){
					//$this->ajaxReturn(array("status"=>1,"msg"=>'自动匹配成功','url'=>"/C2c/deal/id/".$sign));
					$this->ajaxReturn(array("status"=>1,"msg"=>'买自动匹配成功','id'=>$sign));
					exit;
				}
				
				//插入买入记录表trade
			    $code_num="B".rand(100000,999999);
                $data=array(
				'market' => 'USDT',
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
				'remark' =>"C2C交易-挂单买入-市场USDT",
			    'match_id' =>",",                  //主订单
			    'mid'=>$uid,
				'is_pay' => 0,                    //是否交易
                'is_ts' => 0,                    //0默认值  1确认收款 2投诉
				'is_done' => 0,                   //是否完成
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
					$wallet=M('user_coin')->where("userid=".$uid)->getField('usdt');
					parent::addCashhistory($uid,1,1,"买入",$num."USDT",$price,$wallet."USDT","C2C挂单买入".$code_num);
					$this->ajaxReturn(array("status"=>1,"msg"=>'买入订单提交成功'));
                } else {
					$this->ajaxReturn(array("status"=>0,"msg"=>'挂买订单提交失败'));
                }
			}
			
        }
    }
	//自动匹配买入订单()
	
	//c2c自动买入订单-->用户卖出
	public function autodeal($num,$price,$type,$wechat,$alipay,$bank){
    
		$number=$num;                        	//当前用户卖出数量获得
        $uid=userid();                       	//当前用户ID
	
		//自动匹配已设置支付方式的订单
		
		if($bank==1){			
			$where .= ' bank=1';
		}
		if($wechat==1){				
			$where .= ' OR wechat=1';
		}
		if($alipay==1){
		
			$where .= ' OR alipay=1';
		}			
		if(strpos($where,'O')==1){
			 $str = substr($where,3);
			 $where = '('.$str;
		}else{
			$where = '('.$where;
		}	

		if($type==1){						    
			
			$where.=') and ( type=2 and is_done=0 and mid>0 and is_cancel=0 and residue>='.$number." and price<=".$price." and minnum<=".$number.")";
		$where2='price ASC';
		}else{
			
			$where.=') and ( type=1 and is_done=0 and mid>0 and is_cancel=0 and residue>='.$number." and price>=".$price." and minnum<=".$number.")";
		$where2='price DESC';
        }

		$res=M('C2c_trade')->where($where)->order($where2)->select();
		if($res){
				foreach($res as $val){
				
					$id=$val['id'];                             //订单ID

					if($type==1 and $uid==$val['sid']){
						//$this->ajaxReturn(array("status"=>0,"msg"=>"不能匹配自己的订单"));
						continue;
					}
					
					if($type==2 and $uid==$val['bid']){
						//$this->ajaxReturn(array("status"=>0,"msg"=>"不能匹配自己的订单"));
						continue;
					}

					$interest=0;                            //调用C2C后台设置交易利息

					//判断订单是否匹配成功
					$trade=M('c2c_trade')->where("id=".$id)->find();
					
					$data=array();
					if($trade['residue']>=$number){	
						
							if($type==1){
								//买匹配卖单
								$code_num="S".rand(100000,999999);
								$user = M('user')->where('id=' . $uid)->find();
								$data['number'] = $code_num;
								$data['market']='USDT';
								$data['sid'] = $trade['sid'];
								$data['price'] = $trade['price'];
								$data['cprice'] = round($trade['price'] * $number,8);
								$data['num'] = $number;
								$data['minnum']=$trade['minnum'];
								$data['type'] = $trade['type'];			//2
								$data['isshop'] = $user['senior_past'];
								$data['remark'] = "C2C交易-匹配买入-市场USDT";
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
									
									return $result;
								}else{
									continue;
								} 
									
							
							}elseif($type==2){
								//卖匹配买单
									$code_num="B".rand(100000,999999);
									$user=M('user')->where('id='.$uid)->find();
									$data['number']=$code_num;
									$data['market']='USDT';
									$data['sid']=$uid;
									$data['price']=$trade['price'];
									$data['cprice']=round($trade['price']*$number,8);
									$data['num']=$number;
									$data['minnum']=$trade['minnum'];
									$data['type']=$trade['type'];						//1		
									$data['isshop']=$user['senior_past']; 				//是否高级认证
									$data['remark']="C2C交易-匹配卖出-市场USDT";
									$data['addtime']=$trade['addtime'];
									$data['paytime']=time();
									$data['bid']=$trade['bid'];
									$data['is_pay']=1;
									$data['is_ts'] = 0;                    				//0默认值  1确认收款 2投诉
									$data['is_cancel']=0;
									$data['match_id'] =$trade['id'];
									$data['wechat'] = $trade['wechat'];
									$data['alipay'] = $trade['alipay'];
									$data['bank'] = $trade['bank'];
									$result=M('c2c_trade')->add($data);
										if($result){
											M("c2c_trade")->where('id='.$id)->setDec('residue',$number);            //当前匹配订单更新订单量
											M('user_coin')->where("userid=".$uid)->setDec('usdt', $number);			//减少USDT金额
											M('user_coin')->where("userid=".$uid)->setInc('usdtc', $number);		//存入USDT冻结金额
											$marchid = M("c2c_trade")->where('id=' . $id)->getField('match_id');
											$marchid = $marchid . $result . ",";
											M("c2c_trade")->where('id=' . $id)->setField('match_id', $marchid);								
											return $result;
										}else{
											continue;
										}
							}	
					}else{	
						continue;
					}						
				}		
			}else{
				
				return 0;
			}	
		
	}
	//超时自动撤销订单
	public function chaoshi(){
	
		$id=I('post.id');	
		$tarde=M('c2c_trade');
		$coin=M('user_coin');
		$res=$tarde->where("is_cancel=0 and id=".$id)->find();		    //子订单
		
		if($res){	
		
			$match=$tarde->where('id='.$res['match_id'])->find();		//主订单
			
			if($match){
							
				$tarde->where('id='.$res['match_id'])->setInc('residue',$res['num']);
				
					
				if($res['type']==1){
					
					$ins=$res['bid'];
				
				}else{
					$ins=$res['sid'];
				
					
				}
				$wallet=M('user_coin')->where("userid=".$ins)->getField('usdt');
				parent::addCashhistory($ins,1,3,"打款超时",0,$res['price'],$wallet."USDT","订单打款超时重新挂单");
				
				if($res['type']==2){					
					$wallet=M('user_coin')->where("userid=".$res['bid'])->getField('usdt');
				parent::addCashhistory($res['bid'],1,3,"打款超时",0,$res['price'],$wallet."USDT","订单打款超时被取消");
				}
				
				$tarde->where('id='.$id)->setField('is_cancel',1);
				if($match['type']==1){
					$coin->where('userid='.$res['sid'])->setDec('usdtc',$res['num']);
					$coin->where('userid='.$res['sid'])->setInc('usdt',$res['num']);
					//退回冻结usdt
					$wallet=M('user_coin')->where("userid=".$res['sid'])->getField('usdt');
					parent::addCashhistory($res['sid'],1,3,"超时撤销",'+'.$res['num']."USDT",$res['price'],$wallet."USDT","订单超时撤销退款".$res['num']);
				}
				$this->ajaxReturn(array('status'=>1,'info'=>"订单超时被撤销"));
				
				
			}else{
				
				$this->ajaxReturn(array('status'=>0,"info"=>"订单不存在"));
				
			}
			
		}else{
			$this->ajaxReturn(array('status'=>0,"info"=>"订单不存在"));
		}
			
	}
	
	//商家订单撤销	
	public function delete(){		
	if(IS_AJAX){
		$id =I('post.id');
    	$tarde=M('c2c_trade');
    	$ucoin=M('user_coin');    
		$res=$tarde->where("id=".$id)->find();
		if($res){	
          //主订单卖出---只返回未匹配的剩余订单金额			
			if($res['is_done']!==1){
				if($res['residue']!=0){
					if($res['type']==2){
						
							$data['is_cancel']=1;		
							$result=$tarde->where("id=".$id)->save($data);
						
						if($result!==false){                	       
								$res1=$ucoin->where("userid=".$res['sid'])->setDec("usdtc",$res['residue']);	
								$res1=$ucoin->where("userid=".$res['sid'])->setInc("usdt",$res['residue']);								
								$wallet=M('user_coin')->where("userid=".$res['sid'])->getField('usdt');
								parent::addCashhistory($res1['sid'],1,3,"撤销",$res1['residue'],0,$wallet."USDT","C2C挂单卖出撤单".$res['number']);
							if($res1){
								$this->ajaxReturn(array('status'=>1,"msg"=>"卖出挂单撤销成功并返回未匹配USDT"));
							}else{
								$this->ajaxReturn(array('status'=>0,"msg"=>"卖出挂单撤销失败"));
							}
						}
					}else{
						$data['is_cancel']=1;		
						$result=$tarde->where("id=".$id)->save($data);
						if($result){						
							$this->ajaxReturn(array('status'=>1,"msg"=>"买入挂单撤销成功"));						
						}else{
							$this->ajaxReturn(array('status'=>0,"msg"=>"买入挂单撤销失败"));
						}
						unset($result);											
					}
				}else{
					
					$this->ajaxReturn(array('status'=>0,"msg"=>"订单已经匹配完不能撤销"));
				}	
				
			}else{
				
				$this->ajaxReturn(array("static"=>0,"msg"=>"订单已完成!"));
			}
		}else{
				$this->ajaxReturn(array("static"=>0,"msg"=>"订单不存在!"));
		}
	}
		return;
	}
		/* 撤销子订单
		else{         	      
          //子订单--返回子订单的USDT到主订单
       		if($res['is_get']==0){                       
            $a=$tarde->where("id=".$res['match_id'])->find();              
              if( $a){              
                    $res=$tarde->where("id=".$res['match_id'])->setInc("residue",$res['num']);			//删除子订单返回NUM到主订单未成交量上
                    if($res!==false){

                        $tarde->where("id=".$id)->delete();

                        echo "<script>alert('删除子订单成功并返回USDT到主订单');</script>";
                    }

              }else{
              	
              		 echo "<script>alert('删除订单的主订单不存在');</script>";
              }
            
            }else{
            
            		 echo "<script>alert('不能删除已打款订单');</script>";
            } 
        	
        }	
		*/	

	public function gujia(){
	
	    $type=I('post.ttype');
	
	    $wechat=intval(I('post.weixin'));
	    $alipay=intval(I('post.zhifubao'));
	    $bank=intval(I('post.bank'));
	    $num=I('post.num');
	    $min=intval(I('post.min'));
	
	    $number=$num;                        	//当前用户卖出数量获得
	    $uid=userid();                       	//当前用户ID
	
	    //自动匹配已设置支付方式的订单
	
	    if($bank==1){
	        $where .= ' bank=1';
	    }
	    if($wechat==1){
	        $where .= ' OR wechat=1';
	    }
	    if($alipay==1){
	
	        $where .= ' OR alipay=1';
	    }
	    if(strpos($where,'O')==1){
	        $str = substr($where,3);
	        $where = '('.$str;
	    }else{
	        $where = '('.$where;
	    }
	
	    if($type==1){
	        	
	        $where.=') and ( type=2 and is_done=0 and mid>0 and is_cancel=0 and residue>='.$number." and minnum<=".$number.")";
	        $where2='price ASC';
	    }else{
	        	
	        $where.=') and ( type=1 and is_done=0 and mid>0 and is_cancel=0 and residue>='.$number." and minnum<=".$number.")";
	        $where2='price DESC';
	    }
	
	
	    $res=M('C2c_trade')->where($where)->order($where2)->select();
	    var_dump($res);
	    if($res){
	        foreach($res as $val){
	
	            $id=$val['id'];                             //订单ID
	
	            if($type==1 and $uid==$val['sid']){
	                //$this->ajaxReturn(array("status"=>0,"msg"=>"不能匹配自己的订单"));
	                continue;
	            }
	            	
	            if($type==2 and $uid==$val['bid']){
	                //$this->ajaxReturn(array("status"=>0,"msg"=>"不能匹配自己的订单"));
	                continue;
	            }
	
	            $interest=0;                            //调用C2C后台设置交易利息
	
	            //判断订单是否匹配成功
	            $trade=M('c2c_trade')->where("id=".$id)->find();
	            	
	            $data=array();
	            if($trade['residue']>=$number){
	
	                $this->ajaxReturn(array('status'=>1,'price'=>$trade['price']));
	                break;
	            }else{
	                continue;
	            }
	        }
	
	    }else{
	
	        if($type==1){
	
	            $buy=M('c2c_trade')->where('type=2 and is_done=0 and is_pay=0 and is_get=0 and mid>0 and residue>0')->field('min(price) as a,price')->find();			//买入价格
	            $price=$buy['a'];
	        }
	        if($type==2){
	
	            $sell=M('c2c_trade')->where('type=1 and is_done=0 and is_pay=0 and is_get=0 and mid>0 and residue>0')->field('max(price) as a,price')->find();
	            $price=$sell['a'];
	        }
	        	
	        $this->ajaxReturn(array('status'=>1,'price'=>$price));
	        exit;
	    }
	}
}
