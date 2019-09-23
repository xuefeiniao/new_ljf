<?php
namespace Home\Controller;

class OtcController extends HomeController {
    public function index()
    {
        $this->lst();

        if (I("get.market")) {
            $where['coinname'] = I("get.market");
        } else {
            $where['coinname'] = 'btc_cny';
        }
        if (I("get.type")) {
            $where['type'] = I("get.type");
        } else {
            $where['type'] = 1;
        }

        $where['status'] = array('eq', 1);

        $count = M('otc_log')->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $lst = M('otc_log')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($lst as $k => $v) {
            $user = M('user')->field('id,username')->where(array('id' => $v['userid']))->find();
            $lst[$k]['uname'] = $user['username'];
            $lst[$k]['names'] = strtoupper(explode('_', $v['coinname'])[0]);
            $mname = strtoupper(explode('_', $v['coinname'])[0]);
            $markets = $v['coinname'];
            $types = $v['type'];
        }

        $this->assign('mname', $mname);
        $this->assign('markets', $markets);
        $this->assign('types', $types);
        $this->assign('list', $lst);
        $this->assign('page', $show);


        $this->display();
    }

    public function lst()
    {

        $m = M('market');
        $rmbjy = $m->where(array('status' => 1, 'jiaoyiqu' => 0))->order('name')->select();

        foreach ($rmbjy as $k => $v) {
            $rmbjy[$k]['names'] = strtoupper(explode('_', $v['name'])[0]);
        }

        $this->assign('rmbjy', $rmbjy);
    }

    public function info($market = null)
    {
        $this->lst();
        $this->display();
    }

    public function upTrade($market = null, $price, $num, $type)
    {

        if (!userid()) {
            $this->error('请先登录！');
        }

        /* if (C('market')[$market]['begintrade']) {
             $begintrade = C('market')[$market]['begintrade'];
         } else {
             $begintrade = "00:00:00";
         }

         if (C('market')[$market]['endtrade']) {
             $endtrade = C('market')[$market]['endtrade'];
         } else {
             $endtrade = "23:59:59";
         }


         $trade_begin_time = strtotime(date("Y-m-d") . " " . $begintrade);
         $trade_end_time = strtotime(date("Y-m-d") . " " . $endtrade);
         $cur_time = time();

         if ($cur_time < $trade_begin_time || $cur_time > $trade_end_time) {
             $this->error('当前市场禁止交易,交易时间为每日' . $begintrade . '-' . $endtrade);
         }*/

        if (!check($price, 'double')) {
            $this->error('交易价格格式错误');
        }

        if (!check($num, 'double')) {
            $this->error('交易数量格式错误');
        }

        if (($type != 1) && ($type != 2)) {
            $this->error('交易类型格式错误');
        }

        if (!$this->check($market)) {
            $this->error('交易市场错误');
        }

        $market = strtolower($market . '_cny');

        $data = ['userid' => userid(), 'market' => $market, 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'numy' => $num, 'type' => $type, 'name' => 'otc交易', 'addtime' => time(), 'status' => 1, 'stat' => 1];


        $trade = M('otc_trade')->add($data);
        if (!$trade) {
            $this->error('订单提交失败');
        }

        $datalog = ['userid' => userid(), 'tradeid' => $trade, 'coinname' => $market, 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'name' => 'otc交易', 'addtime' => time(), 'status' => 1];
        $tradelog = M('otc_log')->add($datalog);

        if ($tradelog) {
            $this->success('订单提交成功');
        } else {
            $this->error('订单提交失败');
        }
    }

    public function upTrade1()
    {
        if (!($uid = userid())) {
            $this->error('您没有登录请先登录！');
        }

        $ot = M('otc_trade');
        $ol = M('otc_log');

        $id = I('post.id');
        if (!is_numeric($id)) $this->error('无效订单');

        $oll = $ol->where(array('id' => $id))->find();
        $ott = $ot->where(array('id' => $oll['tradeid']))->find();
        if (($oll['status'] == -1) && ($ott['status'] == -1)) $this->error('无效订单');
        if ($uid == $ott['userid']) $this->error('自己的订单,不允许买卖');

        $img = I('post.res');
        if (!$img) $this->error('请上传打款凭证');

        $num = I('post.numm');
        if (!is_numeric($num)) $this->error('数量错误');
        if ($num > $ott['num']) $this->error('超出挂卖数量');

        $remark = trim(I('post.re'));

        if ($num == $ott['num']) {
            $up = $ol->add(['userid' => $ott['userid'], 'matchid' => $uid, 'tradeid' => $ott['id'], 'ilffid' => $oll['id'], 'coinname' => $oll['coinname'], 'price' => $ott['price'], 'cprice' => $ott['price'] * $num, 'num' => $num, 'type' => 1, 'name' => 'otc交易-' . $ott['market'] . '买入', 'remark' => $remark, 'img' => $img, 'stat' => 1, 'status' => 4, 'addtime' => $ott['addtime'], 'dktime' => time(),]);
            $up1 = $ol->where(['id' => $id])->save(['status' => -1, 'endtime' => time()]);

            if ($up && $up1) {
                $nnum = $ott['num'] - $num;
                $tradeup = $ot->save(['id' => $ott['id'], 'num' => $nnum, 'numd' => $num, 'status' => 2]);
                if ($tradeup) {
                    $this->success('提交成功');
                } else {
                    $this->error('提交失败');
                }
            } else {
                $this->error('提交失败');
            }

        } elseif($num <= $ott['num']) {
            $dqnum = $ott['num'] - $num;
            $up = $ol->add(['userid' => $ott['userid'], 'matchid' => $uid, 'tradeid' => $ott['id'], 'ilffid' => $oll['id'], 'coinname' => $oll['coinname'], 'price' => $ott['price'], 'cprice' => $ott['price'] * $num, 'num' => $num, 'type' => 1, 'name' => 'otc交易-' . $ott['market'] . '买入', 'remark' => $remark, 'img' => $img, 'stat' => 1, 'status' => 4, 'addtime' => $ott['addtime'], 'dktime' => time(),]);

            $up1 = $ol->add(['userid' => $ott['userid'], 'tradeid' => $ott['id'], 'ilffid' => $oll['id'], 'coinname' => $oll['coinname'], 'price' => $ott['price'], 'cprice' => $ott['price'] * $num, 'num' => $dqnum, 'type' => 2, 'name' => 'otc交易-' . $ott['market'] . '订单拆分', 'stat' => 1, 'status' => 1, 'addtime' => $ott['addtime']]);

            $up2 = $ol->where(['id' => $id])->save(['status' => -1, 'endtime' => time()]);

            if ($up && $up1 && $up2) {
                $tradeup = $ot->save(['id' => $ott['id'], 'num' => $dqnum, 'numd' => $num, 'status' => 1]);

                if ($tradeup) {
                    $this->success('提交成功');
                } else {
                    $this->error('提交失败');
                }
            } else {
                $this->error('提交失败');
            }

            $this->error('提交失败');
        }
    }

    function check($val)
    {
        /*if (!userid()) {
            $this->error('请先登录！');
        }*/

        $m = M('market');
        $rmbjy = $m->where(array('status' => 1, 'jiaoyiqu' => 0))->order('name')->select();

        foreach ($rmbjy as $k => $v) {
            $rmbjy[$k]['names'] = strtoupper(explode('_', $v['name'])[0]);
            $bblst[] = $rmbjy[$k]['names'];
        }

        return in_array(strtoupper($val), $bblst) ? true : false;
    }

    public function setpay()
    {
        /*if (!userid()) {
            $this->error('请先登录！');
        }*/

        $myczType = M('MyczType')->where(array('status' => 1))->select();

        foreach ($myczType as $k => $v) {
            $myczTypeList[$v['name']] = $v['title'];
        }

        $this->assign('myczTypeList', $myczTypeList);
        $this->display();
    }

    public function otclst($status = null, $type = null)
    {
        if (!userid()) {
            redirect('/#login');
        }

        $uid = userid();
        $where['_query'] = "userid=$uid&matchid=$uid&_logic=or";

        if ($status) {
            $where['status'] = $status;
        } else {
            $where['status'] = array('neq', '-1');
        }
        if ($type) {
            $where['type'] = $type;
        }

        $count = M('otc_log')->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $lst = M('otc_log')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($lst as $k => $v) {
            $lst[$k]['names'] = strtoupper(explode('_', $v['coinname'])[0]);
            if (($v['matchid'] == $uid) && ($v['type'] == 1)) {
                $lst[$k]['tn'] = '买入';
            } else {
                $lst[$k]['tn'] = '卖出';
            }
        }

        $this->assign('uid', $uid);
        $this->assign('list', $lst);
        $this->assign('page', $show);
        $this->display();
    }

    public function chexiao($id = null)
    {
        /*if (!userid()) {
            $this->error('请先登录！');
        }*/

        if (!check($id, 'd')) {
            $this->error('参数错误！');
        }

        $ol = M('otc_log');
        $info = $ol->where(['id' => $id])->find();
        if (!$info) {
            $this->error('交易订单不存在！');
        }

        if (($info['status'] != 1) && ($info['status'] != 4)) {
            $this->error('订单已经处理过！');
        }

        if ($info['status']==1){
            $rs = $ol->where(array('id' => $id))->save(array('status' => 11));
        }

        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }

    public function orderlog()
    {
        /*if (!userid()) {
            $this->error('请先登录！');
        }
        $user = M('user')->where(array('userid' => userid()))->find();

        if (!$user['idcardauth']) {
            $this->error('请先实名认证','user/nameauth');
            $this->redirect();
        }*/

        $id = intval(I('get.id'));

        $info = M('otc_log')->where(array('id' => $id))->find();

        if (!$info) {
            $this->error('交易订单不存在！');
        }

        $match = M('user')->where(['id' => $info['matchid']])->find();

        $this->assign('uid', userid());
        $this->assign('match', $match);
        $this->assign('names', strtoupper(explode('_', $info['coinname'])[0]));
        $this->assign('info', $info);


        $this->display();
    }
    public function orderlogw()
    {
        $id = intval(I('get.id'));

        $info = M('otc_log')->where(array('id' => $id))->find();

        if (!$info) {
            $this->error('交易订单不存在！');
        }

        $match = M('user')->where(['id' => $info['matchid']])->find();

        $this->assign('uid', userid());
        $this->assign('match', $match);
        $this->assign('names', strtoupper(explode('_', $info['coinname'])[0]));
        $this->assign('info', $info);


        $this->display();
    }

    public function orderbuy()
    {
        if (!userid()) {
            redirect('/#login');
        }

        $id = intval(I('get.id'));

        $info = M('otc_log')->where(array('id' => $id))->find();
        $user = M('user')->where(array('id' => $info['userid']))->find();

        if (!$info) {
            $this->error('交易订单不存在！');
        }

        $this->assign('mobile', $user['moble']);
        $this->assign('names', strtoupper(explode('_', $info['coinname'])[0]));
        $this->assign('info', $info);
        $this->display();
    }

    public function ordersell()
    {
        $this->orderlog();
        $this->display();
    }

    public function upimg()
    {

//        if (!userid()) {
//            $this->error('请先登录！');
//        }
//        $user = M('user')->where(array('userid' => userid()))->find();
//
//        if (!$user['idcardauth']) {
//            $this->error('请先实名认证');
//            $this->redirect('user/nameauth');
//        }


        $upload = new \Think\Upload();
        $upload->maxSize = 2048000;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath = './Upload/idcard/';
        $upload->autoSub = false;
        $info = $upload->upload();

        if (!$info) {// 上传错误提示错误信息
//            $this->error($upload->getError());
            $this->error('图片上传失败');
        } else {// 上传成功
            foreach ($info as $file) {
                $path = $file['savePath'] . $file['savename'];
            }

            echo $path;

        }


        exit();

    }


    public function qrdk($id = null)
    {
        if (!check($id, 'd')) {
            $this->error('参数错误！');
        }

        $ol = M('otc_log'); $ot = M('otc_trade');
        $info = $ol->where(['id' => $id])->find();
        if (!$info) {
            $this->error('交易订单不存在！');
        }

        if ($info['status'] != 4) {
            $this->error('订单已经处理过！');
        }

        $ott=$ot->where(['id'=>$info['tradeid']])->find();
        $rs = $ol->where(['id' => $id])->save(['status' => 5, 'endtime' => time()]);
        if ($ott['num']==0){
            $upst = $ot->where(['id' => $info['tradeid']])->save(['status' => 5, 'endtime' => time()]);
        }else{
            $upst = $ot->where(['id' => $info['tradeid']])->save(['status' => 1]);
        }

        if ($rs && $upst) {
            $this->success('确认成功');
        } else {
            $this->error('确认失败');
        }
    }

    public function mmset()
    {
        $id = intval(I('get.id'));

        if ($id) {
            $info = M('otc_log')->where(array('id' => $id))->find();
            if (!$info) {
                $this->error('交易订单不存在！');
            }
            $this->assign('names', strtoupper(explode('_', $info['coinname'])[0]));
            $this->assign('info', $info);
        }
        $this->display();
    }

    public function mmset1()
    {
        $id = I('post.id');
        $remark = I('post.re');
        $up = M('otc_log')->where(array('id' => $id))->save(array('remark' => $remark, 'status' => 1));

        if ($up) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    public function t()
    {

//        $where['status'] = array('neq', 11);
//
//        $lst = M('otc_log')->where($where)->select();
//
//        foreach ($lst as $k => $v) {
//            $user = M('user')->field('id,username')->where(array('id' => $v['userid']))->find();
//            $lst[$k]['uname']=$user['username'];
////            dump($user);
//        }
//
//        dump($lst);

        $where['status'] = array('neq', 11);
        $count = M('otc_log')->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $lst = M('otc_log')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($lst as $k => $v) {
            $user = M('user')->field('id,username')->where(array('id' => $v['userid']))->find();
            $lst[$k]['uname'] = $user['username'];
            $lst[$k]['names'] = strtoupper(explode('_', $v['coinname'])[0]);
            $mname = strtoupper(explode('_', $v['coinname'])[0]);
            $markets = $v['coinname'];
            $types = $v['type'];
        }
        dump($lst);
    }


}

?>