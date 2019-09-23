<?php
namespace Admin\Controller;

class TradeController extends AdminController
{
	public function index($field = NULL, $name = NULL, $market = NULL, $status = NULL)
	{
		//$this->checkUpdata();
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($market) {
			$where['market'] = $market;
		}


		$where['userid'] = array('gt',0);




		if ($status) {
			$where['status'] = $status;
		}

		if($status == 0 && $status != null){
			$where['status'] = 0;
		}




		$count = M('Trade')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Trade')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function chexiao($id = NULL)
	{
		$rs = D('Trade')->chexiao($id);

		if ($rs[0]) {
			$this->success($rs[1]);
		}
		else {
			$this->error($rs[1]);
		}
	}

	public function log($field = NULL, $name = NULL, $market = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else if ($field == 'peername') {
				$where['peerid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}


		$where['userid'] = array('gt',0);

		if ($market) {
			$where['market'] = $market;
		}

		$count = M('TradeLog')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('TradeLog')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
			$list[$k]['peername'] = M('User')->where(array('id' => $v['peerid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function chat($field = NULL, $name = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		$count = M('Chat')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Chat')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function chatStatus($id = NULL, $type = NULL, $moble = 'Chat')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function comment($field = NULL, $name = NULL, $coinname = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($coinname) {
			$where['coinname'] = $coinname;
		}

		$count = M('CoinComment')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('CoinComment')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function commentStatus($id = NULL, $type = NULL, $moble = 'CoinComment')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function market($field = NULL, $name = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		$count = M('Market')->where($where)->count();
		$Page = new \Think\Page($count, 100);
		$show = $Page->show();
		$list = M('Market')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach($list as $k=>$v){
			if($v['begintrade']){
				$begintradeqq_3479015851 = substr($v['begintrade'],0,5);
			}else{
				$begintradeqq_3479015851 = "00:00";
			}
			if($v['endtrade']){
				$endtradeqq_3479015851 = substr($v['endtrade'],0,5);
			}else{
				$endtradeqq_3479015851 = "23:59";
			}


			$list[$k]['tradetimezhisucom'] = $begintradeqq_3479015851."-".$endtradeqq_3479015851;
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function marketEdit($id = NULL)
	{


		$zhisucom_getCoreConfig = zhisucom_getCoreConfig();
		if(!$zhisucom_getCoreConfig){
			$this->error('核心配置有误');
		}



		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = array();

				$beginshi = "00";
				$beginfen = "00";
				$endshi = "23";
				$endfen = "59";

			}
			else {
				$market_zhisucom = M('Market')->where(array('id' => $id))->find();
				$this->data = $market_zhisucom;

				if($market_zhisucom['begintrade']){
					$beginshi = explode(":",$market_zhisucom['begintrade'])[0];
					$beginfen = explode(":",$market_zhisucom['begintrade'])[1];
				}else{
					$beginshi = "00";
					$beginfen = "00";
				}

				if($market_zhisucom['endtrade']){
					$endshi = explode(":",$market_zhisucom['endtrade'])[0];
					$endfen = explode(":",$market_zhisucom['endtrade'])[1];
				}else{
					$endshi = "23";
					$endfen = "59";
				}

			}

			$this->assign('zhisucom_getCoreConfig',$zhisucom_getCoreConfig['zhisucom_indexcat']);
			$this->assign('beginshi', $beginshi);
			$this->assign('beginfen', $beginfen);
			$this->assign('endshi', $endshi);
			$this->assign('endfen', $endfen);
			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}
// var_dump($_POST);die;
			$round = array(0, 1, 2, 3, 4, 5, 6);

			if (!in_array($_POST['round'], $round)) {
				$this->error('小数位数格式错误！');
			}

			if ($_POST['id']) {
				$rs = M('Market')->save($_POST);
			}
			else {
				$_POST['name'] = $_POST['sellname'] . '_' . $_POST['buyname'];
				unset($_POST['buyname']);
				unset($_POST['sellname']);

				if (M('Market')->where(array('name' => $_POST['name']))->find()) {
					$this->error('市场存在！');
				}

				$rs = M('Market')->add($_POST);
			}

			if ($rs) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}
		}
	}

	public function marketStatus($id = NULL, $type = NULL, $moble = 'Market')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
		case 'forbid':
			$data = array('status' => 0);
			break;

		case 'resume':
			$data = array('status' => 1);
			break;

		case 'repeal':
			$data = array('status' => 2, 'endtime' => time());
			break;

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($moble)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($moble)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function invit($field = NULL, $name = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		$count = M('Invit')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Invit')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
			$list[$k]['invit'] = M('User')->where(array('id' => $v['invit']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function checkUpdata()
	{
		if (!S(MODULE_NAME . CONTROLLER_NAME . 'checkUpdata')) {
			$list = M('Menu')->where(array(
				'url' => 'Trade/index',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Trade/index', 'title' => '委托管理', 'pid' => 5, 'sort' => 1, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Trade/index',
					'pid' => array('neq', 0)
					))->save(array('title' => '委托管理', 'pid' => 5, 'sort' => 1, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Trade/log',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Trade/log', 'title' => '成交记录', 'pid' => 5, 'sort' => 2, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Trade/log',
					'pid' => array('neq', 0)
					))->save(array('title' => '成交记录', 'pid' => 5, 'sort' => 2, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Trade/chat',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Trade/chat', 'title' => '交易聊天', 'pid' => 5, 'sort' => 3, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Trade/chat',
					'pid' => array('neq', 0)
					))->save(array('title' => '交易聊天', 'pid' => 5, 'sort' => 3, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Trade/comment',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Trade/comment', 'title' => '币种评论', 'pid' => 5, 'sort' => 4, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Trade/comment',
					'pid' => array('neq', 0)
					))->save(array('title' => '币种评论', 'pid' => 5, 'sort' => 4, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Trade/market',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Trade/market', 'title' => '交易市场', 'pid' => 5, 'sort' => 5, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Trade/market',
					'pid' => array('neq', 0)
					))->save(array('title' => '交易市场', 'pid' => 5, 'sort' => 5, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}

			$list = M('Menu')->where(array(
				'url' => 'Trade/invit',
				'pid' => array('neq', 0)
				))->select();

			if ($list[1]) {
				M('Menu')->where(array('id' => $list[1]['id']))->delete();
			}
			else if (!$list) {
				M('Menu')->add(array('url' => 'Trade/invit', 'title' => '交易推荐', 'pid' => 5, 'sort' => 6, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}
			else {
				M('Menu')->where(array(
					'url' => 'Trade/invit',
					'pid' => array('neq', 0)
					))->save(array('title' => '交易推荐', 'pid' => 5, 'sort' => 6, 'hide' => 0, 'group' => '交易', 'ico_name' => 'stats'));
			}

			if (M('Menu')->where(array('url' => 'Chat/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			if (M('Menu')->where(array('url' => 'Tradelog/index'))->delete()) {
				M('AuthRule')->where(array('status' => 1))->delete();
			}

			S(MODULE_NAME . CONTROLLER_NAME . 'checkUpdata', 1);
		}
	}
//c2c订单删除	
  public function delete(){
		$id =I('get.id');
    	$tarde=M('c2c_trade');
    	$ucoin=M('user_coin');    
		$res=$tarde->where("id=".$id)->find();
		// var_dump($res);die;
    	if($res['mid']>0 and $res['is_done']==0){       
          //主订单卖出---只返回未匹配的剩余订单金额	
          if($res['type']==2){
				  $ucoin->where("userid=".$res['sid'])->setDec("usdtc",$res['residue']);
                  $res=$ucoin->where("userid=".$res['sid'])->setInc("usdt",$res['residue']);
                  if($res!==false){                	                  
                      $tarde->where("id=".$id)->delete();                     
                      echo "<script>alert('删除订单成功并返回冻结USDT');</script>";	
						$this->redirect("trade/c2c");	
                  }else{

                       echo "<script>alert('返回主订单冻结USDT失败');</script>";
					   $this->redirect("trade/c2c");
                  }
                  unset($res);
          }else{
			  
			//  $tarde->where("id=".$id)->delete(); 
			   echo "<script>alert('删除主订单成功(已匹配的不能删除)');</script>";	
				$this->redirect("trade/c2c");	
			  
		  }                 
        }else{         	      
          //子订单--返回子订单的USDT到主订单
       		if($res['is_get']==0){                       
            $a=$tarde->where("id=".$res['match_id'])->find();  		//主订单是否存在            
              if($a){              
                    $res1=$tarde->where("id=".$res['match_id'])->setInc("residue",$res['num']);			//删除子订单返回NUM到主订单剩余成交量
					$ucoin->where("userid=".$res['sid'])->setDec("usdtc",$res['num']);					//解除冻结金额
					$ucoin->where("userid=".$res['sid'])->setInc("usdt",$res['num']);					//增加金额
                    if($res1!==false){

                        $tarde->where("id=".$id)->delete();
                        echo "<script>alert('删除子订单成功并返回USDT到主订单');</script>";
						$this->redirect("trade/c2c");
                    }

              }else{
              	
              		 echo "<script>alert('删除订单的主订单不存在');</script>";
					 $this->redirect("trade/c2c");
              }
            
            }else{
            
            		 echo "<script>alert('不能删除已打款订单');</script>";
					 $this->redirect("trade/c2c");
            } 
        	
        }		
	}
    //C2C交易订单
	public function c2c(){
        $where = array();
	//	$where['is_pay'] = array('eq', 1);
		$cl=M('c2c_trade');
        $count = $cl->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = $cl->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    //C2C投诉订单

    public function tousu(){

        $where = array();
        $cl=M('c2c_trade');
        $where["is_ts"]=2;
        $count = $cl->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = $cl->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();

    }
	
	
    //C2C投诉记录

    public function tousujilu(){    
	
        $cl=M('c2c_record');     
        $count = $cl->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = $cl->order('ts_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();

    }
    //C2C投诉重置订单
    public function reset(){
        $c2c=M('c2c_trade');
        $id=I('get.id');
        $result=$c2c->where("id=".$id)->find();
            if($result['is_ts']==2){
                $where['is_ts']=0;
                $where['is_get']=0;
                $where['id']=$id;
				$where['paytime']=time();
                $res=$c2c->save($where);
                if($res!==false){
					
						$wallet=M('user_coin')->where("userid=".$result['sid'])->getField('usdt');
						parent::addCashhistory($result['sid'],1,2,"卖出",$result['num']."USDT",$result['price'],$wallet."USDT",$result['number']."投诉结果:打款被重置");
					
					
						$wallet=M('user_coin')->where("userid=".$result['bid'])->getField('usdt');
						parent::addCashhistory($result['bid'],1,1,"买入",$result['num']."USDT",$result['price'],$wallet."USDT",$result['number']."投诉结果:打款被重置");
						//更新投诉记录表
						$data['ts_done']=time();
						$data['is_ts']=0;  //0:重置订单，1：成交订单 2:投诉订单
						$data['name']=session('admin_username');
						M('c2c_record')->where('ts_done=0 and uid='.$id)->save($data);
 //                  
                    echo "<script>alert('重置订单打款状态成功');</script>";
					$this->redirect("trade/tousu");

                }else{
                    echo "<script>alert('操作失败');</script>";
					$this->redirect("trade/tousu");
                }
            }
    }

    //c2c投诉确定订单
    public function query(){
        $trade=M('c2c_trade');
		$ucoin=M('user_coin');
        $id=I('get.id');
        $result=$trade->where("id=".$id)->find();
		
        if($result['is_ts']==2 and $result['is_done']==0){

		    $data['id']=$id;
			$data['is_done']=1;
			$data['is_ts']=1;
			$data['endtime']=time();
                                                               //判断是否有这个订单
            $res=$trade->save($data);
			  if($res!==false){
                    $c2c=$trade->where("id=".$id." and is_pay=1 and is_done=1 and is_get=1 and is_cancel=0 and is_ts=1")->find();
                    if($c2c){
							 $interest=0;//预留C2C交易手续费						
                             $matchid=$c2c['match_id'];                                               //主订单id
							 $ucoin->where("userid=".$c2c['sid'])->setDec('usdtc',$c2c['num']);      //卖家冻结减款	
                             $ucoin->where("userid=".$c2c['bid'])->setInc('usdt',$c2c['num']);        //买家加款
								

							$wallet=$ucoin->where("userid=".$c2c['sid'])->getField('usdt');
							parent::addCashhistory($c2c['sid'],1,2,"卖出",$c2c['num']."USDT",$c2c['price'],$wallet."USDT",$c2c['number']."投诉结果:被成交");


							$wallet=$ucoin->where("userid=".$c2c['bid'])->getField('usdt');
							parent::addCashhistory($c2c['bid'],1,1,"买入",$c2c['num']."USDT",$c2c['price'],$wallet."USDT",$c2c['number']."投诉结果:被成交");
						
							 //更新投诉记录表
							$data['ts_done']=time();
							$data['is_ts']=1;  //0:重置订单，1：成交订单 2:投诉订单
							$data['name']=session('admin_username');
							M('c2c_record')->where('ts_done=0 and uid='.$id)-save($data);
							 
							$trade->where("id=".$matchid)->setInc('trade',$c2c['num']);              	//更新主订单成交量trade
							$statis=$trade->where('id='.$matchid)->find();
							if($statis['num']==$statis['trade']){                                 		//主订单交易完成
							$matchdata['is_done']=1;
							$matchdata['endtime']=time();
							$trade->where('id='.$matchid)->save($matchdata);
							}
							
						echo "<script>alert('确认订单交易成功');</script>";
						$this->redirect("trade/tousu");
                    }
           
			  }			
        }
    }
	//C2C投诉详情页
	public function details(){
		
		$id=I('id');
		$list=M('C2c_trade')->where("id=".$id)->find();
		$this->assign('list',$list);
		$this->display();		
	}
    public function c2cdo(){
        $id['id']=I('get.id');
        $isshop=I('get.isshop');

        switch ($isshop) {
            case 0:
                $isshop = 5;
                break;
            case 1:
                $isshop = 2;
                break;
            case 2:
                $isshop = 3;
                break;
            case 3:
                $isshop = 4;
                break;
        }

        $data = array('isshop'=>$isshop,'endtime'=>time());
        $list = M('Finance')->where($id)->setField($data);
        if($list){
            $this->success('成功');
        }else{
            $this->success('失败');
        }
    }

    public function otc($field = NULL,$name = NULL)
    {
        if ($field && $name) {
            if ($field == 'username') {
                $where['userid'] = M('User')->where(array('username' => $name))->getField('id');
            }
            else {
                $where[$field] = $name;
            }
        }


        $ol = M('otc_log');
        //$where['status']=array(array('neq',11),array('neq',-1),array('eq',5),'and');
        //$where['stat']=2;
		$where="status!=-1";

        $count = $ol->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = $ol->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();




        foreach($list as $k=>$v){
            $user = M('user')->where('id=' . $v['userid'])->find();
            $user1 = M('user')->where('id=' . $v['matchid'])->find();
            $list[$k]['s1'] = $user['moble'];
            $list[$k]['s2'] = $user1['moble'];
            $list[$k]['s3'] = strtoupper(explode('_',$v['coinname'])[0]);
        }
		
		//dump($list);
		
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }



    #平台所有交易手续费的70%返还给交易用户
	public function fh_user(){
		$trade_db   	  = M('Trade_log');
		$market_db  	  = M('Market');
		$begin_time 	  = strtotime(date('Ymd'));#每天0点时间
		$end_time   	  = strtotime(date('Ymd'))+86400;#每天24点
		$where['addtime'] = array('between',array($begin_time,$end_time));
		$trade_info 	  = $trade_db->where($where)->select();
		$fee_sum          = $trade_db->where($where)->sum('fee');
		// var_dump($fee_sum);die;

		foreach ($trade_info as $k => $v) {
			if($v['type'] == 1){#买
				$market1 = explode('_', $v['market'])['0'];
				$mmp['name'] = $market1.'_ccb';
				$mmp['status'] = '1';
				$new_price1 = $market_db->where($mmp)->getField('new_price');
				$ccb_sum1 += $new_price1 * $v['fee'];
			}elseif ($v['type'] == 2) {#卖
				$market2 = explode('_', $v['market'])['1'];
				$mmp2['name'] = $market2.'_ccb';
				$mmp2['status'] = '1';
				$new_price2 = $market_db->where($mmp)->getField('new_price');
				$ccb_sum2 += $new_price2 * $v['fee'];
			}
		}
		$ccb_sum = $ccb_sum1 + $ccb_sum2;#转化为ccb的手续费总和
		// echo $ccb_sum.'<br/>';

	}	


	//机器人交易
	public function automarket(){
		$this->display();
	}
	
	//杠杆交易
	public function lever($field = NULL, $name = NULL, $market = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($market) {
			$where['market'] = $market;
		}


		$where['userid'] = array('gt',0);




		if ($status) {
			$where['status'] = $status;
		}

		if($status == 0 && $status != null){
			$where['status'] = 0;
		}




		$count = M('Trade_lever')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Trade_lever')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	
	//杠杆成交记录
	public function ok_lever($field = NULL, $name = NULL, $market = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else if ($field == 'peername') {
				$where['peerid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}


		$where['userid'] = array('gt',0);

		if ($market) {
			$where['market'] = $market;
		}

		$count = M('Trade_log_lever')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Trade_log_lever')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
			$list[$k]['peername'] = M('User')->where(array('id' => $v['peerid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
}

?>