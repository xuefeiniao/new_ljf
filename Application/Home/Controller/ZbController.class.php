<?php
namespace Home\Controller;

use \Think\Controller;
use Org\Util\Zb\zbAPI;

class ZbController extends Controller{
    public $Zb;
    public function __construct(){
    
    
        if(!$this->Zb){
            $this->Zb = new zbAPI;
        }
    }
	//市场行情
	
    public function hangqing($coin){
		
		return $this->Zb->Hangqing($coin);
	}
	//市场深度
	
	public function shendu($bi_type){
	
		return $this->Zb->depty($bi_type);
		
	}
	//市场下单
	
	public function xiadan($Price,$Amount,$bi_type,$Tradetype){
		
		//tradeType交易类型1/0[buy/sell]		
		return $this->Zb->Trade($Price,$Amount,$bi_type,$Tradetype);
	}
	
	//查询单个订单状态
	public function xiangqing($OrderID,$bi_type){
		
		return $this->Zb->GetOrder($OrderID,$bi_type);
	}
	//用户信息资产
	
	public function zichan(){
		
		return $this->Zb->Fund();
	}
    
}