<?php
namespace Home\Controller;

class HomeController extends \Think\Controller
{
	protected function _initialize()
	{
		$web_log = M('config')->getField('web_logo');

		$this->assign('web_logo',$web_log);

		if($_SESSION['lang']==1) {include "./lang/zh-cn.php";}
		elseif($_SESSION['lang']==2) {include "./lang/e-yu.php";}
		elseif($_SESSION['lang']==3) {include "./lang/zh-en.php";}
		else {$_SESSION['lang']=1;include "./lang/zh-cn.php";}

	    $this->assign('yuyan',$_SESSION['lang']);
		defined('APP_DEMO') || define('APP_DEMO', 0);

		if (!session('userId')) {
			session('userId', 0);
		} else if (CONTROLLER_NAME != 'Login') {
			$user = D('user')->where('id = ' . session('userId'))->find();
			//没有绑手机号去绑手机号
			/*if (!$user['moble']) {
			    redirect('/Login/identity');
			}
			
			if (!$user['paypassword']) {
				redirect('/Login/paypassword');
			}

			if (!$user['truename']) {
				redirect('/Login/truename');
			}*/
			if($user['token']!=session('token_user')){
				//登录
				session(null);
				session('zhisucom_already',1);
				redirect('/');
			}
		}
		$uid = userid();
		$c2c_user 			= M('User')->where(array('id'=>$uid))->field('zhifubao,weixin,paypassword')->find();
		$c2c_user['bank'] 	= M('user_bank')->where(array('userid'=>$uid))->order('addtime desc')->getField('bankcard');
		$this->assign('c2c_user',$c2c_user);
		$this->assign('uid',$uid);
		$coninfo 			= M('Config')->where('id=1')->find();
		$this->assign('coninfo',$coninfo);
		$keywords 			= $coninfo['web_keywords'];
		$this->assign('web_keywords',$keywords);
		
		//手机端开发完成再打开
		/* if(WAP_URL !=""){
			$ua = @$_SERVER['HTTP_USER_AGENT'];

			if(preg_match('/(iphone|android|Windows\sPhone)/i', $ua)){
				$zhisucom_redirect="";
				if (isset($_GET['invit'])) {
					$invit = $_GET['invit'];
					$user = M('User')->where(array('invit' => $invit))->find();

					if ($user['id']) {
						$zhisucom_redirect = WAP_URL."/Login/register/invit/".$user['id'];
					}else{
						$zhisucom_redirect = WAP_URL;
					}
				}else{
					$zhisucom_redirect = WAP_URL;
				}
				//echo WAP_URL;EXIT;
				header("Location:".$zhisucom_redirect);
				die();
			} 
		}*/
		//20170511 zhisucom 增加获取币类型函数
		if ($uid) {
			$userCoin_top 			= M('UserCoin')->where(array('userid' => $uid))->find();
			$userCoin_top['cny'] 	= round($userCoin_top['cny'], 2);
			$userCoin_top['cnyd'] 	= round($userCoin_top['cnyd'], 2);
			$userCoin_top['allcny'] = round($userCoin_top['bb']+$userCoin_top['bbd'],2);
			$this->assign('userCoin_top', $userCoin_top);
		}
		if (isset($_GET['invit'])) session('invit', $_GET['invit']);
		$config = (APP_DEBUG ? null : S('home_config'));
		if (!$config) {
			$config = M('Config')->where(array('id' => 1))->find();
			S('home_config', $config);
		}
		if ($_GET['zhisucom'] == 'debug') session('web_close', 1);
		if (!session('web_close') && !$config['web_close']) exit($config['web_close_cause']);
		C($config);
		C('contact_qq', explode('|', C('contact_qq')));
		C('contact_qqun', explode('|', C('contact_qqun')));
		C('contact_bank', explode('|', C('contact_bank')));
		$coin = (APP_DEBUG ? null : S('home_coin'));
		if (!$coin) {
			$coin = M('Coin')->where(array('status' => 1))->select();
			S('home_coin', $coin);
		}
		$coinList = array();
		foreach ($coin as $k => $v) {
			$coinList['coin'][$v['name']] = $v;
			if (in_array($v['name'], ['cny','rmb','rgb','qbb'])) 
				$coinList[$v['name'].'_list'][$v['name']] = $v;
			else 
				$coinList['xnb_list'][$v['name']] = $v;
		}
		C($coinList);
		$market 		= (APP_DEBUG ? null : S('home_market'));
		$market_type 	= array();
		$coin_on 		= array();
		if (!$market) {
			$market = M('Market')->where(array('status' => 1))->order('jiaoyiqu,sorts')->select();
			S('home_market', $market);
		}
		foreach ($market as $k => $v) {
			$v['new_price'] 	= round($v['new_price'], $v['round']);
			$v['buy_price'] 	= round($v['buy_price'], $v['round']);
			$v['sell_price'] 	= round($v['sell_price'], $v['round']);
			$v['min_price'] 	= round($v['min_price'], $v['round']);
			$v['max_price'] 	= round($v['max_price'], $v['round']);
			if ($v['name'] == null) dumpS($v);
			$v['xnb'] 			= explode('_', $v['name'])[0];
			$v['rmb'] 			= explode('_', $v['name'])[1];
			$v['xnbimg'] 		= C('coin')[$v['xnb']]['img'];
			$v['rmbimg'] 		= C('coin')[$v['rmb']]['img'];
			$v['volume'] 		= $v['volume'] * 1;
			$v['change'] 		= $v['change'] * 1;
			$v['title'] 		= C('coin')[$v['xnb']]['title'] . '(' . strtoupper($v['xnb']) . '/' . strtoupper($v['rmb']) . ')';
			$v['navtitle'] 		= C('coin')[$v['xnb']]['title'] . '(' . strtoupper($v['xnb']). ')';
			
			if($v['begintrade']) $v['begintrade']= $v['begintrade']; else $v['begintrade'] = "00:00:00"; 
			if($v['endtrade']) $v['endtrade']    = $v['endtrade']; else $v['endtrade']    = "23:59:59";
			$market_type[$v['xnb']] 			 = $v['name'];
			$coin_on[]							 = $v['xnb'];
			$marketList['market'][$v['name']] 	 = $v;
		}

		C('market_type',$market_type);
		C('coin_on',$coin_on);
		C($marketList);
		$C = C();
		foreach ($C as $k => $v)  $C[strtolower($k)] = $v;
		$this->assign('C', $C);
		$this->kefu = './Application/Home/View/Kefu/' . $C['kefu'] . '/index.html';
		if (!S('daohang_aa')) {
			$tables 	= M()->query('show tables');
			$tableMap 	= array();
			foreach ($tables as $table)  $tableMap[reset($table)] = 1;
			if (!isset($tableMap['zhisucom_daohang'])) {
				M()->execute("\r\n" . '                    CREATE TABLE `zhisucom_daohang` (' . "\r\n" . '                        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'自增id\',' . "\r\n" . '                        `name` VARCHAR(255) NOT NULL COMMENT \'名称\',' . "\r\n" . '                         `title` VARCHAR(255) NOT NULL COMMENT \'名称\',' . "\r\n" . '                        `url` VARCHAR(255) NOT NULL COMMENT \'url\',' . "\r\n" . '                        `sort` INT(11) UNSIGNED NOT NULL COMMENT \'排序\',' . "\r\n" . '                        `addtime` INT(11) UNSIGNED NOT NULL COMMENT \'添加时间\',' . "\r\n" . '                        `endtime` INT(11) UNSIGNED NOT NULL COMMENT \'编辑时间\',' . "\r\n" . '                        `status` TINYINT(4)  NOT NULL COMMENT \'状态\',' . "\r\n" . '                        PRIMARY KEY (`id`)' . "\r\n\r\n" . '                  )' . "\r\n" . 'COLLATE=\'gbk_chinese_ci\'' . "\r\n" . 'ENGINE=MyISAM' . "\r\n" . 'AUTO_INCREMENT=1' . "\r\n" . ';' . "\r\n\r\n\r\n\r\n" . 'INSERT INTO `zhisucom_daohang` (`name`,`title`, `url`, `sort`, `status`) VALUES (\'finance\',\'财务中心\', \'Finance/index\', 1, 1);' . "\r\n" . 'INSERT INTO `zhisucom_daohang` (`name`,`title`, `url`, `sort`, `status`) VALUES (\'user\',\'安全中心\', \'User/index\', 2, 1);' . "\r\n" . 'INSERT INTO `zhisucom_daohang` (`name`, `title`,`url`, `sort`, `status`) VALUES (\'game\',\'应用中心\', \'Game/index\', 3, 1);' . "\r\n" . 'INSERT INTO `zhisucom_daohang` (`name`, `title`,`url`, `sort`, `status`) VALUES (\'article\',\'帮助中心\', \'Article/index\', 4, 1);' . "\r\n\r\n\r\n" . '                ');
			}

			S('daohang_aa', 1);
		}

		if (!S('daohang')) {
			$this->daohang = M('Daohang')->where(array('status' => 1))->order('sort asc')->select();
			S('daohang', $this->daohang);
		} else {
			$this->daohang = S('daohang');
		}
		$footerArticleType = (APP_DEBUG ? null : S('footer_indexArticleType'));
		if (!$footerArticleType) {
			$footerArticleType = M('ArticleType')->where(array('status' => 1, 'footer' => 1, 'shang' => ''))->order('sort asc ,id desc')->limit(3)->select();
			S('footer_indexArticleType', $footerArticleType);
		}

		$this->assign('footerArticleType', $footerArticleType);
		$footerArticle = (APP_DEBUG ? null : S('footer_indexArticle'));

		if (!$footerArticle) {
			foreach ($footerArticleType as $k => $v) {
				$footerArticle[$v['name']] = M('ArticleType')->where(array('shang' => $v['name'], 'footer' => 1, 'status' => 1))->order('id asc')->limit(4)->select();
			}
			S('footer_indexArticle', $footerArticle);
		}
		$this->assign('footerArticle', $footerArticle);
		$xinwen = M('Article')->where(array('status'=>1,'type'=>'info','footer'=>1))->select();
		$about 	= M('Article')->where(array('status'=>1,'type'=>'aboutus','footer'=>1))->select();
		$this->assign('xinwen',$xinwen);
		$this->assign('about',$about);
        $mk 	= M('market');
        $lst 	= $mk->where("status=1")->field('name,new_price,change')->select();
        foreach ($lst as $k=>$v){
            $lst[$k]['name']=strtoupper(str_replace('_', '/', $v['name']));
            $lst[$k]['new_price']=round($v['new_price'],2);
        }
        $this->assign('hqlist', $lst);
		$coin 		= C('MARKET');
		$coinKeys 	= ['BDB','USDT','BTC','ETH']; 

        foreach ($coin as $k=>$v) $list[$coinKeys[$v['jiaoyiqu']]] = $v;
        $this->assign('coinlist', $list);
      	$usermylog['myzr'] = M('myzr')->where(['userid'=>$uid])->order('id desc')->select();
      	$usermylog['myzc'] = M('myzc')->where(['userid'=>$uid])->order('id desc')->select();
      	$usermylog['mytx'] = M('mytx')->where(['userid'=>$uid])->order('id desc')->select();
      	$usermylog['mycz'] = M('mycz')->where(['userid'=>$uid])->order('id desc')->select();

      	S('usermylog', $usermylog);
      	$this->assign('usermylog', $usermylog);
	  	$empty=" <table class='sf-grid table-inacc table-inacc-body'>
                        <tbody>
                        <tr class='table-empty' style='display: table-row;'>
                            <td style='text-align: center'><p><i>i</i>暂无成交记录</p></td>
                        </tr>
                        </tbody>
                    </table>";
		$this->assign('empty', $empty);	

	    //登陆状态查询用户VIP级别
    	if ($uid) {
    	    $arr_user_vip = get_vip_fee($uid);
    	    $this->assign('vip', $arr_user_vip[0]);
    	}
	}

    public function _empty() {
        send_http_status(404);
        $this->error();
        echo '模块不存在！';
        die();

    }
    
	//记录
	public function addCashhistory($id,$coin,$type,$action,$num,$price,$wallet,$detail="")
	{
        $chistory=M('chistory');
        $data=array();
        $nowtime=time();
		$data['uid']=$id;
        $data['coin']=$coin;
        $data['time']=$nowtime;
    //  $data['content']=$content;
        $data['wallet']=$wallet;     
        $data['type']=$type;  
		$data['num']=$num; 
		$data['price']=$price;	
		$data['action_type']=$action;		
		$data['mod']=$mod;
		$data['detail']=$detail;	
        $chistory->add($data);
		
    }
    
    //删除测试用户
    public function del_test_user(){
        $where['moble'] = array('in','18611752599,18611305398,13801215876,15600359373,18513830707');
        $user = M('user')->where($where)->field('id')->select();
        echo '<pre>';
        print_r($user);exit;
        if($user){
            foreach ($user as $v){
                if($v['id']){
                    M('user_coin')->where(array('userid'=>$v['id']))->delete();
                    M('user_log')->where(array('userid'=>$v['id']))->delete();
                    M('user_bank')->where(array('userid'=>$v['id']))->delete();
                    M('trade')->where(array('userid'=>$v['id']))->delete();
                    
                    M('trade_log')->where("userid=".$v['id']." OR peerid=".$v['id'])->delete();
                    M('myzr')->where(array('userid'=>$v['id']))->delete();
                    M('myzc')->where(array('userid'=>$v['id']))->delete();
                    M('mytx')->where(array('userid'=>$v['id']))->delete();
                    M('mycz')->where(array('userid'=>$v['id']))->delete();
                    M('finance')->where(array('userid'=>$v['id']))->delete();
                    
                    M('chistory')->where(array('uid'=>$v['id']))->delete();
                    /* M('finance')->where(array('userid'=>$v['id']))->delete();
                    M('finance')->where(array('userid'=>$v['id']))->delete();
                    M('finance')->where(array('userid'=>$v['id']))->delete(); */
                    M('user')->where(array('id'=>$v['id']))->delete();
                }
            }
        }
        echo 'ok';
    }
	
	
	//机器人任务执行
    public function automan_start(){
        $list = M('automan')->where("isend=2")->select();
        if($list){
            foreach($list as $v){
                if($v['yu_num']>0 && $v['isend']==2){
                    //未刷完
                    if($v['iszhang']==1){
                        $js_price = $this->randomfloat_auto($v['min_price'],$v['max_price']);
                    }else{
                        $js_price = $this->randomfloat_auto(-$v['min_price'],-$v['max_price']);
                    }
        
                    $price = $v['new_price'] + $js_price;//rand(-3.999,3.999);
                    $num = $this->randomfloat_auto($v['min_num'],$v['max_num']);//rand(0.001,500);
                    $type = rand(1,2);
                    
                    //error_log('$i='.$i.'&&$market='.$market.'&&$price='.$price.'&&$num='.$num.'&&$type='.$type."\r\n",3,'./cc.txt');
                    //直接插入trade_log表
                    $adddata['userid'] = 1;
                    $adddata['peerid'] = 1;
                    $adddata['market'] = $v['market'];
                    $adddata['price'] = $price;
                    $adddata['num'] = $num;
                    $adddata['mum'] = $price*$num;
                    $adddata['type'] = $type;
                    $adddata['addtime'] = time();
                    $adddata['status'] = 1;
                    $rell = M('trade_log')->add($adddata);
                    if($rell){
                        M('automan')->where('id='.$v['id'])->setDec('yu_num');
						M('market')->where(array('name'=>$v['market'],'status'=>1))->save(array('new_price'=>$price));
                        if($v['yu_num']==1){
                            M('automan')->where('id='.$v['id'])->setField('isend',1); 
                        }
                    }
                    echo $v['market'].'--'.$rell;
                }else{
                    //已刷完
                    if($v['isend']!=1){
                       M('automan')->where('id='.$v['id'])->setField('isend',1); 
                    }
                }
            }
            
            $sum = count($rel);
            error_log('$sum='.$sum."\r\n",3,'./cc.txt');
        }
    }
	
	
	//生成小数
	public function randomfloat_auto($min,$max){
		$isadd = rand(1,2);
		if($isadd==1){
			$random_num = round($min+mt_rand()/mt_getrandmax()*($max-$min),6)+$this->xsFloat();
		}else{
			$random_num = round($min+mt_rand()/mt_getrandmax()*($max-$min),6)-$this->xsFloat();
		}
		return $random_num;
	}
	
	
	/**
	 * 生成0~1随机小数
	 * @param Int  $min
	 * @param Int  $max
	 * @return Float
	 */
	public function xsFloat($min=0, $max=1){
		//$endfix = rand(1,6);
		$xs = round(($min + mt_rand()/mt_getrandmax() * ($max-$min)),6);
		return $xs;
	}
    
    
	
}

?>