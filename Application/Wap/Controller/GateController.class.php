<?php
namespace Home\Controller;

class gateController extends HomeController
{

    public function get(){
      
       print_r(get_pairs());


        //try {
        /*** public API methods examples ***/
        // 所有交易对
        //  print_r( get_pairs());
        //交易市场订单参数
        //  print_r(get_marketinfo());
        //交易市场详细行情
        //    print_r(get_marketlist());
        // 所有交易行情
        //  print_r(get_tickers());
        //单项交易行情
        //  print_r(get_ticker('eth_btc'));
        //交易对的市场深度
        //    print_r(get_orderbooks());
        //指定交易对的市场深度
        //    print_r(get_orderbook('btc_usdt'));
        //历史成交记录
        //    print_r(get_trade_history('btc_usdt', 1000));

        /*** private API methods examples ***/
        // 获取账号资金余额
//    print_r(get_balances());
        //获取充值地址
//    print_r(deposit_address('btc'));
        //获取充值提现历史
//    print_r(deposites_withdrawals('1469092370', '1670713981'));
        //下单交易买入
//    print_r(buy('etc_btc', '0.0035', '0.3'));
        //下单交易卖出
//    print_r(sell('etc_btc', '0.00214', '0.3'));
        //取消下单
//    print_r(cancel_order(263393711), 'etc_btc');
        //取消所有下单
        //    print_r(cancel_all_orders('0', 'etc_btc'));
        //获取下单状态
//    print_r(get_order(263393711, 'etc_btc'));
        //获取我的当前挂单列表
//    print_r(open_orders());
        //获取我的24小时内成交记录
//  print_r(get_trade_history('eth_btc',27817390));
        //提现
//  print_r(withdraw('btc','11','your wallet address'));
//} catch (Exception $e) {
//    echo "Error:".$e->getMessage();
//}
    }

 
}

?>