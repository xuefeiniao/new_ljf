<?php
//$zb=new zbAPI ;
//$Fond_zb=$zb->Fund();
//var_dump($Fond_zb);
namespace Org\Util\Zb;
class zbAPI {
var  $access_key="deb39a3c-d89f-48bb-9d15-dbf58facc241";
var  $secret_key="0f936cc7-9896-4bc0-a8f1-44b1f62d1320";
function httpRequest($pUrl, $pData){
    $tCh = curl_init();
    curl_setopt($tCh, CURLOPT_POST, true);
    curl_setopt($tCh, CURLOPT_POSTFIELDS, $pData);
    curl_setopt($tCh, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
    curl_setopt($tCh, CURLOPT_URL, $pUrl);
    curl_setopt($tCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($tCh, CURLOPT_SSL_VERIFYPEER, false);
    $tResult = curl_exec($tCh);
    curl_close($tCh);
    $tResult=json_decode ($tResult,true);
    return $tResult;
}
function HangqingRequest($pUrl){
    $tCh = curl_init();	
    curl_setopt($tCh, CURLOPT_URL, $pUrl);	
    curl_setopt($tCh, CURLOPT_RETURNTRANSFER, true); 		//1:文件流返回，不是直接输出
    curl_setopt($tCh, CURLOPT_SSL_VERIFYPEER, false);		//禁止 cURL 验证对等证书	
    curl_setopt($tCh, CURLOPT_TIMEOUT, 1);
    $tResult = curl_exec($tCh);	
    curl_close($tCh);
    $tResult=json_decode ($tResult,true);
    return $tResult;
}
function createSign($pParams = array()){    
    $tPreSign = http_build_query($pParams, '', '&');
    $SecretKey = sha1($this->secret_key);
    $tSign=hash_hmac('md5',$tPreSign,$SecretKey);
    $pParams['sign'] = $tSign;
    $pParams['reqTime'] = time()*1000;
    $tResult=http_build_query($pParams, '', '&');
    return $tResult;
}
//行情
function Hangqing($coin){
	$Url_btc="http://api.bitkk.com/data/v1/ticker?market="."$coin";
	$res=array();
	$res=$this->HangqingRequest($Url_btc);
	return $res;
}
//K线
function kline($coin){
    $Url_btc="http://api.bitkk.com/data/v1/kline?market="."$coin";
    $res=array();
    $res=$this->HangqingRequest($Url_btc);
    return $res;
}
function depty($coin,$size=10){
	
	$Url_btc="http://api.zb.cn/data/v1/depth?market=".$coin."&size=".$size;
    $res=array();
    $res=$this->HangqingRequest($Url_btc);
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
   $url= "https://trade.zb.com/api/order";
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
 $post=$this->createSign($parameters);
 $res=$this->httpRequest($url,$post);
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