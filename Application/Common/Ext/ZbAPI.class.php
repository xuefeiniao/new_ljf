<?php
namespace Common\Ext;
class zbAPI {
    public  $access_key="deb39a3c-d89f-48bb-9d15-dbf58facc241";
    public  $secret_key="0f936cc7-9896-4bc0-a8f1-44b1f62d1320";
    function httpRequest($pUrl, $pData){
        $tCh = curl_init();
        
        curl_setopt($tCh, CURLOPT_POST, true);
        curl_setopt($tCh, CURLOPT_POSTFIELDS, $pData);
        curl_setopt($tCh, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
        curl_setopt($tCh, CURLOPT_URL, $pUrl);
        curl_setopt($tCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($tCh, CURLOPT_SSL_VERIFYPEER, false);
        
        //设置代理IP
        //curl_setopt($tCh, CURLOPT_PROXY, '10.200.7.31:80');
        
        $tResult = curl_exec($tCh);
        curl_close($tCh);
        $tResult=json_decode ($tResult,true);
        return $tResult;
    }
    function HangqingRequest($pUrl){
        $tCh = curl_init();
        curl_setopt($tCh, CURLOPT_URL, $pUrl);
        curl_setopt($tCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($tCh, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($tCh, CURLOPT_TIMEOUT, 1);
        $tResult = curl_exec($tCh);
        curl_close($tCh);
        $tResult=json_decode ($tResult,true);
        return $tResult;
    }
    function createSign($pParams = array()){
        $tPreSign = http_build_query($pParams, '', '&');
        //var_dump($tPreSign);exit;
        $SecretKey = sha1($this->secret_key);
        //echo $tPreSign;exit;
        //echo $SecretKey;exit;
        $tSign=hash_hmac('md5',$tPreSign,$SecretKey);
        //return $tSign;
        $pParams['sign'] = $tSign;
        $pParams['reqTime'] = time()*1000;
        $tResult=http_build_query($pParams, '', '&');
        //echo $tResult;exit;
        return $tResult;
    }
    function Hangqing(){
        //$Url_btc="http://api.zb.com/data/v1/ticker?currency=btc_usdt";
        $Url_btc="http://api.bitkk.com/data/v1/ticker?market=btc_usdt";
        $res=array();
        $res=$this->HangqingRequest($Url_btc);
        //var_dump($res);exit;
        return $res;
    }
    function MarketDepth($N=20){
        $res=$this->HangqingBtc();
        $res_ask=array_reverse(array_slice($res["asks"] , -$N, $N));
        $res_bid=array_slice($res["bids"] , 0, $N) ;
        $ans=array("asks"=>$res_ask,"bids"=>$res_bid);
        return $ans;
    }
     
    //BTC 下单
    function  Trade($Price,$Amount,$Tradetype){
        $parameters=array("accesskey"=>$this->access_key,"amount"=>$Amount,"currency"=>"btc","method"=>"order","price"=>$Price,"tradeType"=>$Tradetype);
        $url= "https://trade.bitkk.com/api/order?";
        $post=$this->createSign($parameters);
        $res=$this->httpRequest($url,$post);
        return $res;
    }
    //BTC 取消订单
    function	CancelOrder($OrderID){
        $parameters=array("accesskey"=>$this->access_key,"currency"=>"btc","id"=>$OrderID,"method"=>"cancelOrder");
        $url='https://trade.zb.com/api/cancelOrder';
        $post=$this->createSign($parameters);
        $res=$this->httpRequest($url,$post);
        return $res;
    }
    //LTC 取消订单
    function	CancelOrder_ltc($OrderID){
        $parameters=array("accesskey"=>$this->access_key,"currency"=>"ltc","id"=>$OrderID,"method"=>"cancelOrder");
        $url='https://trade.zb.com/api/cancelOrder';
        $post=$this->createSign($parameters);
        $res=$this->httpRequest($url,$post);
        return $res;
    }
    function Fund(){
        $parameters=array("accesskey"=>$this->access_key,"method"=>"getAccountInfo");
        
        $url='https://trade.zb.com/api/getAccountInfo';
        //$url='https://trade.bitkk.com/api/getAccountInfo';
        $post=$this->createSign($parameters);
        //echo $post;exit;
        $res=$this->httpRequest($url,$post);
        //var_dump($res);exit;
        return $res;
    }
    //获取订单信息
    function  GetOrder($OrderID){
        $parameters=array("accesskey"=>$this->access_key,"currency"=>"btc","id"=>$OrderID,"method"=>"getOrder");
        $url= 'https://trade.zb.com/api/getOrder';
        $post=$this->createSign($parameters);
        $res=$this->httpRequest($url,$post);
        return $res;
    }
    //获取订单信息
    function  GetOrder_ltc($OrderID){
        $parameters=array("accesskey"=>$this->access_key,"currency"=>"ltc","id"=>$OrderID,"method"=>"getOrder");
        $url= 'https://trade.zb.com/api/getOrder';
        $post=$this->createSign($parameters);
        $res=$this->httpRequest($url,$post);
        return $res;
    }
}
?>