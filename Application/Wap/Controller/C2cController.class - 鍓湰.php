<?php
namespace Home\Controller;

class C2cController extends HomeController {
    public function index()
    {
        if (userid()) {
            $user = M('User')->where(array('id' => userid()))->find();
            $stat = $user['isshop'];
        }


        $this->assign('user',$user);
        $this->assign('stat', $stat);
        $this->display();
    }

    public function c2clst($market = NULL, $type = NULL, $status = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}

		$uid=userid();
		$c2c_log = M('c2c_log')->where("status!=-1 and userid=$uid")->select();

	
		$this->assign('c2c_log', $c2c_log);

		if (!$market_list[$market]) {
			$market = $Market[0]['name'];
		}

		$where['market'] = $market;

		if (($type == 1) || ($type == 2)) {
			$where['type'] = $type;
		}

		if (($status == 1) || ($status == 2) || ($status == 3)) {
			$where['status'] = $status - 1;
		}

		$where['userid'] = userid();
		$this->assign('market', $market);
		$this->assign('type', $type);
		$this->assign('status', $status);
		$Moble = M('Trade');
		$count = $Moble->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$Page->parameter .= 'type=' . $type . '&status=' . $status . '&market=' . $market . '&';
		$show = $Page->show();
		$list = $Moble->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['num'] = $v['num'] * 1;
			$list[$k]['price'] = $v['price'] * 1;
			$list[$k]['deal'] = $v['deal'] * 1;
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	
	public function mmset($id = NULL)
	{
		$info=M('c2c_log')->where('id='.$id)->find();
		
		$this->assign('info',$info);
		
		if($info['matchid']){
			$user=M('user')->where('id='.$info['matchid'])->find();
			$this->assign('user',$user);
		}
		
		$this->display();
	}
		
	public function Huikuan($id = NULL)
	{
		$cl=M('c2c_log');
		$info=$cl->where("id=$id")->find();
		
		if(($info['status']==2)&&($info['stat']=1)){
			$info=$cl->where('id='.$id)->save(['status'=>3,'dktime'=>time()]);
			$this->success('提交成功');
		}elseif(($info['status']==2)&&($info['stat']=2)){
			$info=$cl->where('id='.$id)->save(['status'=>3,'endtime'=>time()]);
			$this->success('提交成功');
		}
		
		$this->display();
	}
	
    public function chexiao($id = null)
    {
        
        $ol = M('c2c_log');
        $info = $ol->where(['id' => $id])->find();
        if (!$info) {
            $this->error('交易订单不存在！');
        }

        if (($info['status'] != 1) && ($info['status'] != 4)) {
            $this->error('订单已经处理过！');
        }

        if ($info['status'] == 1) {
            $rs = $ol->where(array('id' => $id))->save(array('status' => -1));
        }

        if ($rs) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败！');
        }
    }
    public function ordersell()
    {
        if (!userid()) {
            redirect('/#login');
        }

        $id = intval(I('get.id'));

        $info = M('c2c_log')->where(array('id' => $id))->find();
        $user = M('user')->where(array('id' => $info['matchid']))->find();
        $remark = M('otc_trade')->where(array('id' => $info['tradeid']))->find();

        if (!$info) {
            $this->error('交易订单不存在！');
        }
        $types=$info['type']==1?'买入':'卖出';

        $this->assign('mobile', $user['moble']);
        $this->assign('types', $types);
        $this->assign('names', strtoupper($info['market']));
        $this->assign('info', $info);
        $this->assign('remark', $remark['remark']);
        $this->display();
    }

    ///有问题
    public function mmset1()
    {
        $id = intval(I('get.id'));

        if ($id) {
            $info = M('c2c_log')->where(array('id' => $id))->find();
            if (!$info) {
                $this->error('交易订单不存在！');
            }

            $user=M('user')->where("id=".$info['matchid'])->find();
            $this->assign('names', strtoupper($info['market']));
            $this->assign('info', $info);
            $this->assign('s1', $user['userid']);
            $this->assign('s2', $user['moble']);
        }
        $this->display();
    }

    public function upshop(){
        $id=userid();
        $user = M('user')->where('id=' . $id)->find();

        if ($user['isshop'] == 1){
            $this->error('已加入商户');
        }elseif ($user['isshop'] == 0) {
            $up = M('user')->where('id=' . $id)->save(['isshop' => -1]);
            $this->success('申请成功');
        }


    }
    public function add()
    {
        if (!userid()) {
			redirect('/#login');
		}

        $uid = userid();

		$user=M('user')->where('id='.$uid)->field('id,idcardauth,alipay,bankname,ddpay')->find();

        if($user['idcardauth']==0){
           // $this->error('实名认证尚未通过，请认证后再来。','/user/nameauth',5);
		  
        }

		if($user['alipay']==""){
			$this->error('请先设置C2C支付和收款方式','/otc/otcpay');
		}

        if (C('market')[$market]['begintrade']) {
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
        }

        $user = M('User')->where(array('id' => userid()))->find();
		$ucoin = M('user_coin')->where("userid=".$uid)->find();

        if ($user && $user['isshop']) {
            $num = I('post.num');
            if ($num == null) $this->error('请填写数量');
            if (!is_numeric($num)) $this->error('请填写正确的数量');

            $price = I('post.price');
            if ($price == null) $this->error('请填写数量');
            if (!is_numeric($price)) $this->error('请填写正确的数量');

            $type = I('post.type');
            $types = $type == 1 ? '买入' : '卖出';
			
			$total = round($num*$price,8);


            $ctm = M('c2c_trade');
            $clm = M('c2c_log');

            if ($type == 2) {
				
                //插入卖出记录trade
				
				
				
				if ($ucoin['bb']<$total) 
				{
					$this->error('BB余额不足，暂无法卖出');
					exit;
				}
				
				//匹配商家
				
				$buyshop = M('User')->where("id>0 and isshop=1")->select();
				
				if (!$buyshop)
				{
					$this->error('暂没有合适的订单可匹配，请稍后再试');
					exit;
				}
			

                foreach ($buyshop as $bv) {
                    $bid[] = $bv['id'];
                }
                $bids = $bid[array_rand($bid, 1)];
				$this->error($bids);
				
                $ct = $ctm->add(['userid' => userid(), 'market' => 'bb', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'stat' => 2, 'status' => 1, 'addtime' => $cur_time,]);

               // if (!$ct) $this->error('提交失败');

                //生成交易订单log
               // $cl = $clm->add(['userid' => userid(), 'tradeid' => $ct, 'market' => 'bb', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'name' => 'c2c-bb商户' . $types . '交易', 'stat' => 2, 'status' => 1, 'addtime' => $cur_time,]);

                if ($ct) {
					//扣除bb余额
					$newbb = round($ucoin['bb']-$total,8);
					M('user_coin')->where("userid=".$uid)->setField('bb',$newbb);
                    $this->success('提交成功','/C2c/c2clst');
					
                } else {
                    $cts = $ctm->where('id=' . $ct)->save(['status' => -1]);
                    $this->error('提交失败');
                }
            } elseif ($type == 1) 
			
			{
				$buyshop = M('User')->where("id>0 and isshop=1")->select();
				foreach($buyshop as $bp)
				{
					$ucoin = M('user_coin')->where("userid=".$bp['id']." and bb>".$total)->find();
					if($ucoin['id']>0)
					{
						$cid = $bp['id'];
						break;
					}
				}
				
				$buyt = $ctm->add(['userid' => userid(), 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'stat' => 1, 'status' => 2, 'addtime' => $cur_time,]);
				if ($buyt) {
                    $this->success('提交成功','/C2c/c2clst');
					
                } else {
                    $cts = $ctm->where('id=' . $buyt)->save(['status' => -1]);
                    $this->error('提交失败');
                }
				
			}
			
			/*
			{
			
                $find = $clm->where("status=1 and type=2 and price<=$price and num>=$num and userid!=$uid and s1=0")->select();//找log订单


                if (!$find) $this->error('没有合适的订单');

                foreach ($find as $v) {
                    $fid[] = $v['id'];
                }
                $fids = $fid[array_rand($fid, 1)];

                $sell = $clm->where('id=' . $fids)->find();//匹配订单
                $sells1 = $clm->where('id=' . $sell['id'])->save(['s1' => 1]);

                $selltype = $sell['type'] == 1 ? '买入' : '卖出';

                if ($num == $sell['num']) {

                    //买委托订单
                    $buyt = $ctm->add(['userid' => userid(), 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'stat' => 1, 'status' => 2, 'addtime' => $cur_time,]);

                    if (!$buyt) $this->error('提交失败');

                    //买交易订单
                    $buyl = $clm->add(['userid' => userid(), 'matchid' => $sell['userid'], 'tradeid' => $buyt, 'ppid' => $sell['id'], 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'name' => 'c2c-bb商户' . $types . '交易', 'stat' => 1, 'status' => 2, 'addtime' => $cur_time, 'mptime' => time()]);

                    if ($buyl){
                        //修了卖交易记录
                        $sells = $clm->where('id=' . $sell['id'])->save(['matchid' => userid(), 'ppid' => $buyl,'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'status' => 2,'mptime' => time()]);
                        $sellts=$ctm->where('id=' . $sell['id'])->save(['status'=>2]);
                        if ($sells && $sellts) {
                            $this->success('提交成功');
                        }else{
                            $buylrollbak = $clm->where('id=' . $buyl)->save(['status' => -1]);
                            $buytrollbak = $ctm->where('id=' . $buyt)->save(['status' => -1]);
                            $selllsrollbak = $ctm->where('id=' . $sell['id'])->save(['status' => 1]);
                            $this->error('提交失败');
                        }
                    }else{
                        $buyts = $ctm->where('id='.$buyt)->save(['status'=>-1]);
                        $this->error('提交失败');
                    }

                } elseif ($num <= $sell['num']) {

                    $curnum = $sell['num'] - $num;

                    $buynewt = $ctm->add(['userid' => userid(), 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'stat' => 1, 'status' => 2, 'addtime' => $cur_time,]);

                    $buynewl = $clm->add(['userid' => userid(), 'tradeid' => $buynewt, 'ppid' => $sell['id'], 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $type, 'name' => 'c2c-usdt商户' . $types . '交易', 'stat' => 1, 'status' => 2, 'addtime' => $cur_time, 'mptime' => time()]);

                    if ($buynewt && $buynewl) {
                        $sellnew = $clm->add(['userid' => $sell['userid'], 'matchid' => userid(), 'tradeid' => $sell['tradeid'], 'ppid' => $buynewl, 'market' => 'usdt', 'price' => $price, 'cprice' => $price * $num, 'num' => $num, 'type' => $sell['type'], 'name' => 'c2c-usdt商户' . $selltype . '交易', 'stat' => $sell['stat'], 'status' => 2, 'addtime' => $cur_time, 'mptime' => time()]);echo $sellnew;
                        $selllow=$clm->where('id='.$sell['id'])->save(['numd'=>$num,'num'=>$curnum,'s1'=>0]);
                        if ($sellnew && $selllow) {
                            $buynewls=$clm->where('id='.$buynewl)->fetchSql(true)->save(['matchid' => $sellnew]);
                            echo $buynewls;die;
                            $this->success('提交成功');
                        }else{
                            $buynewtrobk=$ctm->where('id='.$buynewt)->save(['status'=>-1]);
                            $buynewltrobk=$ctm->where('id='.$buynewl)->save(['status'=>-1]);
                            $selllowrobk=$ctm->where('id='.$sell['id'])->save(['stat'=>0]);
                            $this->error('提交失败');
                        }
                    } else {
                        $sellyrobk=$ctm->where('id='.$sell['id'])->save(['stat'=>0]);
                        $this->error('提交失败');
                    }

                }
            }
			*/
        }
    }
}
