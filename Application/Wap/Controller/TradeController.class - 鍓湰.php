<?php
namespace Wap\Controller;

class TradeController extends HomeController
{
    public function test(){}
    

    #返回CNY的最新价
    public function newjia(){
        $coin1 = I('post.market');
        $coin=explode('_',$coin1)[1];
        // $coin = 'btc';
        $uid = userid();
        // $uid = 1;
        $bz_yue = M('user_coin')->where(array('userid'=>$uid))->getField($coin);
        $newjia = M('Market')->where(array('name'=>$coin.'_cny'))->getField('new_price');
        $this->ajaxReturn(array('newjia'=>$newjia,'bz_yue'=>$bz_yue));
    }
    #历史委托
    public function getTradels($market=null)
    {
        $uid=userid();
        $market=I('get.market');
        $type=I('get.type');
        $map['market'] = $market;
        switch($type){
            case 1:
                //当前委托
                $map['status'] = ['in','0'];
                break;
            case 2:
                //历史委托
                $map['status'] = ['in','1,2'];
                break;
            default:
                $map['status'] = ['in','0,1,2'];
                break;
        }
        $map['_query'] = "userid=$uid or peerid=$uid";
        //$map['userid']=$uid;
        $list=M('trade')->where($map)->limit(30)->order('id desc')->select();
    
        //echo '<pre>';
        //print_r($list);die;
    
        $this->ajaxReturn(array(
            'code'  =>  200,
            'result'    =>  '获取成功',
            'body'  =>  array(
                'list'  =>  $list?$list:[]
            )
        ));
    }
    
    #历史成交
    public function getTradecj($market=null)
    {
        $uid=userid();
        $$market=I('get.market');
        $map['userid'] = $uid;
        $map['market'] = $market;
        $map['status'] = 1;
        //$map['_query'] = "userid=$uid or peerid=$uid";
        $list=M('trade_log')->where($map)->limit(30)->order('id desc')->select();
        //dump($list);
    
    
        if ($list) {
            exit(json_encode($list));
        }
        else {
            return $list;
        }
    
    }
    
    public function index($market = NULL)
    {
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
		
		
		$whe['name']=$market;
		
		$price=M('market')->where($whe)->find();
		$this->assign('price',$price);
		
		
		$data['s1']=explode('_',$_GET['market'])[0]; 
		$data['s2']=explode('_',$_GET['market'])[1]; 
		$coininfo = M('coin')->where(['name'=>$data['s1']])->find();
		$this->assign('coininfo',$coininfo);
        $this->assign('data',$data);
		
	
        $market_time_zhisucom = C('market')[$market]['begintrade']."-".C('market')[$market]['endtrade'];
               
        //根据用户VIP算出总手续费
        $vip_arr = get_vip_fee(userid());
        $buy_shouxufee = $vip_arr[1]*$price['fee_buy'];
        $sell_shouxufee = $vip_arr[1]*$price['fee_sell'];
        $this->assign('buy_shouxufee',$buy_shouxufee);
        $this->assign('sell_shouxufee',$sell_shouxufee);
        //

        $this->assign('market_time', $market_time_zhisucom);
        $this->assign('showPW', $showPW);
        $this->assign('market', $market);
        $this->assign('xnb', explode('_', $market)[0]);
        $this->assign('rmb', explode('_', $market)[1]);
        
        $this->ajaxReturn(array(
            'code'  =>  200,
            'result'    =>  '获取成功',
            'body'  =>  array(
                'buy_shouxufee' =>  $buy_shouxufee,
                'sell_shouxufee'    =>  $sell_shouxufee,
                'showPW'    =>  $showPW,
            )
        ));
        
        $this->display();
    }

    public function chart($market = NULL)
    {
        //if (!userid()) {
        //}

        if (!$market) {
            $market = C('market_mr');
        }
        // TODO: SEPARATE
        // TODO: SEPARATE
		
		
		
		$whe['name']=$market;
		
		$price=M('market')->where($whe)->find();
		$this->assign('price',$price);
		

        $coin=C('MARKET');
        foreach ($coin as $k=>$v){
            if ($v['jiaoyiqu']==0){
                $list['BB'][]=$v;
            }
            if ($v['jiaoyiqu']==1){
                $list['USDT'][]=$v;
            }
            if ($v['jiaoyiqu']==2){
                $list['BTC'][]=$v;
            }
        }
        //dump($list);die;
        $this->assign('coinlist', $list);
        $this->assign('market', $market);
        $this->assign('xnb', explode('_', $market)[0]);
        $this->assign('rmb', explode('_', $market)[1]);
        $this->display();
    }

    public function info($market = NULL)
    {
        if (!userid()) {
        }

        check_server();

        if (!$market) {
            $market = C('market_mr');
        }
        // TODO: SEPARATE
        // TODO: SEPARATE

        $this->assign('market', $market);
        $this->assign('xnb', explode('_', $market)[0]);
        $this->assign('rmb', explode('_', $market)[1]);
        $this->display();
    }

    public function comment($market = NULL)
    {
        if (!userid()) {
        }

        check_server();

        if (!$market) {
            $market = C('market_mr');
        }

        if (!$market) {
            $market = C('market_mr');
        }
        // TODO: SEPARATE
        // TODO: SEPARATE

        $this->assign('market', $market);
        $this->assign('xnb', explode('_', $market)[0]);
        $this->assign('rmb', explode('_', $market)[1]);
        $where['coinname'] = explode('_', $market)[0];
        $Moble = M('CoinComment');
        $count = $Moble->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = $Moble->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $v) {
            $list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
        }

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    public function ordinary($market = NULL)
    {
        if (!$market) {
            $market = C('market_mr');
        }

        $this->assign('market', $market);
        $this->display();
    }

    public function specialty($market = NULL)
    {
        if (!$market) {
            $market = C('market_mr');
        }

        $this->assign('market', $market);
        $this->display();
    }

    public function upTrade($paypassword = NULL, $market = NULL, $price=0, $num, $type,$tradetype,$trade_pt)
    {
        date_default_timezone_set('PRC'); 
        
        //控制下单时间
        $endtime=M('trade')->where('userid='.userid())->order('addtime desc')->find();
        error_log('userid='.userid().'---endtime='.$endtime['addtime'].'---time='.time()."\r\n",3,'./time.txt');        
            if(($endtime['addtime']+2)>=time()){            
                //$this->error('1001');
				$this->ajaxReturn(array('code'=>1001));
                exit;               
            }           
        if (!userid()) {
            //$this->error('请先登录！','/#login');
			$this->ajaxReturn(array('code'=>1002));
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
            //$this->error('当前市场禁止交易,交易时间为每日'.$begintrade.'-'.$endtrade);
			$this->ajaxReturn(array('code'=>1003));
            exit;
        }
        error_log('tradetype='.$tradetype."\r\n",3,'./price.txt');      
        //自动匹配  
        $exclude=array();
        if($tradetype==2){
            while(true){
                if($type==1){                   
                    $where1['market']=$market;
                    $where1['type']=2;
                    $where1['userid']=array('gt',0);
                    $where1['status']=0;
					$where1['trade_pt'] = $trade_pt;
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
					$where2['trade_pt'] = $trade_pt;
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
        if (!check($price, 'double')) {
            //$this->error('交易价格格式错误');
			$this->ajaxReturn(array('code'=>1004));
        }

        if (!check($num, 'double')) {
            //$this->error('交易数量格式错误');
			$this->ajaxReturn(array('code'=>1004));
        }

        if (($type != 1) && ($type != 2)) {
            // $this->error('交易类型格式错误');
            $this->ajaxReturn(array('code'=>1004));
        }

        $user = M('User')->where(array('id' => userid()))->find();

        if ($user['tpwdsetting'] == 3) {
        }

        if ($user['tpwdsetting'] == 2) {
            if (md5($paypassword) != $user['paypassword']) {
                // $this->error('交易密码错误！');
                $this->ajaxReturn(array('code'=>1005));
            }
        }

        if ($user['tpwdsetting'] == 1) {
            if (!session(userid() . 'tpwdsetting')) {
                if (md5($paypassword) != $user['paypassword']) {
                    // $this->error('交易密码错误！');
                    $this->ajaxReturn(array('code'=>1005));
                }
                else {
                    session(userid() . 'tpwdsetting', 1);
                }
            }
        }
        //error_log('market1='.$C('market')[$market].'market2='. $market,3,'./abcb.txt');
        if (!C('market')[$market]) {
            // $this->error('交易市场错误');
            $this->ajaxReturn(array('code'=>1006));
        }
        else {
            $xnb = explode('_', $market)[0];
            $rmb = explode('_', $market)[1];
        }
        // TODO: SEPARATE
        
        $price = round(floatval($price), C('market')[$market]['round']);
 

        if (!$price) {
            // $this->error('交易价格错误' . $price);
            $this->ajaxReturn(array('code'=>1004));
        }

        $num = round($num, C('market')[$market]['round']);

        if (!check($num, 'double')) {
            // $this->error('交易数量错误');
            $this->ajaxReturn(array('code'=>1004));
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
            // $this->error('交易类型错误');
            $this->ajaxReturn(array('code'=>1004));
        }

        if ($max_price < $price) {
            // $this->error('交易价格超过最大限制！');
            $this->ajaxReturn(array('code'=>1004));
        }

        if ($price < $min_price) {
            // $this->error('交易价格超过最小限制！');
            $this->ajaxReturn(array('code'=>1004));
        }
    
        $hou_price = C('market')[$market]['hou_price'];

        if ($hou_price) {
            if (C('market')[$market]['zhang']) {
                // TODO: SEPARATE
                $zhang_price = round(($hou_price / 100) * (100 + C('market')[$market]['zhang']), C('market')[$market]['round']);

                if ($zhang_price < $price) {
                    // $this->error('交易价格超过今日涨幅限制！');
                    $this->ajaxReturn(array('code'=>1004));
                }
            }

            if (C('market')[$market]['die']) {
                // TODO: SEPARATE
                $die_price = round(($hou_price / 100) * (100 - C('market')[$market]['die']), C('market')[$market]['round']);

                if ($price < $die_price) {
                    // $this->error('交易价格超过今日跌幅限制！');
                    $this->ajaxReturn(array('code'=>1004));
                }
            }
        }

        $user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
        // dump($user_coin);die;
        
        if ($type == 1) {
            //根据VIP等级计算佣金费率
            $return_vip_fee = get_vip_fee(userid());
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
                // $this->error(C('coin')[$rmb]['title'] . '余额不足！');
                $this->ajaxReturn(array('code'=>1007));
            }
        }
        else if ($type == 2) {
            //根据VIP等级计算佣金费率
            $return_vip_fee = get_vip_fee(userid());
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
                // $this->error(C('coin')[$xnb]['title'] . '余额不足！');
                $this->ajaxReturn(array('code'=>1007));
            }
        }
        else {
            // $this->error('交易类型错误');
            $this->ajaxReturn(array('code'=>1004));
        }

        if (C('coin')[$xnb]['fee_bili']) {
            if ($type == 2) {
                // TODO: SEPARATE
                $bili_user = round($user_coin[$xnb] + $user_coin[$xnb . 'd'], C('market')[$market]['round']);

                if ($bili_user) {
                    // TODO: SEPARATE
                    $bili_keyi = round(($bili_user / 100) * C('coin')[$xnb]['fee_bili'], C('market')[$market]['round']);

                    if ($bili_keyi) {
                        $bili_zheng = M()->query('select id,price,sum(num-deal)as nums from zhisucom_trade where userid=' . userid() . ' and status=0 and type=2 and market like \'%' . $xnb . '%\' ;');

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
                        // $this->error('可交易量错误');
                        $this->ajaxReturn(array('code'=>1004));
                    }
                }
            }
        }

        if (C('coin')[$xnb]['fee_meitian']) {
            if ($type == 2) {
                $bili_user = round($user_coin[$xnb] + $user_coin[$xnb . 'd'], 8);

                if ($bili_user < 0) {
                    // $this->error('可交易量错误');
                    $this->ajaxReturn(array('code'=>1004));
                }

                $kemai_bili = ($bili_user / 100) * C('coin')[$xnb]['fee_meitian'];

                if ($kemai_bili < 0) {
                    $this->error('您今日只能再卖' . C('coin')[$xnb]['title'] . 0 . '个', '', 5);
                }

                $kaishi_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $jintian_sell = M('Trade')->where(array(
                    'userid'  => userid(),
                    'addtime' => array('egt', $kaishi_time),
                    'type'    => 2,
                    'status'  => array('neq', 2),
                    'market'  => array('like', '%' . $xnb . '%')
                ))->sum('num');

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
                // $this->error('交易总额不能小于' . C('market')[$market]['trade_min']);
                $this->ajaxReturn(array('code'=>1004));
            }
        }

        if (C('market')[$market]['trade_max']) {
            if (C('market')[$market]['trade_max'] < $mum) {
                // $this->error('交易总额不能大于' . C('market')[$market]['trade_max']);
                $this->ajaxReturn(array('code'=>1004));
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
        $mo->execute('lock tables zhisucom_status write, zhisucom_trade write ,zhisucom_user_coin write ,zhisucom_finance write,zhisucom_chistory write,zhisucom_user write');
        $rs = array();
        // dump(userid());die;
        $user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find(); 
        // dump($user_coin);die; 
        if ($type == 1) {
            if ($user_coin[$rmb] < $mum) {
                // $this->error(C('coin')[$rmb]['title'] . '余额不足！');
                $this->ajaxReturn(array('code'=>1007));

            }

            $finance = $mo->table('zhisucom_finance')->where(array('userid' => userid()))->order('id desc')->find();
            $finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();
            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec($rmb, $mum);   
            //2018-07-18
            //$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec($xnb, $mum);
            //委托买入账单明细
    
                $wallet=M('user_coin')->where("userid=".userid())->getField($rmb);
                $mark['curr']=$market;  
                $mid=M('status')->where($mark)->getField('id');
                parent::addCashhistory(userid(),$mid,1,"买入","-".$mum.strtoupper($rmb),$price,$wallet.strtoupper($rmb),"委托买入".strtoupper($xnb));                                         
            
            //结束            
            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setInc($rmb . 'd', $mum);
            //2018-07-18
            //$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setInc($xnb . 'd', $mum);
            $rs[] = $finance_nameid = $mo->table('zhisucom_trade')->add(array('userid' => userid(), 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $fee1, 'type' => 1, 'addtime' => time(), 'status' => 0,'trade_pt'=>$trade_pt));



            //20170531 修改只统计人民币交易金额变化
            if($rmb == "cny"){
                $finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();
                $finance_hash = md5(userid() . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.zhisucom.com');
                $finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

                if ($finance['mum'] < $finance_num) {
                    $finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
                }
                else {
                    $finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
                }

                $rs[] = $mo->table('zhisucom_finance')->add(array('userid' => userid(), 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $mum, 'type' => 2, 'name' => 'trade', 'nameid' => $finance_nameid, 'remark' => '交易中心-委托买入-市场' . $market, 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
            }



        }
        else if ($type == 2) {
            if ($user_coin[$xnb] < $num) {
                // $this->error(C('coin')[$xnb]['title'] . '余额不足2！');
                $this->ajaxReturn(array('code'=>1007));
                
            }

            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec($xnb, $num);

            //委托卖出账单明细
            $wallet=$mo->table('zhisucom_user_coin')->where("userid=".userid())->getField($xnb);
            $mark['curr']=$market;
            $mid=M('status')->where($mark)->getField('id');
            parent::addCashhistory(userid(),$mid,2,"卖出","-".$num.strtoupper($xnb),$price,$wallet.strtoupper($xnb),"委托卖出".strtoupper($xnb));
            
            //结束

            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setInc($xnb . 'd', $num);
            $rs[] = $mo->table('zhisucom_trade')->add(array('userid' => userid(), 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $fee, 'type' => 2, 'addtime' => time(), 'status' => 0,'trade_pt'=>$trade_pt));
        }
        else {
            $mo->execute('rollback');
            $mo->execute('unlock tables');
            // $this->error('交易类型错误');
            $this->ajaxReturn(array('code'=>1007));
        }

        if (check_arr($rs)) {
            $mo->execute('commit');
            $mo->execute('unlock tables');
            S('getDepth', null);
            $this->matchingTrade($market,$trade_pt);
            // $this->success('交易成功！');
            $this->ajaxReturn(array('code'=>1008));
        }
        else {
            $mo->execute('rollback');
            $mo->execute('unlock tables');
            // $this->error('交易失败！');
            $this->ajaxReturn(array('code'=>1009));
        }
    }


    public function ggfh_price(){
        $type = I('post.type');
        $market = I('post.market');
        $xnb = explode('_', $market)[0];
        $rmb = explode('_', $market)[1];
        $yue = M('UserCoin')->where(array('userid'=>userid()))->getField('gg'.$rmb);
        $market_price = M('Market')->where(array('name'=>$market))->getField('new_price');
           // dump($yue);die;
        if($type == 1){
            $new_price = M('Market')->where(array('name'=>$rmb.'_cny'))->getField('new_price');
        }elseif($type == 2){
            $yue = M('UserCoin')->where(array('userid'=>userid()))->getField('gg'.$xnb);
            $new_price = M('Market')->where(array('name'=>$xnb.'_cny'))->getField('new_price');
        }
        $ress['yue'] = $yue;
        $ress['new_price'] = $new_price;
        if($ress){
            $this->ajaxReturn(array(
                'code'=>200,
                'yue'=>$yue,
                'new_price'=>$new_price,
                'market_price'=>$market_price
            ));
        }
    }
	
	public function fh_price(){
        $type = I('post.type');
        $market = I('post.market');
        $xnb = explode('_', $market)[0];
        $rmb = explode('_', $market)[1];
        $yue = M('UserCoin')->where(array('userid'=>userid()))->getField($rmb);
        $market_price = M('Market')->where(array('name'=>$market))->getField('new_price');
           // dump($yue);die;
        if($type == 1){
            $new_price = M('Market')->where(array('name'=>$rmb.'_cny'))->getField('new_price');
        }elseif($type == 2){
            $yue = M('UserCoin')->where(array('userid'=>userid()))->getField($xnb);
            $new_price = M('Market')->where(array('name'=>$xnb.'_cny'))->getField('new_price');
        }
        $ress['yue'] = $yue;
        $ress['new_price'] = $new_price;
        if($ress){
            $this->ajaxReturn(array(
                'code'=>200,
                'yue'=>$yue,
                'new_price'=>$new_price,
                'market_price'=>$market_price
            ));
        }
    }

    public function upTrade1111($paypassword = NULL, $market = NULL, $price, $num, $type)
    {
		/*
        if (!userid()) {
            
        }
		*/
        
        error_log('market='.$market.'&&price='.$price.'&&num='.$num.'&&type='.$type."\r\n",3,'./aa.txt');
		if (!userid()) {
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
        }


        if (!check($price, 'double')) {
            $this->error('交易价格格式错误');
        }

        if (!check($num, 'double')) {
            $this->error('交易数量格式错误');
        }

        if (($type != 1) && ($type != 2)) {
            $this->error('交易类型格式错误');
        }

        $user = M('User')->where(array('id' => userid()))->find();

        if ($user['tpwdsetting'] == 3) {
        }

        /* if ($user['tpwdsetting'] == 2) {
            if (md5($paypassword) != $user['paypassword']) {
                $this->error('交易密码错误！');
            }
        }

        if ($user['tpwdsetting'] == 1) {
            if (!session(userid() . 'tpwdsetting')) {
                if (md5($paypassword) != $user['paypassword']) {
                    $this->error('交易密码错误！');
                }
                else {
                    session(userid() . 'tpwdsetting', 1);
                }
            }
        } */


        if (!C('market')[$market]) {
            $this->error('交易市场错误');
        }
        else {
            $xnb = explode('_', $market)[0];
            $rmb = explode('_', $market)[1];
        }
        // TODO: SEPARATE

        $price = round(floatval($price), C('market')[$market]['round']);
        //error_log('cl='.C('market')[$market]['round'].'&price='.$price,3,'./bb.txt');

        if (!$price) {
            $this->error('交易价格错误' . $price);
        }

        $num = round($num, 8 - C('market')[$market]['round']);

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

        $user_coin = M('UserCoin')->where(array('userid' => userid()))->find();

        
        if ($type == 1) {
            //根据VIP等级计算佣金费率
            $return_vip_fee = get_vip_fee(userid());
            $vip_fee = $return_vip_fee[1];
            //$trade_fee = C('market')[$market]['fee_buy'];
//error_log('$vip_fee2='.$vip_fee,3,'./bb.txt');
            $trade_fee = C('market')[$market]['fee_buy'] * $vip_fee;//0.13
            //
//error_log('$trade_fee='.$trade_fee,3,'./bb.txt');
            if ($trade_fee) {
                $fee = round((($num * $price) / 100) * $trade_fee, 8);//0.156
                //$mum = round((($num * $price) / 100) * (100 + $trade_fee), 8);//120.156
                //TODO 2018-07-25 修改手续费扣在净入币种上
                $mum = round((($num * $price) / 100) * (100), 8);//120.156//120
//error_log('$fee2='.$fee.'&$mum2='.$mum,3,'./bb.txt');
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
            $return_vip_fee = get_vip_fee(userid());
            $vip_fee = $return_vip_fee[1];
//error_log('$vip_fee='.$vip_fee,3,'./bb.txt');
            $trade_fee = C('market')[$market]['fee_sell'] * $vip_fee;
            //
//error_log('$trade_fee='.$trade_fee,3,'./bb.txt');            

            if ($trade_fee) {
                $fee = round((($num * $price) / 100) * $trade_fee, 8);
                //按VIP级别在币种手续费基础上再乘以VIP佣金折扣
                $mum = round((($num * $price) / 100) * (100 - $trade_fee), 8);
                
                //$fee = round((($num * $price) / 100) * $trade_fee, 8);
                //$mum = round((($num * $price) / 100) * (100 - $trade_fee), 8);
//error_log('$fee='.$fee.'&$mum='.$mum,3,'./bb.txt');
                
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
                        $bili_zheng = M()->query('select id,price,sum(num-deal)as nums from zhisucom_trade where userid=' . userid() . ' and status=0 and type=2 and market like \'%' . $xnb . '%\' ;');

                        if (!$bili_zheng[0]['nums']) {
                            $bili_zheng[0]['nums'] = 0;
                        }

                        $bili_kegua = $bili_keyi - $bili_zheng[0]['nums'];

                        if ($bili_kegua < 0) {
                            $bili_kegua = 0;
                        }

                        if ($bili_kegua < $num) {
                            $this->error('您的挂单总数量超过系统限制，您当前持有' . C('coin')[$xnb]['title'] . $bili_user . '个，已经挂单' . $bili_zheng[0]['nums'] . '个，还可以挂单' . $bili_kegua . '个', '', 5);
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
                $jintian_sell = M('Trade')->where(array(
                    'userid'  => userid(),
                    'addtime' => array('egt', $kaishi_time),
                    'type'    => 2,
                    'status'  => array('neq', 2),
                    'market'  => array('like', '%' . $xnb . '%')
                ))->sum('num');

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

                    $this->error('您的挂单总数量超过系统限制，您今日只能再卖' . C('coin')[$xnb]['title'] . $kemai . '个', '', 5);
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

        $mo = M();
        $mo->execute('set autocommit=0');
        $mo->execute('lock tables zhisucom_status write, zhisucom_trade write ,zhisucom_user_coin write ,zhisucom_finance write,zhisucom_chistory write,zhisucom_user write');
        $rs = array();
        
        $user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();
        
        if ($type == 1) {
            if ($user_coin[$rmb] < $mum) {
                $this->error(C('coin')[$rmb]['title'] . '余额不足！');
            }

            $finance = $mo->table('zhisucom_finance')->where(array('userid' => userid()))->order('id desc')->find();
            $finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();
            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec($rmb, $mum);	
            //2018-07-18
            //$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec($xnb, $mum);
			//委托买入账单明细
				$wallet=M('user_coin')->where("userid=".userid())->getField($rmb);
				$mark['curr']=$market;	
				$mid=M('status')->where($mark)->getField('id');
				parent::addCashhistory(userid(),$mid,1,"买入","-".$mum.strtoupper($rmb),$price,$wallet.strtoupper($rmb),"委托买入".strtoupper($xnb));											
            //结束			
            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setInc($rmb . 'd', $mum);
            //2018-07-18
			//$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setInc($xnb . 'd', $mum);
            $rs[] = $finance_nameid = $mo->table('zhisucom_trade')->add(array('userid' => userid(), 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $fee, 'type' => 1, 'addtime' => time(), 'status' => 0));



            //20170531 修改只统计人民币交易金额变化
            if($rmb == "cny"){
                $finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();
                $finance_hash = md5(userid() . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.zhisucom.com');
                $finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

                if ($finance['mum'] < $finance_num) {
                    $finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
                }
                else {
                    $finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
                }

                $rs[] = $mo->table('zhisucom_finance')->add(array('userid' => userid(), 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $mum, 'type' => 2, 'name' => 'trade', 'nameid' => $finance_nameid, 'remark' => '交易中心-委托买入-市场' . $market, 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
            }



        }
        else if ($type == 2) {
            error_log('$xnb='.$user_coin[$xnb].'&&num1='.$num."\r\n",3,'./aa.txt');
            if ($user_coin[$xnb] < $num) {
                $this->error(C('coin')[$xnb]['title'] . '余额不足2！');
            }

            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setDec($xnb, $num);

            //委托卖出账单明细
            $wallet=$mo->table('zhisucom_user_coin')->where("userid=".userid())->getField($xnb);
            $mark['curr']=$market;
            $mid=M('status')->where($mark)->getField('id');
            parent::addCashhistory(userid(),$mid,2,"卖出","-".$num.strtoupper($xnb),$price,$wallet.strtoupper($xnb),"委托卖出".strtoupper($xnb));
			
            //结束

            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->setInc($xnb . 'd', $num);
            $rs[] = $mo->table('zhisucom_trade')->add(array('userid' => userid(), 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $fee, 'type' => 2, 'addtime' => time(), 'status' => 0));
        }
        else {
            $mo->execute('rollback');
            $mo->execute('unlock tables');
            $this->error('交易类型错误');
        }

        if (check_arr($rs)) {
            $mo->execute('commit');
            $mo->execute('unlock tables');
            S('getDepth', null);
            $this->matchingTrade($market);
            $this->success('交易成功！');
        }
        else {
            $mo->execute('rollback');
            $mo->execute('unlock tables');
            $this->error('交易失败！');
        }
    }

    public function matchingTrade($market = NULL,$trade_pt = NULL)
    {
        if (!$market) {
            return false;
        }
        else {
            $xnb = explode('_', $market)[0];			//BTC_USDT  的BTC
            $rmb = explode('_', $market)[1];			//BTC_USDT  的USDT
        }

        //
        $return_vip_fee = get_vip_fee(userid());
        $vip_fee = $return_vip_fee[1];
        $fee_buy = C('market')[$market]['fee_buy']*$vip_fee;
        $fee_sell = C('market')[$market]['fee_sell']*$vip_fee;
        $invit_buy = C('market')[$market]['invit_buy'];
        $invit_sell = C('market')[$market]['invit_sell'];
        $invit_1 = C('market')[$market]['invit_1'];
        $invit_2 = C('market')[$market]['invit_2'];
        $invit_3 = C('market')[$market]['invit_3'];
		
//error_log('$fee_buy11='.C('market')[$market]['fee_buy'].'&$fee_sell11='.C('market')[$market]['fee_sell']."\r\n",3,'./bb.txt');		
error_log('$fee_buy='.$fee_buy.'&$fee_sell='.$fee_sell."\r\n",3,'./bb.txt');
        $mo = M();
        $new_trade_zhisucom = 0;

        for (; true; ) {
            $buy = $mo->table('zhisucom_trade')->where(array('market' => $market, 'type' => 1,'userid' => array('gt',0), 'status' => 0,'trade_pt'=>$trade_pt))->order('price desc,id asc')->find();
            $sell = $mo->table('zhisucom_trade')->where(array('market' => $market, 'type' => 2,'userid' => array('gt',0), 'status' => 0,'trade_pt'=>$trade_pt))->order('price asc,id asc')->find();

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
                $amount = min(round($buy['num'] - $buy['deal'], 8 - C('market')[$market]['round']), round($sell['num'] - $sell['deal'], 8 - C('market')[$market]['round']));
                $amount = round($amount, 8 - C('market')[$market]['round']);
				
				

                if ($amount <= 0) {
                    $log = '错误1交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . "\n";
                    $log .= 'ERR: 成交数量出错，数量是' . $amount;
                    //数量有小于等于0的，交易中记录直接改为已操作状态，操作结束
                    M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                    M('Trade')->where(array('id' => $sell['id']))->setField('status', 1);
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
                    $log = '错误2交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
                    $log .= 'ERR: 成交价格出错，价格是' . $price;
                    break;
                }
                else {
                    // TODO: SEPARATE
                    $price = round($price, C('market')[$market]['round']);
                }

                $mum = round($price * $amount, 8);//（1.2*100）//120

                if (!$mum) {
                    $log = '错误3交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
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
                    //$buy_save = round(($mum / 100) * (100 + $fee_buy), 8);//（120/100*（100+0.2））=120.24
                    //TODO:2018-07-25
                    $buy_save = round(($mum / 100) * (100), 8);//（120/100*（100+0.2））=120.24
                }
                else {
                    $buy_fee = 0;
                    $buy_save = $mum;
                }

                if (!$buy_save) {
                    $log = '错误4交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新数量出错，更新数量是' . $buy_save;
                    mlog($log);
                    break;
                }

                if ($fee_sell) {
                    $sell_fee = round(($mum / 100) * $fee_sell, 8);//（120/100*0.2=0.24）
			
                    $sell_save = round(($mum / 100) * (100 - $fee_sell), 8);//（120/100*（100-0.2））=119.76
					
error_log('sell='.$sell_fee.'sellsave='.$sell_save."\r\n",3,'./a.txt');
                }
                else {
                    $sell_fee = 0;
                    $sell_save = $mum;
                }

                if (!$sell_save) {
                    $log = '错误5交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 卖家更新数量出错，更新数量是' . $sell_save;
                    mlog($log);
                    break;
                }

                $user_buy = M('UserCoin')->where(array('userid' => $buy['userid']))->find();//买家货币信息

                //判断买家交易的币量保存到冻结币字段中
                if (!$user_buy[$rmb . 'd']) {
                    $log = '错误6交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家财产错误，冻结财产是' . $user_buy[$xnb . 'd'];
                    mlog($log);
                    break;
                }

                $user_sell = M('UserCoin')->where(array('userid' => $sell['userid']))->find();
                //判断卖家交易的币量保存到冻结币字段中
                if (!$user_sell[$xnb . 'd']) {
                    $log = '错误7交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 卖家财产错误，冻结财产是' . $user_sell[$xnb . 'd'];
                    mlog($log);
                    break;
                }
                if ($user_buy[$rmb . 'd'] < 1.0E-8) {
                //2018-07-18
                //if ($user_buy[$xnb . 'd'] < 1.0E-8) {
                    $log = '错误88交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新冻结人民币出现错误,应该更新' . $buy_save . '账号余额' . $user_buy[$xnb . 'd'] . '进行错误处理';
                    mlog($log);
                    M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                    break;
                }
error_log('$buy_save='.$buy_save.'&&&$rmbd='.round($user_buy[$rmb . 'd'], 8)."\r\n",3,'./a.txt');
                //买的需要更新的总价格 和 冻结买的币价格对比
                if ($buy_save <= round($user_buy[$rmb . 'd'], 8)) {
                    $save_buy_rmb = $buy_save;
                }
                else if ($buy_save <= round($user_buy[$rmb . 'd'], 8) + 1) {
                    $save_buy_rmb = $user_buy[$rmb . 'd'];
                    $log = '错误8交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新冻结人民币出现误差,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '实际更新' . $save_buy_rmb;
                    mlog($log);
                }
                else {
                    $log = '错误9交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新冻结人民币出现错误,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '进行错误处理';
                    mlog($log);
                    M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
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
                        $log = '错误10交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 卖家更新冻结虚拟币出现误差,应该更新' . $amount . '账号余额' . $user_sell[$xnb . 'd'] . '实际更新' . $save_sell_xnb;
                        mlog($log);
                    }
                    else {
                        $log = '错误11交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                        $log .= 'ERR: 卖家更新冻结虚拟币出现错误,应该更新' . $amount . '账号余额' . $user_sell[$xnb . 'd'] . '进行错误处理';
                        mlog($log);
                        M('Trade')->where(array('id' => $sell['id']))->setField('status', 1);
                        break;
                    }
                }

                if (!$save_buy_rmb) {
                    $log = '错误12交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新数量出错错误,更新数量是' . $save_buy_rmb;
                    mlog($log);
                    M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                    break;
                }

                if (!$save_sell_xnb) {
                    $log = '错误13交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 卖家更新数量出错错误,更新数量是' . $save_sell_xnb;
                    mlog($log);
                    M('Trade')->where(array('id' => $sell['id']))->setField('status', 1);
                    break;
                }

                $mo->execute('set autocommit=0');
				$mo->execute('lock tables zhisucom_status write ,zhisucom_chistory write ,zhisucom_trade write ,zhisucom_trade_log write ,zhisucom_user write,zhisucom_user_coin write,zhisucom_invit write ,zhisucom_finance write ,zhisucom_market write');


				$rs[] = $mo->table('zhisucom_trade')->where(array('id' => $buy['id']))->setInc('deal', $amount);
                $rs[] = $mo->table('zhisucom_trade')->where(array('id' => $sell['id']))->setInc('deal', $amount);
                $rs[] = $finance_nameid = $mo->table('zhisucom_trade_log')->add(array('userid' => $buy['userid'], 'peerid' => $sell['userid'], 'market' => $market, 'price' => $price, 'num' => $amount, 'mum' => $mum, 'type' => $type, 'fee_buy' => $buy_fee, 'fee_sell' => $sell_fee, 'addtime' => time(), 'status' => 1));
                
                
                //TODO:2018-07-25 买的手续费扣在买入币种上面（注意：要先把对应交易市场兑换成净入币种，再扣除）
                $duihuan_buy_fee = C('market')[$market]['new_price'];
                $new_amount = $amount - round($buy_fee/$duihuan_buy_fee,5);
                $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->setInc($xnb, $new_amount);
                //$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->setInc($xnb, $amount);
                
                
			//账单明细			
				$wallet=M('user_coin')->where("userid=".$buy['userid'])->getField($xnb);
				$mark['curr']=$market;
				$mid=M('status')->where($mark)->getField('id');	
				parent::addCashhistory($buy['userid'],$mid,1,"买入","+".$new_amount.strtoupper($xnb),$price,$wallet.strtoupper($xnb),strtoupper($rmb)."买入".strtoupper($xnb));				
			//结束
			
				
                $finance = $mo->table('zhisucom_finance')->where(array('userid' => $buy['userid']))->order('id desc')->find();
                $finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->find();
                
                //减去手续费按最后币种计算的
                $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->setDec($rmb . 'd', $save_buy_rmb);
                //2018-07-18(冻结币种清0)
                //$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->setDec($xnb . 'd', $save_buy_rmb);
                $finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->find();
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
                $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $sell['userid']))->setInc($rmb, $sell_save);
                $finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $sell['userid']))->find();
				
				
				
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



                $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $sell['userid']))->setDec($xnb . 'd', $save_sell_xnb);
                $buy_list = $mo->table('zhisucom_trade')->where(array('id' => $buy['id'], 'status' => 0))->find();

                if ($buy_list) {
                    if ($buy_list['num'] <= $buy_list['deal']) {
                        $rs[] = $mo->table('zhisucom_trade')->where(array('id' => $buy['id']))->setField('status', 1);
                    }
                }

                $sell_list = $mo->table('zhisucom_trade')->where(array('id' => $sell['id'], 'status' => 0))->find();

                if ($sell_list) {
                    if ($sell_list['num'] <= $sell_list['deal']) {
                        $rs[] = $mo->table('zhisucom_trade')->where(array('id' => $sell['id']))->setField('status', 1);
                    }
                }

                if ($price < $buy['price']) {
                    $chajia_dong = round((($amount * $buy['price']) / 100) * (100 + $fee_buy), 8);
                    $chajia_shiji = round((($amount * $price) / 100) * (100 + $fee_buy), 8);
                    $chajia = round($chajia_dong - $chajia_shiji, 8);

                    if ($chajia) {
                        $chajia_user_buy = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->find();

                        if ($chajia <= round($chajia_user_buy[$rmb . 'd'], 8)) {
                            $chajia_save_buy_rmb = $chajia;
                        }
                        else if ($chajia <= round($chajia_user_buy[$rmb . 'd'], 8) + 1) {
                            $chajia_save_buy_rmb = $chajia_user_buy[$rmb . 'd'];
                            mlog('错误91交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount, '成交价格' . $price . '成交总额' . $mum . "\n");
                            mlog('交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '成交数量' . $amount . '交易方式：' . $type . '卖家更新冻结虚拟币出现误差,应该更新' . $chajia . '账号余额' . $chajia_user_buy[$rmb . 'd'] . '实际更新' . $chajia_save_buy_rmb);
                        }
                        else {
                            mlog('错误92交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount, '成交价格' . $price . '成交总额' . $mum . "\n");
                            mlog('交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '成交数量' . $amount . '交易方式：' . $type . '卖家更新冻结虚拟币出现错误,应该更新' . $chajia . '账号余额' . $chajia_user_buy[$rmb . 'd'] . '进行错误处理');
                            $mo->execute('rollback');
                            $mo->execute('unlock tables');
                            M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                            M('Trade')->execute('commit');
                            break;
                        }

                        if ($chajia_save_buy_rmb) {
                            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->setDec($rmb . 'd', $chajia_save_buy_rmb);
                            $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->setInc($rmb, $chajia_save_buy_rmb);
                        }
                    }
                }

                $you_buy = $mo->table('zhisucom_trade')->where(array(
                    'market' => array('like', '%' . $rmb . '%'),
                    'status' => 0,
                    'userid' => $buy['userid']
                ))->find();
                $you_sell = $mo->table('zhisucom_trade')->where(array(
                    'market' => array('like', '%' . $xnb . '%'),
                    'status' => 0,
                    'userid' => $sell['userid']
                ))->find();

                if (!$you_buy) {
                    $you_user_buy = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->find();

                    if (0 < $you_user_buy[$rmb . 'd']) {
                        $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->setField($rmb . 'd', 0);
                        $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $buy['userid']))->setInc($rmb, $you_user_buy[$rmb . 'd']);
                    }
                }

                if (!$you_sell) {
                    $you_user_sell = $mo->table('zhisucom_user_coin')->where(array('userid' => $sell['userid']))->find();

                    if (0 < $you_user_sell[$xnb . 'd']) {
                        $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $sell['userid']))->setField($xnb . 'd', 0);
                        $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $sell['userid']))->setInc($rmb, $you_user_sell[$xnb . 'd']);
                    }
                }

                $invit_buy_user = $mo->table('zhisucom_user')->where(array('id' => $buy['userid']))->find();
                $invit_sell_user = $mo->table('zhisucom_user')->where(array('id' => $sell['userid']))->find();
				//赠送手续费等量BDB
					//买入
					$coindui['name']=$rmb."_bdb";
					if($buy_fee>0){							
						if($rmb=="bdb"){
							$buyprice=1;
						}else{
							$buyprice=$mo->table('zhisucom_market')->where($coindui)->getField("new_price");
						}
						//获取币种对BDB的最新价格
						$buyfee=round($buy_fee*$buyprice,8);
						M('user_coin')->where("userid=".$buy['userid'])->setInc('bdb',$buyfee);
						//账单明细			
						$wallet=M('user_coin')->where("userid=".$buy['userid'])->getField('bdb');
						$mark['curr']="bdb";
						$mid=M('status')->where($mark)->getField('id');	
						parent::addCashhistory($buy['userid'],$mid,8,"赠送","+".$buyfee."BDB",0,$wallet."BDB","买入".strtoupper($xnb)."赠送等额手续费"."BDB");				
						unset($buyfee);
						//结束
					}		
					//卖出
					if($sell_fee>0){
						if($rmb=="bdb"){
							$sellprice=1;
						}else{
							$sellprice=$mo->table('zhisucom_market')->where($coindui)->getField("new_price");
						}
						//获取币种对BDB的最新价格				
						$sellfee=round($sell_fee*$sellprice,8);
						M('user_coin')->where("userid=".$sell['userid'])->setInc('bdb',$sellfee);
						//账单明细			
						$wallet=M('user_coin')->where("userid=".$sell['userid'])->getField('bdb');
						$mark['curr']="bdb";
						$mid=M('status')->where($mark)->getField('id');	
						parent::addCashhistory($sell['userid'],$mid,8,"赠送","+".$sellfee."BDB",0,$wallet.'BDB',"卖出".strtoupper($xnb)."赠送等额手续费"."BDB");				
						unset($sellfee);
						//结束
					}			
				//结束				
                if ($invit_buy) {
                    if ($invit_1) {
                        if ($buy_fee) {		
                            if ($invit_buy_user['invit_1']) {
                                $invit_buy_save_1 = round(($buy_fee / 100) * $invit_1, 6);	

							//返利30%到推荐人								
								$buyfee=round($invit_buy_save_1*$buyprice,8);		
								if($buyfee>0){
									$mo->table('zhisucom_user_coin')->where('userid='.$invit_buy_user['invit_1'])->setInc("bdb",$buyfee);																	
									//账单明细			
									$wallet=M('user_coin')->where("userid=".$invit_buy_user['invit_1'])->getField('bdb');
									$mark['curr']="bdb";
									$mid=M('status')->where($mark)->getField('id');	
									$name=M('user')->where("id=".$buy['userid'])->getField('truename');
									parent::addCashhistory($invit_buy_user['invit_1'],$mid,9,"一代返佣","+".$buyfee."BDB",0,$wallet."BDB","一代推荐:".$name."交易返佣"."BDB");
								}								
							//结束
							
                                if ($invit_buy_save_1) {
                            //        $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $invit_buy_user['invit_1']))->setInc($rmb, $invit_buy_save_1);
                                    $rs[] = $mo->table('zhisucom_invit')->add(array('userid' => $invit_buy_user['invit_1'], 'invit' => $buy['userid'], 'name' => '一代买入赠送', 'type' => $market . '买入交易赠送', 'num' => $new_amount, 'mum' => $mum, 'fee' => $buyfee, 'addtime' => time(), 'status' => 1));
                                }
                            }

                            if ($invit_buy_user['invit_2']) {
                                $invit_buy_save_2 = round(($buy_fee / 100) * $invit_2, 6);

                                if ($invit_buy_save_2) {
                                    $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $invit_buy_user['invit_2']))->setInc($rmb, $invit_buy_save_2);
                                    $rs[] = $mo->table('zhisucom_invit')->add(array('userid' => $invit_buy_user['invit_2'], 'invit' => $buy['userid'], 'name' => '二代买入赠送', 'type' => $market . '买入交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_buy_save_2, 'addtime' => time(), 'status' => 1));
                                }
                            }

                            if ($invit_buy_user['invit_3']) {
                                $invit_buy_save_3 = round(($buy_fee / 100) * $invit_3, 6);

                                if ($invit_buy_save_3) {
                                    $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $invit_buy_user['invit_3']))->setInc($rmb, $invit_buy_save_3);
                                    $rs[] = $mo->table('zhisucom_invit')->add(array('userid' => $invit_buy_user['invit_3'], 'invit' => $buy['userid'], 'name' => '三代买入赠送', 'type' => $market . '买入交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_buy_save_3, 'addtime' => time(), 'status' => 1));
                                }
                            }
                        }
                    }

                    if ($invit_sell) {
                        if ($sell_fee) {
                            if ($invit_sell_user['invit_1']) {
                                $invit_sell_save_1 = round(($sell_fee / 100) * $invit_1, 6);
								
							//返利30%到推荐人	
								
								$sellfee=round($invit_sell_save_1*$sellprice,8);		
								if($sellfee>0){
									$mo->table('zhisucom_user_coin')->where('userid='.$invit_sell_user['invit_1'])->setInc("bdb",$sellfee);																		
									//账单明细			
									$wallet=M('user_coin')->where("userid=".$invit_sell_user['invit_1'])->getField("bdb");
									$mark['curr']="bdb";
									$mid=M('status')->where($mark)->getField('id');	
									$name=M('user')->where("id=".$sell['userid'])->getField('truename');
									parent::addCashhistory($invit_sell_user['invit_1'],$mid,9,"一代返佣","+".$sellfee."BDB",0,$wallet."BDB","一代推荐:".$name."交易返佣"."BDB");					
								}	
							//结束

                                if ($invit_sell_save_1) {
                            //        $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $invit_sell_user['invit_1']))->setInc($rmb, $invit_sell_save_1);
                                    $rs[] = $mo->table('zhisucom_invit')->add(array('userid' => $invit_sell_user['invit_1'], 'invit' => $sell['userid'], 'name' => '一代卖出赠送', 'type' => $market . '卖出交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $sellfee, 'addtime' => time(), 'status' => 1));
                                }
                            }

                            if ($invit_sell_user['invit_2']) {
                                $invit_sell_save_2 = round(($sell_fee / 100) * $invit_2, 6);

                                if ($invit_sell_save_2) {
                                    $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $invit_sell_user['invit_2']))->setInc($rmb, $invit_sell_save_2);
                                    $rs[] = $mo->table('zhisucom_invit')->add(array('userid' => $invit_sell_user['invit_2'], 'invit' => $sell['userid'], 'name' => '二代卖出赠送', 'type' => $market . '卖出交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_sell_save_2, 'addtime' => time(), 'status' => 1));
                                }
                            }

                            if ($invit_sell_user['invit_3']) {
                                $invit_sell_save_3 = round(($sell_fee / 100) * $invit_3, 6);

                                if ($invit_sell_save_3) {
                                    $rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $invit_sell_user['invit_3']))->setInc($rmb, $invit_sell_save_3);
                                    $rs[] = $mo->table('zhisucom_invit')->add(array('userid' => $invit_sell_user['invit_3'], 'invit' => $sell['userid'], 'name' => '三代卖出赠送', 'type' => $market . '卖出交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_sell_save_3, 'addtime' => time(), 'status' => 1));
                                }
                            }
                        }
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

        if ($new_trade_zhisucom) {
            $new_price = round(M('TradeLog')->where(array('market' => $market, 'status' => 1))->order('id desc')->getField('price'), 6);
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
                M('Market')->where(array('name' => $market))->save($upCoinData);
                M('Market')->execute('commit');
                S('home_market', null);
            }
        }
    }

    public function matchingAutoTrade($market = NULL)
    {
        if (!$market) {
            return false;
        }
        else {
            $xnb = explode('_', $market)[0];
            $rmb = explode('_', $market)[1];
        }

        $mo = M();
        $new_trade_zhisucom = 0;

        for (; true; ) {
            $buy = $mo->table('zhisucom_trade')->where(array('market' => $market, 'type' => 1, 'userid'=>0,'status' => 0))->order('price desc,id asc')->find();
            $sell = $mo->table('zhisucom_trade')->where(array('market' => $market, 'type' => 2, 'userid'=>0,'status' => 0))->order('price asc,id asc')->find();

            if ($sell['id'] < $buy['id']) {
                $type = 1;
            }
            else {
                $type = 2;
            }

            if ($buy && $sell && (0 <= floatval($buy['price']) - floatval($sell['price']))) {
                $rs = array();

                if ($buy['num'] <= $buy['deal']) {
                }

                if ($sell['num'] <= $sell['deal']) {
                }

                $amount = min(round($buy['num'] - $buy['deal'], 8 - C('market')[$market]['round']), round($sell['num'] - $sell['deal'], 8 - C('market')[$market]['round']));
                $amount = round($amount, 8 - C('market')[$market]['round']);

                if ($amount <= 0) {
                    $log = '错误1交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . "\n";
                    $log .= 'ERR: 成交数量出错，数量是' . $amount;
                    M('Trade')->where(array('id' => $buy['id']))->setField('status', 1);
                    M('Trade')->where(array('id' => $sell['id']))->setField('status', 1);
                    break;
                }

                if ($type == 1) {
                    $price = $sell['price'];
                }
                else if ($type == 2) {
                    $price = $buy['price'];
                }
                else {
                    break;
                }


                if (!$price) {
                    $log = '错误2交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
                    $log .= 'ERR: 成交价格出错，价格是' . $price;
                    break;
                }
                else {
                    // TODO: SEPARATE
                    $price = round($price, 4);
                }

                $mum = round($price * $amount, 4);

                if (!$mum) {
                    $log = '错误3交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
                    $log .= 'ERR: 成交价格'.$price.'成交总额出错，总额是' . $mum;
                    mlog($log);
                    break;
                }
                else {
                    $mum = round($mum, 4);
                }

                if ($fee_buy) {
                    $buy_fee = round(($mum / 100) * $fee_buy, 4);
                    $buy_save = round(($mum / 100) * (100 + $fee_buy), 4);
                }
                else {
                    $buy_fee = 0;
                    $buy_save = $mum;
                }

                if (!$buy_save) {
                    $log = '错误4交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 买家更新数量出错，更新数量是' . $buy_save;
                    mlog($log);
                    break;
                }

                if ($fee_sell) {
                    $sell_fee = round(($mum / 100) * $fee_sell, 8);
                    $sell_save = round(($mum / 100) * (100 - $fee_sell), 8);
                }
                else {
                    $sell_fee = 0;
                    $sell_save = $mum;
                }

                if (!$sell_save) {
                    $log = '错误5交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
                    $log .= 'ERR: 卖家更新数量出错，更新数量是' . $sell_save;
                    mlog($log);
                    break;
                }


                $mo->execute('set autocommit=0');
                $mo->execute('lock tables zhisucom_trade write ,zhisucom_trade_log write ');

                $rs[] = $mo->table('zhisucom_trade')->where(array('id' => $buy['id']))->setInc('deal', $amount);
                $rs[] = $mo->table('zhisucom_trade')->where(array('id' => $sell['id']))->setInc('deal', $amount);

                $rs[] = $mo->table('zhisucom_trade_log')->add(array('userid' => 0, 'peerid' => 0, 'market' => $market, 'price' => $price, 'num' => $amount, 'mum' => $mum, 'type' => $type, 'fee_buy' => 0, 'fee_sell' => 0, 'addtime' => time(), 'status' => 1));

                $buy_list = $mo->table('zhisucom_trade')->where(array('id' => $buy['id'], 'status' => 0))->find();

                if ($buy_list) {
                    if ($buy_list['num'] <= $buy_list['deal']) {
                        $rs[] = $mo->table('zhisucom_trade')->where(array('id' => $buy['id']))->setField('status', 1);
                    }
                }

                $sell_list = $mo->table('zhisucom_trade')->where(array('id' => $sell['id'], 'status' => 0))->find();

                if ($sell_list) {
                    if ($sell_list['num'] <= $sell_list['deal']) {
                        $rs[] = $mo->table('zhisucom_trade')->where(array('id' => $sell['id']))->setField('status', 1);
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

        mlog("duqunew_trade_".$new_trade_zhisucom);

        if ($new_trade_zhisucom) {

            mlog("wojinlail".$new_trade_zhisucom);


            $new_price = round(M('TradeLog')->where(array('market' => $market, 'status' => 1))->order('id desc')->getField('price'), 6);
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
                M('Market')->where(array('name' => $market))->save($upCoinData);
                M('Market')->execute('commit');
                S('home_market', null);
            }
        }
    }
	
	public function chexiao(){
		if (!userid()) {
            // $this->error('请先登录！');
            $this->ajaxReturn(array('code'=>1002));
        }
		$id = I('post.id');
		$trade = M('Trade')->where(array('id' => $id))->save(array('status'=>2));
		if($trade){
			// $this->success('撤单成功');
            $this->ajaxReturn(array('code'=>1012));
		}else{
			// $this->error('撤单失败');
            $this->ajaxReturn(array('code'=>1013));
		}
	}

    public function chexiao2($id)
    {
        if (!userid()) {
            // $this->error('请先登录！');
            $this->ajaxReturn(array('code'=>1013));
        }
		$id = I('post.id');
        if (!check($id, 'd')) {
            // $this->error('请选择要撤销的委托！');
            $this->ajaxReturn(array('code'=>1013));
        }

        $trade = M('Trade')->where(array('id' => $id))->find();

        if (!$trade) {
            // $this->error('撤销委托参数错误！');
            $this->ajaxReturn(array('code'=>1013));
        }

        if ($trade['userid'] != userid()) {
            // $this->error('参数非法！');
            $this->ajaxReturn(array('code'=>1013));
        }

        $this->show(D('Trade')->chexiao($id));
    }

    public function show($rs = array())
    {
        if ($rs[0]) {
            // $this->success($rs[1]);
            $this->ajaxReturn(array('code'=>1012));
        }
        else {
            // $this->error($rs[1]);
            $this->ajaxReturn(array('code'=>1013));
        }
    }

    public function install()
    {
    }

    public function c2c(){
        $stat = 1;

        if (userid()) {
            $user = M('User')->where(array('id' => userid()))->find();
            $stat = $user['isshop'];
        }
        $this->assign('stat', $stat);
        $this->display();
    }
    public function c2cdo($act=null){

        if (!userid()) {
            $this->error('请先登录！');
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
        }

        $user = M('User')->where(array('id' => userid()))->find();



        if ($user && $user['isshop']) {
            $num = I('post.num');
            if ($num == null) $this->error('请填写数量');
            if (!is_numeric($num)) $this->error('请填写正确的数量');

            unset($data);
            $data = [
                'userid'   => userid(),
                'coinname' => 'usdt',
                'price'    => C('market')['usdt_cny']['new_price'] * $num,
                'num'      => $num,
                'remark'   => 'c2c-usdt商户买入交易',
                'isshop'   => 1,
				'stat'		=>1,
                'addtime'  => $cur_time,
				'cprice'  => C('market')['usdt_cny']['new_price'],
                ];
            }

        if($data){
            $info = M('finance')->add($data);
            $this->success('订单提交成功,等待客服确认！');
        }else{
            $this->error('订单提交失败！请再次确认信息！');
        }
    }

    public function c2cdo1($act=null){

        /*if (!userid()) {
            redirect('/#login');
        }*/


        if (!userid()) {
            $this->error('请先登录！');
        }
        /*if (!userid()) {
            redirect('/#login');
        }*/
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
        }

        $user = M('User')->where(array('id' => userid()))->find();



        if ($user && $user['isshop']) {
            $num = I('post.num');
            if ($num == null) $this->error('请填写数量');
            if (!is_numeric($num)) $this->error('请填写正确的数量');

            unset($data);
            $data = [
                'userid'   => userid(),
                'coinname' => 'usdt',
				'cprice'  => C('market')['usdt_cny']['new_price'],
                'price'    => C('market')['usdt_cny']['new_price'] * $num,
                'num'      => $num,
                'remark'   => 'c2c-usdt商户卖出交易',
                'isshop'   => 1,
				'stat'=>2,
                'addtime'  => $cur_time,
            ];
        }

        if($data){
            $info = M('finance')->add($data);
            $this->success('订单提交成功,等待客服确认！');
        }else{
            $this->error('订单提交失败！请再次确认信息！');
        }
    }


     //风险率
    public function ajax_fx123(){
        $market = $_POST['market'];
        // $market = 'eth';
        $coin = M('User_coin')->where(array('userid'=>userid()))->find();
        $zzc = $coin['gg'.$market] + $coin[$market.'bow'];
        $where = $market."_jtc";
        $mmp = M('Market')->where(array('name'=>$where))->getField('new_price');
        $jtc_zh = $zzc * $mmp + $coin[$market.'jtc'] + $coin['jtcbow'];#总资产

        $lever = M('Lever')->where(array('userid'=>userid(),'status'=>1))->select();
        foreach ($lever as $k => $v) {
            if($v['currency'] == $market){
                $fee_sum += $v['interest']*$mmp;#总利息
                $z_borrow += $v['borrow']*$v['new_price'];
            }elseif($v['currency'] == 'jtc'){
                $jtc_sum += $v['interest'];
                $jtc_borrow += $v['borrow'];
            }
        }
        //dump($z_borrow);die;
        $fxl = ($jtc_zh - ($fee_sum+$jtc_sum))/($z_borrow+$jtc_borrow);
        // echo $fxl;die;
        $config = M('Config')->where('id=1')->field('lever_fx')->find();
        if(($fxl*100) <= $config['lever_fx'] && $fxl>0){
            $levers = M("Lever")->where(array('currency'=>$market,'status'=>1))->select();
            // dump($levers);
            if($levers){
                foreach ($levers as $k => $v) {
                    $this->repayment_auto($v['b_order']);
                }
            }
        }
        $config['fxl'] = round($fxl,2);
        if($fxl > 0){
            $this->ajaxReturn(array('code'=>'200','result'=>$config));
        }
        

    }
 //风险率
    public function ajax_fx(){
        $market = $_POST['market'];
        // $market = 'eth';
        $coin = M('User_coin')->where(array('userid'=>userid()))->find();
        $zzc = $coin['gg'.$market] + $coin[$market.'bow'];
        $where = $market."_jtc";
        $mmp = M('Market')->where(array('name'=>$where))->getField('new_price');
        $jtc_zh = $zzc * $mmp + $coin[$market.'jtc'] + $coin['jtcbow'];#总资产

        $lever = M('Lever')->where(array('userid'=>userid(),'status'=>1))->select();
        foreach ($lever as $k => $v) {
            if($v['currency'] == $market){
                $fee_sum += $v['interest']*$mmp;#总利息
                $z_borrow += $v['borrow']*$v['new_price'];
            }elseif($v['currency'] == 'jtc'){
                $jtc_sum += $v['interest'];
                $jtc_borrow += $v['borrow'];
            }
        }
        //dump($z_borrow);die;
        $fxl = ($jtc_zh - ($fee_sum+$jtc_sum))/($z_borrow+$jtc_borrow);
        // echo $fxl;die;
        $config = M('Config')->where('id=1')->field('lever_fx')->find();
        if(($fxl*100) <= $config['lever_fx'] && $fxl>0){
            $levers = M("Lever")->where(array('currency'=>$market,'status'=>1))->select();
            // dump($levers);
            if($levers){
                foreach ($levers as $k => $v) {
                    $this->repayment_auto($v['b_order']);
                }
            }
        }
        $config['fxl'] = round($fxl,2);
        if($fxl > 0){
            $this->ajaxReturn(array('code'=>'200','result'=>$config));
        }
        

    }

    //虚拟币借款
    public function xnb_borrow(){
        // dump($_POST);
        if(!userid()){
            // $this->error('请登录');
            $this->ajaxReturn(array('code'=>1002));
        }
        if($_POST){
            $borrow = I('post.borrow_num');
            $currency = I('post.coin_type').'bow';
            $currencyss = I('post.coin_type');
            $fee = M('Config')->where('id=1')->getField('lever_lixi');
            $interest = $borrow * ($fee/100);
            $zj = $borrow - $interest;
            $where = $currencyss."_jtc";
            $new_price = M('Market')->where(array('name'=>$where))->getField('new_price');
            $coin = M('User_coin')->where(array('userid'=>userid()))->setInc($currency,$zj);
            // M('User_coin')->where(array('userid'=>userid()))->setInc($currencyss,$zj);
            $info = M('Lever')->add(array('userid'=>userid(),'borrow'=>$borrow,'interest'=>$interest,'currency'=>$currencyss,'addtime'=>time(),'status'=>1,'b_order'=>round_order(),'interest_fee'=>$fee,'new_price'=>$new_price));
            if($info){
                // $this->success('借款成功');
                $this->ajaxReturn(array('code'=>1012));
            }else{
                // $this->error('借款失败');
                $this->ajaxReturn(array('code'=>1013));
            }
        }
    }


    //JTC借款
    public function rgb_borrow(){
       if(!userid()){
            // $this->error('请登录');
        $this->ajaxReturn(array('code'=>1002));
        }
        if($_POST){
            $borrow = I('post.borrow_num');
            $currency = I('post.coin_type').'bow';
            $currencyss = I('post.coin_type');
            $fee = M('Config')->where('id=1')->getField('lever_lixi');
            $interest = $borrow * ($fee/100);
            $zj = $borrow - $interest;
            $coin = M('User_coin')->where(array('userid'=>userid()))->setInc($currency,$zj);
            // M('User_coin')->where(array('userid'=>userid()))->setInc($currencyss,$zj);
            $info = M('Lever')->add(array('userid'=>userid(),'borrow'=>$borrow,'interest'=>$interest,'currency'=>$currencyss,'addtime'=>time(),'status'=>1,'b_order'=>round_order(),'interest_fee'=>$fee));
            if($info){
                // $this->success('借款成功');
                $this->ajaxReturn(array('code'=>1012));
            }else{
                // $this->error('借款失败');
                $this->ajaxReturn(array('code'=>1013));
            }
        } 
    }

    //杠杆管理借款数据
    public function borrow_info(){
        if(!userid()){
            $this->error('请登录');
        }
        $lever = M('Lever');
        $bz = I('post.coin');
        $config = M('Config')->where('id=1')->find();
        $xnb_sum = $lever->where(array('userid'=>userid(),'currency'=>$bz,'status'=>1))->sum('borrow');
        // echo $xnb_sum;die;
        $rgb_sum = $lever->where(array('userid'=>userid(),'currency'=>'jtc','status'=>1))->sum('borrow');
        $coin = M('User_coin')->where(array('userid'=>userid()))->find();
        $xnb_kejie = $coin['gg'.$bz] * ($config['lever_bs'] - 1) - $xnb_sum;
        $rgb_kejie = $coin['gg'.'jtc'] * ($config['lever_bs'] - 1) - $rgb_sum;
        $xnb_ky = $coin['gg'.$bz] + $xnb_sum;
        $rgb_ky = $coin['ggjtc'] + $rgb_sum;
        $lilv = M('Config')->where('id=1')->getField('lever_lixi');
        $this->ajaxReturn(array(
            'xnb_ky'=>$xnb_ky,
            'rgb_ky'=>$rgb_ky,
            'xnb_kejie'=>$xnb_kejie,
            'rgb_kejie'=>$rgb_kejie,
            'xnb_sum'=>$xnb_sum,
            'rgb_sum'=>$rgb_sum,
            'lilv'=>$lilv,
        ));
    }

    //杠杆币种
    public function bz(){
         $market = C('MARKET');
        foreach ($market as $k => $v) {
          if ($v['jiaoyiqu']==0){
                $list['JTC'][]=$v;
            }
        }
        $this->ajaxReturn(array('code'=>200,'jtc'=>$list['JTC']));
    }

    //杠杆当前申请的账单
    public function now_list(){
        if(!userid()){
            $this->error('请登录');
        }
		$lever = M('Lever');
        $info1 = $lever->where(array('userid'=>userid(),'status'=>1))->order('addtime desc')->select();
        if($info1){
            $this->ajaxReturn(array('code'=>200,'info'=>$info1));
        }else{
            $this->ajaxReturn(array('code'=>404,'info'=>'查询为空'));
        }
    }

    //还款
    public function repayment(){
        if($_POST){
            $b_order = $_POST['order'];
        }
        $lever = M('Lever');
        $market = M('Market');
        // $aaa = 'HA18346095688448';
		//$b_order = 'HA18346095688448';
		$uid = userid();
		//$uid = 7;
        $res = $lever->where(array('b_order'=>$b_order))->find();
        //dump($res);die;
        $res['yh'] = $res['borrow']+$res['interest'];
        // echo $res['yh'];die;
        $coin = M('User_coin')->where(array('userid'=>$uid))->find();
        $new_price = $market->where(array('name'=>$res['currency'].'_jtc'))->getField('new_price');
        // echo $coin[$res['currency'].'bow'];die;
        if($res['currency'] != 'jtc'){
            if($coin[$res['currency'].'bow'] >= $res['yh']){
				$a = $coin[$res['currency'].'bow'] - $res['yh'];
				$b[$coin[$res['currency'].'bow']] = $a;
				//dump($b);die;
				//$coin_info = M('User_coin')->where(array('userid'=>$uid))
                $coin_info = M('User_coin')->where(array('userid'=>$uid))->setDec($res['currency'].'bow',$res['yh']);
				//var_dump($coin_info);die;
            }elseif($coin[$res['currency'].'bow'] > 0 && $coin[$res['currency'].'bow'] < $res['yh'] && $coin['gg'.$res['currency']] > 0){
                $coin_z = $coin['gg'.$res['currency']]-($res['borrow'] + $res['interest'] - $coin[$res['currency'].'bow']);
                $coin_info = M('User_coin')
                            ->where(array('userid'=>$uid))
                            ->save(array('gg'.$res['currency']=>$coin_z,$res['currency'].'bow'=>0));
            }elseif($coin['ggjtc']>0 && $coin['gg'.$res['currency']]>0 && $coin['gg'.$res['currency']]<$res['yh']){
                $jtc_zh = $coin['ggjtc']/$new_price;
                $jtc_bow = $coin['jtcbow']/$new_price;
                $coin_z = $jtc_zh-($res['borrow'] + $res['interest']-$coin[$res['currency'].'bow']-$coin['gg'.$res['currency']]);
                if($coin_z<0){
                    $coin_s = $jtc_bow-$coin_z;
                    $jtc = $coin_s*$new_price;
                    $coin_info = M('User_coin')
                            ->where(array('userid'=>$uid))
                            ->save(array('gg'.$res['currency']=>0,$res['currency'].'bow'=>0,'ggjtc'=>0,'jtcbow'=>$coin_s));
                }
                $jtc = $coin_z*$new_price;
                $coin_info = M('User_coin')
                            ->where(array('userid'=>$uid))
                            ->save(array('gg'.$res['currency']=>0,$res['currency'].'bow'=>0,'ggjtc'=>$coin_z));
            }
        }
        else{
            if($coin['jtcbow']>=$res['yh']){
                $coin_info = M('User_coin')->where(array('userid'=>$uid))->setDec('jtcbow',$res['yh']);
            }elseif($coin['jtcbow']<$res['yh'] && $coin['ggjtc']>0 && $coin['jtcbow']>0){
                $coin_z = $coin['ggjtc']-($res['borrow'] + $res['interest'] - $coin['jtcbow']);
                $coin_info = M('User_coin')
                            ->where(array('userid'=>$uid))
                            ->save(array('ggjtc'=>$coin_z,'jtcbow'=>0));
            }
        }
        //dump($coin_info);die;
        if($coin_info){
            $repay = $res['borrow']+$res['interest'];
            $lever_info = $lever->where(array('b_order'=>$b_order))->save(array('status'=>2,'endtime'=>time(),'r_order'=>round_order(),'repayment'=>$repay));
            if($lever_info){
                // $this->success('还款成功');
                $this->ajaxReturn(array('code'=>1012));
            }else{
                // $this->error('还款失败');
                $this->ajaxReturn(array('code'=>1013));
            }
        }
    }
	
	
	public function repaymentdddd(){
        if($_POST){
            $b_order = $_POST['order'];
        }
        
        $lever = M('Lever');
        $market = M('Market');
        // $aaa = 'H912408715752030';
        $res = $lever->where(array('b_order'=>$b_order))->find();
		//dump($res);die;
        $res['yh'] = $res['borrow']+$res['interest'];
        // echo $res['yh'];die;
        $coin = M('User_coin')->where(array('userid'=>userid()))->find();
        $new_price = $market->where(array('name'=>$res['currency'].'_jtc'))->getField('new_price');
        // echo $coin[$res['currency'].'bow'];die;
        if($res['currency'] != 'jtc'){
            if($coin[$res['currency'].'bow'] >= $res['yh']){
                $coin_info = M('User_coin')->where(array('userid'=>userid()))->setDec($coin[$res['currency'].'bow'],$res['yh']);
            }elseif($coin[$res['currency'].'bow'] > 0 && $coin[$res['currency'].'bow'] < $res['yh'] && $coin['gg'.$res['currency']] > 0){
                $coin_z = $coin['gg'.$res['currency']]-($res['borrow'] + $res['interest'] - $coin[$res['currency'].'bow']);
                $coin_info = M('User_coin')
                            ->where(array('userid'=>userid()))
                            ->save(array('gg'.$res['currency']=>$coin_z,$res['currency'].'bow'=>0));
            }elseif($coin['ggjtc']>0 && $coin['gg'.$res['currency']]>0 && $coin['gg'.$res['currency']]<$res['yh']){
                $jtc_zh = $coin['ggjtc']/$new_price;
                $jtc_bow = $coin['jtcbow']/$new_price;
                $coin_z = $jtc_zh-($res['borrow'] + $res['interest']-$coin[$res['currency'].'bow']-$coin['gg'.$res['currency']]);
                if($coin_z<0){
                    $coin_s = $jtc_bow-$coin_z;
                    $jtc = $coin_s*$new_price;
                    $coin_info = M('User_coin')
                            ->where(array('userid'=>userid()))
                            ->save(array('gg'.$res['currency']=>0,$res['currency'].'bow'=>0,'ggjtc'=>0,'jtcbow'=>$coin_s));
                }
                $jtc = $coin_z*$new_price;
                $coin_info = M('User_coin')
                            ->where(array('userid'=>userid()))
                            ->save(array('gg'.$res['currency']=>0,$res['currency'].'bow'=>0,'ggjtc'=>$coin_z));
            }
        }
        else{
            if($coin['jtcbow']>=$res['yh']){
                $coin_info = M('User_coin')->where(array('userid'=>userid()))->setDec('jtcbow',$res['yh']);
            }elseif($coin['jtcbow']<$res['yh'] && $coin['ggjtc']>0 && $coin['jtcbow']>0){
                $coin_z = $coin['ggjtc']-($res['borrow'] + $res['interest'] - $coin['jtcbow']);
                $coin_info = M('User_coin')
                            ->where(array('userid'=>userid()))
                            ->save(array('ggjtc'=>$coin_z,'jtcbow'=>0));
            }
        }
		//dump($coin_info);die;
        if($coin_info){
            $repay = $res['borrow']+$res['interest'];
            $lever_info = $lever->where(array('b_order'=>$b_order))->save(array('status'=>2,'endtime'=>time(),'r_order'=>round_order(),'repayment'=>$repay));
            if($lever_info){
                // $this->success('还款成功');
				 $this->ajaxReturn(array('code'=>1012));
            }else{
                // $this->error('还款失败');
				 $this->ajaxReturn(array('code'=>1013));
            }
        }
    }

    //自动还款
     public function repayment_auto($b_order){
        
        
        $lever = M('Lever');
        $market = M('Market');
        // $aaa = 'H912408715752030';
        $res = $lever->where(array('b_order'=>$b_order))->find();
        //dump($res);die;
        $res['yh'] = $res['borrow']+$res['interest'];
        // echo $res['yh'];die;
        $coin = M('User_coin')->where(array('userid'=>userid()))->find();
        $new_price = $market->where(array('name'=>$res['currency'].'_jtc'))->getField('new_price');
        // echo $coin[$res['currency'].'bow'];die;
        if($res['currency'] != 'jtc'){
            if($coin[$res['currency'].'bow'] >= $res['yh']){
                $coin_info = M('User_coin')->where(array('userid'=>userid()))->setDec($coin[$res['currency'].'bow'],$res['yh']);
            }elseif($coin[$res['currency'].'bow'] > 0 && $coin[$res['currency'].'bow'] < $res['yh'] && $coin['gg'.$res['currency']] > 0){
                $coin_z = $coin['gg'.$res['currency']]-($res['borrow'] + $res['interest'] - $coin[$res['currency'].'bow']);
                $coin_info = M('User_coin')
                            ->where(array('userid'=>userid()))
                            ->save(array('gg'.$res['currency']=>$coin_z,$res['currency'].'bow'=>0));
            }elseif($coin['ggjtc']>0 && $coin['gg'.$res['currency']]>0 && $coin['gg'.$res['currency']]<$res['yh']){
                $jtc_zh = $coin['ggjtc']/$new_price;
                $jtc_bow = $coin['jtcbow']/$new_price;
                $coin_z = $jtc_zh-($res['borrow'] + $res['interest']-$coin[$res['currency'].'bow']-$coin['gg'.$res['currency']]);
                if($coin_z<0){
                    $coin_s = $jtc_bow-$coin_z;
                    $jtc = $coin_s*$new_price;
                    $coin_info = M('User_coin')
                            ->where(array('userid'=>userid()))
                            ->save(array('gg'.$res['currency']=>0,$res['currency'].'bow'=>0,'ggjtc'=>0,'jtcbow'=>$coin_s));
                }
                $jtc = $coin_z*$new_price;
                $coin_info = M('User_coin')
                            ->where(array('userid'=>userid()))
                            ->save(array('gg'.$res['currency']=>0,$res['currency'].'bow'=>0,'ggjtc'=>$coin_z));
            }
        }
        else{
            if($coin['jtcbow']>=$res['yh']){
                $coin_info = M('User_coin')->where(array('userid'=>userid()))->setDec('jtcbow',$res['yh']);
            }elseif($coin['jtcbow']<$res['yh'] && $coin['ggjtc']>0 && $coin['jtcbow']>0){
                $coin_z = $coin['ggjtc']-($res['borrow'] + $res['interest'] - $coin['jtcbow']);
                $coin_info = M('User_coin')
                            ->where(array('userid'=>userid()))
                            ->save(array('ggjtc'=>$coin_z,'jtcbow'=>0));
            }
        }
        //dump($coin_info);die;
        if($coin_info){
            $repay = $res['borrow']+$res['interest'];
            $lever_info = $lever->where(array('b_order'=>$b_order))->save(array('status'=>2,'endtime'=>time(),'r_order'=>round_order(),'repayment'=>$repay));
            
        }
    }
	
	//账单明细
	public function list_detail(){
		if(!userid()){
            $this->error('请登录');
        }
		$type = I('post.type');#1是bb账户，2是杠杆账户
		$uid = userid();
		switch($type){
			case 1:
				$info = M('trade')->where(array('userid'=>$uid,'trade_pt'=>1))->select();
				break;
			case 2:
				$info = M('trade')->where(array('userid'=>$uid,'trade_pt'=>2))->select();
				break;
		}
		if($info){
			$this->ajaxReturn(array('code'=>200,'info'=>$info));
		}else{
			$this->ajaxReturn(array('code'=>404,'info'=>'暂无数据'));
		}
		
	}
	
	//还款记录
	public function repayment_list(){
		if(!userid()){
            $this->error('请登录');
        }
		$uid = userid();
		$info = M('Lever')->where(array('userid'=>$uid,'status'=>2))->select();
		if($info){
			$this->ajaxReturn(array('code'=>200,'info'=>$info));
		}else{
			$this->ajaxReturn(array('code'=>404,'info'=>'暂无数据'));
		}
	}
	
	//转账记录
	public function zz_list(){
		$uid = userid();
		$info = M('transfer')->where(array('userid'=>$uid))->order('addtime desc')->select();
		if($info){
			$this->ajaxReturn(array('code'=>200,'info'=>$info));
		}else{
			$this->ajaxReturn(array('code'=>404,'info'=>'暂无数据'));
		}
	}
	
	//工单记录
	public function work_list(){
		if(!userid()){
            $this->error('请登录');
        }
		$uid = userid();
		$info = M('worklist')->where(array('send_id'=>$uid))->order('id desc')->select();
		if($info){
			$this->ajaxReturn(array('code'=>200,'info'=>$info));
		}else{
			$this->ajaxReturn(array('code'=>404,'info'=>'暂无数据'));
		}
	}
	
	//c2c打款页面取消交易
	public function delete_xxx(){
		$id =I('post.id');#订单ID
		$coin = I('post.coin');
		$max_coin = strtoupper($coin);
    	$tarde=M('c2c_trade');
    	$ucoin=M('user_coin');    
		$res=$tarde->where(array('id'=>$id))->find();
		if($res){
			if($res['is_done']!=1 && $res['is_cancel']!=1){
				$data['is_cancel']=1;		
				$result=$tarde->where(array('id'=>$id))->save($data);
				if($result){
					$this->ajaxReturn(array('code'=>1012));
				}else{
					$this->ajaxReturn(array('code'=>1013));
				}
			}
		}
	}

}

?>