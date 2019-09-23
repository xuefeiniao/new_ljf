<?php
namespace Home\Controller;

use \Think\Controller;
use Org\Util\Binance\Binance;

class BianController extends Controller{
    
	public $Bian;
	
    public function __construct(){
    
        if(!$this->Bian){
            $this->Bian = new Binance;
        }
    }
		
	//市场价格
	public function hangqing(){
		
		return $this->Bian->prices();
		
	}
	// 系统时间
	public function systime(){
		
		return $this->Bian->useServerTime();
		
	}
	
	//市场深度
	public function shendu($bi_type){
				
		return $this->Bian->depth(strtoupper($bi_type));
	}
	//市场挂买单
	public function xiadanbuy($bi_type,$num,$price){

        $this->systime();
		return $this->Bian->buy(strtoupper($bi_type),$num,$price);
		
	}
	
	//市场挂卖单
	public function xiadansell($bi_type,$num,$price){
        $this->systime();
		return $this->Bian->sell(strtoupper($bi_type),$num,$price);
		
	}
	
	//获取一个订单状态
	public function xiangqing($bi_type, $orderid){
        $this->systime();
		return $this->Bian->orderStatus(strtoupper($bi_type), $orderid);
		
	}
	//获取资产
	public function zichan(){
		
		$ticker =$this->hangqing();
        $this->systime();
		return $this->Bian->balances($ticker);
		
	}
	
	
	
	
	
}	
