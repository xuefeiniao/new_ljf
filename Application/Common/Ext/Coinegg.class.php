<?php
/**
 * User: coinegg.com 王小花
 * Date: 2017/11/22
 * Time: 上午10:53
 * Coinegg的api接口功能
 * version:1.0
 *
 * 100    必选参数不能为空
 * 101    非法参数
 * 102    请求的虚拟币不存在
 * 103    密钥不存在
 * 104    签名不匹配
 * 105    权限不足
 * 106    请求过期(nonce错误)
 * 200    余额不足
 * 201    买卖的数量小于最小买卖额度
 * 202    下单价格必须在 0 - 1000000 之间
 * 203    订单不存在
 * 204    挂单金额必须在 0.001 BTC以上
 * 206    小数位错误
 * 401    系统错误
 * 402    请求过于频繁
 * 403    非开放API
 * 404    IP限制不能请求该资源
 * 405    币种交易暂时关闭
 */
namespace Common\Ext;
class Coinegg
{
    //申请到的私钥 如 MS3%N-O{%gw-%mSE!-q4hT!-zPI;}-;wm;y-L67%y带乱码的 以下是示例
    private $private_key = '&}h/d-@,UzN-$7q4f-N&Iy@-K~~~~~~';
    //公钥. 如 aff11-11223-33fgf-11331-ad23f-3gdw2-2dds2   中横线隔开标准数字加小写
    private $public_key = 'cbz6u-z9xbm-ziksa-nq34b-vctx1-idmfs-~~~~';
    private $api_base_url = 'http://api.coinegg.com/';//api基础地址


    /**
     * get请求url
     * @param $url
     * @return bool|string
     */
    private function  get($url)
    {
//        echo $url;
        return file_get_contents($url);
    }

    /**
     * Post data
     * @param $url
     * @param $data
     * @param array $header
     * @return mixed
     */
    private function post($url, $data, $header = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $header or curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $rs = curl_exec($ch);
        curl_close($ch);
        return $rs;

    }

    /**
     * Ticker（牌价） 获取基本的交易信息
     * @param string $coin
     * @return bool|string
     */
    public  function getTicker($coin = 'eth', $region = 'btc')
    {
        $api_url = $this->api_base_url . "/api/v1/ticker/region/$region?coin=$coin";
        //echo $api_url;exit;
        return $this->get($api_url);
    }

    /**
     * 返回所有的市场深度，此回应的数据量会较大，所以请勿频繁调用(过于频繁可能被自动封ip)
     * @param string $coin
     * @return bool|string
     */
    public function getDepth($coin = 'eth', $region = 'btc')
    {
        $api_url = $this->api_base_url . "/api/v1/depth/region/$region?coin=$coin";
        return $this->get($api_url);
    }

    /**
     * 描述：返回100个最近的市场交易，按时间倒序排列，此回应的数据量会较大，所以请勿频繁调用。(过于频繁可能被自动封ip)
     * @param string $coin
     * @return bool|string
     */

    public function getOrders($coin = 'eth', $region = 'btc')
    {
        $api_url = $this->api_base_url . "/api/v1/orders/region/$region?coin=$coin&since=1";
        echo $api_url;exit;
        return $this->get($api_url);
    }

    /**
     * 根据请求的post,返回签名
     * @param array &$post_data
     * @return string
     */
    private function createSign(&$post_data = array())
    {
        $post_data['signature'] =
            hash_hmac('sha256',
                http_build_query($post_data, '', '&'),
                md5($this->private_key)
            );
        return $post_data;
    }

    /**
     * 获取余额接口
     * @return mixed
     */
    public function getBalance()
    {
        $api_url = $this->api_base_url . '/api/v1/balance/';
        $post_data = array(
            'key' => $this->public_key,
            'nonce' => time(),//nonce为不重复的整数.
        );
        $this->createSign($post_data);
        return $this->post($api_url, $post_data);
    }

    /**
     * 您指定时间后的挂单，可以根据类型查询，比如查看正在挂单和全部挂单
     * @param string $coin
     * @param string $type
     * @param int $since
     * @return mixed
     */
    public function getTradeList($coin = 'eth', $type = 'all', $since = 0, $region = 'btc')
    {
        $api_url = $this->api_base_url . '/api/v1/trade_list/region/' . $region;
        $post_data = array(
            'key' => $this->public_key,
            'nonce' => time(),//nonce为不重复的整数.
            'since' => $since,//创建时间 php中的time()
            'coin' => $coin, //币种
            'type' => $type, //open或all
        );
        $this->createSign($post_data);
        return $this->post($api_url, $post_data);
    }

    /**
     * 查询订单信息
     * @param $coin
     * @param $trade_id
     * @return mixed 返回 203或105说明该用户没这个订单
     */
    public function getTrade($coin, $trade_id , $region = 'btc')
    {
        $api_url = $this->api_base_url . '/api/v1/trade_view/region/' . $region;
        $post_data = array(
            'key' => $this->public_key,
            'nonce' => time(),//nonce为不重复的整数.
            'coin' => $coin, //币种
            'id' => $trade_id, //open或all
        );
        $this->createSign($post_data);
        return $this->post($api_url, $post_data);
    }

    /**
     * 撤销挂单 如果不存在返回301
     * @param $coin
     * @param $trade_id
     * @return mixed
     */
    public function setTradeCancel($coin, $trade_id, $region = 'btc')
    {
        $api_url = $this->api_base_url . '/api/v1/trade_cancel/region/' . $region;
        $post_data = array(
            'key' => $this->public_key,
            'nonce' => time(),//nonce为不重复的整数.
            'coin' => $coin, //币种
            'id' => $trade_id, //open或all
        );
        $this->createSign($post_data);
        return $this->post($api_url, $post_data);
    }

    /**
     * 下单
     * @param $coin
     * @param $type
     * @param $amount
     * @param $price
     * @return mixed
     * 500 超系统限额,501总额过低 ,505余额不足
     */
    public function setTrade($coin, $type, $amount, $price, $region = 'btc')
    {
        $api_url = $this->api_base_url . '/api/v1/trade_add/region/'.$region;

        $post_data = array(
            'key' => $this->public_key,
            'nonce' => time(),//nonce为不重复的整数.
            'coin' => $coin, //币种
            'type' => $type, //sell buy
            'amount' => $amount, //数量
            'price' => $price, //价格
        );
        $this->createSign($post_data);
        return $this->post($api_url, $post_data);
    }

}
