<?php
namespace Home\Controller;
use Home\Controller\HomeController;
class BeggController extends HomeController{
    public $Beggapi;
    //public $api_key;
    //public $api_secret;
    public function __construct(){
        if(!$this->Beggapi){
            //$this->api_key  = 'Q3li6wThDSpxRm9Pakdbk4BYIv5WHHSxIfo6QVvRi502X3A7WaBfvbmCaq2oyqTK'; //
            //$this->api_secret = 'ABlvmMR9uudAQhpjKa810kmMQO3I6tIMSSx50om8BR2SikKWG7C4pqnMLNtwcxq8';
            $this->Beggapi = new \Common\Ext\Coinegg();
        }
    }
    
    /**
     * 取10档交易记录历史成交【此回应的数据量会较大，所以请勿频繁调用。(过于频繁可能被自动封ip)】
     * @param string $coin 
     * @param string $region
     */
    public function bd_get_ten_trades($coin = 'eth', $region = 'btc'){
    
        /* $result = $this->Hbapi->get_history_trade($bi_type='btcusdt',$size='30');
         echo '<pre>';
         print_r($result);exit; */
    
        //判断在zb交易所是否有金额
        //if(!$this->hb_get_user_balance($bi_name='')){
            $return_str = $this->Beggapi->getOrders($coin = 'eth', $region = 'btc');
    
            $return_arr_buy = [];//买档
            $return_arr_ask = [];//买档
            $return = json_decode($return_str,true);//json格式转数组
            if($return[0]['type']){
                foreach ($return as $v){
                    if(count($return_arr_buy)==10 && count($return_arr_ask)==10){
                        break;
                    }else{
                        $v['extenchange_type'] = 'BD';
                        if($v['type']=='buy'){
                            //买
                            array_push($return_arr_buy, $v);
                        
                        }elseif($v['type']=='sell'){
                            //卖
                            array_push($return_arr_ask, $v);
                        }
                    }
                }
                //按买价格最低，卖价格最高排序
                array_multisort(array_column($return_arr_buy,'price'),SORT_ASC,$return_arr_buy);
                array_multisort(array_column($return_arr_ask,'price'),SORT_DESC,$return_arr_ask);
    
    
                //取前十档
                $arr_buy = [];
                $arr_ask = [];
                foreach ($return_arr_buy as $k=>&$v){
                    if($k<10){
                        array_push($arr_buy, $v);
                    }
                }
    
                foreach ($return_arr_ask as $j=>&$m){
                    if($j<10){
                        array_push($arr_ask, $m);
                    }
                }
    
                $big_arr = [];
                array_push($big_arr, $arr_buy);
                array_push($big_arr, $arr_ask);
                /* echo '<pre>';
                 print_r($big_arr);
                exit; */
                return $big_arr;
            }else{
                $big_arr = [];
            }
        /* }else{
            $big_arr = [];
        } */
        return $big_arr;
        //echo '<pre>';
        //print_r($result);exit;
    
    }
    
    
    //获取用户资产
    public function bd_get_user_balance($bi_name=''){
        $user_balance = $this->Beggapi->getBalance();
        $balance = 0;
        if($user_balance['status']=='ok' && $user_balance['data']['list']){
            $list = $user_balance['data']['list'];
            foreach ($list as $v){
                if($v['currency']==$bi_name){
                    $balance = $v['currency'];
                    break;
                }
            }
        }else{
            return false;
        }
        return $balance;
    }
    
    /**
     * 下单
     * @param string $coin  币种
     * @param string $type  买/卖(buy/sell)
     * @param string $amount    购买数量
     * @param string $price     购买单价
     * @return mixed|boolean
     */
    public function bd_order($coin='', $type='sell', $amount='100', $price='1.2'){
        $result = $this->Beggapi->setTrade($coin, $type, $amount, $price);
        if($result->result==true && $result->id){
            return $result->id;//返回挂单id
        }else{
            return false;
        }
    }
    
    /**
     * 取消某个委托买单
     * @param unknown $coin     币种
     * @param unknown $trade_id 订单id
     */
    public function bd_cancelOrder($coin, $trade_id){
        $return = $this->Beggapi->setTradeCancel($coin, $trade_id);
        if($return->result==true && $return->id){
            return true;//取消委托成功
        }else{
            return false;//取消委托失败
        }
    }
    
}