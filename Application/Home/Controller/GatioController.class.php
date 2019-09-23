<?php
namespace Home\Controller;
use \Org\Util\gtio\Gate;
use \Think\Controller;
class GatioController extends Controller{
    public $Gatio;
    public function __construct(){
    
        if(!$this->Gatio){
            $this->Gatio = new Gate;
        }
    }
    //市场深度
	
	public function shendu($bi_type){
		
		return	$this->Gatio->get_orderbook($bi_type);
		
	}
	
	//市场行情
	
	public function hangqing($bi_type){
		
		return	$this->Gatio->get_ticker($bi_type);
		
	}
	/*下单买入
	*$currency_pair 交易对
	*$rate			价格
	*$amount		交易量
	*/
	public function buy($currency_pair, $rate, $amount){
		
		return $this->Gatio->buy($currency_pair, $rate, $amount);
	}
	//下单卖出
	public function sell($currency_pair, $rate, $amount){
		
		return $this->Gatio->sell($currency_pair, $rate, $amount);
	}	
	/*查询订单
	*$order_number  订单ID
	$currency_pair  交易对
	*/
	public function xiangqing($order_number, $currency_pair){
		
		return $this->Gatio->get_order($order_number, $currency_pair);	
	}
	
	//账号资金
	public function zichan(){
				
		return $this->Gatio->get_balances();
	//	return $this->Gatio->get_tickers();
	}
	
	//当前挂单列表
	public function listorder($bi_type=""){
			
		return $this->Gatio->open_orders($bi_type="");
	}
	//撤销单个挂单
	
	public function chexiao($num,$bi_type){
				
		return $this->Gatio->cancel_order($num,$bi_type);
	}
    
}