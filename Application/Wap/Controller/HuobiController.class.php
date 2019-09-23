<?php
namespace Home\Controller;
class HuobiController extends HomeController{
    public $Hbapi;
    public function __construct(){
    
        //echo '<pre>';
        if(!$this->Hbapi){
            $this->Hbapi = new \Common\Ext\HuobiAPI();
        }
    }
    
    //取10档交易记录历史成交
    public function hb_get_ten_trades($bi_type='btcusdt'){

        /* $result = $this->Hbapi->get_history_trade($bi_type='btcusdt',$size='30');
        echo '<pre>';
        print_r($result);exit; */
        
        //判断在zb交易所是否有金额
        if(!$this->hb_get_user_balance($bi_name='')){
            $return = $this->Hbapi->get_history_trade($bi_type,$size='50');
        
            $return_arr_buy = [];//买档
            $return_arr_ask = [];//买档
            if($return['status']=='ok' && $return['data']){
                foreach ($return['data'] as $v){
                    $v['data']['extenchange_type'] = 'HB';
                    if($v['data']['direction']=='buy'){
                        //买
                        array_push($return_arr_buy, $v['data']);
        
                    }elseif($v['data']['direction']=='sell'){
                        //卖
                        array_push($return_arr_ask, $v['data']);
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
        }else{
            $big_arr = [];
        }
        return $big_arr;
        //echo '<pre>';
        //print_r($result);exit;
        
    }
    
    //获取用户account_id
    public function hb_get_account_accounts(){
        $result  =$this->Hbapi->get_account_accounts();//有问题
        var_dump($result);exit;
        if($result['status']=='ok' && $result['data']['id']){
            $account_id = $result['data']['id'];//用户account_id
            return $account_id;
        }else{
            return false;
        }
    }
    //获取用户资产
    public function hb_get_user_balance($bi_name=''){
        $account_id  =$this->hb_get_account_accounts();//有问题
        if($account_id){
            $user_balance = $this->Hbapi->get_account_balance($account_id);
            $balance = 0;
            if($user_balance['status']=='ok' && $user_balance['data']['list']){
                $list = $user_balance['data']['list'];
                foreach ($list as $v){
                    if($v['currency']==$bi_name){
                        $balance = $v['currency'];
                        break;
                    }
                }
            }
            return $balance;
        }else{
            return false;
        }
    }
    
    
    /**
     * 委托下单
     * @param number $account_id    账户 ID，使用accounts方法获得
     * @param number $amount    限价单表示下单数量，市价买单时表示买多少钱，市价卖单时表示卖多少币
     * @param number $price 下单价格，市价单不传该参数
     * @param string $symbol    交易对btcusdt, bchbtc, rcneth ...
     * @param string $type  订单类型    buy-market：市价买, sell-market：市价卖, buy-limit：限价买, sell-limit：限价卖, buy-ioc：IOC买单, sell-ioc：IOC卖单
     */
    public function hb_order($account_id=0,$amount=0,$price=0,$symbol='btcusdt',$type='buy-market'){
        $return = $this->Hbapi->place_order($account_id=0,$amount=0,$price=0,$symbol='btcusdt',$type='buy-market');
        if($return['status'] == 'ok' && $return['data']){
            return $return['data'];//返回下单成功的订单ID
        }else{
            return false;
        }
    }
    
    //取消某个委托买单
    /**
     * 
     * @param unknown $order_id 订单ID
     */
    public function zb_cancelOrder($order_id){
        $return = $this->Hbapi->cancel_order($order_id);
        if($return['status'] == 'ok' && $return['data']){
            return true;
        }else{
            return false;
        }
    }
    
    public function test(){
        $api = new \Common\Ext\BaAPI();
        //var_dump($api);exit;
        $ticker = $api->exchangeInfo();
        print_r($ticker); exit;
    }
    
}