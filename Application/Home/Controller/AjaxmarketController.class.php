<?php
namespace Home\Controller;


class AjaxmarketController extends HomeController {

   public  function  index(){
    //平台市场和币种
		$usdt=array();
		$btc=array();
		$coin=array('BTC','ETH','EOS','LTC','XRP','ETC','QTUM','DASH','BCH','TRX');
		$coin1=array("btc_usdt","eth_usdt","eos_usdt","ltc_usdt","xrp_usdt","etc_usdt","qtum_usdt","dash_usdt","bch_usdt","trx_usdt"); //USDT交易对
		$coin2=array("eth_btc","eos_btc","ltc_btc","xrp_btc","etc_btc","qtum_btc","dash_btc","bch_btc","trx_btc");	//BTC交易对
 		$coin3=array("btcusdt","ethusdt","eosusdt","ltcusdt","xrpusdt","etcusdt","qtumusdt","dashusdt","bchusdt","trxusdt");
		$coin4=array("ethbtc","eosbtc","ltcbtc","xrpbtc","etcbtc","qtumbtc","dashbtc","bchbtc","trxbtc");  
   
   //ZB市场行情获取
       $mark="zb";
       $zbcoinusdt=$coin1;      
       $zbcoinbtc=$coin2;                  
       $zbusdt=$this->coin($mark,$zbcoinusdt);
       $zbbtc=$this->coin($mark,$zbcoinbtc);
	     
	   $zb1=$this->zbarray($zbusdt);
	   $zb2=$this->zbarray($zbbtc);
	   
	 
	//火币市场行情
	  $mark="hb";
	  $hbcoinusdt=$coin3;                       
	  $hbcoinbtc=$coin4;                                
	  $hbusdt=$this->coin($mark,$hbcoinusdt);                                                                                                          //浏览器访问有数据，程序无数据
	  $hbbtc=$this->coin($mark,$hbcoinbtc);
	  
		$hb1=$this->hbarray($hbusdt);
		$hb2=$this->hbarray($hbbtc);
		
	//gatio行情

	$mark="gatio";
	$gtcoinusdt=$coin1;
	$gtcoinbtc=$coin2;
	$gtusdt=$this->coin($mark,$gtcoinusdt);                                                                                                          
	$gtbtc=$this->coin($mark,$gtcoinbtc);
	
		$gt1=$this->gtarray($gtusdt);
		$gt2=$this->gtarray($gtbtc);		
				
	//币安行情
	
		$bian=new \Home\Controller\BianController();			
		$res=$bian->hangqing();
		$biancoinusdt=$coin3;                
		$biancoinbtc=$coin4;

		foreach($biancoinusdt as $v){
			
			//交易对名称有变化
			if($v=="bchusdt"){
				$v="bccusdt";
			}
			if($v=="bchbtc"){
				$v="bccbtc";
			}
			
			$bian1[]=$res[strtoupper($v)];

		}			
		foreach($biancoinbtc as $v){
			
			if($v=="bchusdt"){
				$v="bccusdt";
			}
			if($v=="bchbtc"){
				$v="bccbtc";
			}			
			$bian2[]=$res[strtoupper($v)];				
		}			
			
		//OKEX市场行情卡顿注释代码
  
		$mark="okex";
		$okexcoinusdt=$coin1;                 
		$okexcoinbtc=$coin2;                   
		$okexusdt=$this->coin($mark,$okexcoinusdt);
		$okexbtc=$this->coin($mark,$okexcoinbtc);
		
		
			$okex1=$this->zbarray($okexusdt);
			$okex2=$this->zbarray($okexbtc);
	        

       //分类市场
           for($j=0;$j<10;$j++){			   
				$usdt[$coin[$j]]['zb']=$zb1[$j];
				$usdt[$coin[$j]]['hb']=$hb1[$j];
				$usdt[$coin[$j]]['gt']=$gt1[$j];
				$usdt[$coin[$j]]['bian']=$bian1[$j];
				$usdt[$coin[$j]]['okex']=$okex1[$j];
			   			  			   
				if($j<9){
					$btc[$coin[$j]]['zb']=$zb2[$j];             
					$btc[$coin[$j]]['hb']=$hb2[$j];
					$btc[$coin[$j]]['gt']=$gt2[$j];
					$btc[$coin[$j]]['bian']=$bian2[$j];
					$btc[$coin[$j]]['okex']=$okex2[$j];	
				}		
           }	
		   
		/*   
		   $bigarr = [];
		   $bigarr['usdt'] = $usdt;
		   $bigarr['btc'] = $btc;		   
		   return $bigarr;		   
		*/				
			$this->uptable($usdt,1);
			$this->uptable($btc,2);		
   }

   protected function coin($mark,$coin){                //获取对接平台交易行情

	 $result=array();
     foreach($coin as $k=>$val){
        if($mark=="zb"){                                    //ZB币         
			
			//交易对名字有变化
			if($val=="bch_usdt"){
				$val="bcc_usdt";
			}
			if($val=="bch_btc"){
				$val="bcc_btc";
			}			
			$zb=new \Home\Controller\ZbController();
			$result[]=$zb->hangqing($val);		 
        }
        if($mark=="okex"){                                  //okex币				
			$okex=new \Home\Controller\OkexController();	
			$result[]= $okex->hangqing($val);
        }
        if($mark=="hb"){
			$huobi=new \Home\Controller\HuobiController();
            $result[]=$huobi->hangqing($val);			
        }
		if($mark=="gatio"){	
			$gatio=new \Home\Controller\GatioController();
			$result[]=$gatio->hangqing($val);	
		}
     }
     return $result;
   }
	//格式化数组
   protected function zbarray($m){
	   
	   foreach ($m as $k=>$val){
		   
		  if(array_key_exists("error",$val)){
			  
			  $result[]=0;
			  
		  }else{
			  
			  $result[]=$val['ticker']['last'];
		  }				   
	   }
	   return $result;
   }
   
   protected function hbarray($m){
	   
	    foreach ($m as $k=>$val){
		   
		$result[]=$val['tick']['close'];
	
	   }
	   return $result;      
   }
   
    protected function gtarray($m){
	   
	    foreach ($m as $k=>$val){
		   
		$result[]=$val['last'];
	   
	   }
	   return $result;      
   }
   
   //写USDT,BTC表
	protected function uptable($m,$status){		
			
			if($status==1){
				$table=M("usdt");
			}
			if($status==2){
				$table=M('btc');
			}							
			$i=1;
			foreach($m as $k=>$val){
				$where=array();
					$where['id']=$i;					
					
						$where['zb']=$val['zb'];
						$val['zb']=0;
						
						$where['hb']=$val['hb'];
						$val['hb']=0;
					
						$where['gt']=$val['gt'];
						$val['gt']=0;
					
						$where['bian']=$val['bian'];
						$val['bian']=0;				
					
						$where['okex']=$val['okex'];
						$val['okex']=0;
										
					$table->save($where);	
					$i++;		
			}				
	}
	
	//获取5个平台的市场深度信息一个平台10卖10买
	public function depth(){
		
		//卖价格从低到高
		//买价格从高到低	
		$market=I('market');	
		//gatio市场深度
		//注意交易对格式不同btc_usdt
			$gt_type=$market;			
			if($gt_type!='trx_btc'){
				
				$gatio=new \Home\Controller\GatioController();
				$gt[]=$gatio->shendu($gt_type);				
				$gt_res=$this->gatiogs($gt);
			
			}else{
				
				$gt_res=array('sell'=>array(),'buy'=>array());
			}
			
		//huobi市场深度   
		//注意交易对格式不同btcusdt
			$xnb = explode('_', $market)[0];
            $rmb = explode('_', $market)[1];
			$hb_type=$xnb.$rmb;		
			$huobi=new \Home\Controller\HuobiController();
			$hb[]=$huobi->shendu($hb_type);
			$hb_res=$this->huobigs($hb);
		//ZB市场深度   
		//注意交易对格式不同btc_usdt	bch==>bcc	
		
			$zb_type=$market;
			$xnb = explode('_', $market)[0];
			if($xnb!="trx"){
				
				if($zb_type=="bch_usdt"){
					$zb_type="bcc_usdt";
				}

				if($zb_type=="bch_btc"){
					$zb_type="bcc_btc";
				}					
				$zb1=new \Home\Controller\ZbController();
				$zb[]=$zb1->shendu($zb_type);
				$zb_res=$this->zbgs($zb);	
			
			}else{
				
				$zb_res=array('sell'=>array(),'buy'=>array());
			}
		//OKEX市场深度
		//注意交易对格式btc_usdt
		
			$okex_type=$market;		
			$okex=new \Home\Controller\OkexController();		
			$ok=$okex->shendu($okex_type);
			$ok_res=$this->okexgs($ok);
			
		//bian市场深度
		//注意交易对格式BTCUSDT  无dash_usdt
			$xnb = explode('_', $market)[0];
            $rmb = explode('_', $market)[1];
			$bian_type=strtoupper($xnb.$rmb);
			if($bian_type!="DASHUSDT"){
				$bian=new \Home\Controller\BianController();		
				$ba[]=$bian->shendu($bian_type);
				$bian_res=$this->binancegs($ba);
			}else{
				
				$bian_res=array('sell'=>array(),'buy'=>array());
			}
			
			//处理结果集
			$sell_res=array_merge($gt_res['sell'],$hb_res['sell'],$zb_res['sell'],$ok_res['sell'],$bian_res['sell']);
			
			array_multisort(array_column($sell_res,'price'),SORT_ASC,$sell_res);
			$buy_res=array_merge($gt_res['buy'],$hb_res['buy'],$zb_res['buy'],$ok_res['sell'],$bian_res['buy']);
		
			array_multisort(array_column($buy_res,'price'),SORT_DESC,$buy_res);			
			$sell=array_slice($sell_res,0,10);
			$buy=array_slice($buy_res,0,10);

			$result['sell']=$sell;
			$result['buy']=$buy;
			$this->ajaxReturn($result);				
	}
	//gatio深度格式化
	private function gatiogs($m){
			
		foreach ($m as $val){	
	
			//卖的10档价格从低到高
			$vals=array_slice(array_reverse($val['asks']),0,10);//反转数组取卖的从低到高
			for($i=0;$i<10;$i++){	
				$v1['price']=$vals[$i][0];
				$v1['num']=$vals[$i][1];
				$v1['market']='gatio';			
				$sell[]=$v1;	
			}
			$valb=array_slice($val['bids'],0,10);
			//买的10档价格从高到低
			for($j=0;$j<10;$j++){				
				$v2['price']=$valb[$j][0];
				$v2['num']=$valb[$j][1];
				$v2['market']='gatio';		
				$buy[]=$v2;				
			}			
		}
		$res['sell']=$sell;
		$res['buy']=$buy;
		return $res;
	}
	//huobi市场深度格式化
	private function huobigs($m){
		
		foreach ($m as $val){	
				
				$val1=array_slice($val['tick']['asks'],0,10);					
				for($i=0;$i<10;$i++){					
					$v1['price']=$val1[$i][0];
					$v1['num']=$val1[$i][1];
					$v1['market']='huobi';			
					$sell[]=$v1;									
				}
				$val2=array_slice($val['tick']['bids'],0,10);	
				for($j=0;$j<10;$j++){					
					$v2['price']=$val2[$j][0];
					$v2['num']=$val2[$j][1];
					$v2['market']='huobi';		
					$buy[]=$v2;						
				}						
		}		
		$res['sell']=$sell;
		$res['buy']=$buy;
		return $res;
	}
	//ZB市场深度格式化
	public function zbgs($m){		
		foreach ($m as $val){
			
			$val1=$val['asks'];				
			for($i=0;$i<10;$i++){					
				$v1['price']=$val1[$i][0];
				$v1['num']=$val1[$i][1];
				$v1['market']='zb';			
				$sell[]=$v1;									
			}
			$val2=$val['bids'];
			
			for($j=0;$j<10;$j++){				
				$v2['price']=$val2[$j][0];
				$v2['num']=$val2[$j][1];
				$v2['market']='zb';		
				$buy[]=$v2;						
			}				
		}
		$res['sell']=$sell;
		$res['buy']=$buy;
		return $res;		
	}
	//OKEX市场深度格式化
	public function okexgs($m){
			$val1=array_reverse($m['asks']);			
			for($i=0;$i<10;$i++){		
				$v1['price']=$val1[$i][0];
				$v1['num']=$val1[$i][1];
				$v1['market']='okex';			
				$sell[]=$v1;		
			}
		
			$val2=$m['bids'];
			for($j=0;$j<10;$j++){				
				$v2['price']=$val2[$j][0];
				$v2['num']=$val2[$j][1];
				$v2['market']='okex';		
				$buy[]=$v2;	
			}			
		$res['sell']=$sell;
		$res['buy']=$buy;
		return $res;	
	}
	//BIAN市场深度格式化
	public function binancegs($m){
				
		$val1=array_slice($m[0]['asks'],0,10);
		foreach($val1 as $k1=>$value1){	
		
			$v1['price']=$k1;
			$v1['num']=$value1;
			$v1['market']='bian';		
			$sell[]=$v1;	
		}			
		$val2=array_slice($m[0]['bids'],0,10);
		foreach($val2 as $k=>$value){			
			$v2['price']=$k;
			$v2['num']=$value;
			$v2['market']='bian';		
			$buy[]=$v2;	
		}				
		$res['sell']=$sell;
		$res['buy']=$buy;
		return $res;		
	}
		
//下单
	public function xiadan($num,$price,$market,$mtype,$pingtai){
//	public function xiadan(){
	
			date_default_timezone_set('PRC');  
			$time=date('Y-m-d H:i:s',time());
			/*
				$num=0.001;									 	 //num限价单表示下单数量，市价买单时表示买多少钱，市价卖单时表示卖多少币
				$price=6933;						             //price下单价格，市价单不传该参数
				$market="btc_usdt";
				$mtype=1;
				$pingtai='huobi';
            */
			$xnb = explode('_', $market)[0];
            $rmb = explode('_', $market)[1];	
			$mum=round($num*$price,8);
							
			$return_vip_fee = get_vip_fee(userid());
			$vip_fee = $return_vip_fee[1];
				
			$trade_fee1 = C('market')[$market]['fee_buy'] * $vip_fee;

			if($trade_fee1){

				$buy_fee = round(($num  / 100) * $trade_fee1, 8);

			}else{

				$buy_fee=0;	
			}	
			$trade_fee2 = C('market')[$market]['fee_sell'] * $vip_fee;		

			if($trade_fee2){

				$sell_fee = round((($num * $price) / 100) * $trade_fee2, 8);

			}else{
				
				$sell_fee=0;	
				
			}			  
			
		    $user_coin = M('user_coin')->where(array('userid' => userid()))->find();
			if($mtype==1){
								
				if ($user_coin[$rmb] < $mum) {
					$this->error(C('coin')[$rmb]['title'] . '余额不足！');
					exit;
				}
			
				M('user_coin')->where(array('userid' => userid()))->setDec($rmb, $mum);
				M('user_coin')->where(array('userid' => userid()))->setInc($rmb . 'd', $mum);
										
			}else if ($mtype == 2) {
				
				if ($user_coin[$xnb] < $num) {
					$this->error(C('coin')[$xnb]['title'] . '余额不足！');
					exit;
				}
				
				M('user_coin')->where(array('userid' => userid()))->setDec($xnb, $num);
				M('user_coin')->where(array('userid' => userid()))->setInc($xnb . 'd', $num);
								 							
			}else{
			
				$this->error('交易类型错误');
				exit;
      				
			}
						
			$orderid=0;			
			//火币交易
			if($pingtai=="huobi"){
								
				$bi_type=$xnb.$rmb;				//交易对
				
				if($mtype==1){
					
					$type='buy-limit';		//buy-market：市价买, sell-market：市价卖, buy-limit：限价买, sell-limit：限价卖, buy-ioc：IOC买单, sell-ioc：IOC卖单, 
				
				}else if($mtype==2){
					
					$type='sell-limit';		
								
				}

				$huobi=new \Home\Controller\HuobiController();
					
				$res=$huobi->xiadan($num,$price,$bi_type,$type);
					
				if($res['status']=='ok'){
			
					$orderid=$res['data'];	
					
				}else{

				    if($mtype==1){

				        $coin_type=$rmb;
				        $val=$mum;

                    }else if($mtype==2){

                        $coin_type=$xnb;
                        $val=$num;

                    }
				    $this->cancel_order(userid(),$coin_type,$val);

					if($res['status']=='error'){
						
						$this->error('资金不足');
						
						exit;
					}
					
					$this->error('提交订单失败');
					exit;
				}	
			}			
			//gatio交易
			if($pingtai=="gatio"){
				
				if($market=='trx_btc'){

                    if($mtype==1){
                        $coin_type=$rmb;
                        $val=$mum;
                    }else if($mtype==2){
                        $coin_type=$xnb;
                        $val=$num;
                    }
                    $this->cancel_order(userid(),$coin_type,$val);

					$this->error('Gatio平台不支持该交易对');
					exit;
				}
				
				$gatio=new \Home\Controller\GatioController();
					
				if($mtype==1){
										
					$order=$gatio->buy($market,$price,$num);					
					
				}else if($mtype==2){
					
					$order=$gatio->sell($market,$price,$num);
									
				}

				if($order['orderNumber']){
					
						$orderid=$order['orderNumber'];	
				}else{

                    if($mtype==1){

                        $coin_type=$rmb;
                        $val=$mum;

                    }else if($mtype==2){

                        $coin_type=$xnb;
                        $val=$num;

                    }
                    $this->cancel_order(userid(),$coin_type,$val);

                    if($order['code']==21){

                        $this->error('资金不足');
                        exit;
                    }

                    $this->error('提交订单失败');
						exit;
				}						
			}		
			//zb订单 未测试
			if($pingtai=="zb"){
				
				if($xnb=="trx"){

                    if($mtype==1){

                        $coin_type=$rmb;
                        $val=$mum;

                    }else if($mtype==2){

                        $coin_type=$xnb;
                        $val=$num;

                    }
                    $this->cancel_order(userid(),$coin_type,$val);

					$this->error('ZB平台不支持该交易对');
					exit;
				}
				if($xnb=="bch"){

					$xnb="bcc";
				}
				$zb_type=$xnb."_".$rmb;
				
				//tradeType交易类型1/0[buy/sell]
				if($mtype==1){
					
					$tradeType=1;	//买					
				}else{
					$tradeType=0;	//卖				
				}
				
				$zb=new \Home\Controller\ZbController();
				$order=$zb->xiadan($price,$num,$zb_type,$tradeType);

				if($order['code']==true) {

                        $orderid=$order['orderNumber'];

                }else{

                    if($mtype==1){

                        $coin_type=$rmb;
                        $val=$mum;

                    }else if($mtype==2){

                        if($xnb=="bcc"){

                            $xnb="bch";
                        }
                        $coin_type=$xnb;
                        $val=$num;
                    }
                    $this->cancel_order(userid(),$coin_type,$val);

                    if ($order['code'] == '2009') {

                        $this->error('资金余额不足');

                    } else {

                        $this->error('提交订单失败');
                        exit;
                    }

                }
			}			
			if($pingtai=="okex"){
			
				$okex=new \Home\Controller\OkexController();	
				
				$order=$okex->xiadan($num,$price,$market,$mtype);
				
				if($order['result']==true){
					
						if($order['order_id']){
							
							$orderid=$order['order_id'];	
						}					

				}else{

                    if($mtype==1){

                        $coin_type=$rmb;
                        $val=$mum;

                    }else{

                        $coin_type=$xnb;
                        $val=$num;

                    }
                    $this->cancel_order(userid(),$coin_type,$val);

                     if($order['error_code']==1002) {

                         $this->error('资金不足');
                         exit;
                     }
                        $this->error('提交订单失败');
						exit;
				}
			}
			
			if($pingtai=='bian'){
				
				if($xnb=='bch'){
					
					$xnb='bcc';
				}
				$bian_type=strtoupper($xnb.$rmb);	
				
				$bian=new \Home\Controller\BianController();			
				
				if($mtype==1){
					
						$bian_res=$bian->xiadanbuy($bian_type,$num,$price);
						
				}else if($mtype==2){
					
						$bian_res=$bian->xiadansell($bian_type,$num,$price);
				}

				if($bian_res['orderId']){

                    $orderid=$bian_res['orderId'];

				}else{

                    if($mtype==1){

                        $coin_type=$rmb;
                        $val=$mum;

                    }else{

                        if($xnb=='bcc'){
                            $xnb='bch';
                        }
                        $coin_type=$xnb;
                        $val=$num;

                    }
                    $this->cancel_order(userid(),$coin_type,$val);

                    if($bian_res['code']=='-2010') {

                        $this->error('资金不足');
                        exit;
                    }
                        $this->error('提交订单失败');
                        exit;
                }				
			}
			
			if($orderid){				
					
					if($mtype==1){
						
						$fee1=$buy_fee;	
						$fee2=$sell_fee;
						$fee=$buy_fee;
						
					}else if($mtype==2){
						
						$fee1=$sell_fee;
						$fee2=$buy_fee;	
					}	$fee=$sell_fee;	
					
					if($pingtai=='huobi'){
						
						$tradeorder="HB_".$orderid;
					}
					if($pingtai=='gatio'){
						
						$tradeorder="GT_".$orderid;
					}
					if($pingtai=='zb'){
						
						$tradeorder="ZB_".$orderid;
					}
					if($pingtai=='okex'){
						
						$tradeorder="EX_".$orderid;
					}
					if($pingtai=='bian'){
						
						$tradeorder="BI_".$orderid;
					}
				
				$uid=M('trade')->add(array('userid' => userid(), 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $fee1, 'fee2' => $fee2,'type' => $mtype, 'addtime' => time(), 'status' => 0,'orderid'=>$tradeorder));
				
				$oid=M('terrace')->add(array('terrace'=>$pingtai,'market'=>$market,'num'=>$num,'price'=>$price,'fee'=>0,'type'=>$mtype,'time'=>0,'uid'=>userid(),'orderid'=>$tradeorder,'status'=>0));
				//火币订单
				if($pingtai=="huobi"){
					
					$huobi=new \Home\Controller\HuobiController();
					$order=$huobi->xiangqing($orderid);
						
					if($order['status']=='ok'){
					
						if($order['data']['state']=='filled'){
						
							$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee,1);
							
							error_log('$orderid=='.$tradeorder.'||$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='."okex".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');
							
							//更新交易记录							
							M('terrace')->save(array('id'=>$oid,'fee'=>$order['data']['field-fees'],'time'=>time(),'status'=>1)); 
							
							if($order_id){
								
							      $this->success('交易成功！');
								  exit;
							}												
						}else {		// submitted 已提交, partial-filled 部分成交, partial-canceled 部分成交撤销, filled 完全成交, canceled 已撤销
							
							$this->success('挂单成功');
							exit;
						}							
					}				
				}
				//GATIO订单
				if($pingtai=="gatio"){
					$gatio=new \Home\Controller\GatioController();							
					$gt_order=$gatio->xiangqing($orderid,$market);
				
					error_log('$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='.$pingtai.'||$orderid=='.$orderid.'||$state=='.$order['data']['status'].'||$time=='.$time."\r\n",3,'./order_log.txt');
					
					if($gt_order['order']['status']=='closed'){	// status: 订单状态 open已挂单 cancelled已取消 closed已完成
											
							$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee,1);
							
							error_log('$orderid=='.$tradeorder.'||$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='."gatio".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');

							//更新交易记录							
							M('terrace')->save(array('id'=>$oid,'fee'=>$gt_order['order']['feeValue'],'time'=>time(),'status'=>1)); 
							
							if($order_id){
								
							      $this->success('交易成功！');
								  exit;
							}							
					}else{
						
							$this->success("挂单成功");
							exit;
					}					
				}
				//ZB订单
				if($pingtai=="zb"){
					
						$zb=new \Home\Controller\ZbController();
						$zb_order=$zb->xiangqing($orderid,$market);
						
						
						if($zb_order['status']==2){		//挂单状态（1：取消，2：交易完成，0/3：待成交/待成交未交易部份）
						
							$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee,1);
							
							error_log('$orderid=='.$tradeorder.'||$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='."zb".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');

							//更新交易记录							
							M('terrace')->save(array('id'=>$oid,'fee'=>$fee,'time'=>time(),'status'=>1));
							
							if($order_id){	
							
							      $this->success('交易成功！');
								  exit;
							}											
						}else{
							
							$this->success("挂单成功");
							exit;							
						}										
				}	
				//OKEX订单
				if($pingtai=='okex'){
					
					$okex=new \Home\Controller\OkexController();
					$ok_order=$okex->xiangqing($market,$orderid);
					$ok_status=$ok_order['orders'][0]['status'];		//status: 订单状态(0等待成交 1部分成交 2全部成交 -1撤单 4撤单处理中 5撤单中)				
									
					if($ok_status==2){					//成交
						
						$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee,1);
						
							error_log('$orderid=='.$tradeorder.'||$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='."okex".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');
	
							//更新交易记录							
							M('terrace')->save(array('id'=>$oid,'fee'=>$fee,'time'=>time(),'status'=>1));
							
							if($order_id){	
							
							      $this->success('交易成功！');
								  exit;
							}			
					}else{			
					
						$this->success("挂单成功");
						exit;			
					}
				}
				//BIAN订单
				if($pingtai=='bian'){
										
					$bian=new \Home\Controller\BianController();
					$bian_res=$bian->xiangqing($bian_type,$orderid);
					
					if($bian_res['status']=='FILLED'){
						
							$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee,1);
							
							error_log('$orderid=='.$tradeorder.'||$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='."bian".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');
	
							//更新交易记录							
							M('terrace')->save(array('id'=>$oid,'fee'=>$fee,'time'=>time(),'status'=>1));
							
							if($order_id){	
							
							      $this->success('交易成功！');
								  exit;
							}							
					}else{
						
						$this->success("挂单成功");
						exit;	
					}
				}		
			}else{				
				$this->error('挂单失败');
				exit;
			}			
	}

	//挂单失败退回冻结金额
	protected function cancel_order($id,$bi_type,$val){

           M('user_coin')->where('userid='.$id)->setDec($bi_type."d", $val);
           M('user_coin')->where('userid='.$id)->setInc($bi_type, $val);

    }
	//更新订单
	protected function order_done($id,$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee,$status){		
		/*
		id：当前用户ID 
		uid:该订单本地ID
		num:已完成的订单数量/平台返回成交的数量
		status:1该订单完成更新，2该订单部分更新	
		*/	
		$xnb = explode('_', $market)[0];
		$rmb = explode('_', $market)[1];
		$res=M('trade')->where('id='.$uid)->find();
		$amount=$num;
		if($num<=$res['deal']){
			
			return false;	
		}		
		if($status==2 || ($res['deal'] && ($status==1))){
					
			$num=$num-$res['deal'];
			$mum=round($num*$price,8);
			$return_vip_fee = get_vip_fee($id);
			$vip_fee = $return_vip_fee[1];
			$fee_buy = C('market')[$market]['fee_buy']*$vip_fee;			
			$fee_sell = C('market')[$market]['fee_sell']*$vip_fee;	
			
			if($fee_buy){
				
				$buy_fee = round(($num / 100) * $fee_buy, 8);
			}else{
				$buy_fee=0;
			}
			if($fee_sell){
			
				$sell_fee = round(($mum / 100) * $fee_sell, 8);
			
			}else{				
				$sell_fee=0;				
			}			
		}
		
		//注意:并不需要扣对方钱	
		if($mtype==1){
			
			M('user_coin')->where(array('userid' => $id))->setDec($rmb . 'd', $mum);
			M('user_coin')->where(array('userid' => $id))->setInc($xnb, round($num-$buy_fee,8));									
			$buyid=$id;
			$sellid=1;		
		}		
		if($mtype==2){
			
			M('user_coin')->where(array('userid' => $id))->setDec($xnb . 'd', $num);
			M('user_coin')->where(array('userid' => $id))->setInc($rmb, round($mum-$sell_fee,8));		
			$buyid=1;
			$sellid=$id;		
		}
	
		M('trade_log')->add(array('userid' => $buyid, 'peerid' => $sellid, 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'type' => $mtype, 'fee_buy' => $buy_fee, 'fee_sell' => $sell_fee, 'addtime' => time(), 'status' => 1));
		M('trade')->where('id='.$uid)->setField('deal',$amount);
		
		if($status==1){
			
			$result=M('trade')->where('id='.$uid)->save(array('status'=>1)); //deal已成交完
		}
		
		return $result;		
		
	}
	
	//更新平台订单成交状态
	public function uporder(){
		
		date_default_timezone_set('PRC');  
		$time=date('Y-m-d H:i:s',time());	
		$where="status=0 and orderid is not null";		
		$res=M('trade')->where($where)->select();
		foreach($res as $val){
					
			$xnb = explode('_', $val['market'])[0];
            $rmb = explode('_', $val['market'])[1];
			
			$pingtai = explode('_', $val['orderid'])[0];
            $orderid = explode('_', $val['orderid'])[1];
			
			if($val['type']==1){
							
				$buy_fee=$val['fee'];
				$sell_fee=$val['fee2'];
				$okexfee=round($val['num']*1.5/1000,8);//okex手续费无返回 级别1 挂单0.15%	吃单0.2% 
				$bianfee=round($val['num']*1/1000,8);  //bian手续费无返回 静入币种的0.1%
			    $zbfee=round($val['num']*2/1000,8);    //普通会员0.2% 黄金会员 0.09% 铂金会员 0.08% 钻石会员0.07% 至尊会员0.06%
 		 						
			}else{
				
				$buy_fee=$val['fee2'];
				$sell_fee=$val['fee'];	
				$okexfee=round(($val['num']*$val['price'])*1.5/1000,8);		
				$bianfee=round(($val['num']*$val['price'])*1/1000,8);	
				$zbfee=round(($val['num']*$val['price'])*2/1000,8);				
			}
						
				if($pingtai=="HB"){
					
					$huobi=new \Home\Controller\HuobiController();
					$order=$huobi->xiangqing($orderid);
					$status=$order['data']['state'];				    //订单状态
					$traded=$order['data']['field-amount'];		    	//已成交数量
					$tradedprice=$order['data']['price'];	    		//成交价格
				
					if($status=='filled'){
					
						$order_id=$this->order_done($val['userid'],$val['id'],$traded,$tradedprice,$val['mum'],$val['market'],$val['type'],$buy_fee,$sell_fee,1);				
																		
						error_log('$orderid=='.$val['orderid'].'||$num=='.$val['num'].'||$price=='.$tradedprice.'||$market=='.$val['market'].'||$type=='.$val['type'].'||$pingtai=='."huobi".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');
						
						if($order_id){														
							//更新交易记录								
							$tu=M('terrace')->where(array('orderid'=>$val['orderid']))->save(array('time'=>time(),'status'=>1,'fee'=>$order['data']['field-fees'])); 
						}
															
					}else if($status=="partial-filled"){
																																				
							$order_id=$this->order_done($val['userid'],$val['id'],$traded,$tradedprice,0,$val['market'],$val['type'],0,0,2);
							
							if(!$order_id){
								
								continue;
							}
				
					}else if($status=='submitted'){		// submitted 已提交, partial-filled 部分成交, partial-canceled 部分成交撤销, filled 完全成交, canceled 已撤销
						
						continue;
					}														
				}
				if($pingtai=="GT"){
													
					$gatio=new \Home\Controller\GatioController();					
					$gt_order=$gatio->xiangqing($orderid,$val['market']);					
				
					$status=$gt_order['order']['status'];
					$amount=$gt_order['order']['amount'];			//剩余成交量0代表订单完成 
					$traded=$val['num']-$amount;					//转成已成交量
	
					if($status=='open'){							//已挂单	
						
						$order_id=$this->order_done($val['userid'],$val['id'],$traded,$val['price'],0,$val['market'],$val['type'],0,0,2);				

						if(!$order_id){
							
							continue;
						}									
					}else if($status=='closed'){					//成交
																	
						$order_id=$this->order_done($val['userid'],$val['id'],$traded,$val['price'],$val['mum'],$val['market'],$val['type'],$buy_fee,$sell_fee,1);				
						
						error_log('$orderid=='.$val['orderid'].'||$num=='.$val['num'].'||$price=='.$val['price'].'||$market=='.$val['market'].'||$type=='.$val['type'].'||$pingtai=='."gatio".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');
							
						if($order_id){														
							//更新交易记录								
							$tu=M('terrace')->where(array('orderid'=>$val['orderid']))->save(array('time'=>$gt_order['order']['timestamp'],'status'=>1,'fee'=>$gt_order['order']['feeValue'])); 
						}
					}
				}
				if($pingtai=="ZB"){
					
					if($xnb=="bch"){
					
						$xnb="bcc";
					}
					$zb_type=$xnb."_".$rmb;
					$zb=new \Home\Controller\ZbController();
					$order=$zb->xiangqing($orderid,$zb_type);
									
					$traded=$order['trade_amount'];
					$tradedprice=$order['price'];					
					if($order['status']==2){  //挂单状态（1：取消，2：交易完成，0/3：待成交/待成交未交易部份）
														
						$order_id=$this->order_done($val['userid'],$val['id'],$val['num'],$val['price'],$val['mum'],$val['market'],$val['type'],$buy_fee,$sell_fee,1);				
						
						error_log('$orderid=='.$val['orderid'].'||$num=='.$val['num'].'||$price=='.$tradedprice.'||$market=='.$val['market'].'||$type=='.$val['type'].'||$pingtai=='."zb".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');
								
						if($order_id){														
							//更新交易记录								
							$tu=M('terrace')->where(array('orderid'=>$val['orderid']))->save(array('time'=>time(),'status'=>1,'fee'=>$zbfee)); 
						}
						
					}else if($order['status']==3){
												
						$order_id=$this->order_done($val['userid'],$val['id'],$traded,$tradedprice,0,$val['market'],$val['type'],0,0,2);
							
						if(!$order_id){
							
							continue;
						}	
					}else{
						
							continue;
					}				
				}	

				if($pingtai=='EX'){
					
					$bi_type=$val['market'];				
					$okex=new \Home\Controller\OkexController;
					$ok_order=$okex->xiangqing($bi_type,$orderid);
					
					$ok_status=$ok_order['orders'][0]['status'];		//status: 订单状态(0等待成交 1部分成交 2全部成交 -1撤单 4撤单处理中 5撤单中)									
					$traded=$ok_order['orders'][0]['deal_amount'];		
					$tradedprice=$val['price'];	    		
					$tradedprice2=$ok_order['orders'][0]['price'];	    		
					
					if($ok_status==2){					//成交
																	
						$order_id=$this->order_done($val['userid'],$val['id'],$traded,$tradedprice,$val['mum'],$val['market'],$val['type'],$buy_fee,$sell_fee,1);				
						
						error_log('$orderid=='.$val['orderid'].'||$num=='.$val['num'].'||$price=='.$tradedprice2.'||$market=='.$val['market'].'||$type=='.$val['type'].'||$pingtai=='."okex".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');
								
						if($order_id){														
							//更新交易记录								
							$tu=M('terrace')->where(array('orderid'=>$val['orderid']))->save(array('time'=>time(),'status'=>1,'fee'=>$okexfee)); 
						}

					}else if($ok_status==1){	//部分成交		
						
						$order_id=$this->order_done($val['userid'],$val['id'],$traded,$tradedprice,0,$val['market'],$val['type'],0,0,2);
							
						if(!$order_id){
							
							continue;
						}	
					
					}else if($ok_status==0){	//未成交
						
						continue;	
					}
				}	
				
				if($pingtai=='BI'){
					
					if($xnb=='bch'){

						$xnb='bcc';					
					}					
					$bian_type=strtoupper($xnb.$rmb);	
					$bian=new \Home\Controller\BianController();
					$bian_res=$bian->xiangqing($bian_type,$orderid);
					
					$traded=$bian_res['executedQty'];		
					$tradedprice=$bian_res['price'];	 	
					
					if($bian_res['status']=='FILLED'){
																				
						$order_id=$this->order_done($val['userid'],$val['id'],$traded,$tradedprice,$val['mum'],$val['market'],$val['type'],$buy_fee,$sell_fee,1);				

						error_log('$orderid=='.$val['orderid'].'||$num=='.$val['num'].'||$price=='.$val['price'].'||$market=='.$val['market'].'||$type=='.$val['type'].'||$pingtai=='."bian".'||$state=='."filled".'||$time=='.$time."\r\n",3,'./order_log.txt');
							
						if($order_id){														
							//更新交易记录										
							$tu=M('terrace')->where(array('orderid'=>$val['orderid']))->save(array('time'=>time(),'status'=>1,'fee'=>$bianfee)); 
						}
				
					}else if($bian_res['status']=='NEW'){		  //未成交
									
						$order_id=$this->order_done($val['userid'],$val['id'],$traded,$tradedprice,0,$val['market'],$val['type'],0,0,2);
							
						if(!$order_id){
							
							continue;
						}								
					}
				}	
			unset($traded);
			unset($amount);
			unset($tradedprice);			
		}		
	}
	//火币K线
	public function kxian(){
	
		header("Access-Control-Allow-Origin:*");
		header('Access-Control-Allow-Methods:POST');
		header('Access-Control-Allow-Headers:x-requested-with, content-type');
		
		$market=I('market');
		//$market='btcusdt';
		//$ktime=I('ktime');
		$ktime = '1day';
		$num = '500';
		//$num=I('num');				
		$xnb = explode('_', $market)[0];
		$rmb = explode('_', $market)[1];
		$bi_type=$xnb.$rmb;	
		$huobi=new \Home\Controller\HuobiController;
		$res1=$huobi->kxian($bi_type,$ktime,$num);
		$res=$this->gskxian($res1);	
		$kline=array_reverse($res);
		$this->ajaxReturn($kline);					
	}	
	protected function gskxian($i){
					
		foreach($i['data'] as $key=>$val){			
			$res[$key][0]=$i['ts'];
			$res[$key][1]=$val['open'];	
			$res[$key][2]=$val['high'];	
			$res[$key][3]=$val['low'];	
			$res[$key][4]=$val['close'];	
			$res[$key][5]=$val['amount'];				
		}		
		return $res;		
	}		
	//gatio 订单查询
	public function gtorder(){
		$a="1183553156";
		$b='btc_usdt';
			$gatio=new \Home\Controller\GatioController();
			$gt_order=$gatio->xiangqing($a,$b);
			var_dump($gt_order);die;		
	}
	//gatio资产查询
	public function gtzc(){
		
		$gatio=new \Home\Controller\GatioController();
		$a=$gatio->zichan();
		var_dump($a);die;
	}
	//GATIO撤销订单
	public function ceshi(){
				
		$gatio=new \Home\Controller\GatioController();
		$zc=$gatio->chexiao('1160676630','eos_usdt');
		var_dump($zc);die;
		
	}
	//zb
	public function hangqing(){
		
				$zb1=new \Home\Controller\ZbController();
				$zb[]=$zb1->hangqing('btc_usdt');
				var_dump($zb);die;
	}
	public function zichan(){
		
		
				$zb1=new \Home\Controller\ZbController();
				$zb=$zb1->zichan();
				var_dump($zb);die;
	}
	
	//bian
	public function bianle(){
		
		$bian=new \Home\Controller\BianController();		
		$res=$bian->zichan();
	//	var_dump($res['USDT']['available']);die;
		
	}
	
	public function bianorder(){

        $bian=new \Home\Controller\BianController();
        $order=45396835;
        $bi_type="BCCUSDT";
        $res=$bian->xiangqing($bi_type,$order);
        var_dump($res);die;


    }
	//huobi
	public function hbzc(){
		$huobi=new \Home\Controller\HuobiController();
			$zijin=$huobi->zichan();	
			var_dump($zijin);die;
	}
	public function hborder(){
		$orderid="9974182828";
		$huobi=new \Home\Controller\HuobiController();
		$order=$huobi->xiangqing($orderid);
		var_dump($order);die;

	}
	
	//okex
	public function okorder(){
		
		$bi_type="btc_usdt";
		$orderid="936034444";
		$okex=new \Home\Controller\OkexController;
		$res=$okex->xiangqing($bi_type,$orderid);
		var_dump($res);die;	
	}
	
	public function okzc(){

		$okex=new \Home\Controller\OkexController;
		$res=$okex->zichan($$orderid,$orderid);
		var_dump($res);die;
	}
	
	public function select(){
		
		$where['id']=array('NOT IN',array(1167,1168));
		
		$res=M('trade')->where($where)->find();
		var_dump($res);die;
		
	}
	
	//测试匹配
	public function test(){
		
		$tradetype=1;		
		$market="trx_btc";
		$type=1;
		$num=1000;
		 //自动匹配
		$exclude=array();
		if($tradetype==1){
			while(true){
				if($type==1){
					
					$where1['market']=$market;
					$where1['type']=2;
					$where1['userid']=array('gt',0);
					$where1['status']=0;
					if(count($exclude)){
						
						$where1['id']=array('NOT IN',$exclude);
					}

					$res=M('trade')->where($where1)->order('price asc')->find();
                    if($res){
                            if (($res['num'] - $res['deal']) < $num) {

                                $exclude[] = $res['id'];
                                continue;

                            } else {

                                $price=$res['price'];
                                break;
                            }
                    }else{

                        $price=M('market')->where(array('name'=>$market))->getField('new_price');
                        break;
                    }
				}
				if($type==2){

                    $where2['market']=$market;
                    $where2['type']=1;
                    $where2['userid']=array('gt',0);
                    $where2['status']=0;
                   if(count($exclude)){
					
						$where2['id']=array('NOT IN',$exclude);
				   
				   }
					$res2=M('trade')->where($where2)->order('price desc')->find();
					if($res2){
                        if (($res2['num'] - $res2['deal']) < $num) {
							
                            $exclude[] = $res2['id'];
                            continue;
				
                        } else {

                            $price=$res2['price'];
                            break;
                        }
                    }else{

                       $price=M('market')->where(array('name'=>$market))->getField('new_price');					 
                       break;
                    }
				}
			}	
		}		
		echo "--------".$price;
		//自动匹配		
	}
	
		
}



?>