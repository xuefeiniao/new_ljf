<?php
namespace Home\Controller;

class OkexController extends HomeController{
    public $Okapi;
    public $api_key = "e63a571c-d87d-4e3c-92c9-fe03495d96a1";
    public $api_secret = "7AFBCF56CE2733CB4B2810FFEB1D0A1F";
    public function __construct(){
        //echo $this->Okapi;exit;
        if(!$this->Okapi){
            include_once './Application/Common/Ext/OKCoin/OKCoin.php';
            //include_once './ThinkPHP/Library/Org/Util/OKCoin/OKCoin.php';
            //$this->Okapi = new \OKCoin();
            $this->Okapi = new \OKCoin(new \OKCoin_ApiKeyAuthentication($this->api_key, $this->api_secret));
        }
    }
    
    //取10档交易记录历史成交
    public function ok_get_ten_trades($symbol='btc_usd'){
        //判断在zb交易所是否有金额
        $bi_name_arr = explode('_', $symbol);
        $bi_name = $bi_name_arr[0];
        if($bi_name=='usdt') $bi_name='usd';
        
        if(strrpos($symbol, 'usdt')){
            $symbol = str_replace('usdt', 'usd', $symbol);
        }
        if(!$this->ok_get_user_balance($bi_name)){

            $params = array('symbol' => $symbol);
            $result = $this->Okapi -> tradesApi($params);
            //var_dump($result_return);
            //返回对象报错，返回数组是正确
            if($result->error_code){
                return [];//1007没有交易市场记录
            }
           
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
            
            
                echo '<pre>';
                 print_r($big_arr);
                exit;
                return $big_arr;
            }else{
                return [];
            }
        }else{
            return [];
        }
        //print_r($result);
    }
    
    //获取用户信息
    /**
     * 
     * @param string $app_key   用户app_key
     */
    public function ok_get_user_balance($bi_name='btc'){
        //TODO：签名有问题
        $params = array('api_key' => $this->api_key);
        
        /* echo '<pre>';
        print_r($post_params);
        
         */
        $result = $this->Okapi -> userinfoApi($params);
        if($result->error_code){
            return 0;//10005    SecretKey不存在
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
     * 下单交易
     * @param number $amount    交易数量 市价买单不传amount,市价买单需传price作为买入总金额
     * @param number $price 下单价格 市价卖单不传price
     * @param string $symbol    币对如ltc_btc
     * @param string $type  买卖类型：限价单(buy/sell) 市价单(buy_market/sell_market)
     */
    public function ok_order($amount=0,$price=0,$symbol='btc_usd',$type='buy'){
        $params = array('api_key' => $this->api_key, 'symbol' => $symbol, 'type' => $type, 'price' => $price, 'amount' => $amount);
        $return = $this->Okapi-> tradeApi($params);
        //var_dump($result);
        if($return->status == 'true' && $return->order_id){
            return $return->order_id;//返回下单成功的订单ID
        }else{
            return false;
        }
    }
    
    /**
     * 撤销订单
     * @param string $order_id  订单ID(多个订单ID中间以","分隔,一次最多允许撤消3个订单)
     * @param string $symbol    币对如ltc_btc
     * @param string $api_key   用户申请的apiKey
     */
    public function ok_cancelOrder($order_id='546,456,998,65656',$symbol='btc_usd'){
        
        $params = array('api_key' => $this->api_key, 'symbol' => $symbol, 'order_id' => $order_id);
        $result = $this->Okapi -> cancelOrderApi($params);
        if(strpos($order_id, ',')){
            //多笔订单
            if($result->success){
                return $result->success;//撤单请求成功的订单ID，等待系统执行撤单(用于多笔订单)
            }else{
                return $result->error;//撤单请求失败的订单ID(用户多笔订单)
            }
        }else{
            //单笔订单
            if($result->result == true && $result->order_id){
                return $result->order_id;//订单ID(用于单笔订单)
            }else{
                return false;//true撤单请求成功，等待系统执行撤单；false撤单失败(用于单笔订单)
            }
        }
    }
    
    
    
    public function gate_get_tickers(){
        include_once './Application/Common/Common/gate.php'; 
        $arr = get_tickers();
        echo '<pre>';
        print_r($arr);
        exit;
    }
}