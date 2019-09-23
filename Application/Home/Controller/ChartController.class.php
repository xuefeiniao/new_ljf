<?php
namespace Home\Controller;

class ChartController extends HomeController
{
	public function getJsonData($market = NULL, $ajax = 'json')
	{
		if ($market) {
			$data = (APP_DEBUG ? null : S('ChartgetJsonData' . $market));

			$zhisucom_getCoreConfig = zhisucom_getCoreConfig();
			if(!$zhisucom_getCoreConfig){
				$this->error('核心配置有误');
			}else{
				$zhisucom_putong = $zhisucom_getCoreConfig['zhisucom_userTradeDetailNum'];
				$zhisucom_teshu = $zhisucom_getCoreConfig['zhisucom_specialUserTradeDetailNum'];
			}
			
			$limit = $zhisucom_putong;
			if(userid()){
				$usertype = M('User')->where(array($id => $userid))->getField('usertype');
				if($usertype ==1){
					$limit = $zhisucom_teshu;
				}else{
					$limit = $zhisucom_putong;
				}
			}

			if (!$data) {
				$data[0] = $this->getTradeBuy($market,$limit);
				$data[1] = $this->getTradeSell($market,$limit);
				$data[2] = $this->getTradeLog($market,$limit);
				S('ChartgetJsonData' . $market, $data);
			}

			exit(json_encode($data));
		}
	}

	protected function getTradeBuy($market,$limit)
	{
		
		$mo = M();
		$buy = $mo->query('select id,price,sum(num-deal)as nums from zhisucom_trade  where status=0 and type=1 and market =\'' . $market . '\' group by price order by price desc limit '.$limit);
		$data = '';
		
		if ($buy) {
			$maxNums = maxArrayKey($buy, 'nums') / 2;

			foreach ($buy as $k => $v) {
				/*$data .= '<tr style=""><td class=\'buy\'  width=\'50\'>买' . ($k + 1) . '</td><td class=\'buy\'  width=\'80\'>' . floatval($v['price']) . '</td><td class=\'buy\'  width=\'120\'>' . floatval($v['nums']) . '</td><td  width=\'100\'><span class=\'buySpan\' style=\'width: ' . ((($maxNums < $v['nums'] ? $maxNums : $v['nums']) / $maxNums) * 100) . 'px;\' ></span></td></tr>';*/
                $data .= '<tr onclick="autotrust(this,\'buy\',1)" style="cursor:pointer;"><td class=\'buy\'  width=\'50\'>买' . ($k+1) . '</td><td class=\'buy\'  width=\'80\'>' . floatval($v['price']) . '</td><td class=\'buy\'  width=\'120\'>' . floatval($v['nums']) . '</td></tr>';
			}
		}

		return $data;
	}

	protected function getTradeSell($market,$limit)
	{
		$limit = intval($limit);
		$mo = M();
		$sell = $mo->query('select id,price,sum(num-deal)as nums from zhisucom_trade where status=0 and type=2 and market =\'' . $market . '\' group by price order by price desc limit '.$limit);
		$data = '';
		$ccc = count($sell);
		if ($sell) {
			$maxNums = maxArrayKey($sell, 'nums') / 2;

			foreach ($sell as $k => $v) {
				
				/*$data .= '<tr><td class=\'sell\'  width=\'50\'>卖' . ($k + 1) . '</td><td class=\'sell\'  width=\'80\'>' . floatval($v['price']) . '</td><td class=\'sell\'  width=\'120\'>' . floatval($v['nums']) . '</td><td style=\'width: 100px;\'><span class=\'sellSpan\' style=\'width: ' . ((($maxNums < $v['nums'] ? $maxNums : $v['nums']) / $maxNums) * 100) . 'px;\' ></span></td></tr>';*/
                $data .= '<tr onclick="autotrust(this,\'sell\',1)" style="cursor:pointer;"><td class=\'sell\'  width=\'50\'>卖' . ($ccc-$k) . '</td><td class=\'sell\'  width=\'80\'>' . floatval($v['price']) . '</td><td class=\'sell\'  width=\'120\'>' . floatval($v['nums']) . '</td></tr>';
			}
		}

		return $data;
	}

	protected function getTradeLog($market,$limit)
	{
		
		$log = M('TradeLog')->where(array('status' => 1, 'market' => $market))->order('id desc')->limit($limit)->select();
		$data = '';

		if ($log) {
			foreach ($log as $k => $v) {
				if ($v['type'] == 1) {
					$type = 'buy';
				}
				else {
					$type = 'sell';
				}

				/*$data .= '<tr><td class=\'' . $type . '\'  width=\'70\'>' . date('H:i:s', $v['addtime']) . '</td><td class=\'' . $type . '\'  width=\'70\'>' . floatval($v['price']) . '</td><td class=\'' . $type . '\'  width=\'100\'>' . floatval($v['num']) . '</td><td class=\'' . $type . '\'>' . floatval($v['mum']) . '</td></tr>';*/
                $data .= '<tr><td class=\'' . $type . '\'  width=\'70\'>' . date('H:i:s', $v['addtime']) . '</td><td class=\'' . $type . '\'  width=\'70\'>' . floatval($v['price']) . '</td><td class=\'' . $type . '\'  width=\'100\'>' . floatval($v['num']) . '</td></tr>';
			}
		}

		return $data;
	}

	public function trend()
	{
		// TODO: SEPARATE
		$input = I('get.');
		$market = (is_array(C('market')[$input['market']]) ? trim($input['market']) : C('market_mr'));
		$this->assign('market', $market);
		$this->display();
	}

	public function getMarketTrendJson()
	{
		// TODO: SEPARATE
		$input = I('get.');
		$market = (is_array(C('market')[$input['market']]) ? trim($input['market']) : C('market_mr'));
		$data = (APP_DEBUG ? null : S('ChartgetMarketTrendJson' . $market));

		if (!$data) {
			$data = M('TradeLog')->where(array(
				'market'  => $market,
				'addtime' => array('gt', time() - (60 * 60 * 24 * 30 * 2))
				))->select();
			S('ChartgetMarketTrendJson' . $market, $data);
		}

		$json_data = array();
		foreach ($data as $k => $v) {
			$json_data[$k][0] = $v['addtime'];
			$json_data[$k][1] = $v['price'];
		}

		exit(json_encode($json_data));
	}

	public function ordinary()
	{
		// TODO: SEPARATE
		$input = I('get.');
		$market = (is_array(C('market')[$input['market']]) ? trim($input['market']) : C('market_mr'));
		$this->assign('market', $market);
		$this->display();
	}

	public function getMarketOrdinaryJson()
	{
		// TODO: SEPARATE
		$input = I('get.');
		$market = (is_array(C('market')[$input['market']]) ? trim($input['market']) : C('market_mr'));
		$timearr = array(1, 3, 5, 10, 15, 30, 60, 120, 240, 360, 720, 1440, 10080);

		if (in_array($input['time'], $timearr)) {
			$time = $input['time'];
		}
		else {
			$time = 5;
		}

		$timeaa = (APP_DEBUG ? null : S('ChartgetMarketOrdinaryJsontime' . $market . $time));

		if (($timeaa + 60) < time()) {
			S('ChartgetMarketOrdinaryJson' . $market . $time, null);
			S('ChartgetMarketOrdinaryJsontime' . $market . $time, time());
		}

		$tradeJson = (APP_DEBUG ? null : S('ChartgetMarketOrdinaryJson' . $market . $time));

		if (!$tradeJson) {
			$tradeJson = M('TradeJson')->where(array(
				'market' => $market,
				'type'   => $time,
				'data'   => array('neq', '')
				))->order('id desc')->limit(100)->select();
			S('ChartgetMarketOrdinaryJson' . $market . $time, $tradeJson);
		}

		krsort($tradeJson);

		$json_data = array();
		foreach ($tradeJson as $k => $v) {
			$json_data[] = json_decode($v['data'], true);
		}

		exit(json_encode($json_data));
	}

	public function getMarketNewKzhisucomJson()
	{
		// TODO: SEPARATE
		$input = I('get.');
		$market = (is_array(C('market')[$input['market']]) ? trim($input['market']) : C('market_mr'));
		
		$timearr = array(1, 3, 5, 10, 15, 30, 60, 120, 240, 360, 720, 1440, 10080);
		
		
		
		if (in_array($input['time'], $timearr)) {
			$time = $input['time'];
		}
		else {
			$time = 5;
		}

		$timeaa = (APP_DEBUG ? null : S('ChartgetMarketOrdinaryJsontime' . $market . $time));

		if (($timeaa + 60) < time()) {
			S('ChartgetMarketOrdinaryJson' . $market . $time, null);
			S('ChartgetMarketOrdinaryJsontime' . $market . $time, time());
		}

		$tradeJson = (APP_DEBUG ? null : S('ChartgetMarketOrdinaryJson' . $market . $time));

		if (!$tradeJson) {
			$tradeJson = M('TradeJson')->where(array(
				'market' => $market,
				'type'   => $time,
				'data'   => array('neq', '')
				))->order('id desc')->limit(100)->select();
			S('ChartgetMarketOrdinaryJson' . $market . $time, $tradeJson);
		}

		krsort($tradeJson);

		$json_data = array();
		foreach ($tradeJson as $k => $v) {
			$json_data[] = json_decode($v['data'], true);
		}

		exit(json_encode($json_data));
	}


	public function specialty()
	{
		// TODO: SEPARATE
		$input = I('get.');
		$market = (is_array(C('market')[$input['market']]) ? trim($input['market']) : C('market_mr'));
		$this->assign('market', $market);
		$this->display();
	}

	public function getMarketSpecialtyJson_xxx()
	{
		// TODO: SEPARATE
		$input = I('get.');
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
			$data[$k][0] = $v[0];
			$data[$k][1] = 0;
			$data[$k][2] = 0;
			$data[$k][3] = $v[2];
			$data[$k][4] = $v[5];
			$data[$k][5] = $v[3];
			$data[$k][6] = $v[4];
			$data[$k][7] = $v[1];//
		}

		exit(json_encode($data));
	}
	
	public function getMarketSpecialtyJson()
	{
		header("Access-Control-Allow-Origin:*");
		header('Access-Control-Allow-Methods:POST');
		header('Access-Control-Allow-Headers:x-requested-with, content-type');
		
		$market=I('get.market');
		//echo $market;
		//$market='ltc_usdt';
		//$ktime=I('ktime');
		$input = I('get.');
		$time1 = $input['step'] / 60;
		
		if($time1 <=60){
			if($time1 == 3){
				$time1 = 5;
			}
			$ktime = $time1.'min';
		}elseif(60 < $time1 && $time1 < 1440){
			if($time1 == 120){
				$time1 = 240;
			}
			$ktime = floor($time1/60).'hour';
		}elseif($time1 >=1440 && $time1 < 10080  ){
			$ktime =floor($time1/60/24).'day';
		}elseif($time1 == 10080){
			$ktime = '1week';	
		}
		$k_time = array('1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week', '1year');
		//echo $ktime;die;
		if(!in_array($ktime,$k_time)){
			$ktime = '1day';
		}
		//$ktime = '3min';
		$num = '150';
		//$num=I('num');				
		$xnb = explode('_', $market)[0];
		$rmb = explode('_', $market)[1];
		$bi_type=$xnb.$rmb;	
		$huobi=new \Home\Controller\HuobiController;
		$res1=$huobi->kxian($bi_type,$ktime,$num);

		//$this->ajaxReturn($res1);
		$res=$this->gskxian($res1);	
		//var_dump($res1);die;
		$kline=array_reverse($res);
		//$this->ajaxReturn($kline);
		if($res1['status'] === 'error'){
			
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
				$data[$k][0] = $v[0];
				$data[$k][1] = 0;
				$data[$k][2] = 0;
				$data[$k][3] = $v[2];
				$data[$k][4] = $v[5];
				$data[$k][5] = $v[3];
				$data[$k][6] = $v[4];
				$data[$k][7] = $v[1];//
			}
			exit(json_encode($data));die;
		}else{
			exit(json_encode($kline));
		}
		
		
	}
	//lines: K线图, 依次是: 时间(ms), 开盘价, 最高价, 最低价, 收盘价, 成交量
	
	/*		$res[$key][0]=$i['ts'];
			$res[$key][1]=$val['open'];	
			$res[$key][2]=$val['high'];	
			$res[$key][3]=$val['low'];	
			$res[$key][4]=$val['close'];	
			$res[$key][5]=$val['amount'];
			\
			$data[$k][0] = $v[0];na
			$data[$k][1] = 0;
			$data[$k][2] = 0;
			$data[$k][3] = $v[2];sta
			$data[$k][4] = $v[5];end
			$data[$k][5] = $v[3];max
			$data[$k][6] = $v[4];min
			$data[$k][7] = $v[1];//sum
			array($na, $sum, $sta, $max, $min, $end);
	*/
	protected function gskxian($i){
					
		foreach($i['data'] as $key=>$val){			
			$res[$key][0]=$val['id'];//时间
			$res[$key][1]=0;
			$res[$key][2]=0;
			$res[$key][3]=$val['open'];//开盘价			
			$res[$key][4]=$val['close'];//收盘价	
			$res[$key][5]=$val['high'];//最高价	
			$res[$key][6]=$val['low'];	//最低价
			$res[$key][7]=$val['amount'];//成交量
							
		}		
		return $res;		
	}	

	public function getSpecialtyTrades()
	{
		$input = I('get.');

		if (!$input['since']) {
			$tradeLog = M('TradeLog')->where(array('market' => $input['market']))->order('id desc')->find();
			$json_data[] = array('tid' => $tradeLog['id'], 'date' => $tradeLog['addtime'], 'price' => $tradeLog['price'], 'amount' => $tradeLog['num'], 'trade_type' => $tradeLog['type'] == 1 ? 'bid' : 'ask');
			exit(json_encode($json_data));
		}
		else {
			$tradeLog = M('TradeLog')->where(array(
	'market' => $input['market'],
	'id'     => array('gt', $input['since'])
	))->order('id desc')->select();
			$json_data = array();
			foreach ($tradeLog as $k => $v) {
				$json_data[] = array('tid' => $v['id'], 'date' => $v['addtime'], 'price' => $v['price'], 'amount' => $v['num'], 'trade_type' => $v['type'] == 1 ? 'bid' : 'ask');
			}

			exit(json_encode($json_data));
		}
	}


    public function getCoinlist()
    {
        $coin=C('MARKET');
        foreach ($coin as $k=>$v){
            if ($v['jiaoyiqu']==0){
                $list[1][]=$v;
            }
            if ($v['jiaoyiqu']==1){
                $list[2][]=$v;
            }
            if ($v['jiaoyiqu']==2){
                $list[3][]=$v;
            }
        }
        dump($list);
    }


}

?>