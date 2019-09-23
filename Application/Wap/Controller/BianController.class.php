<?php
namespace Home\Controller;
class BianController extends HomeController{
    public $Bnapi;
    public $api_key;
    public $api_secret;
    public function __construct(){
        if(!$this->Bnapi){
            $this->api_key  = 'Q3li6wThDSpxRm9Pakdbk4BYIv5WHHSxIfo6QVvRi502X3A7WaBfvbmCaq2oyqTK'; //
            $this->api_secret = 'ABlvmMR9uudAQhpjKa810kmMQO3I6tIMSSx50om8BR2SikKWG7C4pqnMLNtwcxq8';
            $this->Bnapi = new \Common\Ext\BaAPI($this->api_key, $this->api_secret);
        }
    }
    
    //取10档交易记录历史成交
    public function ba_get_ten_trades($symbol='btc_usd'){
        //判断在zb交易所是否有金额
        if(!$this->ba_get_user_balance($bi_name='')){
    
            $params = array('symbol' => $symbol);
            $result = $this->Bnapi -> tradesApi($params);
    
            if($result[0]){
                $return_arr_buy = [];//买档
                $return_arr_ask = [];//买档
    
    
    
                foreach ($result as $v){
                    $arr = [];
                    //$arr = json_decode($v,true);
                    $arr = (array)$v;
    
                    $arr['extenchange_type'] = 'OKE';
                    if($arr['type']=='buy'){
                        //买
                        array_push($return_arr_buy, $arr);
    
                    }elseif($arr['type']=='sell'){
                        //卖
                        array_push($return_arr_ask, $arr);
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
    
    
                /*echo '<pre>';
                 print_r($big_arr);
                exit;*/
                return $big_arr;
            }else{
                return [];
            }
        }else{
            return [];
        }
        print_r($result);
    }
    
    //获取用户信息
    /**
     *
     * @param string $app_key   用户app_key
     */
    public function ba_get_user_balance($bi_name='btc'){
        /* echo '<pre>';
         print_r($post_params);
    
        */
        $result = $this->Bnapi -> account();
        if($result['code']){
            return false;
        }
        var_dump($result);exit;
        echo '<pre>';
        print_r($result);
        if($result['result'] == 'true' && $result['info']['funds']['free']){
            $balance = $result['info']['funds']['free'][$bi_name];
            if($balance>0){
                return $balance;
            }else{
                return 0;
            }
        }else{
            return false;
        }
    }
    
    /**
     * 
     * @param number $amount    数量
     * @param number $price     单价
     * @param string $symbol    币种对
     * @return boolean
     */
    public function ba_order($amount=0,$price=0,$symbol='btcusd'){
        $return = $this->Bnapi->buy(strtoupper($symbol), $amount, $price);
        //var_dump($result);
        if($return->orderId){
            return $return->orderId;//返回下单成功的订单ID
        }else{
            return false;
        }
    }
    
    /**
     * 
     * @param string $order_id  要取消的订单id
     * @param string $symbol    币种对
     */
    public function ba_cancelOrder($order_id='546',$symbol='btcusd'){
    
        $result = $this->bnapi -> cancel(strtoupper($symbol), $order_id);
        if($result->success){
            return $result->success;//撤单请求成功的订单ID，等待系统执行撤单(用于多笔订单)
        }else{
            return $result->error;//撤单请求失败的订单ID(用户多笔订单)
        }
    }
}