<?php
namespace Home\Controller;

class ArticleController extends HomeController
{
	public function index($id = 19)
	{
		if (empty($id)) {
			redirect(U('Article/detail'));
		}

		if (!check($id, 'd')) {
			redirect(U('Article/detail'));
		}

		$Articletype = M('ArticleType')->where(array('id' => $id))->find();
		$ArticleTypeList = M('ArticleType')->where(array('status' => 1, 'index' => 1, 'shang' => $Articletype['shang']))->order('sort asc ,id asc')->select();
		$Articleaa = M('Article')->where(array('status'=>1,'id' => $ArticleTypeList[0]['id']))->find();
		$this->assign('shang', $Articletype);

		foreach ($ArticleTypeList as $k => $v) {
			$ArticleTypeLista[$v['name']] = $v;
		}
		$news = M('Article')->where('status=1')->select();
		$this->assign('news',$news);

		$this->assign('ArticleTypeList', $ArticleTypeLista);
		$this->assign('data', $Articleaa);
		$where = array('type' => $Articletype['name'],'status'=>1);
		$Model = M('Article');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	
	public function upgrade(){
		$this->display();
	}
	
	public function detail()
	{   $id = $_GET['id'];
    // echo $id;die;
		if (empty($id)) {
			$id = 1;
		}

		if (!check($id, 'd')) {
			$id = 1;
		}

		$data = M('Article')->where(array('id' => $id))->find();
     //var_dump($data);die;
		$list_news = M('Article')->where(array('type'=>$data['type']))->select();
		$ArticleType = M('ArticleType')->where(array('status' => 1, 'index' => 1))->order('sort asc ,id desc')->select();

		foreach ($ArticleType as $k => $v) {
			$ArticleTypeList[$v['name']] = $v;
		}

		$this->assign('ArticleTypeList', $ArticleTypeList);
		$this->assign('data', $data);
		$this->assign('list_news', $list_news);
		$this->assign('type', $data['type']);
		$this->display();
	}

	public function type($id = NULL)
	{
		if (empty($id)) {
			$id = 1;
		}

		if (!check($id, 'd')) {
			$id = 1;
		}

		$Article = M('ArticleType')->where(array('id' => $id))->find();

		if ($Article['shang']) {
			$shang = M('ArticleType')->where(array('name' => $Article['shang']))->find();
			$ArticleType = M('ArticleType')->where(array('status' => 1, 'shang' => $Article['shang']))->order('sort asc ,id desc')->select();
			$Articleaa = $Article;
		}
		else {
			$shang = M('ArticleType')->where(array('id' => $id))->find();
			$ArticleType = M('ArticleType')->where(array('status' => 1, 'shang' => $Article['name']))->order('sort asc ,id desc')->select();
			$Articleaa = M('ArticleType')->where(array('id' => $ArticleType[0]['id']))->find();
		}

		$this->assign('shang', $shang);
		
		
		foreach ($ArticleType as $k => $v) {
			$ArticleTypeList[$v['name']] = $v;
		}


		
		$this->assign('ArticleTypeList', $ArticleTypeList);
		$this->assign('data', $Articleaa);
		$this->display();
	}
	
	
	//登录验证
	public function modalLogin(){
		$this->display();
	}
	
	//公告模块
	public function officialAdModal(){
		// echo $_SESSION['lang'];
		$news = M('Article')->where('status=1')->select();
		/*if($_SESSION['lang'] == 1){
			$news = M('Article')->where('status=1')->field('title,id')->select();
		}elseif($_SESSION['lang'] == 3){
			$news = M('Article')->where('status=1')->field('title_en,id')->select();
			//dump($news);
		}elseif($_SESSION['lang'] == 2){
			$news = M('Article')->where('status=1')->field('title_e,id')->select();
		}*/
		
		$this->assign('news',$news);
		$this->display();
	}


}

?>