<?php
namespace Home\Controller;
//use Home\Controller\ZbController;
class UnionTradeController extends HomeController{
    //公共调用5个交易所方法
    //按交易对查5个交易所的买卖各十档
    public function jiaoyi($btc='etc_usdt'){
        unit_ten_trades();
    }
}