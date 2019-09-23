<?php
namespace Home\Controller;

class MoniController extends Home1Controller {
    public function index()
    {
        $this->display();
    }

    public function updo()
    {


        $arr = I('post.');
        foreach ($arr as $k => $v) {
            $url .= $k . '/' . $v . '/';
        }

        if ($url) {
            $this->redirect("Sql/index/$url",'页面跳转中...');
        }
    }

	public function updo1(){
		//$arr = I('post.');
		//Array ( [market] => ib_bb [minprice] => 0.08 [maxprice] => 1 [flsp] => 2 [minnum] => 100 [maxnum] => 100 [fls] => 2 [type] => 1 [fee] => -0.02 )
		//print_r($arr);
		
		$price=$this->randomFloat(I('post.minprice'), I('post.maxprice'));
		$num=$this->randomFloat(I('post.minprice'), I('post.maxprice'));
		$newprice=round($price,I('post.flsp'));
		$newnum=round($price,I('post.fls'));
		
		
		
	}

    /*public function updo()
    {
        $m = I('post.market');
        $p1 = I('post.minprice');
        $p2 = I('post.maxprice');
        $fls = I('post.fls');

        $n1 = $this->randomFloat(I('post.minnum'), I('post.maxnum'));

        $type = I('post.type');

        $url = "Sql/index/market/$m/p1/$p1/p2/$p2/num/$n1/fls/$fls/type/$type";

        if ($url) {
            $this->redirect("$url", '页面跳转中...');
        }
    }*/

    function randomFloat($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }


    /*$num=randomFloat(1,8);
    // echo $num;  
    $newNum  = round($num,3);  
    echo $newNum;*/

	  
	}