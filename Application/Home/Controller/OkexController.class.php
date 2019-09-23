<?php
namespace Home\Controller;

class OkexController extends HomeController{
    	
	public $Okex;
    public function __construct(){
    
        if(!$this->Okex){
			
			include_once './ThinkPHP/Library/Org/Util/okex/OKCoin.php';			
			$this->Okex= new \OKCoin(new \OKCoin_ApiKeyAuthentication());
        }
    }
	//市场行情
	public function hangqing($bi_type){
		
			$params = array('symbol' => $bi_type);
			
		return 	json_decode(json_encode($this->Okex->tickerApi($params)), true);	
	}
	//市场深度
	public function shendu($bi_type){
			//size返回条数,默认200
			$params = array('symbol' => $bi_type, 'size' => 10);
		
		return 	json_decode(json_encode($this->Okex->depthApi($params)), true);
	}
	
	//委托下单
    public function xiadan($num,$price,$bi_type,$type){
		
		if($type==1){
			$type="buy";
		}else{
			$type="sell";
		}	
		return 	json_decode(json_encode($this->Okex->tradeApi($num,$price,$bi_type,$type)), true);
	}
	//查询订单信息
	
	public function xiangqing($bi_type,$orderid){
				
		return json_decode(json_encode($this->Okex->orderInfoApi($bi_type,$orderid)), true);
	}
	
	//用户信息
	public function zichan(){
		
		return json_decode(json_encode($this->Okex->userinfoApi()), true);

	}
	
}