<?php
namespace Home\Controller;

class IndexController extends HomeController
{
	public function index()
	{
		$this->assign('userId',$_SESSION['userId']);
		$indexAdver = (APP_DEBUG ? null : S('index_indexAdver'));
		if (!$indexAdver) {
			$indexAdver = M('Adver')->where(array('status' => 1))->order('id asc')->select();
			S('index_indexAdver', $indexAdver);
		}

		$this->assign('indexAdver', $indexAdver);
		switch(C('index_html')){
            //如果a模版
			case "a":
				$indexArticle = (APP_DEBUG ? null : S('index_indexArticle'));
				$indexArticleType = array(
					"gonggao" => "aaa",
					"taolun"  => "币友说币",
					"hangye"  => "bbb"
				);
				if (!$indexArticle) {
					foreach ($indexArticleType as $k => $v) {
						$indexArticle[$k] = M('Article')->where(array('type' => $v, 'status' => 1, 'index' => 1))->order('id desc')->limit(4)->select();
						foreach($indexArticle[$k] as $kk =>$vv){
							$indexArticle[$k][$kk]['content'] = mb_substr(clear_html($vv['content']),0,40,'utf-8');
							if($indexArticle[$k][$kk]['img']){
								$indexArticle[$k][$kk]['img'] = "/upload/article/".$indexArticle[$k][$kk]['img'];	
							}else{
								$indexArticle[$k][$kk]['img'] = "/zhisucom/default/defaultImg.jpg";
							}	
						}
					}
					S('index_indexArticle', $indexArticle);
				}
				break;
			default:
				$indexArticleType = (APP_DEBUG ? null : S('index_indexArticleType'));
				if (!$indexArticleType) {
					$indexArticleType = M('ArticleType')->where(array('status' => 1, 'index' => 1))->order('sort asc ,id desc')->limit(3)->select();
					S('index_indexArticleType', $indexArticleType);
				}
				$indexArticle = (APP_DEBUG ? null : S('index_indexArticle'));
				if (!$indexArticle) {
					foreach ($indexArticleType as $k => $v) {
						$indexArticle[$k]   = M('Article')->where(array('type' => $v['name'], 'status' => 1, 'index' => 1))->order('id desc')->limit(6)->select();
                        $indexArticles[$k]  = M('Article')->where(array('type' => $v['name'], 'status' => 1, 'index' => 1))->order('id desc')->limit(3)->select();
					}
					S('index_indexArticle', $indexArticle);
                    S('index_indexArticles', $indexArticles);
				}
		}
		$this->assign('indexArticleType', $indexArticleType);
		$this->assign('indexArticle', $indexArticle);
		$this->assign('indexArticles', $indexArticles[0]);
		$indexLink = (APP_DEBUG ? null : S('index_indexLink'));
		if (!$indexLink) $indexLink = M('Link')->where(array('status' => 1))->order('sort asc ,id desc')->select();
		$zhisucom_getCoreConfig = zhisucom_getCoreConfig();
		if(!$zhisucom_getCoreConfig){
			$this->error('核心配置有误');
		}
		$this->assign('zhisucom_jiaoyiqu', $zhisucom_getCoreConfig['zhisucom_indexcat']);
		$this->assign('indexLink', $indexLink);
        $ajaxMenu   = new AjaxController();
        $indexMenu  = $ajaxMenu->getJsonMenu('');
        $this->assign('indexMenu', $indexMenu);
		$huobi      = A("Huobi");
		$coin       = C('MARKET');
		foreach($coin as $ks=>$vs){
			$b1=strtoupper($vs['xnb']);
			$b2=strtoupper($vs['rmb']);
			$coin[$ks]['s1']         ="<b>$b1</b><font color='#bbb'>/ $b2</font>";
			$coin[$ks]['urlname']    = $vs['xnb'].'_'.$vs['rmb'];
		}
        /*
        *is_new==1主区，is_new==2创新区
        */
        $name_en    = ['usdt'=>'usdt','btc'=>'btc','eth'=>'eth'];
        $allmarket  = json_decode(getUrl("http://api.zb.plus/data/v1/allTicker"),1);
        $names      = '';
        $sql        = "UPDATE zhisucom_market SET new_price = CASE name ";
        foreach ($name_en as $k => $v) {
            $names .= "'".$v."_cny',";
            $price = $allmarket[$v.'qc']['last'] ? $allmarket[$v.'qc']['last'] : 1;
            $sql .= sprintf("WHEN %s THEN %s ", "'".$k."_cny'", "'".$price."'");
        }
        $names = rtrim($names,',');
        $sql .= "END WHERE name IN ($names)";
        M('')->execute($sql);
		/*foreach ($name_en as $k => $v) {
			$name_en_str = $v.'_qc';
			$price       = getUrl("http://api.zb.plus/data/v1/ticker?market={$name_en_str}");
			$price       = json_decode($price,1);
			$prices      = $price['ticker']['last'] ? $price['ticker']['last'] : 1;
			$name        = $k.'_cny';
			M('Market')->where(array('name'=>$name))->save(array('new_price'=>$prices));
		}*/
        // dumpS($allmarket);
        foreach ($coin as $k=>$v)
        {
            if($v['is_new'] == 1)
            {
                if ($v['jiaoyiqu']==0)
                {
                    $xnb=explode('_',$v['name'])[0];
                    /*if($xnb == 'amfc')
                    {
                      	$rrs = $this->amfcfbt();
                        if($rrs)
                        {
                        	$v['new_price'] =$rrs['last'] ? $rrs['last'] : 0;
                            $v['min_price'] =$rrs['low'];
                            $v['max_price'] =$rrs['high'];
                            $v['volume']    =$rrs['vol24hour'];
                          	$v['change']    = $rrs['chg'];
                            M('market')->where(['name'=>$v['name']])->save(['new_price'=>$v['new_price'],'min_price'=>$v['min_price'],'max_price'=>$v['max_price'],'volume'=>$v['volume'],'change'=>$v['change']]);
                        }
                    }*/
                    $list['JEFF'][]=$v;
                }

                if ($v['jiaoyiqu']==1){
                    $xnb        = explode('_',$v['name'])[0];
                    $xnb_h      = $xnb.'usdt';
                    /*$hb_price   = $huobi->hangqing($xnb_h);
                    if ($hb_price['status']!=='error'){
                        $v['new_price'] = $hb_price['tick']['close'] ? $hb_price['tick']['close']: 0;
                        $v['min_price'] = $hb_price['tick']['low'] ? $hb_price['tick']['low']: 0;
                        $v['max_price'] = $hb_price['tick']['high'] ? $hb_price['tick']['high']: 0;
                        $v['volume']    = $hb_price['tick']['amount'] ? $hb_price['tick']['amount']: 0;
                      	$v['change']    = round(($hb_price['tick']['close']-$hb_price['tick']['open'])/$hb_price['tick']['close'],2);
                    }*/
                    if (array_key_exists($xnb_h, $allmarket)){
                        $v['new_price'] = $allmarket[$xnb_h]['close'] ? $allmarket[$xnb_h]['close']: 0;
                        $v['min_price'] = $allmarket[$xnb_h]['low'] ? $allmarket[$xnb_h]['low']: 0;
                        $v['max_price'] = $allmarket[$xnb_h]['high'] ? $allmarket[$xnb_h]['high']: 0;
                        $v['volume']    = $allmarket[$xnb_h]['vol'] ? $allmarket[$xnb_h]['vol']: 0;
                        $v['change']    = round(($allmarket[$xnb_h]['close']-$allmarket[$xnb_h]['open'])/$allmarket[$xnb_h]['close'],2);
                    }
                    if (is_nan($v['change'])) $v['change']=0;
                    M('market')->where(['name'=>$v['name']])->save(['new_price'=>$v['new_price'],'min_price'=>$v['min_price'],'max_price'=>$v['max_price'],'volume'=>$v['volume'],'change'=>$v['change']]);
                    $list['USDT'][]=$v;
                }
                if ($v['jiaoyiqu']==2){
                    $xnb        = explode('_',$v['name'])[0];
                    $xnb_h      = $xnb.'btc';
                    /*$hb_price   = $huobi->hangqing($xnb_h);
                    if ($hb_price['status']!=='error'){
                        $v['new_price'] = $hb_price['tick']['close'];
                        $v['min_price'] = $hb_price['tick']['low'];
                        $v['max_price'] = $hb_price['tick']['high'];
                        $v['volume']    = $hb_price['tick']['amount'];
                    }*/
                    if (array_key_exists($xnb_h, $allmarket)){
                        $v['new_price'] = $allmarket[$xnb_h]['close'] ? $allmarket[$xnb_h]['close']: 0;
                        $v['min_price'] = $allmarket[$xnb_h]['low'] ? $allmarket[$xnb_h]['low']: 0;
                        $v['max_price'] = $allmarket[$xnb_h]['high'] ? $allmarket[$xnb_h]['high']: 0;
                        $v['volume']    = $allmarket[$xnb_h]['vol'] ? $allmarket[$xnb_h]['vol']: 0;
                        $v['change']    = round(($allmarket[$xnb_h]['close']-$allmarket[$xnb_h]['open'])/$allmarket[$xnb_h]['close'],2);
                    }
                    $list['BTC'][]=$v;
                }
    			if ($v['jiaoyiqu']==3){
    			    $xnb         = explode('_',$v['name'])[0];
                    $xnb_h      = $xnb.'eth';
                    /*$hb_price   = $huobi->hangqing($xnb_h);
                    if ($hb_price['status']!=='error'){
                         $v['new_price']=$hb_price['tick']['close'];
                         $v['min_price']=$hb_price['tick']['low'];
                         $v['max_price']=$hb_price['tick']['high'];
                         $v['volume']   =$hb_price['tick']['amount'];
                    }*/
                    if (array_key_exists($xnb_h, $allmarket)){
                        $v['new_price'] = $allmarket[$xnb_h]['close'] ? $allmarket[$xnb_h]['close']: 0;
                        $v['min_price'] = $allmarket[$xnb_h]['low'] ? $allmarket[$xnb_h]['low']: 0;
                        $v['max_price'] = $allmarket[$xnb_h]['high'] ? $allmarket[$xnb_h]['high']: 0;
                        $v['volume']    = $allmarket[$xnb_h]['vol'] ? $allmarket[$xnb_h]['vol']: 0;
                        $v['change']    = round(($allmarket[$xnb_h]['close']-$allmarket[$xnb_h]['open'])/$allmarket[$xnb_h]['close'],2);
                    }
                    $list['ETH'][]=$v;
                }
            }elseif($v['is_new'] == 2){
                if ($v['jiaoyiqu']==0){
                    $new_list['JEFF'][]=$v;
                }
                if ($v['jiaoyiqu']==1){
                    $new_list['USDT'][]=$v;
                }
                if ($v['jiaoyiqu']==2){
                    $new_list['BTC'][]=$v;
                }
    			if ($v['jiaoyiqu']==3){
                    $new_list['ETH'][]=$v;
                }
            }
        }


		$this->assign('list', $list['JEFF']);
		$this->assign('jtc', $list['JEFF']);
		$this->assign('usdt', $list['USDT']);
		$this->assign('btc', $list['BTC']);
		$this->assign('eth', $list['ETH']);
		$this->assign('new_jtc', $new_list['JEFF']);
		$this->assign('new_usdt', $new_list['USDT']);
		$this->assign('new_btc', $new_list['BTC']);
		$this->assign('new_eth', $new_list['ETH']);

		/*
		*首页公告开始
		*/
		$notice       = M('Article')->where(array('type'=>'notice','index'=>1,'status'=>1,'sort'=>array('gt','0')))->limit(3)->select();
		$new_notice   = M('Article')->where(array('type'=>'notice','index'=>1,'status'=>1,'sort'=>0))->order('addtime desc')->find();
		$xinwen       = M('Article')->where(array('status'=>1,'type'=>'info','footer'=>1))->select();
		$about        = M('Article')->where(array('status'=>1,'type'=>'aboutus','footer'=>1))->select();
		$this->assign('xinwen',$xinwen);
		$this->assign('about',$about);
		$this->assign('new_notice',$new_notice);
		$this->assign('notice',$notice);
		/*结束*/
		$this->assign('yuyan',$_SESSION['lang']);
		if (C('index_html')) {
			$this->display('Index/' . C('index_html') . '/index');
		}
		else {
			$this->display();
		}
	}
  
  
  public function amfcfbt()
  {
  	   $accesskey = urlencode('kaXZhiCbrAD71Ei0JRybBxxNTeTc19nrr5uzlMj3Z5A=');
       $url = "https://api.fubt.co/v1/market/tickers?symbol=amfcusdt&klineType=min&klineStep=step5&accessKey=".$accesskey."&signature=a2FYWmhpQ2JyQUQ3MUVpMEpSeWJCeHhOVGVUYzE5bnJyNXV6bE1qM1o1QSUzRA==";
       $postFields = "";
       $ch = curl_init ();
       curl_setopt( $ch, CURLOPT_URL, $url );
       curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
       //curl_setopt( $ch, CURLOPT_POST, 1 );
       // curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
       curl_setopt( $ch, CURLOPT_TIMEOUT,60);
       $res = json_decode(curl_exec($ch),1);
       // $a = $res['data']['tradeName']['amfcfbt'];
      dumpS($res);
      if($res['status'] == 'success')
      {
      	foreach($res['data'] as $k=>$v){
          if($v['tradeName'] == "amfcfbt") $amfc = $v;
        }
        return $amfc;
      } else {
    	return false;
      }
  }
  
  
  
  
	//主页USDT BTC区数据
	public function usdtbtc(){
				
			$bigarr = [];
			$usdt=M("usdt")->select();
			$btc=M('btc')->select();			
			$bigarr['usdt'] = $usdt;
			$bigarr['btc'] = $btc;
			$this->ajaxReturn($bigarr);  			
	}
	//UDB区数据
	public function udb(){
			
		$coin=C('MARKET');
		foreach($coin as $ks=>$vs){
			$b1=strtoupper($vs['xnb']);
			$b2=strtoupper($vs['rmb']);
			$coin[$ks]['s1']="<b>$b1</b><font color='#bbb'>/ $b2</font>";
		}
						
        foreach ($coin as $k=>$v){
            if ($v['jiaoyiqu']==0){
                $list['BDB'][]=$v;
            }
        }
		$this->ajaxReturn($list['BDB']);
	} 

	public function monesay($monesay = NULL)
	{
	}

	public function install()
	{
	}

    public function fragment()
    {
        $ajax = new AjaxController();
        $data  = $ajax->allcoin('');
        $this->assign('data', $data);
        $this->display('Index/d/fragment');
    }

    public function newPrice()
    {
        ini_set('display_errors', 'on');
        error_reporting(E_ALL);
        //var_dump(C('market'));
        $data = $this->allCoinPrice();
        //var_dump($data);
       // exit;
        $last_data = S('ajax_all_coin_last');
        $_result = array();
        if (empty($last_data)) {
            foreach (C('market') as $k => $v) {
                $_result[$v['id'] . '-' . strtoupper($v['xnb'])] =  $data[$k][1] . '-0.0';
            }
        } else {
            foreach (C('market') as $k => $v) {
                $_result[$v['id'] . '-' . strtoupper($v['xnb'])] =  $data[$k][1] . '-' . ($data[$k][1] - $last_data[$k][1]);
            }
        }

        S('ajax_all_coin_last', $data);

        $data = json_encode(
            array(
                'result' => $_result,
            )
        );
        exit($data);

        //exit('{"result":{"25-BTC":"4099.0-0.0","1-LTC":"26.43--0.22650056625141082","26-DZI":"1.72-0.0","6-DOGE":"0.00151-0.0"},"totalPage":5}');
    }


    protected function allCoinPrice()
    {
        $data = (APP_DEBUG ? null : S('allcoin'));

        // 市场交易记录
        $marketLogs = array();
        foreach (C('market') as $k => $v) {
            $tradeLog = M('TradeLog')->where(array('status' => 1, 'market' => $k))->order('id desc')->limit(50)->select();
            $_data = array();
            foreach ($tradeLog as $_k => $v) {
                $_data['tradelog'][$_k]['addtime'] = date('m-d H:i:s', $v['addtime']);
                $_data['tradelog'][$_k]['type'] = $v['type'];
                $_data['tradelog'][$_k]['price'] = $v['price'] * 1;
                $_data['tradelog'][$_k]['num'] = round($v['num'], 6);
                $_data['tradelog'][$_k]['mum'] = round($v['mum'], 2);
            }
            $marketLogs[$k] = $_data;
        }

        $themarketLogs = array();
        if ($marketLogs) {
            $last24 = time() - 86400;
            $_date = date('m-d H:i:s', $last24);
            foreach (C('market') as $k => $v) {
                $tradeLog = isset($marketLogs[$k]['tradelog']) ? $marketLogs[$k]['tradelog'] : null;
                if ($tradeLog) {
                    $sum = 0;
                    foreach ($tradeLog as $_k => $_v) {
                        if ($_v['addtime'] < $_date) {
                            continue;
                        }
                        $sum += $_v['mum'];
                    }
                    $themarketLogs[$k] = $sum;
                }
            }
        }

        foreach (C('market') as $k => $v) {
            $data[$k][0] = $v['title'];
            $data[$k][1] = round($v['new_price'], $v['round']);
            $data[$k][2] = round($v['buy_price'], $v['round']);
            $data[$k][3] = round($v['sell_price'], $v['round']);
            $data[$k][4] = isset($themarketLogs[$k]) ? $themarketLogs[$k] : 0;//round($v['volume'] * $v['new_price'], 2) * 1;
            $data[$k][5] = '';
            $data[$k][6] = round($v['volume'], 2) * 1;
            $data[$k][7] = round($v['change'], 2);
            $data[$k][8] = $v['name'];
            $data[$k][9] = $v['xnbimg'];
            $data[$k][10] = '';
        }
        return $data;
    }

}

?>