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
        $market=I('post.market');
        //$type=I('get.type');
        $map['market'] = $market;
       /* switch($type){
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
        }*/
		$map['status'] = ['in','0,1,2'];
        //$map['_query'] = "userid=$uid or peerid=$uid";
        $map['userid']=$uid;
        $list=M('trade')->where($map)->limit(30)->order('id desc')->select();
        foreach ($list as $k => $v) {
            $list[$k]['addtime'] = date("Y-m-d H:i:s",$v['addtime']);
        }
    
        //echo '<pre>';
        //print_r($list);die;
    
        $this->ajaxReturn(array(
            'code'  =>  200,
            'result'    =>  '获取成功',
            'body'  =>  array(
                'list'  =>  $list?$list:[],
				
            )
        ));
    }
	
	#历史委托
    public function getTradelsgg($market=null)
    {
        $uid=userid();
        $market=I('post.market');
        //$type=I('get.type');
        $map['market'] = $market;
       /* switch($type){
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
        }*/
		$map['status'] = ['in','0,1,2'];
        //$map['_query'] = "userid=$uid or peerid=$uid";
        $map['userid']=$uid;
        $list=M('trade_lever')->where($map)->limit(30)->order('id desc')->select();
    
        //echo '<pre>';
        //print_r($list);die;
    
        $this->ajaxReturn(array(
            'code'  =>  200,
            'result'    =>  '获取成功',
            'body'  =>  array(
                'list'  =>  $list?$list:[],
				
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
	
	 #杠杆历史成交
    public function getTradecjgg($market=null)
    {
        $uid=userid();
        $$market=I('get.market');
        $map['userid'] = $uid;
        $map['market'] = $market;
        $map['status'] = 1;
        //$map['_query'] = "userid=$uid or peerid=$uid";
        $list=M('trade_log_lever')->where($map)->limit(30)->order('id desc')->select();
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

    public function upTrade($paypassword = NULL, $market = NULL, $price=0, $num, $type,$tradetype,$trade_pt=1)
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
            $this->error('请先登录！','/#login');
			// $this->ajaxReturn(array('code'=>1002));
            // redirect();
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
			// $this->ajaxReturn(array('code'=>1003));
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
            $this->error('交易价格格式错误');
			// $this->ajaxReturn(array('code'=>1004));
        }

        if (!check($num, 'double')) {
            $this->error('交易数量格式错误');
			// $this->ajaxReturn(array('code'=>1004));
        }

        if (($type != 1) && ($type != 2)) {
            $this->error('交易类型格式错误');
            // $this->ajaxReturn(array('code'=>1004));
        }

        $user = M('User')->where(array('id' => userid()))->find();

        if ($user['tpwdsetting'] == 3) {
        }

        if ($user['tpwdsetting'] == 2) {
            if (md5($paypassword) != $user['paypassword']) {
                $this->error('交易密码错误！');
                // $this->ajaxReturn(array('code'=>1005));
            }
        }

        if ($user['tpwdsetting'] == 1) {
            if (!session(userid() . 'tpwdsetting')) {
                if (md5($paypassword) != $user['paypassword']) {
                     $this->error('交易密码错误！');
                    //$this->ajaxReturn(array('code'=>1005));
                }
                else {
                    session(userid() . 'tpwdsetting', 1);
                }
            }
        }
        //error_log('market1='.$C('market')[$market].'market2='. $market,3,'./abcb.txt');
        if (!C('market')[$market]) {
            $this->error('交易市场错误');
            // $this->ajaxReturn(array('code'=>1006));
        }
        else {
            $xnb = explode('_', $market)[0];
            $rmb = explode('_', $market)[1];
        }
        // TODO: SEPARATE
        
        $price = round(floatval($price), C('market')[$market]['round']);
 

        if (!$price) {
            $this->error('交易价格错误' . $price);
            // $this->ajaxReturn(array('code'=>1004));
        }

        $num = round($num, C('market')[$market]['round']);

        if (!check($num, 'double')) {
             $this->error('交易数量错误');
            // $this->ajaxReturn(array('code'=>1004));
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
            // $this->ajaxReturn(array('code'=>1004));
        }

        if ($max_price < $price) {
            $this->error('交易价格超过最大限制！');
            // $this->ajaxReturn(array('code'=>1004));
        }

        if ($price < $min_price) {
             $this->error('交易价格超过最小限制！');
            // $this->ajaxReturn(array('code'=>1004));
        }
    
        $hou_price = C('market')[$market]['hou_price'];

        if ($hou_price) {
            if (C('market')[$market]['zhang']) {
                // TODO: SEPARATE
                $zhang_price = round(($hou_price / 100) * (100 + C('market')[$market]['zhang']), C('market')[$market]['round']);

                if ($zhang_price < $price) {
                    $this->error('交易价格超过今日涨幅限制！');
                   // $this->ajaxReturn(array('code'=>1004));
                }
            }

            if (C('market')[$market]['die']) {
                // TODO: SEPARATE
                $die_price = round(($hou_price / 100) * (100 - C('market')[$market]['die']), C('market')[$market]['round']);

                if ($price < $die_price) {
                    $this->error('交易价格超过今日跌幅限制！');
                   // $this->ajaxReturn(array('code'=>1004));
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
                 $this->error(C('coin')[$rmb]['title'] . '余额不足！');
                //$this->ajaxReturn(array('code'=>1007));
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
                $this->error(C('coin')[$xnb]['title'] . '余额不足！');
                //$this->ajaxReturn(array('code'=>1007));
            }
        }
        else {
            $this->error('交易类型错误');
           // $this->ajaxReturn(array('code'=>1004));
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
                        // $this->ajaxReturn(array('code'=>1004));
                    }
                }
            }
        }

        if (C('coin')[$xnb]['fee_meitian']) {
            if ($type == 2) {
                $bili_user = round($user_coin[$xnb] + $user_coin[$xnb . 'd'], 8);

                if ($bili_user < 0) {
                    $this->error('可交易量错误');
                    // $this->ajaxReturn(array('code'=>1004));
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
                // $this->ajaxReturn(array('code'=>1004));
            }
        }

        if (C('market')[$market]['trade_max']) {
            if (C('market')[$market]['trade_max'] < $mum) {
                $this->error('交易总额不能大于' . C('market')[$market]['trade_max']);
                // $this->ajaxReturn(array('code'=>1004));
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
        $mo->execute('lock tables zhisucom_status write, zhisucom_trade write ,zhisucom_user_coin write ,zhisucom_finance write,zhisucom_chistory write,zhisucom_user write,zhisucom_config write,zhisucom_user write,zhisucom_coin write');
        $rs = array();
        // dump(userid());die;
        $user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find(); 
        // dump($user_coin);die; 
        if ($type == 1) {
            if ($user_coin[$rmb] < $mum) {
                $this->error(C('coin')[$rmb]['title'] . '余额不足！');
                // $this->ajaxReturn(array('code'=>1007));

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

			//dump($rs);die;
			//
			//商家返利
			$coinx = strtolower($rmb);
                      $c2c_fee = $mo->table('zhisucom_coin')->where(array('name'=>$coinx))->getField('c2c_fee');
                      if($c2c_fee>0)
                      {
                        $interest = $fee1 * ($c2c_fee / 100);
                        $fanli = $mo->table('zhisucom_config')->where("id=1")->field('shop1,shop2')->find();

                        $user1 = $mo->table('zhisucom_user')->where(array('id'=>userid()))->find();

                        if($fanli['shop1']>0)
                        {
                          $fan = $interest*$fanli['shop1'];
                          if($user1['invit_1']>0)
                          {
                            $mo->table('zhisucom_user_coin')->where("userid=".$user1['invit_1'])->setInc($coinx,$fan);
                          }
                        }
                        if($fanli['shop2']>0)
                        {
                          $fan = $interest*$fanli['shop2'];
                          if($user1['invit_2']>0)
                          {
                            $mo->table('zhisucom_user_coin')->where("userid=".$user1['invit_2'])->setInc($coinx,$fan);
                          }
                        }
                      }
//商家返利

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
                $this->error(C('coin')[$xnb]['title'] . '余额不足2！');
                // $this->ajaxReturn(array('code'=>1007));
                
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
            $this->error('交易类型错误');
            // $this->ajaxReturn(array('code'=>1007));
        }
//echo $price;die;
        if (check_arr($rs)) {
            $mo->execute('commit');
            $mo->execute('unlock tables');
            S('getDepth', null);
            $this->matchingTrade($market,$trade_pt);
            $this->success('交易成功！');
            // $this->ajaxReturn(array('code'=>1008));
        }
        else {
            $mo->execute('rollback');
            $mo->execute('unlock tables');
            $this->error('交易失败！');
            // $this->ajaxReturn(array('code'=>1009));
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
             $this->error('请先登录！');
            //$this->ajaxReturn(array('code'=>1013));
        }
		$id = I('post.id');
        if (!check($id, 'd')) {
             $this->error('请选择要撤销的委托！');
           // $this->ajaxReturn(array('code'=>1013));
        }

        $trade = M('Trade')->where(array('id' => $id))->find();

        if (!$trade) {
             $this->error('撤销委托参数错误！');
            //$this->ajaxReturn(array('code'=>1013));
        }

        if ($trade['userid'] != userid()) {
             $this->error('参数非法！');
            //$this->ajaxReturn(array('code'=>1013));
        }

        $this->show(D('Trade')->chexiao($id));
    }

    public function show($rs = array())
    {
        if ($rs[0]) {
             $this->success($rs[1]);
            //$this->ajaxReturn(array('code'=>1012));
        }
        else {
             $this->error($rs[1]);
            //$this->ajaxReturn(array('code'=>1013));
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


    
 
	/*
		风险率 = （杠杆总资产 - 总利息）/ 总借款
	*/
	public function ajax_fx()
	{
		$market = $_POST['market'];
		
		$lever_coin = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$market))->find();
		$where = $market."_jeff";
        $new_price = M('Market')->where(array('name'=>$where))->getField('new_price');#最新价
		
		$lever_list = M('Lever')->where(array('userid'=>userid(),'status'=>1))->select();
		foreach($lever_list as $k=>$v)
		{
			if($v['currency'] == $market)
			{
				$xnb_sum += $v['interest']*$new_price;#总利息
			}
			elseif($v['currency'] == 'jeff')
			{
				$jeff_sum += $v['interest'];#总利息
			}
		}
		$fee_sum = $xnb_sum + $jeff_sum;
		#折合JEFF后的总资产
		$lever_sum = ($lever_coin['yue'] * $new_price) + $lever_coin['p_yue'];
		
		#折合JEFF后的总借款
		$borrow_sum = ($lever_coin['borrow'] * $new_price) + $lever_coin['p_borrow'];
		
		$fxl = ($lever_sum - $fee_sum) / $borrow_sum;#风险率
		//var_dump($borrow_sum);die;
		//echo $fxl;die;
		$config = M('Config')->where('id=1')->field('lever_fx')->find();
		$config['fxl'] = round($fxl,2)>0?round($fxl,2):0;
		if(($fxl*100) <= $config['lever_fx'] + 5 && $fxl>0)
		{
			$levers = M("Lever")->where(array('currency'=>$market,'status'=>1,'userid'=>userid()))->select();
			if($levers)
			{
				foreach($levers as $k => $v)
				{
					$repay = $this->auto_repayment($v['b_order'],$v['dy_coin']);
					if(!$repay)
					{
						$this->ggupTrade(NULL,$where,$new_price,$lever_coin['borrow'],2,2,2,1);
					}
				}
				
			}
		}
		$this->ajaxReturn(array('code'=>'200','result'=>$config));
		
	}

   
	
	//虚拟币借款
	public function xnb_borrow()
	{
		if(!userid()){
            //$this->ajaxReturn(array('code'=>1002));
			$this->error('请登录');
        }
		if($_POST)
		{
			$num = I('post.borrow_num');
			$coin = I('post.coin_type');
			$fee = C('lever_lixi');
			$interest = $num * ($fee/100);
			$zj = $num - $interest;
			$where = $coin."_jeff";
            $new_price = M('Market')->where(array('name'=>$where))->getField('new_price');
			$res = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->setInc('borrow',$zj);
			$ress = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->setInc('yue',$zj);
			if($res && $ress)
			{
				$info = M('Lever')->add(array('userid'=>userid(),'borrow'=>$num,'interest'=>$interest,'currency'=>$coin,'addtime'=>time(),'status'=>1,'b_order'=>round_order(),'interest_fee'=>$fee,'new_price'=>$new_price,'dy_coin'=>$coin));
				
				if($info)
				{
					$this->success('借款成功');
					//$this->ajaxReturn(array('code'=>1012));
				}
				else
				{
					$this->error('借款失败');
					//$this->ajaxReturn(array('code'=>1013));
				}
			}
			
		}
	}


    
	
	//JEFF借款
	public function rgb_borrow()
	{
		if(!userid())
		{
            $this->error('请登录','/');
			//$this->ajaxReturn(array('code'=>1002));
        }
		
		if($_POST)
		{
			$borrow = I('post.borrow_num');
			$coin = I('post.coin_type');
			$fee = C('lever_lixi');
			$interest = $borrow * ($fee/100);
			$zj = $borrow - $interest;
			$res = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->setInc('p_borrow',$zj);
			$res = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->setInc('p_yue',$zj);
			if($res)
			{
				$info = M('Lever')->add(array('userid'=>userid(),'borrow'=>$borrow,'interest'=>$interest,'currency'=>'jeff','addtime'=>time(),'status'=>1,'b_order'=>round_order(),'interest_fee'=>$fee,'dy_coin'=>$coin));
				
				if($info)
				{
					$this->success('借款成功');
					//$this->ajaxReturn(array('code'=>1012));
				}
				else
				{
					$this->error('借款失败');
					//$this->ajaxReturn(array('code'=>1013));
				}
			}
		}
	}

    //杠杆管理借款数据
    public function borrow_info(){
        if(!userid()){
            $this->error('请登录');
        }
        $lever = M('Lever');
        $bz = I('request.coin');
		
		//var_dump($bz);die;
		$lever_coin = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$bz))->find();
		if($lever_coin['yue'] > 0)
		{
			$xnb_kejie = ($lever_coin['yue'] - $lever_coin['borrow']) * (C('lever_bs') - 1) - $lever_coin['borrow'];
		}
		
		if($lever_coin['p_yue'] > 0)
		{
			$rgb_kejie = ($lever_coin['p_yue'] - $lever_coin['p_borrow']) * (C('lever_bs') - 1) - $lever_coin['p_borrow'];
		}
		
        
        $this->ajaxReturn(array(
            'xnb_ky'=>$lever_coin['yue'] > 0 ? $lever_coin['yue'] : 0,
            'rgb_ky'=>$lever_coin['p_yue'] > 0 ? $lever_coin['p_yue'] : 0,
            'xnb_kejie'=>$xnb_kejie > 0 ? $xnb_kejie : 0,
            'rgb_kejie'=>$rgb_kejie > 0 ? $rgb_kejie : 0,
            'xnb_sum'=>$lever_coin['borrow'] > 0 ? $lever_coin['borrow'] : 0,
            'rgb_sum'=>$lever_coin['p_borrow'] > 0 ? $lever_coin['p_borrow'] : 0,
            'lilv'=>C('lever_lixi') > 0 ? C('lever_lixi') : 0,
        ));
    }

    //杠杆币种
    public function bz(){
         $market = C('MARKET');
        foreach ($market as $k => $v) {
          if ($v['jiaoyiqu']==0){
                $list['JEFF'][]=$v;
            }
        }
        $this->ajaxReturn(array('code'=>200,'jeff'=>$list['JEFF']));
    }
	
	
	//转入
	public function turninto()
	{
		if(!userid()){
            $this->error('请登录','/');
        }
		
		$coin = I('post.bz');
		$num = I('post.turnnum');
		
		if($num <= 0)
		{
			$this->error('转账金额格式错误');
		}
		
		$user_coin_num = M('User_coin')->where(array('userid'=>userid()))->getField('jeff');
		$lever_coin = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->find();
		
		if($num > $user_coin_num)		
		{
			$this->error('账户余额不足');
		}
		
		$lever_coins = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->setInc('p_yue',$num);
		$user_coin = M('User_coin')->where(array('userid'=>userid()))->setDec('jeff',$num);
		
		if($lever_coins && $user_coin)
		{
			$data = array(
				'userid'  => userid(),
				'coin'	  => 'jeff',
				'num'	  => $num,
				'detail'  => '币币账户转向杠杆账户',
				'addtime' => time(),
				'type'	  => 1
			);
			$transfer = M('Transfer')->add($data);
			if($transfer)
			{
				$this->success('转入成功');
			}
			else
			{
				$this->error('转入失败');
			}
		}
		else
		{
			$this->error('转入失败');
		}
	}
	//虚拟币转入
	public function turn_xnbs()
	{
		if(!userid()){
            $this->error('请登录','/');
        }
		//dump($_POST);die;
		$coin = I('post.bz');
		$num = I('post.infonum');
		
		if($num <= 0)
		{
			$this->error('转账金额格式错误');
		}
		$user_coin_num = M('User_coin')->where(array('userid'=>userid()))->getField($coin);
		$lever_coin = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->find();
		if($num > $user_coin_num)		
		{
			$this->error('账户余额不足');
		}
		
		$lever_coins = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->setInc('yue',$num);
		$user_coin = M('User_coin')->where(array('userid'=>userid()))->setDec($coin,$num);
		
		if($lever_coins && $user_coin)
		{
			$data = array(
				'userid'  => userid(),
				'coin'	  => $coin,
				'num'	  => $num,
				'detail'  => '币币账户转向杠杆账户',
				'addtime' => time(),
				'type'	  => 1
			);
			$transfer = M('Transfer')->add($data);
			if($transfer)
			{
				$this->success('转入成功');
			}
			else
			{
				$this->error('转入失败');
			}
		}
		else
		{
			$this->error('转入失败');
		}
	}
  
  
  
  //杠杆转账记录
  public function lever_zz_detail()
  {
  	$info = M('transfer')->where(array('userid'=>userid()))->field('id,addtime,coin,num,detail')->order('id desc')->select();
    if($info)
    {
    	foreach($info as $k=>$v)
        {
        	$info[$k]['addtime'] = date("Y-m-d H:i:s",$v['addtime']);
          	$info[$k]['coin'] = strtoupper($v['coin']);
          	$info[$k]['num'] = round($v['num'],4);
        }
      $this->ajaxReturn(array('code'=>200,'info'=>$info));
    }
    else{
            $this->ajaxReturn(array('code'=>404,'info'=>'查询为空'));
        }
  }
  
  
  
  
    //杠杆当前申请的账单
    public function now_list(){
        if(!userid()){
            $this->error('请登录');
        }
		$lever = M('Lever');
        $info1 = $lever->where(array('userid'=>userid(),'status'=>1))->order('addtime desc')->select();
        foreach ($info1 as $k=> $v) {
            $info1[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
            $info1[$k]['yinghuan'] = $v['borrow'] + $v['interest'];
        }
        if($info1){
            $this->ajaxReturn(array('code'=>200,'info'=>$info1));
        }else{
            $this->ajaxReturn(array('code'=>404,'info'=>'查询为空'));
        }
    }
	
	//主动还款
	public function repayment()
	{
		if($_POST){
            $b_order = $_POST['order'];
			$bz = $_POST['coin_type'];
        }
		
		$lever = M('Lever');
		$lever_coin = M('Lever_coin');
		$res = $lever->where(array('b_order'=>$b_order))->find();
		
		$yh = $res['borrow']+$res['interest'];
		$lever_yue = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->find();
		//var_dump($lever_yue);die;
		if($res['currency'] == 'jeff')
		{
			if($lever_yue['p_yue'] > $yh && $lever_yue['p_borrow'] > 0)
			{
				$r = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->setDec('p_yue',$yh);
				$rs = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->setDec('p_borrow',$yh);
			}
			else
			{
				$this->error('余额不足');exit;
			}
			
		}
		else
		{
			if($lever_yue['yue'] > $yh && $lever_yue['borrow'] > 0)
			{
				$r = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->setDec('yue',$yh);
				$rs = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->setDec('borrow',$yh);
			}
			else
			{
				$this->error('余额不足');exit;
			}
		}
		
		if($r && $rs)
		{
            $lever_info = $lever->where(array('b_order'=>$b_order))->save(array('status'=>2,'endtime'=>time(),'r_order'=>round_order(),'repayment'=>$yh));
			if($lever_info)
			{
				$this->success('还款成功');
				// $this->ajaxReturn(array('code'=>1012));
			}
			else
			{
				$this->error('还款失败');
				// $this->ajaxReturn(array('code'=>1013));
			}
		}
		else
		{
			$this->error('还款失败');
			// $this->ajaxReturn(array('code'=>1013));
		}
	}

   //自动还款
	public function auto_repayment($b_order,$bz)
	{
		/*if($_POST){
            $b_order = $_POST['order'];
			$bz = $_POST['coin_type'];
        }*/
		
		$lever = M('Lever');
		$lever_coin = M('Lever_coin');
		$res = $lever->where(array('b_order'=>$b_order))->find();
		
		$yh = $res['borrow']+$res['interest'];
		$lever_yue = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->find();
		//var_dump($lever_yue);die;
		if($res['currency'] == 'jeff')
		{
			if($lever_yue['p_yue'] > $yh && $lever_yue['p_borrow'] > 0)
			{
				$r = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->setDec('p_yue',$yh);
				$rs = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->setDec('p_borrow',$yh);
			}
			else
			{
				return false;
			}
			
		}
		else
		{
			if($lever_yue['yue'] > $yh && $lever_yue['borrow'] > 0)
			{
				$r = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->setDec('yue',$yh);
				$rs = $lever_coin->where(array('userid'=>userid(),'name_en'=>$bz))->setDec('borrow',$yh);
			}
			else
			{
				return false;
			}
		}
		
		if($r && $rs)
		{
            $lever_info = $lever->where(array('b_order'=>$b_order))->save(array('status'=>2,'endtime'=>time(),'r_order'=>round_order(),'repayment'=>$yh));
			if($lever_info)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
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
				$info = M('trade_lever')->where(array('userid'=>$uid,'trade_pt'=>2))->select();
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
        foreach ($info as $key => $value) {
            $info[$key]['addtime'] = date('Y-m-d H:i:s',$value['addtime']);
        }
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
	
	
	/*
	*paypassword资金密码
	*market交易对
	*price价格
	*num数量
	*type ：1是买，2是卖
	*tradetype：1是限价交易，2是市价交易
	*trade_pt：1是bb交易，2是杠杆交易
	*gg:风险率自动还款用到
	**/
    public function ggupTrade($paypassword = NULL, $market = NULL, $price=0, $num=NULL, $type,$tradetype,$trade_pt=2,$auto=0,$gg=NULL)
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
			//redirect();
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
        
		$user_coin[$xnb] = $user_coin['yue'];
		$user_coin[$xnb.'d'] = $user_coin['yued'];
		$user_coin[$rmb] = $user_coin['p_yue'];
		$user_coin[$rmb.'d'] = $user_coin['p_yued'];

        if ($type == 1) {
            //根据VIP等级计算佣金费率
            $return_vip_fee = get_vip_fee(userid('','',$auto));
            $vip_fee = $return_vip_fee[1];
            //$trade_fee = C('market')[$market]['fee_buy'];

            //$trade_fee = C('market')[$market]['fee_buy'] * $vip_fee;//0.13
            $trade_fee = C('market')[$market]['fee_buy'];
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

            //$trade_fee = C('market')[$market]['fee_sell'] * $vip_fee;
           $trade_fee = C('market')[$market]['fee_sell'];
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
        /*echo $xnb.'_'.$rmb;echo "<pre/>";
        echo userid();echo "<pre/>";*/
        $mo = M();
        $mo->execute('set autocommit=0');
        //$mo->execute('lock tables zhisucom_status write, zhisucom_trade_lever write ,zhisucom_user_coin write ,zhisucom_finance write,zhisucom_chistory write');
        if($auto){
            $user_coin = M('LeverCoin')->where(array('userid' => 1,'name_en'=>$xnb))->find();
        }else{
            $user_coin = M('LeverCoin')->where(array('userid' => userid('','',$auto),'name_en'=>$xnb))->find();
            // var_dump($user_coin);echo "<pre/>";
        }

		$mo->execute('lock tables ');
        $rs = array();
        //$user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();	
		/*if($auto){
            $user_coin = $mo->table('zhisucom_lever_coin')->where(array('userid' => 1,'name_en'=>$xnb))->find();
        }else{
            $user_coin = $mo->table('zhisucom_lever_coin')->where(array('userid' => userid(),'name_en'=>$xnb))->find();
            var_dump($user_coin);echo "<pre/>";
        }*/


		
		$user_coin[$xnb] = $user_coin['yue'];
		$user_coin[$xnb.'d'] = $user_coin['yued'];
		$user_coin[$rmb] = $user_coin['p_yue'];
		$user_coin[$rmb.'d'] = $user_coin['p_yued'];
		$rmbgg = 'p_yue';
		$xnbgg = 'yue';
		/*echo $price;echo "<pre/>";
        echo $num;echo "<pre/>";
        echo $mum;echo "<pre/>";
        echo $user_coin[$rmb];die;*/
        if ($type == 1) {
            if ($user_coin[$rmb] < $mum) {
                $this->error(C('coin')[$rmb]['title'] . '余额不足！');

            }
			
            /*$finance = $mo->table('zhisucom_finance')->where(array('userid' => userid('','',$auto)))->order('id desc')->find();
            $finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid('','',$auto)))->find();*/
            $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => userid('','',$auto),'name_en'=>$xnb))->setDec($rmbgg, $mum);	
            
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


//var_dump($rs);die;
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

            $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => userid('','',$auto),'name_en'=>$xnb))->setDec($xnbgg, $num);

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
            $this->ggmatchingTrade($market,$trade_pt,$auto,$gg);
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

    protected function ggmatchingTrade($market = NULL,$trade_pt = NULL,$auto,$gg=NULL)#匹配
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
				$mo->execute('lock tables zhisucom_status write ,zhisucom_trade_lever write ,zhisucom_trade_log_lever write ,zhisucom_user write,zhisucom_lever_coin write,zhisucom_market write');
				
			
				
				$rs[] = $mo->table('zhisucom_trade_lever')->where(array('id' => $buy['id']))->setInc('deal', $amount);
                $rs[] = $mo->table('zhisucom_trade_lever')->where(array('id' => $sell['id']))->setInc('deal', $amount);
                $rs[] = $finance_nameid = $mo->table('zhisucom_trade_log_lever')->add(array('userid' => $buy['userid'], 'peerid' => $sell['userid'], 'market' => $market, 'price' => $price, 'num' => $amount, 'mum' => $mum, 'type' => $type, 'fee_buy' => $buy1_fee, 'fee_sell' => $sell_fee, 'addtime' => time(), 'status' => 1));
                
				
                //TODO:2018-07-25 买的手续费扣在买入币种上面（注意：要先把对应交易市场兑换成净入币种，再扣除）
                $new_amount = round($amount - $buy1_fee,8);
                $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setInc($xnbgg, $new_amount);
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
                $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setDec($rmbgg . 'd', $save_buy_rmb);
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
                $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->setInc($rmbgg, $sell_save);
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


                $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->setDec($xnbgg . 'd', $save_sell_xnb);
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
                        $chajia_user_buy = $mo->table('zhisucom_lever_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->find();
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
                            $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setDec($rmbgg . 'd', $chajia_save_buy_rmb);
                            $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setInc($rmbgg, $chajia_save_buy_rmb);
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
                    $you_user_buy = $mo->table('zhisucom_lever_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->find();
					$you_user_buy[$rmb . 'd'] = $you_user_buy['p_yued'];
                    if (0 < $you_user_buy[$rmb . 'd']) {
                        $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setField($rmbgg . 'd', 0);
                        $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $buy['userid'],'name_en'=>$xnb))->setInc($rmbgg, $you_user_buy[$rmb . 'd']);
                    }
                }

                if (!$you_sell) {
                    $you_user_sell = $mo->table('zhisucom_lever_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->find();
					$you_user_sell[$xnb . 'd'] = $you_user_sell['yued'];
                    if (0 < $you_user_sell[$xnb . 'd']) {
                        $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->setField($xnbgg . 'd', 0);
                        $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $sell['userid'],'name_en'=>$xnb))->setInc($rmbgg, $you_user_sell[$xnb . 'd']);
                    }
                }

                if (check_arr($rs)) {
					
                    $mo->execute('commit');
                    $mo->execute('unlock tables');
                    $new_trade_zhisucom = 1;
					
					//自动还款
					if($gg == 1)
					{
						$levers = M("Lever")->where(array('currency'=>$xnb,'status'=>1,'userid'=>userid()))->select();
						if($levers)
						{
							foreach($levers as $k => $v)
							{
								$repay = $this->auto_repayment($v['b_order'],$v['dy_coin']);
								
							}
							
						}
					}
					
					//自动还款结束
					
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
            $new_price = round(M('TradeLoglever')->where(array('market' => $market, 'status' => 1))->order('id desc')->getField('price'), 6);
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
	
	//杠杆交易撤销
	public function gg_chexiao($id)
    {
        if (!userid()) {
            $this->error('请先登录！');
        }

        if (!check($id, 'd')) {
            $this->error('请选择要撤销的委托！');
        }

        $trade = M('TradeLever')->where(array('id' => $id))->find();
		//var_dump($trade);die;

        if (!$trade) {
            $this->error('撤销委托参数错误！');
        }

        if ($trade['userid'] != userid()) {
            $this->error('参数非法！');
        }

        $this->show(D('TradeLever')->chexiao($id));
    }
	
	//bb最近成交
	public function recent_deal()
	{
		
		$market = I('post.market')?I('post.market'):'eth_jeff';
		$list = M('Trade_log')->where(array('market'=>$market,'status'=>1))->field('price,num,addtime')->order('addtime desc')->limit(50)->select();
        foreach ($list as $k => $v) {
            $list[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
        }
		
		if($list)
		{
			$this->success($list);
		}
		
	}

    //bb成交记录，搜索用的币种列表
    public function bb_coin_search_list()
    {
        $coin = M('Coin')->where(array('status'=>1))->field('name')->select();
        foreach ($coin as $k => $v) {
            $coin[$k]['name'] = strtoupper($v['name']);
        }
        $this->success($coin);
    }
	
	
	//bb成交 记录
	public function bb_deal()
	{
        $market = I('post.market');
        $type = I('post.type');
       // $start_time = I('post.start_time');
        // $end_time = I('post.end_time');
        $where = "(userid=".userid()." or peerid=".userid().")";
        if($market)
        {
            $is_market = M('Market')->where(array('name'=>$market,'status'))->find();
            if(!$is_market)
            {
                $this->error('交易对错误');
            }
            $where .= " and (market="."'$market'";
            
        }

        if($type)
        {
            if(!in_array($type,[1,2]))
            {
                $this->error('交易类型错误');
            }

            $where .= " and type=".$type.")";
            
        }

        // var_dump($where);
        $list = M('Trade_log')->where($where)->order('addtime desc')->select();
		// var_dump($list);die;
		if($list)
		{
			$this->success($list);
		}
	}
	
	//bb资产
	public function bb_assets()
	{
		$search = I('request.coin');#搜索的币种
		if(!empty($search))
		{
			$where['name'] = array('like','%'.$search.'%');
		}
		$where['status'] = 1;
        //var_dump($where);die;
		$coin = M('Coin')->where($where)->field('name')->select();
        if(!$coin)
        {
            $coin = M('Coin')->where(array('status'=>1))->field('name')->select();
        }

        foreach ($coin as $k => $v) 
        {
            $user_coin1 = M('User_coin')->where(array('userid'=>userid()))->getField($v['name']);
            $user_coin2 = M('User_coin')->where(array('userid'=>userid()))->getField($v['name'].'d');
            
            $coin[$k]['keyong'] = $user_coin1;
            $coin[$k]['dongjie'] = $user_coin2; 
            $market = M('Market')->where(array('name'=>$v['name'].'_cny'))->getField('new_price');
            if($market > 0){
                $sum = round(($user_coin1 + $user_coin2) * $market ,3);
                
                $coin[$k]['zhehe'] += $sum;
                $zongzichan += $sum;
            }
            $coin[$k]['name'] = strtoupper($v['name']);
            $coin[$k]['floag2'] = false;
        }
       
        $this->ajaxReturn(array('code'=>200,'zongzichan'=>round($zongzichan ,3),'coin'=>$coin));
	}

    //最新价 人民币 美元 
    public function usd_cny()
    {
        $market = I('post.market');
        $coin=explode('_',$market)[1];
        $cny = $coin."_cny";
        $usd = $coin."_usdt";
        $new_price = M('Market')->where(array('name'=>$market,'status'=>1))->getField('new_price');
        $cny_price  = M('Market')->where(array('name'=>$cny,'status'=>1))->getField('new_price');
        $usd_price = M('Market')->where(array('name'=>$usd,'status'=>1))->getField('new_price');
        $price = array('new_price'=>round($new_price,4),'cny_price'=>round($cny_price ,3),'usd_price'=>round($usd_price ,4));
        $this->success($price);
    }

    //bb可用和折合
    public function bb_ky_zhehe()
    {
        $coin = I('post.coin');
        $cny = $coin."_cny";
        $user_coin['keyong'] = M('User_coin')->where(array('userid'=>userid()))->getField($coin);
        $new_price = M('market')->where(array('name'=>$cny))->getField('new_price');
        $user_coin['cny'] = $user_coin['keyong'] / $new_price;
        $this->success($user_coin);
    }

    //杠杆可用折合(弃用)
    public function gg_ky_zhehe()
    {
        $market = I('post.market');
        $xnb = explode('/',$market)[0];
        $rmb = explode('/',$market)[1];
       
        // $cny = $coin."_cny";
        if($rmb == 'jeff')
        {
            $coin = $xnb;
            $cny = $coin."_cny";
            $lever_coin = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->find();
            $user_coin['keyong'] = round($lever_coin['p_yue'] ,6);
            $new_price = M('market')->where(array('name'=>$cny))->getField('new_price');
            $user_coin['cny'] = round($user_coin['keyong'] / $new_price ,4);
        }
        else
        {
            $coin = $rmb;
            $cny = $coin."_cny";
            $lever_coin = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->find();
            $user_coin['keyong'] = round($lever_coin['p_yue'] ,6);
            $new_price = M('market')->where(array('name'=>$cny))->getField('new_price');
            $user_coin['cny'] = round($user_coin['keyong'] / $new_price ,4);
        }

        $this->success($user_coin);
    }

    //杠杆可用折合（新）
    //buy_info买入时可用
    //sell_info卖出时可用
    public function gg_ky_zhehe2()
    {
        $coin = I('post.coin');
        $levers = M('Lever_coin')->where(array('userid'=>userid(),'name_en'=>$coin))->find();

        $buy_info['keyong'] = round($levers['p_yue'],4) > 0 ? round($levers['p_yue'],4) : 0;
        $xnb_market = $coin.'_cny';
        $jeff_market = 'jeff_cny';
        $jeff_new_price = M('market')->where(array('name'=>$jeff_market))->getField('new_price');
        $buy_info['cny'] = round($buy_info['keyong'] / $jeff_new_price ,6) > 0 ? round($buy_info['keyong'] / $jeff_new_price ,6) : 0;

        $sell_info['keyong'] = round($levers['yue'] ,6) > 0 ? round($levers['yue'] ,6) : 0;
        $xnb_new_price = M('market')->where(array('name'=>$xnb_market))->getField('new_price');
        $sell_info['cny'] = round($sell_info['keyong'] / $xnb_new_price ,4) > 0 ? round($sell_info['keyong'] / $xnb_new_price ,4) : 0;

        $this->ajaxReturn(array('code'=>200,'buy_info'=>$buy_info,'sell_info'=>$sell_info));
    }

    //提币币种列表
    public function tibi_coin_list()
    {
        $coin = I('post.coin');
        $coin = 'eth';
        $coin_list = M('Coin')->where(array('status'=>1))->field('name')->select();
        $data = array();
        foreach ($coin_list as $k => $v) {
            if($v['name'] != $coin)
            {
                $coin_list[$k]['label'] = strtoupper($v['name']);
                $coin_list[$k]['value'] = $k;
            }
        }
        $this->ajaxReturn(array('code'=>200,'data'=>$coin_list));
    }

    

}

?>