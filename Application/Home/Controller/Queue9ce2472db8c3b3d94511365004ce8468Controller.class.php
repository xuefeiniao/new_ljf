<?php
namespace Home\Controller;
header("content-type:text/html;charset=gb2312");
class Queue9ce2472db8c3b3d94511365004ce8468Controller extends HomeController
{

	public function index()
	{
		foreach (C('market') as $k => $v) {
			
		}

		foreach (C('coin_list') as $k => $v) {
			
		}
		echo "ok";
	}

	public function checkYichang()
	{
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_trade write');
		$Trade = M('Trade')->where('deal > num')->order('id desc')->find();

		if ($Trade) {
			if ($Trade['status'] == 0) {
				$mo->table('zhisucom_trade')->where(array('id' => $Trade['id']))->save(array('deal' => Num($Trade['num']), 'status' => 1));
			}
			else {
				$mo->table('zhisucom_trade')->where(array('id' => $Trade['id']))->save(array('deal' => Num($Trade['num'])));
			}

			$mo->execute('commit');
			$mo->execute('unlock tables');
		}
		else {
			$mo->execute('rollback');
			$mo->execute('unlock tables');
		}
	}

	public function checkDapan()
	{
		foreach (C('market') as $k => $v) {
			A('Trade')->matchingTrade($v['name']);
		}
	}

	public function checkUsercoin()
	{
		foreach (C('coin') as $k => $v) {
			
		}
	}

	public function marketandcoinb8c3b3d94512472db8()
	{
		foreach (C('market') as $k => $v) {
			$this->setMarket($v['name']);
		}

		foreach (C('coin_list') as $k => $v) {
			$this->setcoin($v['name']);
		}
	}

	public function setMarket($market = NULL)
	{
	    
	    if (!$market) {
			return null;
		}

		$market_json = M('Market_json')->where(array('name' => $market))->order('id desc')->find();

		if ($market_json) {
			$addtime = $market_json['addtime'] + 60;
		}
		else {
			$addtime = M('TradeLog')->where(array('market' => $market))->order('addtime asc')->find()['addtime'];
		}

		$t = $addtime;
		$start = mktime(0, 0, 0, date('m', $t), date('d', $t), date('Y', $t));//���
		$end = mktime(23, 59, 59, date('m', $t), date('d', $t), date('Y', $t));//23��59
		
		
		$trade_num = M('TradeLog')->where(array(
			'market'  => $market,
			'addtime' => array(
				array('egt', $start),
				array('elt', $end)
				)
			))->sum('num');//һ���ܽ�����

		if ($trade_num) {
			$trade_mum = M('TradeLog')->where(array(
				'market'  => $market,
				'addtime' => array(
					array('egt', $start),
					array('elt', $end)
					)
				))->sum('mum');//һ�콻���ܶ�
			$trade_fee_buy = M('TradeLog')->where(array(
				'market'  => $market,
				'addtime' => array(
					array('egt', $start),
					array('elt', $end)
					)
				))->sum('fee_buy');//һ��������������
			$trade_fee_sell = M('TradeLog')->where(array(
				'market'  => $market,
				'addtime' => array(
					array('egt', $start),
					array('elt', $end)
					)
				))->sum('fee_sell');//һ��������������
			$d = array($trade_num, $trade_mum, $trade_fee_buy, $trade_fee_sell);

			if (M('Market_json')->where(array('name' => $market, 'addtime' => $end))->find()) {
				M('Market_json')->where(array('name' => $market, 'addtime' => $end))->save(array('data' => json_encode($d)));
			}
			else {
				M('Market_json')->add(array('name' => $market, 'data' => json_encode($d), 'addtime' => $end));
			}
		}
		else {
			$d = null;

			if (M('Market_json')->where(array('name' => $market, 'data' => ''))->find()) {
				M('Market_json')->where(array('name' => $market, 'data' => ''))->save(array('addtime' => $end));
			}
			else {
				M('Market_json')->add(array('name' => $market, 'data' => '', 'addtime' => $end));
			}
		}
	}

	public function setcoin($coinname = NULL)
	{
		if (!$coinname) {
			return null;
		}

		if (C('coin')[$coinname]['type'] == 'qbb') {
			$dj_username = C('coin')[$coinname]['dj_yh'];
			$dj_password = C('coin')[$coinname]['dj_mm'];
			$dj_address = C('coin')[$coinname]['dj_zj'];
			$dj_port = C('coin')[$coinname]['dj_dk'];
			$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
			$json = $CoinClient->getinfo();

			if (!isset($json['version']) || !$json['version']) {
				return null;
			}

			$data['trance_mum'] = $json['balance'];
		}
		else {
			$data['trance_mum'] = 0;
		}

		$market_json = M('CoinJson')->where(array('name' => $coinname))->order('id desc')->find();

		if ($market_json) {
			$addtime = $market_json['addtime'] + 60;
		}
		else {
			$addtime = M('Myzr')->where(array('name' => $coinname))->order('id asc')->find()['addtime'];
		}

		$t = $addtime;
		$start = mktime(0, 0, 0, date('m', $t), date('d', $t), date('Y', $t));
		$end = mktime(23, 59, 59, date('m', $t), date('d', $t), date('Y', $t));

		if ($addtime) {
			if ((time() + (60 * 60 * 24)) < $addtime) {
				return null;
			}

			$trade_num = M('UserCoin')->where(array(
				'addtime' => array(
					array('egt', $start),
					array('elt', $end)
					)
				))->sum($coinname);
			$trade_mum = M('UserCoin')->where(array(
				'addtime' => array(
					array('egt', $start),
					array('elt', $end)
					)
				))->sum($coinname . 'd');
			$aa = $trade_num + $trade_mum;

			if (C($coinname)['type'] == 'qbb') {
				$bb = $json['balance'];
			}
			else {
				$bb = 0;
			}

			$trade_fee_buy = M('Myzr')->where(array(
				'name'    => $coinname,
				'addtime' => array(
					array('egt', $start),
					array('elt', $end)
					)
				))->sum('fee');
			$trade_fee_sell = M('Myzc')->where(array(
				'name'    => $coinname,
				'addtime' => array(
					array('egt', $start),
					array('elt', $end)
					)
				))->sum('fee');
			$d = array($aa, $bb, $trade_fee_buy, $trade_fee_sell);

			if (M('CoinJson')->where(array('name' => $coinname, 'addtime' => $end))->find()) {
				M('CoinJson')->where(array('name' => $coinname, 'addtime' => $end))->save(array('data' => json_encode($d)));
			}
			else {
				M('CoinJson')->add(array('name' => $coinname, 'data' => json_encode($d), 'addtime' => $end));
			}
		}
	}

	public function paicuo()
	{

	}


	public function houpriceb8c3b3d94512472db8()
	{
	    
		foreach (C('market') as $k => $v) {
		    //if (!$v['hou_price'] || (date('H', time()) == '00')) {
			//if ($v['hou_price']) {
				$t = time();
				$start = mktime(0, 0, 0, date('m', $t), date('d', $t), date('Y', $t));
				
				$hou_price = M('TradeLog')->where(array(
					'market'  => $v['name'],
					'addtime' => array('lt', $start)
					))->order('id desc')->getField('price');

				if (!$hou_price) {
					$hou_price = M('TradeLog')->where(array('market' => $v['name']))->order('id asc')->getField('price');
				}

                $hou_price = $hou_price?$hou_price:0;
				//var_dump($hou_price);
				M('Market')->where(array('name' => $v['name']))->setField('hou_price', $hou_price);
				/*$new_price = round(M('TradeLog')->where(array('market' => $v['name'], 'status' => 1))->order('id desc')->getField('price'), 6);
				$Cmarket = M('Market')->where(array('name' => $v['name']))->find();
				$change = round((($new_price - $Cmarket['hou_price']) / $Cmarket['hou_price']) * 100, 2);
				$upCoinData['change'] = $change;
				M('Market')->where(array('name' => $v['name']))->save($upCoinData);*/
				S('home_market', null);
		//	}
		}
	}
	
	//eth���˼�¼
	public function ethonline($coin='jeff')
    {
		$dj_address = C('coin')[$coin]['dj_zj'];
		$dj_port = C('coin')[$coin]['dj_dk'];
		$candh=C('coin')[$coin]['change'];
		$cancoin=C('coin')[$coin]['changecoin'];
		$fang_bili=C('coin')[$coin]['fang_bili'];//����
		if($candh==1){
			$setcoin=$cancoin;
			$rate=C('coin')[$coin]['huilv'];
		}else{
			 $setcoin=$coin;
		     $rate=1;
		}
		$pay = EthCommon($dj_address, $dj_port);
		$accounts=$pay->personal_listAccounts();//��ȡǮ����ַ�б�
		
 		//dump($accounts);die;
		foreach ($accounts as $k => $v) {
			if($v!=$dj_username){
				//$coin = 'eth';
			       // $v = '0x41653da27dc086417313b4df5e539fb48f8c1852';
					$getdz=M('user_coin')->where(array($coin.'b'=>$v))->find();//����Ǯ����ַ��Ӧ���˻�
					 //dump($getdz);
		
					
					if($getdz){
						 //$qbbalance=$pay->eth_getBalance($v);//��ѯǮ����ַ���10����
						 //dump($qbbalance);die;
					  $user=M('User')->where(array('id'=>$getdz['userid']))->getField('username,invit_1');
					  if($coin == 'jeff'){
					      $vv = '0xa137aC66A8C79E2960440E5d792dEF9a0EE23947';
					      $url='http://api.etherscan.io/api?module=account&action=txlist&address='.$vv.'&startblock=7089882&endblock=99999999&sort=asc&apikey=ERXIYCNF6PP3ZNQAWICHJ6N5W7P212AHZI';
						  
						  //https://etherscan.io/token/0xa137aC66A8C79E2960440E5d792dEF9a0EE23947
						  //dump($url);
						  //https://etherscan.io/token/0xb3d239238c659a5ffe569b153b60a3e1fcf8f003
					  }else{
					      $url='http://api.etherscan.io/api?module=account&action=txlist&address='.$v.'&startblock=7089882&endblock=99999999&sort=asc&apikey=ERXIYCNF6PP3ZNQAWICHJ6N5W7P212AHZI';
					  }
					
					
					  
					  
					  //http://api.etherscan.io/api?module=account&action=txlist&address=0x1895633415d24c782651aba64a0c0c2be0e3b5f8&startblock=6889882&endblock=99999999&sort=asc&apikey=ERXIYCNF6PP3ZNQAWICHJ6N5W7P212AHZI
					  
        				$fanhui=file_get_contents($url);
        				$fanhui= json_decode($fanhui,true);
						//dump($fanhui);
        				if($fanhui['message']=='OK'){
        					foreach ($fanhui['result'] as $v2) {
        						if((($coin == 'eth' && $v2['to']==$v) || ($coin == 'jeff' && $v2['to']==$vv)) && $v2['txreceipt_status']==1){
        						   
        							$rs1= M('myzr')->where(array('txid'=>$v2['hash']))->find();
        							if(!$rs1){
        							   // $addr1 = substr($v2['input'],0,2);
        							    $addr2 = substr($v2['input'],34,40);
        							    $addr = '0x'.$addr2;
        							    if($coin == 'jeff'){
        							        //$weiNumber = substr($v2['input'],-20);
        							      // $amount = hexdec($weiNumber) / 100000000;
										  $weiNumber = substr($v2['input'],-4);
        							       $amount = hexdec($weiNumber) / 10000;
        							     //   $amount=$v2['gasPrice']/100000000;
        							        $userids = M('user_coin')->where(array($coin.'b'=>$addr))->getField('userid');
											//dump($userids);
        							    }elseif ($coin == 'eth') {
        							        $amount=$v2['value']/1000000000000000000;
											$addr = $v;
											//echo $amount;
        							    }
        							    
        							 //   echo $addr;die;
        								$getmin=M('Coin')->where(array('name'=>$coin))->getField('getmin');
        								if($amount>=$getmin){
        								    if($coin == 'jeff'){
        								        $data = array('userid' =>$userids,'username' => $addr, 'coinname' => $coin, 'fee' => 0, 'txid' =>$v2['hash'], 'num' => $amount, 'mum' => $amount, 'addtime' => time(), 'status' => 1);
        								    // dump($data);die;
            								$rs2=M('myzr')->add(array('userid' =>$userids,'username' => $addr, 'coinname' => $coin, 'fee' => 0, 'txid' =>$v2['hash'], 'num' => $amount, 'mum' => $amount, 'addtime' => time(), 'status' => 1));
        								    }else{
        								        $data = array('userid' =>$getdz['userid'],'username' => $v, 'coinname' => $coin, 'fee' => 0, 'txid' =>$v2['hash'], 'num' => $amount, 'mum' => $amount, 'addtime' => time(), 'status' => 1);
        								    // dump($data);die;
            								$rs2=M('myzr')->add(array('userid' =>$getdz['userid'],'username' => $v, 'coinname' => $coin, 'fee' => 0, 'txid' =>$v2['hash'], 'num' => $amount, 'mum' => $amount, 'addtime' => time(), 'status' => 1));
        								    }
        								    
            								//$rs3=M('myzr')->getlastsql();
            								// dump($rs3);die;
            								/////////////////////////////
            								//$cc=setlev($getdz['userid'],$amount/$rate);
            								$rs1= M('user_coin')->where(array($coin.'b'=>$addr))->setInc($coin,$amount);//д���û����
            								/*if($fang_bili>0){
            								    $rs1= M('user_coin')->where(array($coin.'b'=>$addr))->setInc($coin.'s',$amount);//д���û��������
            								}else{
            								    $rs1= M('user_coin')->where(array($coin.'b'=>$addr))->setInc($coin,$amount);//д���û����
            								}*/
        								}
        							}else{
        								echo '���׹�ϣ:'.$v2['hash'].'�Ľ��׼�¼�Ѵ���!'.$amount.'<br>';
        							}
        						}
        					}
        				}else{
        					echo '�˻�:'.$v.'���׼�¼δ��ѯ��!<br>';
        				}
					}
			}
		}
     }
     
     
     //��ltc�����ģ������˼�¼����ʱ��
     public function get_ltclist(){
       
		$coinList = M('Coin')->where(array('status' => 1,'name'=>'ltc'))->select();

		foreach ($coinList as $k => $v) {
			if ($v['type'] != 'qbb') {
				continue;
			}

			$coin = $v['name'];

			if (!$coin) {
				echo 'MM';
				continue;
			}
			
			$dj_username = C('coin')[$coin]['dj_yh'];
			$dj_password = C('coin')[$coin]['dj_mm'];
			$dj_address = C('coin')[$coin]['dj_zj'];
			$dj_port = C('coin')[$coin]['dj_dk'];
			echo 'start ' . $coin . "\n";
			$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
			$json = $CoinClient->getinfo();

			if (!isset($json['version']) || !$json['version']) {
				echo '###ERR#####***** ' . $coin . ' connect fail***** ####ERR####>' . "\n";
				continue;
			}

			echo 'Cmplx ' . $coin . ' start,connect ' . (empty($CoinClient) ? 'fail' : 'ok') . ' :' . "\n";
			//$listtransactions = $CoinClient->listtransactions('*', 100, 0);
			
			
			if($coin=='usdt')
			{
			$listtransactions = $CoinClient->omni_listtransactions('*', 100, 0);
			}
			else
			{
			  $listtransactions = $CoinClient->listtransactions('*', 100, 0);  
			}
			
			echo 'listtransactions:' . count($listtransactions) . "\n";
			krsort($listtransactions);

//echo '<pre>';
//print_r($listtransactions);die;

			foreach ($listtransactions as $trans) {
			
			    
				if (!$trans['account'] && !$trans['referenceaddress']) {
					echo 'empty account continue' . "\n";
					continue;
				}
    			if($trans['confirmations']<3){
                
                	continue;
                }
                  $a=0;
    				
                if($coin!='usdt')
                {
    				if (!($user = M('User')->where(array('username' => $trans['account']))->find())) {
    					echo 'no account find continue' . "\n";
    					continue;
    				}
                }
                if($coin=='usdt')
                {
                    if (!($uuu = M('user_coin')->where(array('usdtb' => $trans['referenceaddress']))->find())) {
    					echo 'usdt no account find continue' . "\n";
    					continue;
    				}
    				else{
    				    $user = M('User')->where(array('id' => $uuu['userid']))->find();
                        $user_id=$user['id'];                  	
    				}
                }

				if (M('Myzr')->where(array('txid' => $trans['txid'], 'status' => '1'))->find()) {
					echo 'txid had found continue' . "\n";
					continue;
				}

				echo 'all check ok ' . "\n";

				if ($trans['category'] == 'receive'||$trans['propertyid']=='31') {
					//print_r($trans);
					echo 'start receive do:' . "\n";
					$sfee = 0;
                  	$true_amount=0;					
                  	$true_amount= $trans['amount'];
					
					if($trans['vout']>1 && $coin!='usdt')
					{
					    echo 'usdt fee, give up.'.$trans['txid'];
					   continue;
					}
					
					if($coin=='usdt')
					{
					    $addr=$trans['referenceaddress'];
					}
					else
					{
						$addr = $trans['address'];
					}

					if (C('coin')[$coin]['zr_zs']) {
						$song = round(($trans['amount'] / 100) * C('coin')[$coin]['zr_zs'], 8);

						if ($song) {
							$sfee = $song;
							$trans['amount'] = $trans['amount'] + $song;
						}
					}

					if ($trans['confirmations'] < C('coin')[$coin]['zr_dz']) {
						echo $trans['account'] . ' confirmations ' . $trans['confirmations'] . ' not elengh ' . C('coin')[$coin]['zr_dz'] . ' continue ' . "\n";
						echo 'confirmations <  c_zr_dz continue' . "\n";

						if ($res = M('myzr')->where(array('txid' => $trans['txid']))->find()) {
							M('myzr')->save(array('id' => $res['id'], 'addtime' => time(), 'status' => intval($trans['confirmations'] - C('coin')[$coin]['zr_dz'])));
						}
						else {
                          	$getmin=M('Coin')->where(array('name'=>$coin))->getField('getmin');
                              if($trans['amount']>=$getmin){
                                  M('myzr')->add(array('userid' => $user['id'], 'username' => $addr, 'coinname' => $coin, 'fee' => $sfee, 'txid' => $trans['txid'], 'num' => $true_amount, 'mum' => $trans['amount'], 'addtime' => time(), 'status' => intval($trans['confirmations'] - C('coin')[$coin]['zr_dz'])));
                              }
                        }

						continue;
					}
					else {
						echo 'confirmations full' . "\n";
						//continue;
					}

					$mo = M();
					$mo->execute('set autocommit=0');
					$mo->execute('lock tables  zhisucom_user_coin write , zhisucom_myzr  write, zhisucom_coin  write');
					$rs = array();
                  	$getmin=0;//M('Coin')->where(array('name'=>$coin))->getField('getmin');
                  
                      if ($res = $mo->table('zhisucom_myzr')->where(array('txid' => $trans['txid']))->find()) {
                          echo 'zhisucom_myzr find and set status 1';
                          $rs[] = $mo->table('zhisucom_myzr')->save(array('id' => $res['id'], 'addtime' => time(), 'status' => 1));
                          $a=1;
                      }else {
                        if($trans['amount']>=$getmin){	
                          echo 'zhisucom_myzr not find and add a new zhisucom_myzr' . "\n";
                          $rs[] = $mo->table('zhisucom_myzr')->add(array('userid' => $user['id'], 'username' => $addr, 'coinname' => $coin, 'fee' => $sfee, 'txid' => $trans['txid'], 'num' => $true_amount, 'mum' => $trans['amount'], 'addtime' => time(), 'status' => 1));
                          $a=2;                       
                        }
                      }
                  	  if($a>0){                      
                        if($trans['amount']>=$getmin){		
                       			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $user['id']))->setInc($coin, $trans['amount']);                       			
                   		 }                     
                      }	
                
					if (check_arr($rs)) {
						$mo->execute('commit');
						echo $trans['amount'] . ' receive ok ' . $coin . ' ' . $trans['amount'];
						$mo->execute('unlock tables');
						echo 'commit ok' . "\n";
					}
					else {
						echo $trans['amount'] . 'receive fail ' . $coin . ' ' . $trans['amount'];
						echo var_export($rs, true);
						$mo->execute('rollback');
						$mo->execute('unlock tables');
						print_r($rs);
						echo 'rollback ok' . "\n";
					}
				}
			
			}

		}
	
     }
     

    //��btc�����ģ����˼�¼����ʱ��
    public function get_btclist()
	{
		$coinList = M('Coin')->where(array('status' => 1,'name'=>'btc'))->select();

		foreach ($coinList as $k => $v) {
			if ($v['type'] != 'qbb') {
				continue;
			}

			$coin = $v['name'];

			if (!$coin) {
				echo 'MM';
				continue;
			}
			
			$dj_username = C('coin')[$coin]['dj_yh'];
			$dj_password = C('coin')[$coin]['dj_mm'];
			$dj_address = C('coin')[$coin]['dj_zj'];
			$dj_port = C('coin')[$coin]['dj_dk'];
			echo 'start ' . $coin . "\n";
			$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
			$json = $CoinClient->getinfo();

			if (!isset($json['version']) || !$json['version']) {
				echo '###ERR#####***** ' . $coin . ' connect fail***** ####ERR####>' . "\n";
				continue;
			}

			echo 'Cmplx ' . $coin . ' start,connect ' . (empty($CoinClient) ? 'fail' : 'ok') . ' :' . "\n";
			//$listtransactions = $CoinClient->listtransactions('*', 100, 0);
			
			
			if($coin=='usdt')
			{
			$listtransactions = $CoinClient->omni_listtransactions('*', 100, 0);
			}
			else
			{
			  $listtransactions = $CoinClient->listtransactions('*', 100, 0);  
			}
			
			echo 'listtransactions:' . count($listtransactions) . "\n";
			krsort($listtransactions);

//echo '<pre>';
//print_r($listtransactions);die;

			foreach ($listtransactions as $trans) {
			
			    
				if (!$trans['account'] && !$trans['referenceaddress']) {
					echo 'empty account continue' . "\n";
					continue;
				}
    			if($trans['confirmations']<4){
                
                	continue;
                }
                  $a=0;
    				
                if($coin!='usdt')
                {
    				if (!($user = M('User')->where(array('username' => $trans['account']))->find())) {
    					echo 'no account find continue' . "\n";
    					continue;
    				}
                }
                if($coin=='usdt')
                {
                    if (!($uuu = M('user_coin')->where(array('usdtb' => $trans['referenceaddress']))->find())) {
    					echo 'usdt no account find continue' . "\n";
    					continue;
    				}
    				else{
    				    $user = M('User')->where(array('id' => $uuu['userid']))->find();
                        $user_id=$user['id'];                  	
    				}
                }

				if (M('Myzr')->where(array('txid' => $trans['txid'], 'status' => '1'))->find()) {
					echo 'txid had found continue' . "\n";
					continue;
				}

				echo 'all check ok ' . "\n";

				if ($trans['category'] == 'receive'||$trans['propertyid']=='31') {
					//print_r($trans);
					echo 'start receive do:' . "\n";
					$sfee = 0;
                  	$true_amount=0;					
                  	$true_amount= $trans['amount'];
					
					/*if($trans['vout']>1 && $coin!='usdt')
					{
					    echo 'usdt fee, give up.'.$trans['txid'];
					   continue;
					}*/
					
					if($coin=='usdt')
					{
					    $addr=$trans['referenceaddress'];
					}
					else
					{
						$addr = $trans['address'];
					}

					if (C('coin')[$coin]['zr_zs']) {
						$song = round(($trans['amount'] / 100) * C('coin')[$coin]['zr_zs'], 8);

						if ($song) {
							$sfee = $song;
							$trans['amount'] = $trans['amount'] + $song;
						}
					}

					if ($trans['confirmations'] < C('coin')[$coin]['zr_dz']) {
						echo $trans['account'] . ' confirmations ' . $trans['confirmations'] . ' not elengh ' . C('coin')[$coin]['zr_dz'] . ' continue ' . "\n";
						echo 'confirmations <  c_zr_dz continue' . "\n";

						if ($res = M('myzr')->where(array('txid' => $trans['txid']))->find()) {
							M('myzr')->save(array('id' => $res['id'], 'addtime' => time(), 'status' => intval($trans['confirmations'] - C('coin')[$coin]['zr_dz'])));
						}
						else {
                          	$getmin=M('Coin')->where(array('name'=>$coin))->getField('getmin');
                              if($trans['amount']>=$getmin){
                                  M('myzr')->add(array('userid' => $user['id'], 'username' => $addr, 'coinname' => $coin, 'fee' => $sfee, 'txid' => $trans['txid'], 'num' => $true_amount, 'mum' => $trans['amount'], 'addtime' => time(), 'status' => intval($trans['confirmations'] - C('coin')[$coin]['zr_dz'])));
                              }
                        }

						continue;
					}
					else {
						echo 'confirmations full' . "\n";
						//continue;
					}

					$mo = M();
					$mo->execute('set autocommit=0');
					$mo->execute('lock tables  zhisucom_user_coin write , zhisucom_myzr  write, zhisucom_coin  write');
					$rs = array();
                  	$getmin=0;//M('Coin')->where(array('name'=>$coin))->getField('getmin');
                  
                      if ($res = $mo->table('zhisucom_myzr')->where(array('txid' => $trans['txid']))->find()) {
                          echo 'zhisucom_myzr find and set status 1';
                          $rs[] = $mo->table('zhisucom_myzr')->save(array('id' => $res['id'], 'addtime' => time(), 'status' => 1));
                          $a=1;
                      }else {
                        if($trans['amount']>=$getmin){	
                          echo 'zhisucom_myzr not find and add a new zhisucom_myzr' . "\n";
                          $rs[] = $mo->table('zhisucom_myzr')->add(array('userid' => $user['id'], 'username' => $addr, 'coinname' => $coin, 'fee' => $sfee, 'txid' => $trans['txid'], 'num' => $true_amount, 'mum' => $trans['amount'], 'addtime' => time(), 'status' => 1));
                          
                          //echo $mo->table('zhisucom_myzr')->getlastsql();
                          $a=2;                       
                        }
                      }
                  	  if($a>0){                      
                        if($trans['amount']>=$getmin){		
                       			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $user['id']))->setInc($coin, $trans['amount']);                       			
                   		 }                     
                      }	
                
					if (check_arr($rs)) {
						$mo->execute('commit');
						echo $trans['amount'] . ' receive ok ' . $coin . ' ' . $trans['amount'];
						$mo->execute('unlock tables');
						echo 'commit ok' . "\n";
					}
					else {
						echo $trans['amount'] . 'receive fail ' . $coin . ' ' . $trans['amount'];
						echo var_export($rs, true);
						$mo->execute('rollback');
						$mo->execute('unlock tables');
						print_r($rs);
						echo 'rollback ok' . "\n";
					}
				}
			
			}

			if ($trans['category'] == 'send') {
				echo 'start send do:' . "\n";

				if (3 <= $trans['confirmations']) {
					$myzc = M('Myzc')->where(array('txid' => $trans['txid']))->find();

					if ($myzc) {
						if ($myzc['status'] == 0) {
							M('Myzc')->where(array('txid' => $trans['txid']))->save(array('status' => 1));
							echo $trans['amount'] . '�ɹ�ת��' . $coin . ' ��ȷ��';
						}
					}
				}
			}
		}
	}
    
	public function qianbaob8c3b3d94512472db8()
	{
		$coinList = M('Coin')->where(array('status' => 1))->select();

		foreach ($coinList as $k => $v) {
			if ($v['type'] != 'qbb') {
				continue;
			}

			$coin = $v['name'];

			if (!$coin) {
				echo 'MM';
				continue;
			}
			//$coin = 'usdt';
			if($coin=='eth' || $coin=='jeff'){
                $this->ethonline($coin);
            }
            
			$dj_username = C('coin')[$coin]['dj_yh'];
			$dj_password = C('coin')[$coin]['dj_mm'];
			$dj_address = C('coin')[$coin]['dj_zj'];
			$dj_port = C('coin')[$coin]['dj_dk'];
			//echo 'start ' . $coin . "\n";
			$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
			$json = $CoinClient->getinfo();

			if (!isset($json['version']) || !$json['version']) {
				echo '###ERR#####***** ' . $coin . ' connect fail***** ####ERR####>' . "\n";
				continue;
			}

			echo 'Cmplx ' . $coin . ' start,connect ' . (empty($CoinClient) ? 'fail' : 'ok') . ' :' . "\n";
			
			
			//$listtransactions = $CoinClient->listtransactions('*', 100, 0);
			
			
			if($coin=='usdt')
			{
				//echo 111;die;
			$listtransactions = $CoinClient->omni_listtransactions('*', 100, 0);
			}
			else
			{
				//echo 222;die;
			  $listtransactions = $CoinClient->listtransactions('*', 100, 0);  
			}
			
			echo 'listtransactions:' . count($listtransactions) . "\n";
			krsort($listtransactions);

//echo '<pre>';
//echo $coin;
//echo '<pre>';
//print_r($listtransactions);die;

			foreach ($listtransactions as $trans) {
			
			    
				if (!$trans['account'] && !$trans['referenceaddress']) {
					echo 'empty account continue' . "\n";
					continue;
				}
    			if($trans['confirmations']<3){
                
                	continue;
                }
                  $a=0;
    				
                if($coin!='usdt')
                {
    				if (!($user = M('User')->where(array('username' => $trans['account']))->find())) {
    					echo 'no account find continue' . "\n";
    					continue;
    				}
                }
                if($coin=='usdt')
                {
                    if (!($uuu = M('user_coin')->where(array('usdtb' => $trans['referenceaddress']))->find())) {
    					echo 'usdt no account find continue' . "\n";
    					continue;
    				}
    				else{
    				    $user = M('User')->where(array('id' => $uuu['userid']))->find();
                        $user_id=$user['id'];                  	
    				}
                }

				if (M('Myzr')->where(array('txid' => $trans['txid'], 'status' => '1'))->find()) {
					echo 'txid had found continue' . "\n";
					continue;
				}

				echo 'all check ok ' . "\n";

				if ($trans['category'] == 'receive'||$trans['propertyid']=='31') {
					print_r($trans);
					echo 'start receive do:' . "\n";
					$sfee = 0;
                  	$true_amount=0;					
                  	$true_amount= $trans['amount'];
					
					/*if($trans['vout']>1 && $coin!='usdt')
					{
					    echo 'usdt fee, give up.'.$trans['txid'];
					    continue;
					}*/
					//echo $true_amount;
					if($coin=='usdt')
					{
					    $addr=$trans['referenceaddress'];
					}
					else
					{
						$addr = $trans['address'];
					}

					if (C('coin')[$coin]['zr_zs']) {
						$song = round(($trans['amount'] / 100) * C('coin')[$coin]['zr_zs'], 8);

						if ($song) {
							$sfee = $song;
							$trans['amount'] = $trans['amount'] + $song;
						}
					}

					if ($trans['confirmations'] < C('coin')[$coin]['zr_dz']) {
						
						echo $trans['account'] . ' confirmations ' . $trans['confirmations'] . ' not elengh ' . C('coin')[$coin]['zr_dz'] . ' continue ' . "\n";
						echo 'confirmations <  c_zr_dz continue' . "\n";

						if ($res = M('myzr')->where(array('txid' => $trans['txid']))->find()) {
							M('myzr')->save(array('id' => $res['id'], 'addtime' => time(), 'status' => intval($trans['confirmations'] - C('coin')[$coin]['zr_dz'])));
						}
						else {
                          	$getmin=M('Coin')->where(array('name'=>$coin))->getField('getmin');
                              if($trans['amount']>=$getmin){
                                  M('myzr')->add(array('userid' => $user['id'], 'username' => $addr, 'coinname' => $coin, 'fee' => $sfee, 'txid' => $trans['txid'], 'num' => $true_amount, 'mum' => $trans['amount'], 'addtime' => time(), 'status' => intval($trans['confirmations'] - C('coin')[$coin]['zr_dz'])));
                              }
                        }

						continue;
					}
					else {
						echo 'confirmations full' . "\n";
						//continue;
					}

					$mo = M();
					$mo->execute('set autocommit=0');
					$mo->execute('lock tables  zhisucom_user_coin write , zhisucom_myzr  write, zhisucom_coin  write');
					$rs = array();
                  	$getmin=0;//M('Coin')->where(array('name'=>$coin))->getField('getmin');
                  
                      if ($res = $mo->table('zhisucom_myzr')->where(array('txid' => $trans['txid']))->find()) {
                          echo 'zhisucom_myzr find and set status 1';
                          $rs[] = $mo->table('zhisucom_myzr')->save(array('id' => $res['id'], 'addtime' => time(), 'status' => 1));
                          $a=1;
                      }else {
                        if($trans['amount']>=$getmin){	
                          echo 'zhisucom_myzr not find and add a new zhisucom_myzr' . "\n";
                          $rs[] = $mo->table('zhisucom_myzr')->add(array('userid' => $user['id'], 'username' => $addr, 'coinname' => $coin, 'fee' => $sfee, 'txid' => $trans['txid'], 'num' => $true_amount, 'mum' => $trans['amount'], 'addtime' => time(), 'status' => 1));
                          
                          //echo $mo->table('zhisucom_myzr')->getlastsql();
                          $a=2;                       
                        }
                      }
                  	  if($a>0){                      
                        if($trans['amount']>=$getmin){		
                       			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $user['id']))->setInc($coin, $trans['amount']);                       			
                   		 }                     
                      }	
                
					if (check_arr($rs)) {
						$mo->execute('commit');
						echo $trans['amount'] . ' receive ok ' . $coin . ' ' . $trans['amount'];
						$mo->execute('unlock tables');
						echo 'commit ok' . "\n";
					}
					else {
						echo $trans['amount'] . 'receive fail ' . $coin . ' ' . $trans['amount'];
						echo var_export($rs, true);
						$mo->execute('rollback');
						$mo->execute('unlock tables');
						print_r($rs);
						echo 'rollback ok' . "\n";
					}
				}
			
			/*
				if (!$trans['account']) {
					echo 'empty account continue' . "\n";
					continue;
				}

				if (!($user = M('User')->where(array('username' => $trans['account']))->find())) {
					echo 'no account find continue' . "\n";
					continue;
				}

				if (M('Myzr')->where(array('txid' => $trans['txid'], 'status' => '1'))->find()) {
					echo 'txid had found continue' . "\n";
					continue;
				}

				echo 'all check ok ' . "\n";

				if ($trans['category'] == 'receive') {
					print_r($trans);
					echo 'start receive do:' . "\n";
					$sfee = 0;
					$true_amount = $trans['amount'];

					if (C('coin')[$coin]['zr_zs']) {
						$song = round(($trans['amount'] / 100) * C('coin')[$coin]['zr_zs'], 8);

						if ($song) {
							$sfee = $song;
							$trans['amount'] = $trans['amount'] + $song;
						}
					}

					if ($trans['confirmations'] < C('coin')[$coin]['zr_dz']) {
						echo $trans['account'] . ' confirmations ' . $trans['confirmations'] . ' not elengh ' . C('coin')[$coin]['zr_dz'] . ' continue ' . "\n";
						echo 'confirmations <  c_zr_dz continue' . "\n";

						if ($res = M('myzr')->where(array('txid' => $trans['txid']))->find()) {
							M('myzr')->save(array('id' => $res['id'], 'addtime' => time(), 'status' => intval($trans['confirmations'] - C('coin')[$coin]['zr_dz'])));
						}
						else {
							M('myzr')->add(array('userid' => $user['id'], 'username' => $trans['address'], 'coinname' => $coin, 'fee' => $sfee, 'txid' => $trans['txid'], 'num' => $true_amount, 'mum' => $trans['amount'], 'addtime' => time(), 'status' => intval($trans['confirmations'] - C('coin')[$coin]['zr_dz'])));
						}

						continue;
					}
					else {
						echo 'confirmations full' . "\n";
					}

					$mo = M();
					$mo->execute('set autocommit=0');
					$mo->execute('lock tables  zhisucom_user_coin write , zhisucom_myzr  write ');
					$rs = array();
					$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $user['id']))->setInc($coin, $trans['amount']);

					if ($res = $mo->table('zhisucom_myzr')->where(array('txid' => $trans['txid']))->find()) {
						echo 'zhisucom_myzr find and set status 1';
						$rs[] = $mo->table('zhisucom_myzr')->save(array('id' => $res['id'], 'addtime' => time(), 'status' => 1));
					}
					else {
						echo 'zhisucom_myzr not find and add a new zhisucom_myzr' . "\n";
						$rs[] = $mo->table('zhisucom_myzr')->add(array('userid' => $user['id'], 'username' => $trans['address'], 'coinname' => $coin, 'fee' => $sfee, 'txid' => $trans['txid'], 'num' => $true_amount, 'mum' => $trans['amount'], 'addtime' => time(), 'status' => 1));
					}

					if (check_arr($rs)) {
						$mo->execute('commit');
						echo $trans['amount'] . ' receive ok ' . $coin . ' ' . $trans['amount'];
						$mo->execute('unlock tables');
						echo 'commit ok' . "\n";
					}
					else {
						echo $trans['amount'] . 'receive fail ' . $coin . ' ' . $trans['amount'];
						echo var_export($rs, true);
						$mo->execute('rollback');
						$mo->execute('unlock tables');
						print_r($rs);
						echo 'rollback ok' . "\n";
					}
				}
			*/}

			if ($trans['category'] == 'send') {
				echo 'start send do:' . "\n";

				if (3 <= $trans['confirmations']) {
					$myzc = M('Myzc')->where(array('txid' => $trans['txid']))->find();

					if ($myzc) {
						if ($myzc['status'] == 0) {
							M('Myzc')->where(array('txid' => $trans['txid']))->save(array('status' => 1));
							echo $trans['amount'] . '�ɹ�ת��' . $coin . ' ��ȷ��';
						}
					}
				}
			}
		}
	}

	public function syn_qianbao()
	{
	}

	public function tendencyb8c3b3d94512472db8()
	{
		foreach (C('market') as $k => $v) {
			echo '----��������----' . $v['name'] . '------------';
			$tendency_time = 4;
			$t = time();
			$tendency_str = $t - (24 * 60 * 60 * 3);
			$x = 0;

			for (; $x <= 18; $x++) {
				$na = $tendency_str + (60 * 60 * $tendency_time * $x);
				$nb = $tendency_str + (60 * 60 * $tendency_time * ($x + 1));
				$b = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $v['name'] . '\'')->max('price');

				if (!$b) {
					$b = 0;
				}

				$rs[] = array($na, $b);
			}

			M('Market')->where(array('name' => $v['name']))->setField('tendency', json_encode($rs));
			unset($rs);
			echo '����ɹ�!';
			echo "\n";
		}

		echo '���Ƽ���0k ' . "\n";
	}

	public function chartb8c3b3d94512472db8()
	{
		foreach (C('market') as $k => $v) {
			$this->setTradeJson($v['name']);
		}

		echo '��������0k ' . "\n";
	}

	public function setTradeJson($market)
	{
		$timearr = array(1, 3, 5, 10, 15, 30, 60, 120, 240, 360, 720, 1440, 10080);

		foreach ($timearr as $k => $v) {
			$tradeJson = M('TradeJson')->where(array('market' => $market, 'type' => $v))->order('id desc')->find();

			if ($tradeJson) {
				$addtime = $tradeJson['addtime'];
			}
			else {
				$addtime = M('TradeLog')->where(array('market' => $market))->order('id asc')->getField('addtime');
			}

			if ($addtime) {
				$youtradelog = M('TradeLog')->where('addtime >=' . $addtime . '  and market =\'' . $market . '\'')->sum('num');
			}

			if ($youtradelog) {
				if ($v == 1) {
					$start_time = $addtime;
				}
				else {
					$start_time = mktime(date('H', $addtime), floor(date('i', $addtime) / $v) * $v, 0, date('m', $addtime), date('d', $addtime), date('Y', $addtime));
				}

				$x = 0;

				for (; $x <= 20; $x++) {
					$na = $start_time + (60 * $v * $x);
					$nb = $start_time + (60 * $v * ($x + 1));

					if (time() < $na) {
						break;
					}

					$sum = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->sum('num');

					if ($sum) {
						$sta = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->order('id asc')->getField('price');
						$max = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->max('price');
						$min = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->min('price');
						$end = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->order('id desc')->getField('price');
						$d = array($na, $sum, $sta, $max, $min, $end);

						if (M('TradeJson')->where(array('market' => $market, 'addtime' => $na, 'type' => $v))->find()) {
							M('TradeJson')->where(array('market' => $market, 'addtime' => $na, 'type' => $v))->save(array('data' => json_encode($d)));
						}
						else {
							$aa = M('TradeJson')->add(array('market' => $market, 'data' => json_encode($d), 'addtime' => $na, 'type' => $v));
							M('TradeJson')->execute('commit');
							M('TradeJson')->where(array('market' => $market, 'data' => '', 'type' => $v))->delete();
							M('TradeJson')->execute('commit');
						}
					}
					else {
						M('TradeJson')->add(array('market' => $market, 'data' => '', 'addtime' => $na, 'type' => $v));
						M('TradeJson')->execute('commit');
					}
				}
			}
		}

		return '����ɹ�!';
	}

	
	public function upsssss(){
		$aaaa = $this->upTrade();
		echo $aaaa;
	}
	
	public function upTrade_zhisucom1111111_8a201aa602cd9448($market = NULL)
	{
		die();
		$userid = rand(1, 2);
		$type = rand(1, 2);
		
		if (!$market) {
			//$market = C('market_mr');
			$market = "btc_cny";
		}

		$min_price = round(C('market')[$market]['buy_min'] * 100000000);
		$max_price = round(C('market')[$market]['buy_max'] * 100000000);
		$price = round(rand($min_price, $max_price) / 100000000, C('market')[$market]['round']);
		$max_num = round((C('market')[$market]['trade_max'] / C('market')[$market]['buy_max']) * 10000, 4);
		$min_num = round((1 / C('market')[$market]['buy_max']) * 10000, 4);
		$num = round(rand($min_num, $max_num) / 10000, C('market')[$market]['round']);

		if (!$price) {
			return '���׼۸��ʽ����';
		}
		return '���׼۸��ʽ����'.$max_price;
		if (!check($num, 'double')) {
			return '����������ʽ����';
		}

		if (($type != 1) && ($type != 2)) {
			return '�������͸�ʽ����';
		}

		if (!C('market')[$market]) {
			return '�����г�����';
		}
		else {
			$xnb = explode('_', $market)[0];
			$rmb = explode('_', $market)[1];
		}

		if (!C('market')[$market]['trade']) {
			return '��ǰ�г���ֹ����';
		}
		// TODO: SEPARATE

		$price = round(floatval($price), C('market')[$market]['round']);

		if (!$price) {
			return '���׼۸����';
		}

		$num = round(trim($num), 8 - C('market')[$market]['round']);

		if (!check($num, 'double')) {
			return '������������';
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
			return '�������ʹ���';
		}

		if ($max_price < $price) {
			return '���׼۸񳬹�������ƣ�';
		}

		if ($price < $min_price) {
			return '���׼۸񳬹���С���ƣ�';
		}

		$hou_price = C('market')[$market]['hou_price'];

		if ($hou_price) {
		}

		$user_coin = M('UserCoin')->where(array('userid' => $userid))->find();

		if ($type == 1) {
			$trade_fee = C('market')[$market]['fee_buy'];

			if ($trade_fee) {
				$fee = round((($num * $price) / 100) * $trade_fee, 8);
				$mum = round((($num * $price) / 100) * (100 + $trade_fee), 8);
			}
			else {
				$fee = 0;
				$mum = round($num * $price, 8);
			}

			if ($user_coin[$rmb] < $mum) {
				return C('coin')[$rmb]['title'] . '���㣡';
			}
		}
		else if ($type == 2) {
			$trade_fee = C('market')[$market]['fee_sell'];

			if ($trade_fee) {
				$fee = round((($num * $price) / 100) * $trade_fee, 8);
				$mum = round((($num * $price) / 100) * (100 - $trade_fee), 8);
			}
			else {
				$fee = 0;
				$mum = round($num * $price, 8);
			}

			if ($user_coin[$xnb] < $num) {
				return C('coin')[$xnb]['title'] . '����2��';
			}
		}
		else {
			return '�������ʹ���';
		}

		if (C('coin')[$xnb]['fee_bili']) {
			if ($type == 2) {
				$bili_user = round($user_coin[$xnb] + $user_coin[$xnb . 'd'], 8);

				if ($bili_user) {
					$bili_keyi = round(($bili_user / 100) * C('coin')[$xnb]['fee_bili'], 8);

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
							return '���Ĺҵ�����������ϵͳ���ƣ�����ǰ����' . C('coin')[$xnb]['title'] . $bili_user . '�����Ѿ��ҵ�' . $bili_zheng[0]['nums'] . '���������Թҵ�' . $bili_kegua . '��';
						}
					}
					else {
						return '�ɽ���������';
					}
				}
			}
		}

		if (C('market')[$market]['trade_min']) {
			if ($mum < C('market')[$market]['trade_min']) {
				return '�����ܶ��С��' . C('market')[$market]['trade_min'];
			}
		}

		if (C('market')[$market]['trade_max']) {
			if (C('market')[$market]['trade_max'] < $mum) {
				return '�����ܶ�ܴ���' . C('market')[$market]['trade_max'];
			}
		}

		if (!$rmb) {
			return '���ݴ���1';
		}

		if (!$xnb) {
			return '���ݴ���2';
		}

		if (!$market) {
			return '���ݴ���3';
		}

		if (!$price) {
			return '���ݴ���4';
		}

		if (!$num) {
			return '���ݴ���5';
		}

		if (!$mum) {
			return '���ݴ���6';
		}

		if (!$type) {
			return '���ݴ���7';
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_trade write ,zhisucom_user_coin write,zhisucom_finance write');
		$rs = array();

		if ($type == 1) {
			$finance = $mo->table('zhisucom_finance')->where(array('userid' => userid()))->order('id desc')->find();
			$finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();
			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $userid))->setDec($rmb, $mum);
			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $userid))->setInc($rmb . 'd', $mum);
			$rs[] = $finance_nameid = $mo->table('zhisucom_trade')->add(array('userid' => $userid, 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $fee, 'type' => 1, 'addtime' => time(), 'status' => 0));
			$finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => userid()))->find();
			$finance_hash = md5(userid() . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.zhisucom.com');
			$finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

			if ($finance['mum'] < $finance_num) {
				$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
			}
			else {
				$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
			}

			
			if($rmb == "cny"){
				$rs[] = $mo->table('zhisucom_finance')->add(array('userid' => userid(), 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $mum, 'type' => 2, 'name' => 'trade', 'nameid' => $finance_nameid, 'remark' => '��������-ί������-�г�' . $market, 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
			}
			
			
		}
		else if ($type == 2) {
			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $userid))->setDec($xnb, $num);
			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $userid))->setInc($xnb . 'd', $num);
			$rs[] = $mo->table('zhisucom_trade')->add(array('userid' => $userid, 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $fee, 'type' => 2, 'addtime' => time(), 'status' => 0));
		}
		else {
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			return '�������ʹ���';
		}

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			A('Trade')->matchingTrade($market);
			echo "";
			return '���׳ɹ���';
		}
		else {
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			return '����ʧ�ܣ�';
		}
	}
	
	
	
	
	
	public function upTrade_zhisucom_8a201aa602cd9448($market = NULL)
	{

		$type = rand(1, 2);
		
		if (!$market) {
			$market = C('market_mr');
		}
		
		if (!C('market')[$market]) {
			echo '�����г�����';
			die();
		}
		else {
			$xnb = explode('_', $market)[0];
			$rmb = explode('_', $market)[1];
		}
		
		
		$url = 'http://s301.safone.com.cn/api/v1/ticker/?coin='.$xnb;
		$content = file_get_contents($url);
		$content = json_decode($content, true);
		
		$wei = 1000;
		
		if(floatval($content['buy'])<10){
			$wei = 100000;
		}
		
		if(floatval($content['sell'])<10){
			$wei = 100000;
		}

		$min_price = floatval($content['buy'])*$wei;
		$max_price = floatval($content['sell'])*$wei;
		
		if($max_price<$min_price){
			$temps = $min_price;
			$min_price = $max_price;
			$max_price = $temps;
		}
		
		
		
		$price = round(rand($min_price, $max_price)/$wei, 6);
		
		
		if($xnb == "btc"){
			$max_num = round(10.9999 * 10000, 6);
			$min_num = round(0.9999 * 10000, 6);
		}else{
			$max_num = round(99.9999 * 10000, 6);
			$min_num = round(1.9999 * 10000, 6);
		}
		
		
		$num = round(rand($min_num, $max_num) / 10000,6);
		
		if (!$price) {
			echo '���׼۸��ʽ����';
			die();
		}

		if (!check($num, 'double')) {
			echo '����������ʽ����';
			die();
		}

		if (($type != 1) && ($type != 2)) {
			echo '�������͸�ʽ����';
		}

		// TODO: SEPARATE

		$price = round(floatval($price), 6);

		if (!$price) {
			echo '���׼۸����';
		}

		$num = round(trim($num), 6);

		if (!check($num, 'double')) {
			echo '������������';
		}

		$mum = round($num * $price, 6);

		if (!$rmb) {
			echo '���ݴ���1';
		}

		if (!$xnb) {
			echo '���ݴ���2';
		}

		if (!$market) {
			echo '���ݴ���3';
		}

		if (!$price) {
			echo '���ݴ���4';
		}

		if (!$num) {
			echo '���ݴ���5';
		}

		if (!$mum) {
			echo '���ݴ���6';
		}

		if (!$type) {
			echo '���ݴ���7';
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_trade write ');
		$rs = array();

		if ($type == 1) {
			$rs[] = $mo->table('zhisucom_trade')->add(array('userid' => 0, 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => 0, 'type' => 1, 'addtime' => time(), 'status' => 0));
		}
		else if ($type == 2) {
			$rs[] = $mo->table('zhisucom_trade')->add(array('userid' => 0, 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => 0, 'type' => 2, 'addtime' => time(), 'status' => 0));
		}
		else {
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			echo '�������ʹ���';
		}

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			A('Trade')->matchingAutoTrade($market);
			echo '���׳ɹ���';
		}
		else {
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			echo  '����ʧ�ܣ�';
		}
	}	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

?>