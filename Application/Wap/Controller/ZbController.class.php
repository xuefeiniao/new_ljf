<?php
namespace Home\Controller;

class ZbController extends HomeController
{
    public $Zbapi;
    public function __construct(){
        
        //echo '<pre>';
        if(!$this->Zbapi){
            $this->Zbapi = new \Common\Ext\ZbAPI();
        }
        //var_dump($this->Zbapi);exit;
    }
    
    //取10档交易记录历史成交
    public function zb_get_ten_trades($bi_type='btc_usdt',$username='15836662139'){
        //判断在zb交易所是否有金额
        $bi_name_arr = explode('_', $bi_type);
        $bi_name = $bi_name_arr[0];
        //todo线上此处判断要修改
        if(!$this->zb_get_user_balance($username,$bi_name)){
            $pUrl = 'http://api.bitkk.com/data/v1/trades?market='.$bi_type;
            $return = $this->Zbapi->httpRequest($pUrl);
            $return_arr_buy = [];//买档
            $return_arr_ask = [];//买档
            if($return[0]){
                foreach ($return as &$v){
                    $v['extenchange_type'] = $bi_name;
                    if($v['type']=='buy'){
                        //买
                        array_push($return_arr_buy, $v);
                        
                    }elseif($v['type']=='sell'){
                        //卖
                        array_push($return_arr_ask, $v);
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
        //var_dump($return_arr);exit;
    }
    
    //获取用户在该交易所的余额，（没有余额不允许查看该交易所的档位）
    public function zb_get_user_balance($username='15836662139',$bi_name='ZB'){
        $user_balance = $this->Zbapi->Fund();
        //print_r($user_balance);exit;
        if($user_balance['result']['coins']){
            //各交易所有资金
            $conins = $user_balance['result']['coins'];
            //echo '<pre>';
            //print_r($conins);exit;
            foreach ($conins as $k=>$v){
                if($v['enName']==$bi_name && $username==$user_balance['result']['base']['username']){
                    if($v['available']>0){
                        //echo $v['available'];exit;
                        return $v['available'];//返回用户金额
                    }else{
                        return false;
                    }
                }
            }
        }else{
            //没有资金
            return false;
        }
       
    }
    
    
    
    //委托下单
    public function zb_order($bi_type='btc',$amount='1.052',$price='100',$tradeType='1'){
        
        
        //if($this->zb_get_user_balance($username='15836662139', $bi_name='ZB')){
            //在该平台有资金
            $accesskey = 'deb39a3c-d89f-48bb-9d15-dbf58facc241';
            
            $pParams['accesskey'] = $accesskey;
            $pParams['amount'] = $amount;//交易数量(btc,ltc,eth,etc)
            $pParams['currency'] = $bi_type;//币种
            $pParams['method'] = 'order';
            $pParams['price'] = $price;//单价
            $pParams['tradeType'] = $tradeType; //交易类型1/0[buy/sell]
            //$pParams['acctType'] = 0;//杠杆 1/0[杠杆/现货](可选参数,默认为: 0 现货)
            $sign = $this->Zbapi->createSign($pParams);
            echo $sign;exit;
            $pUrl = 'https://trade.zb.com/api/order';
            
            //echo $pUrl;exit;
            $return = $this->Zbapi->httpRequest($pUrl,$sign);
            if($return && $return->code==1000 && $return->id){
                $arr = json_decode($return,true);
                return $arr;
            }else{
                return [];
            }
            print_r($return);exit;
        /* }else{
            //资金不足
            return false;
        } */
        
    }
    
    //取消某个委托买单
    public function zb_cancelOrder($bi_type='',$id=''){
        $accesskey = 'deb39a3c-d89f-48bb-9d15-dbf58facc241';
        $pParams['accesskey'] = $accesskey;
        $pParams['currency'] = $bi_type;//币种
        $pParams['id'] = $id;//委托挂单号
        $pParams['method'] = 'cancelOrder';
        $sign = $this->Zbapi->createSign($pParams);
        $pUrl = 'https://trade.zb.com/api/cancelOrder';
        $return = $this->Zbapi->httpRequest($pUrl,$sign);
        if($return && $return->code==1000){
            return true;//取消委托成功
        }else{
            return false;//取消委托失败
        }
    }
    
    //查询获取多个委托买单卖单
    public function zb_get_orders($bi_type='bcc_zb',$tradeType='1'){
        //if($this->zb_get_user_balance($username='15836662139', $bi_name='ZB')){
        //在该平台有资金
        $accesskey = 'deb39a3c-d89f-48bb-9d15-dbf58facc241';
    
        $pParams['accesskey'] = $accesskey;
        $pParams['currency'] = $bi_type;//币种
        $pParams['method'] = 'getOrders';
        $pParams['pageIndex'] = 1;//
        $pParams['tradeType'] = 1; //交易类型1/0[buy/sell]
        $sign = $this->Zbapi->createSign($pParams);
        //
        echo $sign;exit;
        $pUrl = 'https://trade.zb.com/api/getOrders?';
    
        //echo $pUrl;exit;
        $return = $this->Zbapi->httpRequest($pUrl,$sign);
        var_dump($return);exit;
        /* }else{
         //资金不足
         return false;
         } */
    
    }
    
    //(废弃)
    public function test(){
        var_dump($this->Zbapi->Fund());exit;
        //var_dump($this->Zbapi->Hangqing());exit;
       
    }
    
    


    //用户各平台账号
    function all_username(){
        $arr['zb']['username'] = '15836662139';
        $arr['okex']['username'] = '';
        return $arr;
    }


    //所需币种
    function allbh(){
        $arr[] = 'IB/BB';
        $arr[] = 'BTC/BB';
        $arr[] = 'ETH/BB';
        $arr[] = 'EOS/BB';
        $arr[] = 'LTC/BB';
        $arr[] = 'HPA/BB';
        $arr[] = 'IB/USDT';
        $arr[] = 'BTC/USDT';
        $arr[] = 'ETH/USDT';
        $arr[] = 'EOS/USDT';
        $arr[] = 'LTC/USDT';
        $arr[] = 'HPA/USDT';
        $arr[] = 'IB/BTC';
        $arr[] = 'ETH/BTC';
        $arr[] = 'EOS/BTC';
        $arr[] = 'LTC/BTC';
        $arr[] = 'HPA/BTC';
        return $arr;
    }
    
    //所有交易所
    function all_exchange(){
        $arr[] = 'ZB';
        $arr[] = 'OKEX';
        $arr[] = 'HBG';//火币
        $arr[] = 'COINEGG';//币蛋
        $arr[] = 'BLANCEAN';
        return $arr;
    }
}

?>