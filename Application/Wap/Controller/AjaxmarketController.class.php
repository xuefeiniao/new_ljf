<?php
namespace Wap\Controller;


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
		//$market="btc_usdt";
		//$market="btc_usdt";		
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
//	public function xiadan($num,$price,$market,$mtype,$pingtai){
	public function xiadan(){		
	
			date_default_timezone_set('PRC');  
			$time=date('Y-m-d H:i:s',time());
				

				$num=1;									 //num限价单表示下单数量，市价买单时表示买多少钱，市价卖单时表示卖多少币
				$price=547;						             //price下单价格，市价单不传该参数
				$market="bcc_usdt";
				$mtype=1;
		//		$pingtai='bian';

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
								
				}else{
					
					$this->error('交易类型错误');
					exit;
				}		
				$huobi=new \Home\Controller\HuobiController();
					
				$res=$huobi->xiadan($num,$price,$bi_type,$type);
					
				if($res['status']=='ok'){
			
					$orderid=$res['data'];	
					
				}else{
					
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
					
					$this->error('Gatio平台不支持该交易对');
					exit;
				}
				
				$gatio=new \Home\Controller\GatioController();
					
				if($mtype==1){
										
					$order=$gatio->buy($market,$price,$num);					
					
				}else if($mtype==2){
					
					$order=$gatio->sell($market,$price,$num);
									
				}else{
					
					$this->error('交易类型错误');
					exit;
				}
				
				if($order['code']==21){
						
						$this->error('资金不足');
						exit;
				}
				if($order['orderNumber']){
					
						$orderid=$order['orderNumber'];	
				}else{
					
						$this->error('提交订单失败');
						exit;
				}						
			}		
			//未测试
			if($pingtai=="zb"){
				
				if($xnb=="trx"){
					
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
				
				if($order['id']){

						$orderid=$order['id'];	
									
				}else{
										
						$this->error('提交订单失败');
						exit;
				}		

			}
			
			if($pingtai=="okex"){
			
				$okex=new \Home\Controller\OkexController();	
				
				$order=$okex->xiadan($num,$price,$market,$mtype);
				
				if($order['result']==true){
					
						if($order['order_id']){
							
							$orderid=$order['order_id'];	
						}					
				}else if($order['error_code']==1002){
						
						$this->error('资金不足');
						exit;
				}else{
					
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

                }else if($bian_res['code']=='-2010'){
					
					$this->error('资金不足');
					exit;
				}else{

                    $this->error('提交订单失败');
                    exit;
                }				
			}
			
			
			$orderid="45396835";
		//	$orderid="9513169559";
		//	$orderid="1160676630";
			
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

				//写入后台交易记录表
				$oid=M('terrace')->add(array('terrace'=>$pingtai,'market'=>$market,'num'=>$num,'price'=>$price,'fee'=>0,'type'=>$mtype,'time'=>0,'uid'=>userid(),'orderid'=>$tradeorder,'status'=>0));

				//火币订单完成
				
				if($pingtai=="huobi"){
					
					$huobi=new \Home\Controller\HuobiController();
					$order=$huobi->xiangqing($orderid);
						
					error_log('$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='.$pingtai.'||$orderid=='.$orderid.'||$state=='.$order['data']['state'].'||$time=='.$time."\r\n",3,'./order_log.txt');				

					if($order['status']=='ok'){
					
						if($order['data']['state']=='filled'){
						
							$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee);
				
							//更新交易记录							
							M('terrace')->save(array('id'=>$oid,'fee'=>$order['data']['field-fees'],'time'=>time(),'status'=>1)); 
							
							if($order_id){
								
							      $this->success('交易成功！');
								  exit;
							}												
						}else if($order['data']['state']=='submitted'){		// submitted 已提交, partial-filled 部分成交, partial-canceled 部分成交撤销, filled 完全成交, canceled 已撤销
							
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
											
							$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee);
							
							//更新交易记录							
							M('terrace')->save(array('id'=>$oid,'fee'=>$gt_order['order']['feeValue'],'time'=>time(),'status'=>1)); 
							
							if($order_id){
								
							      $this->success('交易成功！');
								  exit;
							}							
					}else if($gt_order['order']['status']=='open'){
						
							$this->success("挂单成功");
							exit;
					}					
				}
				//ZB订单
				if($pingtai=="zb"){
					
						$zb=new \Home\Controller\ZbController();
						$zb_order=$zb->xiangqing($orderid,$market);
						
						error_log('$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='.$pingtai.'||$orderid=='.$orderid.'||$state=='.$order['status'].'||$time=='.$time."\r\n",3,'./order_log.txt');
						
						if($zb_order['status']==2){		//挂单状态（1：取消，2：交易完成，0/3：待成交/待成交未交易部份）
						
							$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee);
							
							//更新交易记录							
							M('terrace')->save(array('id'=>$oid,'fee'=>$fee,'time'=>time(),'status'=>1));
							
							if($order_id){	
							
							      $this->success('交易成功！');
								  exit;
							}		
											
						}else if($zb_order['status']==0){
							
							$this->success("挂单成功");
							exit;							
						}										
				}	
				//OKEX订单
				if($pingtai=='okex'){
					
					$okex=new \Home\Controller\OkexController();
					$ok_order=$okex->xiangqing($market,$orderid);
					$ok_status=$ok_order['orders'][0]['status'];		//status: 订单状态(0等待成交 1部分成交 2全部成交 -1撤单 4撤单处理中 5撤单中)				
				
					error_log('$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='.$pingtai.'||$orderid=='.$orderid.'||$state=='.$ok_status.'||$time=='.$time."\r\n",3,'./order_log.txt');
					
					if($ok_status==2){					//成交
						
						$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee);
							
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

				if($pingtai=='bian'){
										
					$bian=new \Home\Controller\BianController();
					$bian_res=$bian->xiangqing($bian_type,$orderid);
					
					error_log('$num=='.$num.'||$price=='.$price.'||$market=='.$market.'||$type=='.$mtype.'||$pingtai=='.$pingtai.'||$orderid=='.$orderid.'||$state=='.$ok_status.'||$time=='.$time."\r\n",3,'./order_log.txt');

					if($bian_res['status']=='FILLED'){
						
							$order_id=$this->order_done(userid(),$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee);

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
	//更新订单完成
	protected function order_done($id,$uid,$num,$price,$mum,$market,$mtype,$buy_fee,$sell_fee){
		
		$xnb = explode('_', $market)[0];
		$rmb = explode('_', $market)[1];
		
		M('trade')->where('id='.$uid)->save(array('status'=>1,'deal'=>$num)); //deal已成交完

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

		$result=M('trade_log')->add(array('userid' => $buyid, 'peerid' => $sellid, 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'type' => $mtype, 'fee_buy' => $buy_fee, 'fee_sell' => $sell_fee, 'addtime' => time(), 'status' => 1));
				
		return $result;		
		
	}
	
	//更新平台订单成交状态
	public function uporder(){
			
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
			
			}else{
				
				$buy_fee=$val['fee2'];
				$sell_fee=$val['fee'];	
				$okexfee=round($val['num']*1.5/1000,8);				
			}
						
				if($pingtai=="HB"){
					
					$huobi=new \Home\Controller\HuobiController();
					$order=$huobi->xiangqing($orderid);
					$status=$order['data']['state'];
					//			var_dump($order);die;
					if($status=='filled'){
					
						$order_id=$this->order_done($val['userid'],$val['id'],$val['num'],$val['price'],$val['mum'],$val['market'],$val['type'],$buy_fee,$sell_fee);				
			
						if($order_id){														
							//更新交易记录								
							$tu=M('terrace')->where(array('orderid'=>$val['orderid']))->save(array('time'=>time(),'status'=>1,'fee'=>$order['data']['field-fees'])); 
						}
															
					}else if($status=='submitted'){		// submitted 已提交, partial-filled 部分成交, partial-canceled 部分成交撤销, filled 完全成交, canceled 已撤销
						
						continue;
					}														
				}
				if($pingtai=="GT"){
													
					$gatio=new \Home\Controller\GatioController();
					
					$gt_order=$gatio->xiangqing($orderid,$val['market']);
					
					$status=$gt_order['order']['status'];
					
					if($status=='open'){
						
						continue;
												
					}else if($status=='closed'){	//成交
																	
						$order_id=$this->order_done($val['userid'],$val['id'],$val['num'],$val['price'],$val['mum'],$val['market'],$val['type'],$buy_fee,$sell_fee);				
						
						if($order_id){														
							//更新交易记录								
							$tu=M('terrace')->where(array('orderid'=>$val['orderid']))->save(array('time'=>time(),'status'=>1,'fee'=>$gt_order['order']['feeValue'])); 
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
					if($order['status']==2){  //挂单状态（1：取消，2：交易完成，0/3：待成交/待成交未交易部份）
														
						$order_id=$this->order_done($val['userid'],$val['id'],$val['num'],$val['price'],$val['mum'],$val['market'],$val['type'],$buy_fee,$sell_fee);				
						
						if($order_id){														
							//更新交易记录								
							$tu=M('terrace')->where(array('orderid'=>$val['orderid']))->save(array('time'=>time(),'status'=>1,'fee'=>$gtfee)); 
						}
						
					}else if($order['status']==0 ||$order['status']==3){
						
						continue;
					}
				
				}	

				if($pingtai=='EX'){
					
					$okex_type=$val['market'];
					$okex=new \Home\Controller\OkexController();
					$ok_order=$okex->xiangqing($okex_type,$orderid);
					$ok_status=$ok_order['orders'][0]['status'];		//status: 订单状态(0等待成交 1部分成交 2全部成交 -1撤单 4撤单处理中 5撤单中)				
					if($ok_status==2){					//成交
																	
						$order_id=$this->order_done($val['userid'],$val['id'],$val['num'],$val['price'],$val['mum'],$val['market'],$val['type'],$buy_fee,$sell_fee);				

						if($order_id){														
							//更新交易记录								
							$tu=M('terrace')->where(array('orderid'=>$val['orderid']))->save(array('time'=>time(),'status'=>1,'fee'=>$okexfee)); 
						}

					}else if($ok_status==1){	//部分成交		
						
						continue;	
						
					}else if($ok_status==0){	//等待成交
						
						continue;	
					}
				}			
		}		
	}
	//火币K线
	public function kxian(){
		/*$market=I('market');
		$ktime=I('ktime');
		$num = I('num');
		$huobi=new \Home\Controller\HuobiController;
		$res=$huobi->kxian($market,$ktime,$num);
		//var_dump($res);die;
		//$res=array_reverse($res);
		$this->ajaxReturn(array('info'=>$res));*/



		header("Access-Control-Allow-Origin:*");
		header('Access-Control-Allow-Methods:POST');
		header('Access-Control-Allow-Headers:x-requested-with, content-type');
		
		$market=I('market');
		// $market='btcusdt';
		//$ktime=I('ktime');
		$ktime = '1day';
		$num = '500';
		//$num=I('num');				
		$xnb = explode('_', $market)[0];
		$rmb = explode('_', $market)[1];
		$bi_type=$xnb.$rmb;	
		$huobi=new \Home\Controller\HuobiController;
		$res1=$huobi->kxian($bi_type,$ktime,$num);
		// $res=$this->gskxian($res1);	
		$kline=array_reverse($res1);
		// var_dump($kline);die;
		$this->ajaxReturn($kline);
		
	}
  
  public function kxian_ceshi(){
		/*$market=I('market');
		$ktime=I('ktime');
		$num = I('num');
		$huobi=new \Home\Controller\HuobiController;
		$res=$huobi->kxian($market,$ktime,$num);
		//var_dump($res);die;
		//$res=array_reverse($res);
		$this->ajaxReturn(array('info'=>$res));*/



		header("Access-Control-Allow-Origin:*");
		header('Access-Control-Allow-Methods:POST');
		header('Access-Control-Allow-Headers:x-requested-with, content-type');
		
		$market=I('market');
		// $market='btcusdt';
		//$ktime=I('ktime');
		$ktime = I('ktime');
		$num = '100';
		//$num=I('num');				
		$xnb = explode('_', $market)[0];
		$rmb = explode('_', $market)[1];
		$bi_type=$xnb.$rmb;	
		$huobi=new \Home\Controller\HuobiController;
		$res1=$huobi->kxian($bi_type,$ktime,$num);
		// $res=$this->gskxian($res1);	
		$kline=array_reverse($res1['data']);
   // dump($kline);die;
    	//$kline = $res1;	
        foreach($kline as $k=>$v)
        {
           $kline[$k]['id'] = $v['id'] * 1000;
         /* $list['s'] = "ok";
          $list['t'][] = $v['id'];
          $list['c'][] = $v['close'];
          $list['o'][] = $v['open'];
          $list['h'][] = $v['high'];
          $list['l'][] = $v['low'];
          $list['v'][] = $v['amount'];*/

        }
		//var_dump($kline['data']);die;
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
	
	
	public function getMarketSpecialtyJson_xxx()
	{
		// TODO: SEPARATE
		$input = I('post.');
		$market = (is_array(C('market')[$input['market']]) ? trim($input['market']) : C('market_mr'));
		$timearr = array(1, 3, 5, 10, 15, 30, 60, 120, 240, 360, 720, 1440, 10080);

		if (in_array($input['step'] / 60, $timearr)) {
			$time = $input['step'] / 60;
		}
		else {
			$time = 5;
		}

		$timeaa = (APP_DEBUG ? null : S('ChartgetMarketSpecialtyJsontime' . $market . $time));

		if (($timeaa + 60) < time()) {
			S('ChartgetMarketSpecialtyJson' . $market . $time, null);
			S('ChartgetMarketSpecialtyJsontime' . $market . $time, time());
		}

		$tradeJson = (APP_DEBUG ? null : S('ChartgetMarketSpecialtyJson' . $market . $time));

		if (!$tradeJson) {
			$tradeJson = M('TradeJson')->where(array(
	'market' => $market,
	'type'   => $time,
	'data'   => array('neq', '')
	))->order('id asc')->select();
			S('ChartgetMarketSpecialtyJson' . $market . $time, $tradeJson);
		}

		$json_data = $data = array();
		foreach ($tradeJson as $k => $v) {
			$json_data[] = json_decode($v['data'], true);
		}
		//var_dump($json_data);die;
		foreach ($json_data as $k => $v) {
			$data[$k]['id'] = $v[0];
			//$data[$k][1] = 0;
			//$data[$k][2] = 0;
			$data[$k]['open'] = $v[2];
			$data[$k]['close'] = $v[5];
			$data[$k]['high'] = $v[3];
			$data[$k]['low'] = $v[4];
			$data[$k]['amount'] = $v[1];//
		}

		// exit(json_encode($data));
		$this->ajaxReturn(array('info'=>$data));
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
				// var_dump($zb);die;
				$this->ajaxReturn(array('hangqing'=>$zb));
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
		$orderid="894830272";
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
  
  
  
  /*测试tradingview*/
public function config()
{
   $config = array(
      	'supported_resolutions' => ["1", "5", "15", "30", "60", "1D", "1W", "1M","12M"],
        'supports_search' => true,
        'supports_group_request' => false,
        'supports_marks' => false,//是否访问marks方法
        'supports_time' => true,
      	"supports_timescale_marks"=>false, //是否访问timescale_marks方法
        "exchanges" => [["value" => "", "name" => "All Exchanges", "desc" => ""]],
      	"symbolsTypes"=> [],
    );
  exit(json_encode($config));
}
  
  public function symbols()
{
    $input = I('get.');
    $market = strtolower(trim($input['symbol']));
    $data = [];
    if (is_array(C('market')[$market])) {
        $marketData = C('market')[$market];
		$data['name'] = trim($input['symbol']);
        $data['symbol'] = trim($input['symbol']);
        $data['minmov'] = 1;
        $data['has_intraday'] = true;
        $data['ticker'] = trim($input['symbol']);
        $data['type'] = "bitcoin";
      	$data['pricescale']=1000;
		$data['timezone'] = 'Asia/Shanghai';
      	$data['intraday_multipliers'] = ["1", "5", "15", "30", "60", "1D", "1W",'1M',"12M"];
      	$data['supported_resolution'] = ["1", "5", "15", "30", "60", "1D", "1W",'1M',"12M"];
      	//$data['regular_session'] = 24*7;
      	$data['session'] = substr(str_replace(':', '', $marketData['begintrade']), 0, 4) . '-' . substr(str_replace(':', '', $marketData['endtrade']), 0, 4) . ':1234567';
      	$data['data_status'] = "streaming";
      	$data['volume_precision'] = 8;
      	$data['has_seconds'] = true;
      	$data['has_daily'] = true;
      	$data['has_weekly_and_monthly'] = true;
      	$data['seconds_multipliers'] = ["1", "5", "15", "30", "60", "1D", "1W",'1M',"12M"];
    }
    exit(json_encode($data));
}

public function historys()
{
    $input = I('get.');
 // dump($input);
   // $market = strtolower((is_array(C('market')[$input['symbol']]) ? trim($input['symbol']) : C('market_mr')));
  	$market = strtolower(trim($input['symbol']));
    $from = intval($input['from']);
    $to = intval($input['to']);
    $resolution = $input['resolution'];
	//$from = 1483453800;
	//$to = 2114352000;
	//$market = "eth_jeff";
	//$from = 1483453800;
	//$to = 2114352000;
	//$resolution = "1D";
	//echo $market;
    $allResolutions = array(
        '1' => 1,
        '5' => 5,
        '15' => 15,
        '30' => 30,
        '60' => 60,
        '1D' => 1440,
        '1W' => 10080,
//      '1M' => 302400,
    );
    $key = $market . $resolution . $from . $to;

    $resolution = key_exists($resolution, $allResolutions) ? $allResolutions[$resolution] : 1;
  //echo $allResolutions[$resolution];die;
  	//$resolution = 1;
//dump($resolution);die;
    $lastUpdateTime = (APP_DEBUG ? null : S('UdfHistoryUpdateTime' . $key));

    if (($lastUpdateTime + 60) < time()) {
        S('UdfHistoryData' . $key, null);
        S('UdfHistoryUpdateTime' . $key, time());
    }

    $tradeJson = (APP_DEBUG ? null : S('UdfHistoryData' . $key));
//dump($tradeJson);die;
    if (!$tradeJson) {
        $tradeJson = M('TradeJsonTrading')->where(array(
            'market' => $market,
            'type' => $resolution,
            'data' => array('neq', ''),
            'addtime' => array(array('egt', $from), array('elt', $to), 'and'),
        ))->order('id asc')->select();
        S('UdfHistoryData' . $key, $tradeJson);
    }
//dump($tradeJson);die;
    $json_data = array();
    foreach ($tradeJson as $k => $v) {
        $json_data[] = json_decode($v['data'], true);
    }

    $data = array(
        's' => 'ok',
        't' => array(),
        'c' => array(),//收盘
        'o' => array(),//开盘
        'h' => array(),//高
        'l' => array(),//低
        'v' => array(),//量
    );

    foreach ($json_data as $k => $v) {
        $data['t'][$k] = $v[0];
        $data['o'][$k] = $v[2];
        $data['c'][$k] = $v[5];
        $data['h'][$k] = $v[3];
        $data['l'][$k] = $v[4];
        $data['v'][$k] = $v[1];
    }
  //	$data = array_merge($data,$this->symbols(),$this->config());

    exit(json_encode($data));
}

  public function time()
{
    echo time();
}
  
  
  public function history()
{
	$market = strtolower(trim(I('get.symbol')));
	$from = I('get.from');
	$to = I('get.to');
	$resolution = I('get.resolution');
	
    $allResolutions = array(
        '1' => 1,
        '5' => 5,
        '15' => 15,
        '30' => 30,
        '60' => 60,
        'D' => 1440,
        '1W' => 10080,
//            '1M' => 302400,
    );
    $key = $market . $resolution . $from . $to;
    $lastUpdateTime = (APP_DEBUG ? null : S('UdfHistoryUpdateTime' . $key));

    if (($lastUpdateTime + 60) < time()) {
        S('UdfHistoryData' . $key, null);
        S('UdfHistoryUpdateTime' . $key, time());
    }

    $tradeJson = (APP_DEBUG ? null : S('UdfHistoryData' . $key));

    if (!$tradeJson) {
        $tradeJson = $this->kxian_ceshi1($resolution,$market);
        S('UdfHistoryData' . $key, $tradeJson);
    }
	//dump($tradeJson);die;
    $json_data = $tradeJson;
//dump($json_data);die;
    $data = array(
        's' => 'ok',
        't' => array(),
        'c' => array(),//收盘
        'o' => array(),//开盘
        'h' => array(),//高
        'l' => array(),//低
        'v' => array(),//量
    );

    foreach ($json_data as $k => $v) {
        $data['t'][$k] = $v['id'];
        $data['o'][$k] = $v['open'];
        $data['c'][$k] = $v['close'];
        $data['h'][$k] = $v['high'];
        $data['l'][$k] = $v['low'];
        $data['v'][$k] = $v['vol'];
    }

    exit(json_encode($data));
}


 public function kxian_ceshi1($resolution=60,$market='btc_usdt'){

		header("Access-Control-Allow-Origin:*");
		header('Access-Control-Allow-Methods:POST');
		header('Access-Control-Allow-Headers:x-requested-with, content-type');
		$allresolution = array(
        	'1'=>'1min',
          	'5'=>'5min',
          	'15'=>'15min',
          	'30'=>'30min',
          	'60'=>'60min',
          	'1D'=>'1day',
          	'1W'=>'1week',
          	'1M'=>'1mon',
          	'M'=>'1year',
          	
        );
		//$market='btc_usdt';
		$ktime= $allresolution[$resolution];
		$num = '500';	
		$xnb = explode('_', $market)[0];
		$rmb = explode('_', $market)[1];
		$bi_type=$xnb.$rmb;	
		$huobi=new \Home\Controller\HuobiController;
		$res1=$huobi->kxian($bi_type,$ktime,$num);
		$kline=array_reverse($res1['data']);
		return $kline;
        
	}
  
  
  //火币数据入库（K线）
	public function huobi_ruku()
	{
		header("Access-Control-Allow-Origin:*");
		header('Access-Control-Allow-Methods:POST');
		header('Access-Control-Allow-Headers:x-requested-with, content-type');
		$timearr = array(1, 5, 15, 30, 60, 1440, 10080, 43200, 525600);
		for($i=0;$i<10;$i++)
		{
			foreach($timearr as $k=>$v)
			{
				if($v >= 1 && $v <= 60)
				{
					$ktime = $v.'min';
				}
				elseif($v == 1440)
				{
					$ktime = '1day';
				}
				elseif($v == 10080)
				{
					$ktime = '1week';
				}
				elseif($v == 43200)
				{
					$ktime = '1mon';
				}
				elseif($v == 525600)
				{
					$ktime = '1year';
				}
				
				$market = "btc_usdt";
				$num = '1';	
				$xnb = explode('_', $market)[0];
				$rmb = explode('_', $market)[1];
				$bi_type=$xnb.$rmb;	
				$huobi=new \Home\Controller\HuobiController;
				$res1=$huobi->kxian($bi_type,$ktime,$num);
				$kline=array_reverse($res1['data']);
				foreach($kline as $k1=>$v1)
				{
					$kline[$k1]['ktime'] = $ktime;
					$a = array($v1['id'],$v1['vol'],$v1['open'],$v1['high'],$v1['low'],$v1['close']);
					M('TradeJsonTrading')->add(array('data' => json_encode($a), 'addtime' => $v1['id'], 'type' => $v,'market' => $market));
				}
			}
			sleep(5);
		}		
	}

}

?>