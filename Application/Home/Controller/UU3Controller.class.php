<?php
namespace Home\Controller;

class UU3Controller extends HomeController{


    public function t(){
        $mk = M('market');
        $lst = $mk->where("status=1")->field('name,new_price,change')->select();
        foreach ($lst as $k=>$v){
            $lst[$k]['name']=strtoupper(str_replace('_', '/', $v['name']));
            $lst[$k]['new_price']=round($v['new_price'],2);
        }

        print_r($lst);
    }
    public function tt(){
        $market='ltc_cny';

        var_dump($this->getDepth($market));
       //var_dump($this->getTradelog($market));
    }
    public function getDepth($market = NULL, $trade_moshi = 1, $ajax = 'json')
    {
        if (!C('market')[$market]) {
            return null;
        }

        $zhisucom_getCoreConfig = zhisucom_getCoreConfig();
        if (!$zhisucom_getCoreConfig) {
            $this->error('核心配置有误');
        } else {
            $zhisucom_putong = $zhisucom_getCoreConfig['zhisucom_userTradeNum'];
            $zhisucom_teshu = $zhisucom_getCoreConfig['zhisucom_specialUserTradeNum'];
        }

        $data_getDepth = (APP_DEBUG ? null : S('getDepth'));

        if (!$data_getDepth[$market][$trade_moshi]) {
            if ($trade_moshi == 1) {
                $limt = 6;
            }

            if (($trade_moshi == 3) || ($trade_moshi == 4)) {
                //20170608增加按用户级别调用信息条数
                if(userid()){
                    $usertype = M('User')->where(array($id => $userid))->getField('usertype');
                    if($usertype ==1){
                        $limt = $zhisucom_teshu;
                    }else{
                        $limt = $zhisucom_putong;
                    }
                }else{
                    $limt = $zhisucom_putong;
                }
            }

            $trade_moshi = intval($trade_moshi);


            $mo = M();
            if ($trade_moshi == 1) {
                $buy = $mo->query('select id,price,sum(num-deal)as nums from zhisucom_trade where status=0 and type=1 and market =\'' . $market . '\' group by price order by price desc limit ' . $limt . ';');
                $sell = array_reverse($mo->query('select id,price,sum(num-deal)as nums from zhisucom_trade where status=0 and type=2 and market =\'' . $market . '\' group by price order by price asc limit ' . $limt . ';'));
            }

            if ($trade_moshi == 3) {
                $buy = $mo->query('select id,price,sum(num-deal)as nums from zhisucom_trade where status=0 and type=1 and market =\'' . $market . '\' group by price order by price desc limit ' . $limt . ';');
                $sell = null;
            }

            if ($trade_moshi == 4) {
                $buy = null;
                $sell = array_reverse($mo->query('select id,price,sum(num-deal)as nums from zhisucom_trade where status=0 and type=2 and market =\'' . $market . '\' group by price order by price asc limit ' . $limt . ';'));
            }

            if ($buy) {
                foreach ($buy as $k => $v) {
                    $data['depth']['bids'][$k] = array(floatval($v['price'] * 1), floatval($v['nums'] * 1));
                }
            }
            else {
                $data['depth']['bids'] = '';
            }

            if ($sell) {
                foreach ($sell as $k => $v) {
                    $data['depth']['asks'][$k] = array(floatval($v['price'] * 1), floatval($v['nums'] * 1));
                }
            }
            else {
                $data['depth']['asks'] = '';
            }

            $data_getDepth[$market][$trade_moshi] = $data;
            S('getDepth', $data_getDepth);
        }
        else {
            $data = $data_getDepth[$market][$trade_moshi];
        }

        $tradeLog = M('TradeLog')->where(array('status' => 1,'market' => $market))->order('id desc')->limit(50)->select();

        if ($tradeLog) {
            foreach ($tradeLog as $k => $v) {
                $data['trades'][$k]['amount'] = round($v['num'], 6);
                $data['trades'][$k]['price'] = $v['price'] * 1;
                $data['trades'][$k]['tid'] = $v['id'];
                $data['trades'][$k]['time'] = $v['addtime'];
                $data['trades'][$k]['type'] = $v['type'];

            }
        }

        $datas['success']=true;
        $datas['data']=$data;


        if ($ajax) {

            exit(json_encode($datas));
        }
        else {
            return $datas;
        }
    }
    public function getTradelog($market = NULL, $ajax = 'json')
    {
        $data = (APP_DEBUG ? null : S('getTradelog' . $market));
        if (!$data) {
            $tradeLog = M('TradeLog')->where(array('status' => 1,'market' => $market))->order('id desc')->limit(50)->select();

            if ($tradeLog) {
                foreach ($tradeLog as $k => $v) {
                    $data['trades'][$k]['amount'] = round($v['num'], 6);
                    $data['trades'][$k]['price'] = $v['price'] * 1;
                    $data['trades'][$k]['tid'] = $v['id'];
                    $data['trades'][$k]['time'] = $v['addtime'];
                    $data['trades'][$k]['type'] = $v['type'];

                }

                S('getTradelog' . $market, $data);
            }
        }

        if ($ajax) {
            exit(json_encode($data));
        }
        else {
            return $data;
        }
    }
    public function getMarketSpecialtyJson()
    {
        // TODO: SEPARATE
        $input = I('get.');
        $market = (is_array(C('market')[$input['market']]) ? trim($input['market']) : C('market_mr'));
        $timearr = array(1, 3, 5, 10, 15, 30, 60, 120, 240, 360, 720, 1440, 10080);

        if (in_array($input['step'] / 60, $timearr)) {
            $time = $input['step'] / 60;
        }
        else {
            $time = 5;
        }

        $timeaa = (APP_DEBUG ? null : S('ChartgetMarketSpecialtyJsontime' . $market . $time));

        if (($timeaa + 60) < time()) {
            S('ChartgetMarketSpecialtyJson' . $market . $time, null);
            S('ChartgetMarketSpecialtyJsontime' . $market . $time, time());
        }

        $tradeJson = (APP_DEBUG ? null : S('ChartgetMarketSpecialtyJson' . $market . $time));

        if (!$tradeJson) {
            $tradeJson = M('TradeJson')->where(array(
                'market' => $market,
                'type'   => $time,
                'data'   => array('neq', ''),
            ))->order('id asc')->select();
            S('ChartgetMarketSpecialtyJson' . $market . $time, $tradeJson);
        }

        $json_data = $data = array();
        foreach ($tradeJson as $k => $v) {
            $json_data[] = json_decode($v['data'], true);
        }

        foreach ($json_data as $k => $v) {
            $data[$k][0] = $v[0];
            $data[$k][1] = 0;
            $data[$k][2] = 0;
            $data[$k][3] = $v[2];
            $data[$k][4] = $v[5];
            $data[$k][5] = $v[3];
            $data[$k][6] = $v[4];
            $data[$k][7] = $v[1];
        }

        exit(json_encode($data));
    }
}

?>