<?php
namespace Home\Controller;

class TestController extends TradeController
{

	/*
	交易对、当前价、执行次数、1是涨，2是跌、浮动最低价、浮动最高价、挂单最低数量、挂单最高数量

	*/
    public function auto_trade($market='',$new_price='',$total_num,$iszhang=1,$min_price=1,$max_price=10,$min_num=0.001,$max_num=10){
		
		set_time_limit(0);//0表示不限时
		
		//echo $market.'<br/>'.$new_price.'<br/>'.$total_num;exit;
		if($iszhang==1){
			$where_zhang['userid'] = 1;
			$where_zhang['status'] = ['in','0,2'];
			$where_zhang['price'] = array('lt',$new_price);
			M('trade')->where($where_zhang)->delete();
		}else{
			$where_die['userid'] = 1;
			$where_die['status'] = ['in','0,2'];
			$where_die['price'] = array('gt',$new_price);
			M('trade')->where($where_die)->delete();
		}
		for($i=1;$i<=$total_num;$i++){
			//echo $i.'<br/>';
			session_write_close();
			sleep(rand(20,60));//延迟执行
			session_write_close();
			if($iszhang==1){
				$js_price = $this->randomfloat($min_price,$max_price);
			}else{
				$js_price = $this->randomfloat(-$min_price,-$max_price);
			}
			
			$price = abs($new_price	+ $js_price);//rand(-3.999,3.999);
			$num = $this->randomfloat($min_num,$max_num);//rand(0.001,500);
			$type = rand(1,2);
			//error_log('i='.$i.'&&js_price='.$js_price.'&&price='.$price.'&&num='.abs($num).'&&type='.$type."\r\n",3,'./bboo.txt');
			$res = $this->upTrade($paypassword = NULL, $market, $price, abs($num), $type,$tradetype=1,'',$auto=1);
			if($type==1){
				$res = $this->upTrade($paypassword = NULL, $market, $price, abs($num), 2,$tradetype=1,'',$auto=1);
			}else{
				$res = $this->upTrade($paypassword = NULL, $market, $price, abs($num), 1,$tradetype=1,'',$auto=1);
			}
			
			
		}
		//echo 'success';
		$this->success('操作成功！');
	}
	
	//生成小数
	public function randomfloat($min,$max){
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

	/**
	 * 测试连接
	 */
	public function connet_eth()
	{
		$coin = M('Coin')->where(['name'=>'eth'])->find();
		$EthClient = EthCommon($coin['dj_zj'], $coin['dj_dk']);
        $result = $EthClient->web3_clientVersion();
        dump($result);
	}

	/**
	 * 测试加密
	 */
	public function index()
	{
		echo 1123;
	}
}