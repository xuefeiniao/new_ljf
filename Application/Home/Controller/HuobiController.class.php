<?php
namespace Home\Controller;

use \Think\Controller;
use Org\Util\huo\Huobi;

class HuobiController extends Controller{
    public $Huobi;
	
    public function __construct(){
    
        if(!$this->Huobi){
            $this->Huobi = new Huobi;
        }
    }
	
	//K线数据
	public function kxian($bi_type,$time,$size){
		
		//time:1min, 5min, 15min, 30min, 60min, 1day, 1mon, 1week, 1year
		//size:1-2000  默认150条
		return json_decode(json_encode($this->Huobi->get_history_kline($bi_type,$time,$size)), true);
	}	
		
	//市场深度
	public function shendu($bi_type){
		
		return json_decode(json_encode($this->Huobi->get_market_depth($bi_type,'step0')), true);
		
	}
	
	//市场行情
	public function hangqing($bi_type){
		
		return json_decode(json_encode($this->Huobi->get_market_detail($bi_type)), true);
	}
	
	//查询当前用户账号信息
	public function account(){
		
		return $this->Huobi->get_account_accounts();	
		
	}
	//查询当前用户账号余额
	public function zichan(){
	
		return json_decode(json_encode($this->Huobi->get_account_balance()), true);
	}
	//下单
	public function xiadan($amount,$price,$symbol,$type){
		
		return json_decode(json_encode($this->Huobi->place_order($amount,$price,$symbol,$type)), true);
	}
	//查询一个订单的详情 需要订单ID
	public function xiangqing($orderid){
		
		return json_decode(json_encode($this->Huobi->get_order($orderid)), true);
	}	
	
}	
