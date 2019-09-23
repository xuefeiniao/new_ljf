<?php
namespace Wap\Controller;

class IndexController extends HomeController
{
	
	//首页轮播图
	public function index_tu(){
		$info = M('adver')->where(array('status'=>1,'is_app'=>0))->select();
		$gonggao = M('article')->where('status=1')->field('id,title,title_en,title_e,img')->select();
		foreach($info as $k=>$v){
			$lunbo[$k]['img'] = PC_URL.'/Upload/ad/'.$v['img'];
		}
		$this->ajaxReturn(array('code'=>200,'gonggao'=>$gonggao,'lunbo'=>$lunbo));
	}
	
	
	public function index()
	{
		//echo 'ok';exit;
		
		$indexAdver = (APP_DEBUG ? null : S('index_indexAdver'));
		
		if (!$indexAdver) {
			$indexAdver = M('Adver')->where(array('status' => 1))->order('id asc')->select();
			S('index_indexAdver', $indexAdver);
		}

		//$this->assign('indexAdver', $indexAdver);
		
		
		switch(C('index_html')){
			case "a":
				//如果a模版
				
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
						$indexArticle[$k] = M('Article')->where(array('type' => $v['name'], 'status' => 1, 'index' => 1))->order('id desc')->limit(6)->select();
                        $indexArticles[$k] = M('Article')->where(array('type' => $v['name'], 'status' => 1, 'index' => 1))->order('id desc')->limit(3)->select();
					}

					S('index_indexArticle', $indexArticle);
                    S('index_indexArticles', $indexArticles);
				}
		}
		//$this->assign('indexArticleType', $indexArticleType);
		//$this->assign('indexArticle', $indexArticle);
		//$this->assign('indexArticles', $indexArticles[0]);

		
		
		
		
		$indexLink = (APP_DEBUG ? null : S('index_indexLink'));

		if (!$indexLink) {
			$indexLink = M('Link')->where(array('status' => 1))->order('sort asc ,id desc')->select();
		}
		
		
		$zhisucom_getCoreConfig = zhisucom_getCoreConfig();
		if(!$zhisucom_getCoreConfig){
			$this->error('核心配置有误');
		}
		
		//$this->assign('zhisucom_jiaoyiqu', $zhisucom_getCoreConfig['zhisucom_indexcat']);
		
		//$this->assign('indexLink', $indexLink);

        $ajaxMenu = new AjaxController();
        $indexMenu = $ajaxMenu->getJsonMenu('');
        //$this->assign('indexMenu', $indexMenu);

		
		$coin=C('MARKET');
		$new_coin = [];
		foreach($coin as $ks=>$vs){
			$b1=strtoupper($vs['xnb']);
			$b2=strtoupper($vs['rmb']);
			$coin[$ks]['s1']="<b>$b1</b><font color='#bbb'>/ $b2</font>";
// 			if($vs['change']>0){
            if($b2 != "CNY"){
                $new_coin[]= $coin["$ks"];
            }
				
// 			}
		}
			
		
        foreach ($coin as $k=>$v){
            if ($v['jiaoyiqu']==0){
                $list['JEFF'][]=$v;
            }
            if ($v['jiaoyiqu']==1){
                $list['USDT'][]=$v;
            }
            if ($v['jiaoyiqu']==2){
                $list['BTC'][]=$v;
            }
            if ($v['jiaoyiqu']==3){
                $list['ETH'][]=$v;
            }

        }
		// echo 111;die;
		// dump($new_coin);die;
		//$this->assign('list', $list);
	
        // foreach ($usdtjy as $k=>$v){
			
            // $name=explode('_',$v['name'])[0];
            // $coin=$c->field('img')->where(array('name'=>$name))->find();
            // $usdtjy[$k]['img']=$coin['img'];
            // $usdtjy[$k]['bm']=strtoupper(strtr($v['name'],'_','/'));
        // }

        // foreach ($bbjy as $k=>$v){
			
            // $name=explode('_',$v['name'])[0];
            // $coin=$c->field('img')->where(array('name'=>$name))->find();
            // $bbjy[$k]['img']=$coin['img'];
            // $bbjy[$k]['bm']=strtoupper(strtr($v['name'],'_','/'));
        // }

        /* $this->assign('rmbjy', $list['BDB']);
        $this->assign('usdtjy', $list['USDT']);
        $this->assign('bbjy', $list['BTC']); */

		
	//	$Ajaxma = new \Home\Controller\AjaxmarketController();
	//	$bigArr = $Ajaxma->index();
		$usdt=M("usdt")->select();
		$btc=M('btc')->select();	
		$this->assign('usdt',$usdt);
		$this->assign('btc',$btc);
		/*$this->ajaxReturn(array(
		    'code'    =>  200,
		    'result'  =>  '获取成功',
		    // 'body'    =>  array('zf'=>$coin,'hq'=>$list)
		    'zf'=>$coin,
		    'hq'=>$list
		));*/
		// $body = array('zf'=>$coin,'hq'=>$list);
		$body['zf'] = $new_coin;
		$body['hq'] = $list;
		$this->success('获取成功',$body);

		//$this->assign('','','end');
		if (C('index_html')) {
			$this->display('Index/' . C('index_html') . '/index');
		}
		else {
			$this->display();
		}
	}
	
	public function hangqing()
	{
		$market = M('Market')->where(array('status'=>1))->select();
		foreach($market as $k=>$v)
		{
			$xnb = explode('_', $v['name'])[0];
            $rmb = explode('_', $v['name'])[1];
			$markets = $xnb."_cny";
			
			$new_price = M('Market')->where(array('name'=>$markets,'status'=>1))->getField('new_price');
			
			
			if($rmb != 'cny')
			{
				$img = M('Coin')->where(array('name'=>$xnb))->field('img,id')->find();
				if($rmb == 'jeff'){
					$list['JEFF'][$k] = $v;
					$list['JEFF'][$k]['coin_name'] = strtoupper($xnb);
					$list['JEFF'][$k]['img'] = PC_URL."/Upload/coin/".$img['img'];
					$list['JEFF'][$k]['rmb_price'] = $new_price;
					$list['JEFF'][$k]['id'] = $img['id'];
					//$list['JEFF']['coin_name'] = 'JEFF';
				}
				if($rmb == 'btc'){
					$list['BTC'][$k] = $v;
					$list['BTC'][$k]['coin_name'] = strtoupper($xnb);
					//$list['BTC']['coin_name'] = 'BTC';
					$list['BTC'][$k]['img'] = PC_URL."/Upload/coin/".$img['img'];
					$list['BTC'][$k]['rmb_price'] = $new_price;
					$list['BTC'][$k]['id'] = $img['id'];
				}
				if($rmb == 'usdt'){
					$list['USDT'][$k] = $v;
					$list['USDT'][$k]['coin_name'] = strtoupper($xnb);
					$list['USDT'][$k]['img'] = PC_URL."/Upload/coin/".$img['img'];
					$list['USDT'][$k]['rmb_price'] = $new_price;
					$list['USDT'][$k]['id'] = $img['id'];
					//$list['USDT']['coin_name'] = 'USDT';
				}
				if($rmb == 'eth'){
					$list['ETH'][$k] = $v;
					$list['ETH'][$k]['coin_name'] = strtoupper($xnb);
					$list['ETH'][$k]['img'] = PC_URL."/Upload/coin/".$img['img'];
					$list['ETH'][$k]['rmb_price'] = $new_price;
					$list['ETH'][$k]['id'] = $img['id'];
					//$list['ETH']['coin_name'] = 'ETH';
				}
				
			}
			
		}
		$list['coin_name'] = array('0'=>array('name'=>'JEFF','id'=>0),'1'=>array('name'=>'BTC','id'=>1),'2'=>array('name'=>'USDT','id'=>2),'3'=>array('name'=>'ETH','id'=>3));
		$this->success($list);
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
      if($res['status'] == 'success')
      {
      	foreach($res['data'] as $k=>$v){
          if($v['tradeName'] == "amfcfbt")
          {
              $amfc = $v;
          }
        }
        return $amfc;
      }
    else{
    	return false;
    }
  }
	
	//行情 
	public function hangqing1()
	{

		$huobi = A("Home/Huobi");
		$name_en = ['usdt'=>'usdt','btc'=>'btc','eth'=>'eth'];
		foreach ($name_en as $k => $v) {
			$name_en_str = $v.'_qc';
			$price = getUrl("http://api.zb.cn/data/v1/ticker?market={$name_en_str}");
			$price = json_decode($price,1);
			$prices = $price['ticker']['last'] ? $price['ticker']['last'] : 1;
			$name = $k.'_cny';
			M('Market')->where(array('name'=>$name))->save(array('new_price'=>$prices));
		}

		$coin=C('MARKET');
		$new_coin = [];
		foreach($coin as $ks=>$vs){
			$b1=strtoupper($vs['xnb']);
			$b2=strtoupper($vs['rmb']);
			$coin[$ks]['s1']="<b>$b1</b><font color='#bbb'>/ $b2</font>";
// 			if($vs['change']>0){
            if($b2 != "CNY"){
                $new_coin[]= $coin["$ks"];
            }
				
// 			}
		}
			
		
        foreach ($coin as $k=>$v){
			// var_dump($v);die;
			if($v['status'] == 1)
			{
				if ($v['jiaoyiqu']==0){
                $list['JEFF'][]=$v;
				foreach($list['JEFF'] as $k1=>$v1)
				{
					$xnb = explode('_', $v1['name'])[0];
					$rmb = explode('_', $v1['name'])[1];
					$markets = $xnb."_cny";
                  
                   if($xnb == 'amfc')
                  {
                  	$rrs = $this->amfcfbt();
                    //dump($rrs);
                    if($rrs)
                    {
                    	 $v['new_price']=$rrs['last'];
                        $v['min_price']=$rrs['low'];
                        $v['max_price']=$rrs['high'];
                        $v['volume']=$rrs['vol24hour'];
                      	$v['change'] = $rrs['chg'];
                       M('market')->where(['name'=>$v['name']])->save(['new_price'=>$v['new_price'],'min_price'=>$v['min_price'],'max_price'=>$v['max_price'],'volume'=>$v['volume'],'change'=>$v['change']]);
                    }
                  }
					
					$new_price = M('Market')->where(array('name'=>$markets,'status'=>1))->getField('new_price');
					$list['JEFF'][$k1]['rmb_price'] = $new_price;
					
				}
				
            }
            if ($v['jiaoyiqu']==1){
                $list['USDT'][]=$v;
				foreach($list['USDT'] as $k1=>$v1)
				{
					$xnb = explode('_', $v1['name'])[0];
					$rmb = explode('_', $v1['name'])[1];
					$markets = $xnb."_cny";
					
					$new_price = M('Market')->where(array('name'=>$markets,'status'=>1))->getField('new_price');
					$list['USDT'][$k1]['rmb_price'] = $new_price;

					//$xnb_h=$xnb.'usdt';
                    //$hb_price=$huobi->hangqing($xnb_h);
                    // dump($hb_price);
                  //if($hb_price['status']!=='error')
                  //{
                 // M('market')->where(['name'=>$v['name']])->save(['new_price'=>$hb_price['tick']['close'],'min_price'=>$hb_price['tick']['low'],'max_price'=>$hb_price['tick']['high'],'volume'=>$hb_price['tick']['amount']]);
                 // }
                    
					
				}
				
            }
            if ($v['jiaoyiqu']==2){
               $list['BTC'][]=$v;
			   foreach($list['BTC'] as $k1=>$v1)
				{
					$xnb = explode('_', $v1['name'])[0];
					$rmb = explode('_', $v1['name'])[1];
					$markets = $xnb."_cny";
					
					$new_price = M('Market')->where(array('name'=>$markets,'status'=>1))->getField('new_price');
					$list['BTC'][$k1]['rmb_price'] = $new_price;
					/*$xnb_h=$xnb.'btc';
                    $hb_price=$huobi->hangqing($xnb_h);
                  if($hb_price['status']!=='error')
                  {
                  M('market')->where(['name'=>$v['name']])->save(['new_price'=>$hb_price['tick']['close'],'min_price'=>$hb_price['tick']['low'],'max_price'=>$hb_price['tick']['high'],'volume'=>$hb_price['tick']['amount']]);
                  }*/
                    
				}
			 
            }
            if ($v['jiaoyiqu']==3){
               $list['ETH'][]=$v;
			   foreach($list['ETH'] as $k1=>$v1)
				{
					$xnb = explode('_', $v1['name'])[0];
					$rmb = explode('_', $v1['name'])[1];
					$markets = $xnb."_cny";
					
					$new_price = M('Market')->where(array('name'=>$markets,'status'=>1))->getField('new_price');
					$list['ETH'][$k1]['rmb_price'] = $new_price;
					/*$xnb_h=$xnb.'eth';
                    $hb_price=$huobi->hangqing($xnb_h);
                  if($hb_price['status']!=='error')
                  {
                   M('market')->where(['name'=>$v['name']])->save(['new_price'=>$hb_price['tick']['close'],'min_price'=>$hb_price['tick']['low'],'max_price'=>$hb_price['tick']['high'],'volume'=>$hb_price['tick']['amount']]);
                  }*/
                   
				}
			 
            }
			}
            
        }
		
		$list['coin_name'] = array('0'=>array('name'=>'JEFF','id'=>0),'1'=>array('name'=>'BTC','id'=>1),'2'=>array('name'=>'USDT','id'=>2),'3'=>array('name'=>'ETH','id'=>3));
		$this->success('获取成功',$list);
		
	}

	//行情 
	public function hangqing2()
	{
		$coin=C('MARKET');
        foreach ($coin as $k=>$v){
			// var_dump($v);die;
			if($v['status'] == 1)
			{
				if ($v['jiaoyiqu']==0){
                	$list['JEFF'][]=$v;
	            }
	            if ($v['jiaoyiqu']==1){
	                $list['USDT'][]=$v;
					
	            }
	            if ($v['jiaoyiqu']==2){
	               $list['BTC'][]=$v;
	            }
	            if ($v['jiaoyiqu']==3){
	               $list['ETH'][]=$v;
	            }
			}
            
        }
		
		$list['coin_name'] = array('0'=>array('name'=>'JEFF','id'=>0),'1'=>array('name'=>'BTC','id'=>1),'2'=>array('name'=>'USDT','id'=>2),'3'=>array('name'=>'ETH','id'=>3));
		$this->success('获取成功',$list);
		
	}
	
	//APP币种介绍
	public function appcoin_js(){
	    $coin = I('post.coin');
	    $coininfo = M('coin')->where(array('name'=>$coin))->find();
	    if($coininfo){
	        $this->success('获取成功',$coininfo);
	    }else{
	        $this->success('获取失败');
	    }
	}
	
	public function jsondate(){
			
		header('Access-Control-Allow-Origin:*');
		header('Access-Control-Allow-Method:POST,GET');
		
			$bigarr = [];
			$usdt=M("usdt")->select();
			$btc=M('btc')->select();			
			$bigarr['usdt'] = $usdt;
			$bigarr['btc'] = $btc;
			$this->ajaxReturn($bigarr);  
			
	}
	
	public function test(){
		//bigArr
		$Ajaxma = new \Home\Controller\AjaxmarketController();
		$Ajaxma->index();
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
        //$this->assign('', '', 'end');
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
	
	
	//首页公告详情
	public function index_detail(){
	    $id = I('post.id');
		//$id = 184;
	    $article = M('Article');
	    $info = $article->where(array('id'=>$id,'status'=>1))->field('title,content,addtime')->find();
	    $this->ajaxReturn(array('code'=>200,'info'=>$info));
	}
	
	
	public function article_list()
	{
		$article = M('Article');
		$gonggao = M('article')->where('status=1')->field('title,title_en,title_e,id,addtime')->order('id desc')->select();
		foreach($gonggao as $k=>$v)
		{
			$gonggao[$k]['addtime'] = date("Y-m-d H:i:s",$v['addtime']);
		}
		$this->ajaxReturn(array('code'=>200,'info'=>$gonggao));
	}




	//东洲AMFC转入
	public function amfc_zr()
	{
		$coin = I('post.coin_en');
		$addr = I('post.addr');
		$sign = I('post.sign');
		$nums = I('post.num');
		$num = number_format($nums,8,'.','');
		$config = M('config')->where(array('id'=>1))->find();

		if(abs($num) <= 0)
		{
			$this->error('数量错误');
		}

		if($num<$config['zr_min'])
		{
			$this->error('最小转入数量是'.$config['zr_min']);
		}

		$val = ['coin_en'=>$coin,'addr'=>$addr,'num'=>$num];
		ksort($val);
		$_val = json_encode($val);
		$_sign = sha1(md5($_val));

		if($_sign != $sign)
		{
			$this->error('签名错误');
		}

		$coins = M('Coin')->where(array('name'=>$coin,'status'=>1))->getField('id');
		if(!$coins)
		{
			$this->error('币种错误！');
		}
		$coinb = $coin.'b';
		$user_coin = M('User_coin')->where(array($coinb=>$addr))->find();
		if(!$user_coin)
		{
			$this->error('地址错误');
		}

		$res = M('User_coin')->where(array($coin.'b'=>$addr))->setInc($coin,$num);
		if($res)
		{
			$txid = md5($addr).md5($addr.$coin);
			$result = M('Myzr')->add(array('userid'=>$user_coin['userid'],'username'=>$addr,'coinname'=>$coin,'txid'=>$txid,'num'=>$num,'fee'=>0,'mum'=>$num,'addtime'=>time(),'status'=>1));
			if($result)
			{
				// $this->success('转账成功！');
				$this->ajaxReturn(array('code'=>200,'result'=>'转账成功','txid'=>$txid));
			}
			else
			{
				$this->error('转账失败1');
			}
		}
		else
			{
				$this->error('转账失败2');
			}
	}

	//C2C最新单价用
	public function cny_jiage()
	{
		$coin = !empty(I('post.coin')) ? I('post.coin') : 'eth';
		$market = $coin.'_cny';
		$cny_price = M('Market')->where(array('name'=>$market))->getField('new_price');
		$cny_price = $cny_price>0 ? $cny_price : 0;
		$this->success('OK',$cny_price);
	}

}

?>