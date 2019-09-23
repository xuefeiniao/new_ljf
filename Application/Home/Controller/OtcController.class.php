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
            $ott = M('otc_trade')->field('remark')->where(array('id' => $v['tradeid']))->find();
            $lst[$k]['uname'] = $user['username'];
            $lst[$k]['names'] = strtoupper(explode('_', $v['coinname'])[0]);
            $mname = strtoupper(explode('_', $v['coinname'])[0]);
            $markets = $v['coinname'];
            $types = $v['type'];
            $remark = $ott['remark'];
        }

        $this->assign('mname', $mname);
        $this->assign('markets', $markets);
        $this->assign('types', $types);
        $this->assign('remark', $remark);
        $this->assign('list', $lst);
        $this->assign('page', $show);


        $this->display();
    }


    function bname($name){
        return strtoupper(explode('_', $name)[0]);
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
		
		$user=M('user')->where('id='.userid())->field('id,idcardauth,alipay,bankname,ddpay')->find();
		
		//dump($user);die;
        if($user['idcardauth']==0){
            $this->error('请先设置实名认证','/user/nameauth');
        }

        if($user['ddpay']==""){
			$this->error('请先设置支付类型','/otc/otcpay');
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

        $market = strtolower($market . '_bb');

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

        $oll = $ol->where(array('id' => $id))->find();//当前订单
        $ott = $ot->where(array('id' => $oll['tradeid']))->find();//trade订单
        if (($oll['status'] == -1) && ($ott['status'] == -1)) $this->error('无效订单');
        if ($uid == $ott['matchid']) $this->error('自己的订单,不允许买卖');

        $img = I('post.res');
        if (!$img) $this->error('请上传打款凭证');

        $num = $oll['num'];

        $remark = trim(I('post.re'));

        $type = I('post.ress');
        $types = $type == 1 ? '买入' : '卖出';

        $ttypes = $ott['type'] == 1 ? '买入' : '卖出';
        /*原订单*/
        $mbb = $ol->where('id=' . $id)->save(['name' => 'otc交易-' . $ott['market'] . '-' . $types, 'remark' => $remark, 'img' => $img, 'status' => 4, 'addtime' => time(), 'dktime' => time()]); //25

        if ($mbb){
			if($oll['type']==1){//买入订单
				$ilffid=$oll['id'];$lnum=$oll['num'];
				$olls = $ol->where("ilffid=$ilffid and num=$lnum status=2")->save(['status' => 4,'dktime' => time()]);
				if ($olls) {
					$this->success('提交成功');
				} else {
					$this->error('提交失败1');
				}
			}elseif($oll['type']==2){
				$ilffid=$oll['id'];
				$olls = $ol->where("ilffid=$ilffid and status=2")->save(['status' => 4,'dktime' => time()]);
				
				if ($olls) {
					$this->success('提交成功');
				} else {
					$this->error('提交失败2');
				}
			}
			
        }else{
            $this->error('提交失败3');
        }
    }

    public function porder()
    {
        $this->lst();

        $id = I('get.id');
        $oll = M('otc_log')->where(array('id' => $id))->find();
        $this->assign('oll',$oll);
        $this->display();
    }

    public function porder1()
    {
        $this->lst();

        $uid = userid();
        $id = I('get.id');

        $ot = M('otc_trade');
        $ol = M('otc_log');

        $oll = $ol->where(['id' => $id])->find();
        if (!$oll) {
            $this->error('交易订单不存在！');
        }

        if (($oll['status'] != 1) && ($oll['status'] != 4)) $this->error('订单已经处理过！');

        $ott = $ot->where(array('id' => $oll['tradeid']))->find();
        $oll = $ol->where(array('id' => $id))->find();

        if (($oll['status'] == -1) && ($ott['status'] == -1)) $this->error('无效订单');
        if ($uid == $ott['userid']) $this->error('自己的订单,不允许买卖');

        $num = I('post.num');
        if (!is_numeric($num)) $this->error('数量错误');
        if ($num > $ott['num']) $this->error('超出挂卖数量');

        $type = I('post.type');
        $types = $type == 1 ? '买入' : '卖出';
        $ttypes = $ott['type'] == 1 ? '买入' : '卖出';

        if ($num == $ott['num']) {

            $mbb = $ol->add(['userid' => $uid, 'matchid' => $ott['userid'], 'tradeid' => $ott['id'], 'ilffid' => $id, 'coinname' => $ott['market'], 'price' => $ott['price'], 'num' => $num, 'cprice' => $ott['price'] * $num, 'type' => $type, 'name' => 'otc交易-' . $ott['market'] . '-' . $types, 'stat' => 1, 'status' => 2, 'addtime' => time(),'mptime' => time()]);
            if (!$mbb) $this->error('匹配失败');

            $neword = $ol->add(['userid' => $ott['userid'], 'matchid' => $uid, 'tradeid' => $ott['id'], 'ilffid' => $mbb, 'coinname' => $ott['market'], 'price' => $ott['price'], 'cprice' => $ott['price'] * $num, 'num' => $num, 'type' => $ott['type'], 'name' => 'otc交易-' . $ott['market'] . '-' . $ttypes, 'stat' => 2, 'status' => 2, 'addtime' => $ott['addtime'],'mptime' => time()]);

            $oldord = $ol->where(['id' => $id])->save(['status' => -1, 'endtime' => time()]);

            if ($neword && $oldord) {
                $nnum = $ott['num'] - $num;
                $tradeup = $ot->save(['id' => $ott['id'], 'num' => $nnum, 'numd' => $num, 'status' => 2]);
                if ($tradeup) {
                    $this->success('匹配成功');
                } else {
                    $mbbs = $ol->where('id=' . $mbb)->save(['status' => -1]);
                    $newords = $ol->where('id=' . $neword)->save(['status' => -1]);
                    $oldords = $ol->where('id=' . $id)->save(['status' => 1]);
                    $this->error('匹配失败');
                }
            } else {
                $this->error('匹配失败');
            }

        } elseif ($num <= $ott['num']) {
            $mbb = $ol->add(['userid' => $uid, 'matchid' => $ott['userid'], 'tradeid' => $ott['id'], 'ilffid' => $id, 'coinname' => $ott['market'], 'price' => $ott['price'], 'num' => $num, 'cprice' => $ott['price'] * $num, 'type' => $type, 'name' => 'otc交易-' . $ott['market'] . '-' . $types, 'stat' => 1, 'status' => 2, 'addtime' => time(),'mptime' => time()]);
            if (!$mbb) $this->error('匹配失败');

            $neword = $ol->add(['userid' => $ott['userid'], 'matchid' => $uid, 'tradeid' => $ott['id'], 'ilffid' => $mbb, 'coinname' => $ott['market'], 'price' => $ott['price'], 'cprice' => $ott['price'] * $num, 'num' => $num, 'type' => $ott['type'], 'name' => 'otc交易-' . $ott['market'] . '-' . $ttypes, 'stat' => 2, 'status' => 2, 'addtime' => $ott['addtime'],'mptime' => time()]);

            $dqnum = $ott['num'] - $num;
            $neword1 = $ol->add(['userid' => $ott['userid'], 'matchid' => $uid, 'tradeid' => $ott['id'], 'ilffid' => $mbb, 'coinname' => $ott['market'], 'price' => $ott['price'], 'cprice' => $ott['price'] * $dqnum, 'num' => $dqnum, 'type' => $ott['type'], 'name' => 'otc交易-' . $ott['market'] . '-' . $ttypes, 'stat' => 2, 'status' => 1, 'addtime' => $ott['addtime'],]);

            $oldord = $ol->where(['id' => $id])->save(['status' => -1, 'endtime' => time()]);

            if ($neword && $neword1 && $oldord) {
                $tradeup = $ot->save(['id' => $ott['id'], 'num' => $dqnum, 'numd' => $num, 'status' => 1]);
                if ($tradeup) {
                    $this->success('匹配成功');
                } else {
                    $mbbs = $ol->where('id=' . $mbb)->save(['status' => -1]);
                    $newords = $ol->where('id=' . $neword)->save(['status' => -1]);
                    $neword1s = $ol->where('id=' . $neword1)->save(['status' => -1]);
                    $oldords = $ol->where('id=' . $id)->save(['status' => 1]);
                    $this->error('匹配失败');
                }
            } else {
                $this->error('匹配失败');
            }
        }
        $this->display();
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

    public function otclst(){
		//dump($_SESSION);die;
        if (!userid()) {
            redirect('/#login');
        }
        $uid = userid();
        $where['sid'] = $uid;
        $where['bid'] = $uid;
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        // $map['is_done'] = 0;
        // $map['is_get'] = 0;
        $map['is_cancel'] = 1;
        $not_pay = M('c2c_trade')->where($map)->order('paytime desc')->select();
        // dump($not_pay);die;
        $mmp['_complex'] = $where;
        $mmp['is_done'] = 1;
        $okpay = M('c2c_trade')->where($mmp)->order('paytime desc')->select();
        $ppp['_complex'] = $where;
        $ppp['is_done'] = 0;
        // $ppp['is_get'] = 0;
        // $ppp['is_pay'] = 0;
        $ppp['is_cancel'] = 0;
        $no_pay = M('c2c_trade')->where($ppp)->order('paytime desc')->select();//dump($no_pay);die;
        $this->assign('notpay',$not_pay);
        $this->assign('okpay',$okpay);
        $this->assign('no_pay',$no_pay);


        $this->display();
    }

    public function otclst109($status = null, $type = null)
    {


         if (!userid()) {
        //  $this->ajaxReturn(array("status"=>0,"msg"=>"请先登录帐号"));
            redirect('/#login');
        }           
            $user = M('User')->where(array('id' => userid()))->find();
            $stat = $user['isshop'];        
            $uid=userid();


            $static=I('curr_static');
            $type=I('curr_type');
            $where['is_pay']=1;
            $str="bid=".$uid." or sid=".$uid;
            if($type==1){$str="bid=".$uid;}                 //买入
            if($type==2){$str="sid=".$uid;}                 //卖出
            if($static==1){$where['is_pay']=1;$where['is_done']=0;}             //当前订单
            if($static==2){$where['is_done']=1;}            //已完成
            if($static==3){$where['is_cancel']=1;}          //已取消
            if($static==4){$where['is_ts']=2;}              //投诉订单                  


            $Moble=M('c2c_trade');
            $count = $Moble->where($where)->where($str)->count();
            $Page = new \Think\Page($count, 15);
            $show = $Page->show();
            $list = $Moble->where($where)->where($str)->order('paytime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
            // dump($list);die;
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->assign('uid',$uid);
            $this->assign('status',$static);
            $this->assign('type',$type);
            $this->display();
            exit;
        // if (!userid()) {
        //     redirect('/#login');
        // }

        // $uid=userid();
        // $where['userid'] = $uid;

        // if ($status) {
        //     $where['status'] = $status;
        // } else {
        //     $where['status'] = array('not in', '-1,11');
        // }
        // if ($type) {
        //     $where['type'] = $type;
        // }

        // $count = M('otc_log')->where($where)->count();
        // $Page = new \Think\Page($count, 10);
        // $show = $Page->show();
        // $lst = M('otc_log')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        // foreach ($lst as $k => $v) {
        //     $lst[$k]['names'] = strtoupper(explode('_', $v['coinname'])[0]);
        //     if (($v['type']==1) && ($v['status']==2)){
        //         $lst[$k]['s1']='已匹配待打款';
        //     }elseif(($v['type']==2) && ($v['status']==2)){
        //         $lst[$k]['s1']='等待对方打款';
        //     }
        //     if (($v['type']==1) && ($v['status']==4)){
        //         $lst[$k]['s1']='已打款,等待商家确认';
        //     }elseif(($v['type']==2) && ($v['status']==4)){
        //         $lst[$k]['s1']='已打款待确认';
        //     }
            
        // }
        // // dump($list);die;
        // $this->assign('uid', $uid);
        // $this->assign('list', $lst);
        // $this->assign('page', $show);
        // $this->display();
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

        if ($info['status'] == 1) {
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
        $ol = M('otc_log');
        $info = $ol->where('id=' . $id)->find();//当前订单
		

        if (!$info) {
            $this->error('交易订单不存在！');
        }

        $infos = $ol->where('ilffid='.$info['id'])->find();//对方订单
        
		$this->assign('infos', $infos);
		
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
        if (!userid()) {
            redirect('/#login');
        }

        $id = intval(I('get.id'));

        $info = M('otc_log')->where(array('id' => $id))->find();
        $user = M('user')->where(array('id' => $info['matchid']))->find();
        $remark = M('otc_trade')->where(array('id' => $info['tradeid']))->find();

        if (!$info) {
            $this->error('交易订单不存在！');
        }
        $types=$info['type']==1?'买入':'卖出';

        $this->assign('mobile', $user['moble']);
        $this->assign('types', $types);
        $this->assign('names', strtoupper(explode('_', $info['coinname'])[0]));
        $this->assign('info', $info);
        $this->assign('remark', $remark['remark']);
		$this->assign('s3', $user);
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

        $ol = M('otc_log');
        $ot = M('otc_trade');
        $info = $ol->where(['id' => $id])->find();//2
        if (!$info) {
            $this->error('交易订单不存在！');
        }

        if ($info['status'] != 4) {
            $this->error('订单已经处理过！');
        }

        $infos = $ol->where(['id' => $id])->save(['status' => 5, 'endtime' => time()]);//2
        if (!$infos) $this->error('确认失败');

		
		$whe['ilffid']=$info['id'];
		$whe['stauts']=array('neq',-1);
        $oll = $ol->where($whe)->find();//11

        $olls = $ol->where(['id' => $oll['id']])->save(['status' => 5, 'endtime' => time()]);
        if ($oll && $olls) {
            $this->success('确认成功');
        } else {
            $this->error('交易订单不存在');
        }

        $ott = $ol->where(['id' => $info['tradeid']])->find();//2
        $otts = $ol->where(['id' => $ott['id']])->save(['status' => 5, 'numb' => 0, 'endtime' => time()]);
        if ($ott && $otts) {
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
			if ($info['type']==2) {
                $userpay = M('user')->where(array('id' => $info['userid']))->find();
            }
			
			$this->assign('s3',$userpay);
			
			
            $infos = M('otc_log')->where(array('id' => $info['ilffid']))->find();
            $remark = M('otc_trade')->where(array('id' => $info['tradeid']))->find(); 
            $user = M('user')->where(array('id' => $infos['userid']))->find();

            $this->assign('names', strtoupper(explode('_', $info['coinname'])[0]));
            $this->assign('info', $info);
            $this->assign('remark', $remark['remark']);
            $this->assign('s1', $remark['userid']);
            $this->assign('s2', $user['moble']);
			
        }
        $this->display();
    }

    public function mmset1()
    {
        $id = I('post.id');
        $remark = I('post.re');
        $find=M('otc_log')->where('id='.$id)->find();
        if (!$find) $this->error('修改失败');

        $up = M('otc_trade')->where(array('id' => $find['tradeid']))->save(array('remark' => $remark, 'status' => 1));

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
	public function otcpay()
    {
		if (!userid()) {
			redirect('/#login');
		}
		$pay=M('user')->where('id='.userid())->field(['id,alipay,bankname,ddpay,ddpayname,ddmobile'])->find();
		
	
		$this->assign('s1',$pay);
		$this->display();
	}
	
	public function upbank($bankname,$ddmobile,$ddpay,$ddpayname)
	{
		
		if (!userid()) {
			redirect('/#login');
		}
		
		if (!check($ddpay, 'd')) {
			$this->error('银行账号格式错误！');
		}
		
		if(strlen($ddpay) < 16 || strlen($ddpay) > 19){
			
			$this->error('银行账号格式错误！');
			
		}
		
		$uid=userid();
		$up=M('user')->where('id='.$uid)->fetchsql(true)->save(['ddpayname' => $ddpayname, 'ddpay' => $ddpay,'bankname'=>$bankname,'ddmobile'=>$ddmobile]);
		
		if ($up) {
			$this->success('添加成功！');
		}
		else {
			$this->error('添加失败！');
		}
	}

}

?>