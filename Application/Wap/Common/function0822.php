<?php
//wap端使用
/*function session($session_key){
    if(session($session_key)){
        return session($session_key);
    }else{
        if($_REQUEST['userid']){
            return $_REQUEST['userid'];
        }else{
            echo json_encode(array('code'=>204,'result'=>'请先登陆'));
        }
    }
}*/

function gate_query($path, array $req = array()) {

        // API settings, add your Key and Secret at here
        $key = "your api key";
        $secret = "your api secret";
        // generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1].substr($mt[0], 2, 6);
        // generate the POST data string
        $post_data = http_build_query($req, '', '&');
        $sign = hash_hmac('sha512', urldecode($post_data), $secret);
        // generate the extra headers
        $headers = array(
            'KEY: '.$key,
            'SIGN: '.$sign,
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
        );
        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36');
        }
        curl_setopt($ch, CURLOPT_URL, 'http://api.gateio.io/api2/'.$path);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // run the query
        $res = curl_exec($ch);
        $getinfo = curl_getinfo($ch);
//      echo $res;die;
        if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
        //print_r($res);
        $dec = json_decode($res, true);
        if (!$dec) throw new Exception('Invalid data received, please make sure connection is working and requested API exists: '.$res);
        return $dec;
    }


    function curl_file_get_contents($url) {

        // our curl handle (initialize if required)
        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,
                'Mozilla/4.0 (compatible; gate PHP bot; '.php_uname('a').'; PHP/'.phpversion().')'
            );
        }
        curl_setopt($ch, CURLOPT_URL, 'https://data.gateio.io/api2/'.$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // run the query
        $res = curl_exec($ch);
        if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
        $dec = json_decode($res, true);
        if (!$dec) throw new Exception('Invalid data: '.$res);

        return $dec;
    }
    function get_top_rate($currency_pair, $type='BUY') {

        $url = '1/orderBook/'.strtoupper($currency_pair);
        $json = curl_file_get_contents($url);

        $rate = 0;
        if (strtoupper($type) == 'BUY') {
            $r =  $json['bids'][0];
            $rate = $r[0];
        } else  {
            $r = end($json['asks']);
            $rate = $r[0];
        }
        return $rate;
    }

    function get_pairs() {

        $url = '1/pairs';
        $json = curl_file_get_contents($url);

        return $json;
    }
    function get_marketinfo(){

        $url = '1/marketinfo';
        $json = curl_file_get_contents($url);

        return $json;
    }
    function get_marketlist(){
        $url = '1/marketlist';
        $json = curl_file_get_contents($url);
        return $json;
    }
    function get_tickers(){

        $url = '1/tickers';
        $json = curl_file_get_contents($url);

        return $json;
    }

    function get_ticker($current_pairs){

        $url = '1/ticker/'.strtoupper($current_pairs);
        $json = curl_file_get_contents($url);

        return $json;
    }

    function get_orderbooks(){

        $url = '1/orderBooks';
        $json = curl_file_get_contents($url);

        return $json;
    }

    function get_orderbook($current_pairs){

        $url = '1/orderBook/'.strtoupper($current_pairs);
        $json = curl_file_get_contents($url);

        return $json;
    }

    function get_trade_history($current_pairs, $tid){

        $url = '1/tradeHistory/'.strtoupper($current_pairs).'/'.$tid;
        $json = curl_file_get_contents($url);

        return $json;
    }

    function get_balances() {

        return gate_query('1/private/balances');
    }

    function get_order_trades($order_number) {

        return gate_query('1/private/orderTrades',
            array(
                'orderNumber' => $order_number
            )
        );
    }

    function withdraw($currency, $amount, $address) {

        return gate_query('1/private/withdraw',
            array(
                'currency' => strtoupper($currency),
                'amount' => $amount,
                'address' => $address
            )
        );
    }

    function get_order($order_number, $currency_pair) {

        return gate_query('1/private/getOrder',
            array(
                'orderNumber' => $order_number,
                'currencyPair' => strtoupper($currency_pair)
            )
        );
    }

    function cancel_order($order_number, $currency_pair) {

        return gate_query('1/private/cancelOrder',
            array(
                'orderNumber' => $order_number,
                'currencyPair' => strtoupper($currency_pair)
            )
        );
    }
    function cancel_orders($orders) {
        return gate_query('1/private/cancelOrders',
//           ['orders_json'=> $orders]
            ['orders_json'=>json_encode( $orders)]
        );
    }

    function cancel_all_orders($type, $currency_pair) {

        return gate_query('1/private/cancelAllOrders',
            array(
                'type' => $type,
                'currencyPair' => strtoupper($currency_pair)
            )
        );
    }

    function sell($currency_pair, $rate, $amount) {

        return gate_query('1/private/sell',
            array(
                'currencyPair' => strtoupper($currency_pair),
                'rate' => $rate,
                'amount' => $amount,
            )
        );
    }

    function buy($currency_pair, $rate, $amount) {

        return gate_query('1/private/buy',
            array(
                'currencyPair' => strtoupper($currency_pair),
                'rate' => $rate,
                'amount' => $amount,
            )
        );
    }

    function get_my_trade_history($currency_pair, $order_number) {

        return gate_query('1/private/tradeHistory',
            array(
                'currencyPair' => strtoupper($currency_pair),
                'orderNumber' => $order_number
            )
        );
    }

    function open_orders($currency_pair='') {

        return gate_query('1/private/openOrders',
            array(
                'currencyPair' => strtoupper($currency_pair)
            ));
    }

    function deposites_withdrawals($start, $end) {

        return gate_query('1/private/depositsWithdrawals',
            array(
                'start' => $start,
                'end' => $end
            )
        );
    }

    function new_adddress($currency) {

        return gate_query('1/private/newAddress',
            array(
                'currency' => strtoupper($currency)
            )
        );
    }

    function deposit_address($currency) {

        return gate_query('1/private/depositAddress',
            array(
                'currency' => strtoupper($currency)
            )
        );
    }

    function check_username($username, $phone, $sign) {


        return gate_query('1/checkUsername',
            array(
                'username' => $username,
                'phone' => $phone,
                'sign' => $sign
            )
        );
    }


?>