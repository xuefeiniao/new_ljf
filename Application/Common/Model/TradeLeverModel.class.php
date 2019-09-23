<?php
namespace Common\Model;

class TradeLeverModel extends \Think\Model
{
	protected $keyS = 'TradeLever';

	public function chexiao($id = NULL)
	{
		if (!check($id, 'd')) {
			return array('0', '参数错误');
		}

		$trade = M('TradeLever')->where(array('id' => $id))->find();
//dump($trade);die;
		if (!$trade) {
			return array('0', '订单不存在');
		}

		if ($trade['status'] != 0) {
			return array('0', '订单不能撤销');
		}

		$xnb = explode('_', $trade['market'])[0];
		$rmb = explode('_', $trade['market'])[1];

		if (!$xnb) {
			return array('0', '卖出市场错误');
		}

		if (!$rmb) {
			return array('0', '买入市场错误');
		}

		$fee_buy = C('market')[$trade['market']]['fee_buy'];
		$fee_sell = C('market')[$trade['market']]['fee_sell'];

		if ($fee_buy < 0) {
			return array('0', '买入手续费错误');
		}

		if ($fee_sell < 0) {
			return array('0', '卖出手续费错误');
		}

		$user_coin = M('LeverCoin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->find();
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables zhisucom_lever_coin write  , zhisucom_trade_lever write');
		$rs = array();
		$user_coin = $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->find();

		
		$rmbgg = 'p_yue';
		$xnbgg = 'yue';
		if ($trade['type'] == 1) {
			$mun = round(((($trade['num'] - $trade['deal']) * $trade['price']) / 100) * (100 + $fee_buy), 8);
			$user_buy = $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->find();
			$user_buy[$rmb . 'd'] = $user_buy['p_yued'];
			if ($mun <= round($user_buy[$rmb . 'd'], 8)) {
				$save_buy_rmb = $mun;
			}
			else if ($mun <= round($user_buy[$rmb . 'd'], 8) + 1) {
				
				$save_buy_rmb = $user_buy[$rmb . 'd'];
			}
			else {
				$mo->execute('rollback');
				$mo->execute('unlock tables');
				//M('Trade')->where(array('id' => $id))->setField('status', 2);
				$mo->execute('commit');
				return array('0', '撤销失败1');
			}

			/*$finance = $mo->table('zhisucom_finance')->where(array('userid' => $trade['userid']))->order('id desc')->find();
			$finance_num_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $trade['userid']))->find();
			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $trade['userid']))->setInc($rmb, $save_buy_rmb);
			$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $trade['userid']))->setDec($rmb . 'd', $save_buy_rmb);
			$finance_nameid = $trade['id'];
			$save_buy_rmb = $save_buy_rmb;
			$finance_mum_user_coin = $mo->table('zhisucom_user_coin')->where(array('userid' => $trade['userid']))->find();
			$finance_hash = md5($trade['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $save_buy_rmb . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.zhisucom.com');
			$finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

			if ($finance['mum'] < $finance_num) {
				$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
			}
			else {
				$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
			}

			$rs[] = $mo->table('zhisucom_finance')->add(array('userid' => $trade['userid'], 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $save_buy_rmb, 'type' => 1, 'name' => 'trade', 'nameid' => $finance_nameid, 'remark' => '交易中心-交易撤销' . $trade['market'], 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
			$sql = $mo->table('zhisucom_finance')->getLastSql();
			error_log('sql='.$sql,3,'./x.txt');
			
			$rs[] = $mo->table('zhisucom_trade')->where(array('id' => $trade['id']))->setField('status', 2);
			$you_buy = $mo->table('zhisucom_trade')->where(array(
				'market' => array('like', '%' . $rmb . '%'),
				'status' => 0,
				'userid' => $trade['userid']
				))->find();

			if (!$you_buy) {
				$you_user_buy = $mo->table('zhisucom_user_coin')->where(array('userid' => $trade['userid']))->find();

				if (0 < $you_user_buy[$rmb . 'd']) {
					$rs[] = $mo->table('zhisucom_user_coin')->where(array('userid' => $trade['userid']))->setField($rmb . 'd', 0);
				}
			}*/
			
			if (0 < $save_buy_rmb) {
			    $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->setInc($rmbgg, $save_buy_rmb);
			    $rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->setDec($rmbgg . 'd', $save_buy_rmb);
			}
			
			$rs[] = $mo->table('zhisucom_trade_lever')->where(array('id' => $trade['id']))->setField('status', 2);
			$you_buy = $mo->table('zhisucom_trade_lever')->where(array(
			    'market' => array('like', $rmb . '%'),
			    'status' => 0,
			    'userid' => $trade['userid']
			))->find();
			
			if (!$you_buy) {
			    $you_user_buy = $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->find();
			
				$you_user_buy[$rmb . 'd'] = $you_user_buy['p_yued'];
			
			    if (0 < $you_user_buy[$rmb . 'd']) {
			        $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->setField($rmbgg . 'd', 0);
			    }
			}
		}
		else if ($trade['type'] == 2) {
			$mun = round($trade['num'] - $trade['deal'], 8);
			$user_sell = $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->find();

			$user_sell[$xnb . 'd'] = $user_sell['yued'];
			
			if ($mun <= round($user_sell[$xnb . 'd'], 8)) {
				$save_sell_xnb = $mun;
			}
			else if ($mun <= round($user_sell[$xnb . 'd'], 8) + 1) {
				$save_sell_xnb = $user_sell[$xnb . 'd'];
			}
			else {
				$mo->execute('rollback');
				//M('Trade')->where(array('id' => $trade['id']))->setField('status', 2);
				$mo->execute('commit');
				return array('0', '撤销失败2');
			}

			if (0 < $save_sell_xnb) {
				$rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->setInc($xnbgg, $save_sell_xnb);
				$rs[] = $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->setDec($xnbgg . 'd', $save_sell_xnb);
			}

			$rs[] = $mo->table('zhisucom_trade_lever')->where(array('id' => $trade['id']))->setField('status', 2);
			$you_sell = $mo->table('zhisucom_trade_lever')->where(array(
				'market' => array('like', $xnb . '%'),
				'status' => 0,
				'userid' => $trade['userid']
				))->find();

			if (!$you_sell) {
				$you_user_sell = $mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->find();
				$you_user_sell[$xnb . 'd'] = $you_user_sell['yued'];
				if (0 < $you_user_sell[$xnb . 'd']) {
					$mo->table('zhisucom_lever_coin')->where(array('userid' => $trade['userid'],'name_en'=>$xnb))->setField($xnbgg . 'd', 0);
				}
			}
			
		}
		else {
			$mo->execute('rollback');
			return array('0', '撤销失败3');
		}

		if (check_arr($rs)) {
			
			$mo->execute('commit');
			
			
			$mo->execute('unlock tables');
			return array('1', '撤销成功');
		}
		else {
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			return array('0', '撤销失败4|' . implode('|', $rs));
		}
	}
}

?>