<?php
namespace Home\Controller;

/**
 * 杠杆交易控制器
 */
class LevertradeController extends HomeController
{
	 //杠杆交易
    public function borrowMoney(){
        $showPW = 1;

        if (userid()) {
            $user = M('User')->where(array('id' => userid()))->find();

            if ($user['tpwdsetting'] == 3) {
                $showPW = 0;
            }

            if ($user['tpwdsetting'] == 1) {
                if (session(userid() . 'tpwdsetting')) {
                    $showPW = 2;
                }
            }
            //$this->upcoin('wfii');
        }
    
        check_server();
        
            
        if (!$market) {
            $market = C('market_mr');
        }
        $markets = C('MARKET');
        foreach ($markets as $k => $v) {
          if ($v['jiaoyiqu']==0){
                $list['JTC'][]=$v;
            }
        }
        //dump($list['JTC']);die;
        $whe['name']=$market;
        
        $price=M('market')->where($whe)->find();
        $this->assign('price',$price);
        
        
        $data['s1']=explode('_',$_GET['market'])[0]; 
        $data['s2']=explode('_',$_GET['market'])[1]; 
        $coininfo = M('coin')->where(['name'=>$data['s1']])->find();
        $this->assign('coininfo',$coininfo);
        $this->assign('data',$data);
        
    
        $market_time_zhisucom = C('market')[$market]['begintrade']."-".C('market')[$market]['endtrade'];
        
        $notice = M('Article')->where(array('status'=>1,'type'=>'notice'))->order('addtime desc')->limit(4)->select();
        
        $introduction = M('Coin')->where(array('name'=>$data['s1'],'status'=>1))->find();#币种介绍

        $buy_coin = M('User_coin')->where(array('userid'=>userid()))->getField('gg'.$data['s1']);
        $sell_coin = M('User_coin')->where(array('userid'=>userid()))->getField('gg'.$data['s2']);
        //根据用户VIP算出总手续费
        $vip_arr = get_vip_fee(userid());
        $buy_shouxufee = $vip_arr[1]*$price['fee_buy'];
        $sell_shouxufee = $vip_arr[1]*$price['fee_sell'];
        $this->assign('buy_shouxufee',$buy_shouxufee);
        $this->assign('sell_shouxufee',$sell_shouxufee);
        //
        $new_price = M('Market')->where(array('status'=>1,'name'=>$_GET['market']))->getField('new_price');
        // echo $sell_coin/$new_price;die;
        $this->assign('new_price',$new_price);
        $this->assign('buy_coin',$buy_coin);
        $this->assign('sell_coin',$sell_coin);
        $this->assign('market_time', $market_time_zhisucom);
        $this->assign('showPW', $showPW);
        $this->assign('market', $market);
        $this->assign('mrl',round($sell_coin/$new_price,6));
        $this->assign('xnb', explode('_', $market)[0]);
        $this->assign('rmb', explode('_', $market)[1]);
        // $market = C('MARKET');
        

            // dump($list['JTC']);
        $notice = M('Article')->where(array('status'=>1,'type'=>'notice'))->order('addtime desc')->limit(4)->select();
        $bow = M('User_coin')->where(array('userid'=>userid()))->getField($data['s1'].'bow');
        $bow_jtc = M('User_coin')->where(array('userid'=>userid()))->getField('jtcbow');

        $this->assign('bow', $bow);
        $this->assign('bow_jtc', $bow_jtc);
        $this->assign('notice', $notice);
        $this->assign('jtc',$list['JTC']);
        $this->display();
    }

	/*
	*paypassword资金密码
	*market交易对
	*price价格
	*num数量
	*type ：1是买，2是卖
	*tradetype：1是限价交易，2是市价交易
	*trade_pt：1是bb交易，2是杠杆交易
	**/
    public function upTrade($paypassword = NULL, $market = NULL, $price=0, $num=NULL, $type,$tradetype,$trade_pt,$auto=0)
    {
		date_default_timezone_set('PRC'); 
	    
		
			
			//控制下单时间
        if($auto){
            //机器人
            $endtime=M('trade_lever')->where('userid=1')->order('addtime desc')->find();
        }else{
            $endtime=M('trade_lever')->where('userid='.userid('','',$auto))->order('addtime desc')->find();
			if(($endtime['addtime']+2)>=time()){
				$this->error('不能重复提交');
				exit;
			}
        }

			
		if (!userid('','',$auto)) {
			$this->error('请先登录！','/#login');
			redirect();
		}
		
        if (C('market')[$market]['begintrade']) {
            $begintrade = C('market')[$market]['begintrade'];
        }else{
            $begintrade = "00:00:00";
        }

        if (C('market')[$market]['endtrade']) {
            $endtrade = C('market')[$market]['endtrade'];
        }else{
            $endtrade = "23:59:59";
        }


        $trade_begin_time = strtotime(date("Y-m-d")." ".$begintrade);
        $trade_end_time = strtotime(date("Y-m-d")." ".$endtrade);
        $cur_time = time();

        if($cur_time<$trade_begin_time || $cur_time>$trade_end_time){
            $this->error('当前市场禁止交易,交易时间为每日'.$begintrade.'-'.$endtrade);
			exit;
        }
		//error_log('tradetype='.$tradetype."\r\n",3,'./price.txt');		
		//自动匹配	
		$exclude=array();
		if($tradetype==2){#市价
			while(true){
				if($type==1){#买					
					$where1['market']=$market;
					$where1['type']=2;
					$where1['userid']=array('gt',0);
					$where1['status']=0;
					//$where1['trade_pt'] = $trade_pt;
					if(count($exclude)){
						$where1['id']=array('NOT IN',$exclude);
					}

					$res=M('trade_lever')->where($where1)->order('price asc')->find();
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
				if($type==2){#卖

                    $where2['market']=$market;
                    $where2['type']=1;
                    $where2['userid']=array('gt',0);
                    $where2['status']=0;
					//$where2['trade_pt'] = $trade_pt;
                   if(count($exclude)){
					
						$where2['id']=array('NOT IN',$exclude);				   
				   }
					$res2=M('trade_lever')->where($where2)->order('price desc')->find();
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
		//echo $price;die;
        if (!check($price, 'double')) {
            $this->error('交易价格格式错误');
        }

        if (!check($num, 'double')) {
            $this->error('交易数量格式错误');
        }

        if (($type != 1) && ($type != 2)) {
            $this->error('交易类型格式错误');
        }

        //$user = M('User')->where(array('id' => userid()))->find();
		 if($auto){
            $user = M('User')->where(array('id' => 1))->find();
        }else{
            $user = M('User')->where(array('id' => userid('','',$auto)))->find();
        }

        if ($user['tpwdsetting'] == 3) {
        }

        if ($user['tpwdsetting'] == 2) {
            if (md5($paypassword) != $user['paypassword']) {
                $this->error('交易密码错误！');
            }
        }

        if ($user['tpwdsetting'] == 1) {
            if (!session(userid('','',$auto) . 'tpwdsetting')) {
                if (md5($paypassword) != $user['paypassword']) {
                    $this->error('交易密码错误！');
                }
                else {
                    session(userid('','',$auto) . 'tpwdsetting', 1);
                }
            }
        }
		
		//error_log('market1='.$C('market')[$market].'market2='. $market,3,'./abcb.txt');
        if (!C('market')[$market]) {
            $this->error('交易市场错误');
        }
        else {
            $xnb = explode('_', $market)[0];
            $rmb = explode('_', $market)[1];
        }
        // TODO: SEPARATE
		
        $price = round(floatval($price), C('market')[$market]['round']);
 

        if (!$price) {
            $this->error('交易价格错误' . $price);
        }

        $num = round($num, C('market')[$market]['round']);

        if (!check($num, 'double')) {
            $this->error('交易数量错误');
        }

        if ($type == 1) {
            $min_price = (C('market')[$market]['buy_min'] ? C('market')[$market]['buy_min'] : 1.0E-8);
            $max_price = (C('market')[$market]['buy_max'] ? C('market')[$market]['buy_max'] : 10000000);
        }
        else if ($type == 2) {
            $min_price = (C('market')[$market]['sell_min'] ? C('market')[$market]['sell_min'] : 1.0E-8);
            $max_price = (C('market')[$market]['sell_max'] ? C('market')[$market]['sell_max'] : 10000000);
        }
        else {
            $this->error('交易类型错误');
        }

        if ($max_price < $price) {
            $this->error('交易价格超过最大限制！');
        }

        if ($price < $min_price) {
            $this->error('交易价格超过最小限制！');
        }
	
        $hou_price = C('market')[$market]['hou_price'];

        if ($hou_price) {
            if (C('market')[$market]['zhang']) {
                // TODO: SEPARATE
                $zhang_price = round(($hou_price / 100) * (100 + C('market')[$market]['zhang']), C('market')[$market]['round']);

                if ($zhang_price < $price) {
                    $this->error('交易价格超过今日涨幅限制！');
                }
            }

            if (C('market')[$market]['die']) {
                // TODO: SEPARATE
                $die_price = round(($hou_price / 100) * (100 - C('market')[$market]['die']), C('market')[$market]['round']);

                if ($price < $die_price) {
                    $this->error('交易价格超过今日跌幅限制！');
                }
            }
        }

        //$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		if($auto){
            $user_coin = M('LeverCoin')->where(array('userid' => 1,'name_en'=>$xnb))->find();
        }else{
            $user_coin = M('LeverCoin')->where(array('userid' => userid('','',$auto),'name_en'=>$xnb))->find();
        }
        echo '<pre>';
		var_dump($user_coin);die;
		
		$user_coin[$xnb] = $user_coin['yue'];
		$user_coin[$xnb.'d'] = $user_coin['yued'];
		$user_coin[$rmb] = $user_coin['p_yue'];
		$user_coin[$rmb.'d'] = $user_coin['p_yued'];
		echo $user_coin[$rmb];die;
        if ($type == 1) {
            //根据VIP等级计算佣金费率
            $return_vip_fee = get_vip_fee(userid('','',$auto));
            $vip_fee = $return_vip_fee[1];
            //$trade_fee = C('market')[$market]['fee_buy'];

            $trade_fee = C('market')[$market]['fee_buy'] * $vip_fee;//0.13

            if ($trade_fee) {
                $fee = round((($num * $price) / 100) * $trade_fee, 8);//0.156
			    $fee1 = round(($num  / 100) * $trade_fee, 8);//0.156
                //$mum = round((($num * $price) / 100) * (100 + $trade_fee), 8);//120.156
                //TODO 2018-07-25 修改手续费扣在净入币种上
                $mum = round((($num * $price) / 100) * (100), 8);//120.156//120
            }
            else {
                $fee = 0;
                $mum = round($num * $price, 8);
            }

            if ($user_coin[$rmb] < $mum) {
                $this->error(C('coin')[$rmb]['title'] . '余额不足！');
            }
        }
        else if ($type == 2) {
            //根据VIP等级计算佣金费率
            $return_vip_fee = get_vip_fee(userid('','',$auto));
            $vip_fee = $return_vip_fee[1];

            $trade_fee = C('market')[$market]['fee_sell'] * $vip_fee;
           
            if ($trade_fee) {
                $fee = round((($num * $price) / 100) * $trade_fee, 8);
                //按VIP级别在币种手续费基础上再乘以VIP佣金折扣
                $mum = round((($num * $price) / 100) * (100 - $trade_fee), 8);
                
                //$fee = round((($num * $price) / 100) * $trade_fee, 8);
                //$mum = round((($num * $price) / 100) * (100 - $trade_fee), 8);

                
            }
            else {
                $fee = 0;
                $mum = round($num * $price, 8);
            }

            if ($user_coin[$xnb] < $num) {
                $this->error(C('coin')[$xnb]['title'] . '余额不足！');
            }
        }
        else {
            $this->error('交易类型错误');
        }

        if (C('coin')[$xnb]['fee_bili']) {
            if ($type == 2) {
                // TODO: SEPARATE
                $bili_user = round($user_coin[$xnb] + $user_coin[$xnb . 'd'], C('market')[$market]['round']);

                if ($bili_user) {
                    // TODO: SEPARATE
                    $bili_keyi = round(($bili_user / 100) * C('coin')[$xnb]['fee_bili'], C('market')[$market]['round']);

                    if ($bili_keyi) {
                        $bili_zheng = M()->query('select id,price,sum(num-deal)as nums from zhisucom_trade_lever where userid=' . userid('','',$auto) . ' and status=0 and type=2 and market like \'%' . $xnb . '%\' ;');

                        if (!$bili_zheng[0]['nums']) {
                            $bili_zheng[0]['nums'] = 0;
                        }

                        $bili_kegua = $bili_keyi - $bili_zheng[0]['nums'];

                        if ($bili_kegua < 0) {
                            $bili_kegua = 0;
                        }

                        if ($bili_kegua < $num) {
                  //          $this->error('您的挂单总数量超过系统限制，您当前持有' . C('coin')[$xnb]['title'] . $bili_user . '个，已经挂单' . $bili_zheng[0]['nums'] . '个，还可以挂单' . $bili_kegua . '个', '', 5);
                        }
                    }
                    else {
                        $this->error('可交易量错误');
                    }
                }
            }
        }

        if (C('coin')[$xnb]['fee_meitian']) {
            if ($type == 2) {
                $bili_user = round($user_coin[$xnb] + $user_coin[$xnb . 'd'], 8);

                if ($bili_user < 0) {
                    $this->error('可交易量错误');
                }

                $kemai_bili = ($bili_user / 100) * C('coin')[$xnb]['fee_meitian'];

                if ($kemai_bili < 0) {
                    $this->error('您今日只能再卖' . C('coin')[$xnb]['title'] . 0 . '个', '', 5);
                }

                $kaishi_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
               
				if($auto){
                    $jintian_sell = M('Trade_lever')->where(array(
                        'userid'  => 1,
                        'addtime' => array('egt', $kaishi_time),
                        'type'    => 2,
                        'status'  => array('neq', 2),
                        'market'  => array('like', '%' . $xnb . '%')
                    ))->sum('num');
                }else{
                    $jintian_sell = M('Trade_lever')->where(array(
                        'userid'  => userid('','',$auto),
                        'addtime' => array('egt', $kaishi_time),
                        'type'    => 2,
                        'status'  => array('neq', 2),
                        'market'  => array('like', '%' . $xnb . '%')
                    ))->sum('num');
                }

                if ($jintian_sell) {
                    $kemai = $kemai_bili - $jintian_sell;
                }
                else {
                    $kemai = $kemai_bili;
                }

                if ($kemai < $num) {
                    if ($kemai < 0) {
                        $kemai = 0;
                    }

            //        $this->error('您的挂单总数量超过系统限制，您今日只能再卖' . C('coin')[$xnb]['title'] . $kemai . '个', '', 5);
                }
            }
        }

        if (C('market')[$market]['trade_min']) {
            if ($mum < C('market')[$market]['trade_min']) {
                $this->error('交易总额不能小于' . C('market')[$market]['trade_min']);
            }
        }

        if (C('market')[$market]['trade_max']) {
            if (C('market')[$market]['trade_max'] < $mum) {
                $this->error('交易总额不能大于' . C('market')[$market]['trade_max']);
            }
        }

        if (!$rmb) {
            $this->error('数据错误1');
        }

        if (!$xnb) {
            $this->error('数据错误2');
        }

        if (!$market) {
            $this->error('数据错误3');
        }
			
        if (!$price) {
            $this->error('数据错误4');
        }

        if (!$num) {
            $this->error('数据错误5');
        }

        if (!$mum) {
            $this->error('数据错误6');
        }

        if (!$type) {
            $this->error('数据错误7');
        }
		//不是UDB去其他平台交易
		/*if($rmb!="bdb"){			
			$pingtai='huobi';
			$ajax=new \Home\Controller\AjaxmarketController;
			$ajax->xiadan($num,$price,$market,$type,$pingtai);	
			exit;
		}*/
        $mo = M();
        $mo->execute('set autocommit=0');
        //$mo->execute('lock tables zhisucom_status write, zhisucom_trade_lever write ,zhisucom_user_coin write ,zhisucom_finance write,zhisucom_chistory write');
		$mo->execute('lock tables zhisucom_trade_lever write ,zhisucom_lever_coin write');
        $rs = array();
        //$user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();	
		if($auto){
            $user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => 1,'name_en'=>$xnb))->find();
        }else{
            $user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid('','',$auto),'name_en'=>$xnb))->find();
        }
		
		$user_coin[$xnb] = $user_coin['yue'];
		$user_coin[$xnb.'d'] = $user_coin['yued'];
		$user_coin[$rmb] = $user_coin['p_yue'];
		$user_coin[$rmb.'d'] = $user_coin['p_yued'];
		$rmbgg = 'p_yue';
		$xnbgg = 'yue';
		
        if ($type == 1) {
            if ($user_coin[$rmb] < $mum) {
                $this->error(C('coin')[$rmb]['title'] . '余额不足！');

            }
			
            /*$finance = $mo->table('zhisucom_finance')->where(array('userid' => userid('','',$auto)))->order('id desc')->find();
            $finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid('','',$auto)))->find();*/
            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid('','',$auto),'name_en'=>$xnb))->setDec($rmbgg, $mum);	
            
			//委托买入账单明细
			/*$wallet=M('user_coin')->where("userid=".userid('','',$auto))->getField($rmb);
			$mark['curr']=$market;	
			$mid=M('status')->where($mark)->getField('id');
			parent::addCashhistory(userid('','',$auto),$mid,1,"买入","-".$mum.strtoupper($rmb),$price,$wallet.strtoupper($rmb),"委托买入".strtoupper($xnb));*/										
            
			//结束			
			
            $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => userid('','',$auto),'name_en'=>$xnb))->setInc($rmbgg . 'd', $mum);
            
            $rs[] = $finance_nameid = $mo->table('zhisucom_trade_lever')
			->add(array(
			'userid' => userid('','',$auto), 
			'market' => $market, 
			'price' => $price, 
			'num' => $num, 
			'mum' => $mum, 
			'fee' => $fee1, 
			'type' => 1, 
			'addtime' => time(), 
			'status' => 0,
			'trade_pt'=>$trade_pt
			));



            //20170531 修改只统计人民币交易金额变化
            /*if($rmb == "cny"){
                $finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid('','',$auto)))->find();
                $finance_hash = md5(userid('','',$auto) . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.zhisucom.com');
                $finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

                if ($finance['mum'] < $finance_num) {
                    $finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
                }
                else {
                    $finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
                }

                $rs[] = $mo->table('zhisucom_finance')->add(array('userid' => userid('','',$auto), 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $mum, 'type' => 2, 'name' => 'trade', 'nameid' => $finance_nameid, 'remark' => '交易中心-委托买入-市场' . $market, 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
            }*/



        }
        else if ($type == 2) {
            if ($user_coin[$xnb] < $num) {
                $this->error(C('coin')[$xnb]['title'] . '余额不足2！');
				
            }

            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid('','',$auto),'name_en'=>$xnb))->setDec($xnbgg, $num);

            //委托卖出账单明细
            /*$wallet=$mo->table('zhisucom_user_coin')->where("userid=".userid('','',$auto))->getField($xnb);
            $mark['curr']=$market;
            $mid=M('status')->where($mark)->getField('id');
            parent::addCashhistory(userid('','',$auto),$mid,2,"卖出","-".$num.strtoupper($xnb),$price,$wallet.strtoupper($xnb),"委托卖出".strtoupper($xnb));*/
			
            //结束

            $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => userid('','',$auto),'name_en'=>$xnb))->setInc($xnbgg . 'd', $num);
            $rs[] = $mo->table('zhisucom_trade_lever')->add(array('userid' => userid('','',$auto), 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $fee, 'type' => 2, 'addtime' => time(), 'status' => 0,'trade_pt'=>$trade_pt));
        }
        else {
            $mo->execute('rollback');
            $mo->execute('unlock tables');
            $this->error('交易类型错误');
        }

        if (check_arr($rs)) {
            $mo->execute('commit');
            $mo->execute('unlock tables');
            S('getDepthgg', null);
            $this->matchingTrade($market,$trade_pt,$auto);
            //$this->success('交易成功！');
			$this->success('交易成功！','','',$auto);
        }
        else {
			//echo userid('','',$auto);
            $mo->execute('rollback');
            $mo->execute('unlock tables');
            $this->error('交易失败！');
        }
    }

    protected function matchingTrade($market = NULL,$trade_pt = NULL,$auto)#匹配
    {
        if (!$market) {
            return false;
        }
        else {
            $xnb = explode('_', $market)[0];			//BTC_USDT  的BTC
            $rmb = explode('_', $market)[1];			//BTC_USDT  的USDT
        }

        //
       
        $invit_buy = C('market')[$market]['invit_buy'];
        $invit_sell = C('market')[$market]['invit_sell'];
        $invit_1 = C('market')[$market]['invit_1'];
        $invit_2 = C('market')[$market]['invit_2'];
        $invit_3 = C('market')[$market]['invit_3'];
		
        $mo = M();
        $new_trade_zhisucom = 0;

        for (; true; ) {
            //$buy = $mo->table('zhisucom_trade')->where(array('market' => $market, 'type' => 1,'userid' => array('gt',0), 'status' => 0))->order('price desc,id asc')->find();
            //$sell = $mo->table('zhisucom_trade')->where(array('market' => $market, 'type' => 2,'userid' => array('gt',0), 'status' => 0))->order('price asc,id asc')->find();
			if($auto){
				//机器人只匹配机器人的订单
				$buy = $mo->table('zhisucom_trade_lever')->where(array('market' => $market, 'type' => 1,'userid' => 1, 'status' => 0))->order('price desc,id asc')->find();
				$sell = $mo->table('zhisucom_trade_lever')->where(array('market' => $market, 'type' => 2,'userid' => 1, 'status' => 0))->order('price asc,id asc')->find();
        
			}else{
				$buy = $mo->table('zhisucom_trade_lever')->where(array('market' => $market, 'type' => 1,'userid' => array('gt',1), 'status' => 0))->order('price desc,id asc')->find();
				$sell = $mo->table('zhisucom_trade_lever')->where(array('market' => $market, 'type' => 2,'userid' => array('gt',1), 'status' => 0))->order('price asc,id asc')->find();
        
			}
				$return_vip_fee = get_vip_fee($buy['userid']);
				$vip_fee1 = $return_vip_fee[1];
				$fee_buy = C('market')[$market]['fee_buy']*$vip_fee1;
				
				$return_vip_fee = get_vip_fee($sell['userid']);
				$vip_fee2 = $return_vip_fee[1];
				$fee_sell = C('market')[$market]['fee_sell']*$vip_fee2;


		   if ($sell['id'] < $buy['id']) {
                $type = 1;//先下卖单，后下买单
            }
            else {
                $type = 2;//先下买单，后下卖单
            }

            
            if ($buy && $sell && (0 <= floatval($buy['price']) - floatval($sell['price']))) {
                //买的价格》=卖的价格
                $rs = array();

                if ($buy['num'] <= $buy['deal']) {
                }

                if ($sell['num'] <= $sell['deal']) {
                }

                //取买,卖记录中量最小的 min(买数量，卖数量)
                $amount = min(round($buy['num'] - $buy['deal'], C('market')[$market]['round']), round($sell['num'] - $sell['deal'], C('market')[$market]['round']));
                $amount = round($amount, C('market')[$market]['round']);
				
				

                if ($amount <= 0) {
                    $log = '错误1杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . "\n";
                    $log .= 'ERR: 成交数量出错，数量是' . $amount;
                    //数量有小于等于0的，交易中记录直接改为已操作状态，操作结束
                    M('Trade_lever')->where(array('id' => $buy['id']))->setField('status', 1);
                    M('Trade_lever')->where(array('id' => $sell['id']))->setField('status', 1);
                    break;
                }

                if ($type == 1) {
                    $price = $sell['price'];//先下卖单，以卖的价格为主
                }
                else if ($type == 2) {
                    $price = $buy['price'];
                }
                else {
                    break;
                }

                if (!$price) {
                    $log = '错误2杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
                    $log .= 'ERR: 成交价格出错，价格是' . $price;
                    break;
                }
                else {
                    // TODO: SEPARATE
                    $price = round($price, C('market')[$market]['round']);
                }

                $mum = round($price * $amount, 8);//（1.2*100）//120

                if (!$mum) {
                    $log = '错误3杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
                    $log .= 'ERR: 成交总额出错，总额是' . $mum;
                    mlog($log);
                    break;
                }
                else {
                    $mum = round($mum, 8);
                }

                if ($fee_buy) {
                    //平台对于兑换币对有手续费etc_usdt
                    $buy_fee = round(($mum / 100) * $fee_buy, 8);//（120/100*0.2=0.24）
					//2018-8-19
					$buy1_fee = round(($amount / 100) * $fee_buy, 8);
					
                    
                    $buy_save = round(($mum / 100) * (100), 8);//（120/100*（100+0.2））=120.24
                }
                else {
                    $buy_fee = 0;
                    $buy_save = $mum;
					//error_log('buy_fee='.$buy_fee.'buyid'.$buy['userid']."\r\n",3,'./abc.txt');
                }

                if (!$buy_save) {
                    $log = '错误4杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新数量出错，更新数量是' . $buy_save;
                    mlog($log);
                    break;
                }

                if ($fee_sell) {
                    $sell_fee = round(($mum / 100) * $fee_sell, 8);//（120/100*0.2=0.24）
			
                    $sell_save = round(($mum / 100) * (100 - $fee_sell), 8);//（120/100*（100-0.2））=119.76
					
					
                }
                else {
                    $sell_fee = 0;
                    $sell_save = $mum;
					//error_log('sell_fee='.$sell_fee.'sellid'.$sell['userid']."\r\n",3,'./abc.txt');
                }

                if (!$sell_save) {
                    $log = '错误5杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 卖家更新数量出错，更新数量是' . $sell_save;
                    mlog($log);
                    break;
                }

                $user_buy = M('LeverCoin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->find();//买家货币信息
				
				$user_buy[$xnb] = $user_buy['yue'];
				$user_buy[$xnb.'d'] = $user_buy['yued'];
				$user_buy[$rmb] = $user_buy['p_yue'];
				$user_buy[$rmb.'d'] = $user_buy['p_yued'];
				$rmbgg = 'p_yue';
				$xnbgg = 'yue';
				
				
				
                //判断买家交易的币量保存到冻结币字段中
                if (!$user_buy[$rmb . 'd']) {
                    $log = '错误6杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家财产错误，冻结财产是' . $user_buy[$xnb . 'd'];
                    mlog($log);
                    break;
                }

                $user_sell = M('LeverCoin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->find();
				
				$user_sell[$xnb] = $user_sell['yue'];
				$user_sell[$xnb.'d'] = $user_sell['yued'];
				$user_sell[$rmb] = $user_sell['p_yue'];
				$user_sell[$rmb.'d'] = $user_sell['p_yued'];
				
                //判断卖家交易的币量保存到冻结币字段中
                if (!$user_sell[$xnb . 'd']) {
                    $log = '错误7杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 卖家财产错误，冻结财产是' . $user_sell[$xnb . 'd'];
                    mlog($log);
                    break;
                }
                if ($user_buy[$rmb . 'd'] < 1.0E-8) {
                    $log = '错误88杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新冻结人民币出现错误,应该更新' . $buy_save . '账号余额' . $user_buy[$xnb . 'd'] . '进行错误处理';
                    mlog($log);
                    M('Trade_lever')->where(array('id' => $buy['id']))->setField('status', 1);
                    break;
                }
				//error_log('$buy_save='.$buy_save.'&&&$rmbd='.round($user_buy[$rmb . 'd'], 8)."\r\n",3,'./a.txt');
                //买的需要更新的总价格 和 冻结买的币价格对比
                if ($buy_save <= round($user_buy[$rmb . 'd'], 8)) {
                    $save_buy_rmb = $buy_save;
                }
                else if ($buy_save <= round($user_buy[$rmb . 'd'], 8) + 1) {
                    $save_buy_rmb = $user_buy[$rmb . 'd'];
                    $log = '错误8杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新冻结人民币出现误差,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '实际更新' . $save_buy_rmb;
                    mlog($log);
                }
                else {
                    $log = '错误9杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新冻结人民币出现错误,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '进行错误处理';
                    mlog($log);
                    M('Trade_lever')->where(array('id' => $buy['id']))->setField('status', 1);
                    break;
                }
                
                // TODO: SEPARATE
                //  判断交易币数量和卖家卖出量冻结币量对比
                if ($amount <= round($user_sell[$xnb . 'd'], C('market')[$market]['round'])) {
                    $save_sell_xnb = $amount;//卖的交易量
                }
                else {
                    // TODO: SEPARATE

                    if ($amount <= round($user_sell[$xnb . 'd'], C('market')[$market]['round']) + 1) {
                        $save_sell_xnb = $user_sell[$xnb . 'd'];
                        $log = '错误10杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 卖家更新冻结虚拟币出现误差,应该更新' . $amount . '账号余额' . $user_sell[$xnb . 'd'] . '实际更新' . $save_sell_xnb;
                        mlog($log);
                    }
                    else {
                        $log = '错误11杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 卖家更新冻结虚拟币出现错误,应该更新' . $amount . '账号余额' . $user_sell[$xnb . 'd'] . '进行错误处理';
                        mlog($log);
                        M('Trade_lever')->where(array('id' => $sell['id']))->setField('status', 1);
                        break;
                    }
                }

                if (!$save_buy_rmb) {
                    $log = '错误12杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新数量出错错误,更新数量是' . $save_buy_rmb;
                    mlog($log);
                    M('Trade_lever')->where(array('id' => $buy['id']))->setField('status', 1);
                    break;
                }

                if (!$save_sell_xnb) {
                    $log = '错误13杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 卖家更新数量出错错误,更新数量是' . $save_sell_xnb;
                    mlog($log);
                    M('Trade_lever')->where(array('id' => $sell['id']))->setField('status', 1);
                    break;
                }

                $mo->execute('set autocommit=0');
				//$mo->execute('lock tables zhisucom_status write ,zhisucom_chistory write ,zhisucom_trade write ,zhisucom_trade_log write ,zhisucom_user write,zhisucom_user_coin write,zhisucom_invit write ,zhisucom_finance write ,zhisucom_market write');
				$mo->execute('lock tables zhisucom_status write ,zhisucom_trade_lever write ,zhisucom_trade_log_level write ,zhisucom_user write,zhisucom_level_coin write,zhisucom_market write');
				
			
				
				$rs[] = $mo->table('zhisucom_trade_lever')->where(array('id' => $buy['id']))->setInc('deal', $amount);
                $rs[] = $mo->table('zhisucom_trade_lever')->where(array('id' => $sell['id']))->setInc('deal', $amount);
                $rs[] = $finance_nameid = $mo->table('zhisucom_trade_log_level')->add(array('userid' => $buy['userid'], 'peerid' => $sell['userid'], 'market' => $market, 'price' => $price, 'num' => $amount, 'mum' => $mum, 'type' => $type, 'fee_buy' => $buy1_fee, 'fee_sell' => $sell_fee, 'addtime' => time(), 'status' => 1));
                
				
                //TODO:2018-07-25 买的手续费扣在买入币种上面（注意：要先把对应交易市场兑换成净入币种，再扣除）
                $new_amount = round($amount - $buy1_fee,8);
                $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setInc($xnbgg, $new_amount);
                //$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->setInc($xnb, $amount);
                
                
			//账单明细			
				/*$wallet=M('user_coin')->where("userid=".$buy['userid'])->getField($xnb);
				$mark['curr']=$market;
				$mid=M('status')->where($mark)->getField('id');	
				parent::addCashhistory($buy['userid'],$mid,1,"买入","+".$new_amount.strtoupper($xnb),$price,$wallet.strtoupper($xnb),strtoupper($rmb)."买入".strtoupper($xnb));	
				error_log('new_amount='.$new_amount,3,'./bb.txt');				
			//结束
			
				
                $finance = $mo->table('zhisucom_finance')->where(array('userid' => $buy['userid']))->order('id desc')->find();
                $finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->find();*/
                
                //减去手续费按最后币种计算的
                $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setDec($rmbgg . 'd', $save_buy_rmb);
                //2018-07-18(冻结币种清0)
                /*$finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->find();
                $finance_hash = md5($buy['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.zhisucom.com');
                $finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

                if ($finance['mum'] < $finance_num) {
                    $finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
                }
                else {
                    $finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
                }


                if($rmb == "cny"){
                    $rs[] = $mo->table('zhisucom_finance')->add(array('userid' => $buy['userid'], 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $save_buy_rmb, 'type' => 2, 'name' => 'tradelog', 'nameid' => $finance_nameid, 'remark' => '交易中心-成功买入-市场' . $market, 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
                }


                $finance = $mo->table('zhisucom_finance')->where(array('userid' => $buy['userid']))->order('id desc')->find();
                $finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $sell['userid']))->find();
				*/
                $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->setInc($rmbgg, $sell_save);
                /*$finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $sell['userid']))->find();
				
				
				
				//账单明细			
				$wallet=M('user_coin')->where("userid=".$sell['userid'])->getField($rmb);
				$mark['curr']=$market;
				$mid=M('status')->where($mark)->getField('id');	
				parent::addCashhistory($sell['userid'],$mid,2,"卖出","+".$sell_save.strtoupper($rmb),$price,$wallet.strtoupper($rmb),"卖出".strtoupper($xnb)."兑换".strtoupper($rmb));				
				//结束
				
				
                $finance_hash = md5($sell['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.zhisucom.com');
                $finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

                if ($finance['mum'] < $finance_num) {
                    $finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
                }
                else {
                    $finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
                }


                if($rmb == "cny"){
                    $rs[] = $mo->table('zhisucom_finance')->add(array('userid' => $sell['userid'], 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $save_buy_rmb, 'type' => 1, 'name' => 'tradelog', 'nameid' => $finance_nameid, 'remark' => '交易中心-成功卖出-市场' . $market, 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
                }
				*/


                $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->setDec($xnbgg . 'd', $save_sell_xnb);
                $buy_list = $mo->table('zhisucom_trade_lever')->where(array('id' => $buy['id'], 'status' => 0))->find();

                if ($buy_list) {
                    if ($buy_list['num'] <= $buy_list['deal']) {
                        $rs[] = $mo->table('zhisucom_trade_lever')->where(array('id' => $buy['id']))->setField('status', 1);
                    }
                }

                $sell_list = $mo->table('zhisucom_trade_lever')->where(array('id' => $sell['id'], 'status' => 0))->find();

                if ($sell_list) {
                    if ($sell_list['num'] <= $sell_list['deal']) {
                        $rs[] = $mo->table('zhisucom_trade_lever')->where(array('id' => $sell['id']))->setField('status', 1);
                    }
                }
				//error_log('price='.$price.'---buyprice='.$buy['price']."\r\n",3,'./price.txt');		
                if ($price < $buy['price']) {
				//error_log('价格不一致'."\r\n",3,'./price.txt');	
                    $chajia_dong = round((($amount * $buy['price']) / 100) * (100 + $fee_buy), 8);
                    $chajia_shiji = round((($amount * $price) / 100) * (100 + $fee_buy), 8);
                    $chajia = round($chajia_dong - $chajia_shiji, 8);

                    if ($chajia) {
                        $chajia_user_buy = $mo->table('zhisucom_level_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->find();
						$chajia_user_buy[$rmb . 'd'] = $chajia_user_buy['p_yued'];

                        if ($chajia <= round($chajia_user_buy[$rmb . 'd'], 8)) {
                            $chajia_save_buy_rmb = $chajia;
                        }
                        else if ($chajia <= round($chajia_user_buy[$rmb . 'd'], 8) + 1) {
                            $chajia_save_buy_rmb = $chajia_user_buy[$rmb . 'd'];
                            mlog('错误91杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount, '成交价格' . $price . '成交总额' . $mum . "\n");
                            mlog('交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '成交数量' . $amount . '交易方式：' . $type . '卖家更新冻结虚拟币出现误差,应该更新' . $chajia . '账号余额' . $chajia_user_buy[$rmb . 'd'] . '实际更新' . $chajia_save_buy_rmb);
                        }
                        else {
                            mlog('错误92杠杆交易' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount, '成交价格' . $price . '成交总额' . $mum . "\n");
                            mlog('交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '成交数量' . $amount . '交易方式：' . $type . '卖家更新冻结虚拟币出现错误,应该更新' . $chajia . '账号余额' . $chajia_user_buy[$rmb . 'd'] . '进行错误处理');
                            //error_log("num=92",3,"/error92.txt");
							$mo->execute('rollback');
                            $mo->execute('unlock tables');
                            M('Trade_lever')->where(array('id' => $buy['id']))->setField('status', 1);
                            M('Trade_lever')->execute('commit');
                            break;
                        }

                        if ($chajia_save_buy_rmb) {
                            $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setDec($rmbgg . 'd', $chajia_save_buy_rmb);
                            $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setInc($rmbgg, $chajia_save_buy_rmb);
                        }
                    }
                }

                $you_buy = $mo->table('zhisucom_trade_lever')->where(array(
                    'market' => array('like', '%' . $rmb . '%'),
                    'status' => 0,
                    'userid' => $buy['userid']
                ))->find();
                $you_sell = $mo->table('zhisucom_trade_lever')->where(array(
                    'market' => array('like', '%' . $xnb . '%'),
                    'status' => 0,
                    'userid' => $sell['userid']
                ))->find();

                if (!$you_buy) {
                    $you_user_buy = $mo->table('zhisucom_level_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->find();
					$you_user_buy[$rmb . 'd'] = $you_user_buy['p_yued'];
                    if (0 < $you_user_buy[$rmb . 'd']) {
                        $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setField($rmbgg . 'd', 0);
                        $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setInc($rmbgg, $you_user_buy[$rmb . 'd']);
                    }
                }

                if (!$you_sell) {
                    $you_user_sell = $mo->table('zhisucom_level_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->find();
					$you_user_sell[$xnb . 'd'] = $you_user_sell['yued'];
                    if (0 < $you_user_sell[$xnb . 'd']) {
                        $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->setField($xnbgg . 'd', 0);
                        $rs[] = $mo->table('zhisucom_level_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->setInc($rmbgg, $you_user_sell[$xnb . 'd']);
                    }
                }

                if (check_arr($rs)) {
                    $mo->execute('commit');
                    $mo->execute('unlock tables');
                    $new_trade_zhisucom = 1;
                    $coin = $xnb;
                    S('allsum', null);
                    S('getJsonTop' . $market, null);
                    S('getTradelog' . $market, null);
                    S('getDepth' . $market . '1', null);
                    S('getDepth' . $market . '3', null);
                    S('getDepth' . $market . '4', null);
                    S('ChartgetJsonData' . $market, null);
                    S('allcoin', null);
                    S('trends', null);
                }
                else {
                    $mo->execute('rollback');
                    $mo->execute('unlock tables');
                }
            }
            else {
                break;
            }

            unset($rs);
        }
            $new_price = round(M('TradeLogLevel')->where(array('market' => $market, 'status' => 1))->order('id desc')->getField('price'), 6);
            $buy_price = round(M('Trade')->where(array('type' => 1, 'market' => $market, 'status' => 0))->max('price'), 6);
            $sell_price = round(M('Trade')->where(array('type' => 2, 'market' => $market, 'status' => 0))->min('price'), 6);
            $min_price = round(M('TradeLog')->where(array(
                'market'  => $market,
                'addtime' => array('gt', time() - (60 * 60 * 24))
            ))->min('price'), 6);
            $max_price = round(M('TradeLog')->where(array(
                'market'  => $market,
                'addtime' => array('gt', time() - (60 * 60 * 24))
            ))->max('price'), 6);
            $volume = round(M('TradeLog')->where(array(
                'market'  => $market,
                'addtime' => array('gt', time() - (60 * 60 * 24))
            ))->sum('num'), 6);
            $sta_price = round(M('TradeLog')->where(array(
                'market'  => $market,
                'status'  => 1,
                'addtime' => array('gt', time() - (60 * 60 * 24))
            ))->order('id asc')->getField('price'), 6);
            $Cmarket = M('Market')->where(array('name' => $market))->find();

            if ($Cmarket['new_price'] != $new_price) {
                $upCoinData['new_price'] = $new_price;
            }

            if ($Cmarket['buy_price'] != $buy_price) {
                $upCoinData['buy_price'] = $buy_price;
            }

            if ($Cmarket['sell_price'] != $sell_price) {
                $upCoinData['sell_price'] = $sell_price;
            }

            if ($Cmarket['min_price'] != $min_price) {
                $upCoinData['min_price'] = $min_price;
            }

            if ($Cmarket['max_price'] != $max_price) {
                $upCoinData['max_price'] = $max_price;
            }

            if ($Cmarket['volume'] != $volume) {
                $upCoinData['volume'] = $volume;
            }

            $change = round((($new_price - $Cmarket['hou_price']) / $Cmarket['hou_price']) * 100, 2);
            $upCoinData['change'] = $change;

            if ($upCoinData) {
				
			//	$upCoinData['new_price'] 
			//	M('newprice')->where(array('name' => $market))->save($upCoinData);
                M('Market')->where(array('name' => $market))->save($upCoinData);
                M('Market')->execute('commit');
                S('home_market', null);
            }
        
    }
	

    
	
	//253测试
	public function sendsms()
	{
		$msg = '您的验证码是656523，请勿泄漏，谨防被骗。';
		//$res = send_mobile('17839214741',$msg);
		if($res){
			echo 111;
		}else{
			echo 222;
		}
		dump($res);die;
	}

	
}
?>