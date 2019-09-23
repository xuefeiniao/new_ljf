<?php
namespace Wap\Controller;
use \Org\Util\GoogleAuthenticator;
class UserController extends HomeController
{
    
    /*public function  add_usertoken(){
        $where['login_token'] = array('eq',1);
        $user = M('user')->where($where)->field('id,moble,password')->select();
        //echo M('user')->getLastSql();exit;
        
        if($user){
            foreach($user as $v){
                $login_token = md5($v['moble'].$v['password']);
                $res[] = M('user')->where("id=".$v['id'])->setField('login_token',$login_token);
            }
        }
        if(count($user)== count($res)){
            echo 'ok';
        }else{
            echo 'fail';
        }
    }*/
    
    
    
    //删除测试用户(上线前不可删除)
    public function del_test_user(){
        $where['moble'] = array('in','18611305398,18611752599');
        $user = M('user')->where($where)->field('id')->select();
        
        if($user){
            foreach ($user as $v){
                if($v['id']){
                    //M('user_coin')->where(array('userid'=>$v['id']))->save(['btc'=>0,'btcd'=>0,'bdb'=>0,'bdbd'=>0]);
                    M('user_coin')->where(array('userid'=>$v['id']))->delete();
                    M('user_log')->where(array('userid'=>$v['id']))->delete();
                    M('user_bank')->where(array('userid'=>$v['id']))->delete();
                    M('trade')->where(array('userid'=>$v['id']))->delete();
    
                    M('trade_log')->where("userid=".$v['id']." OR peerid=".$v['id'])->delete();
                    M('myzr')->where(array('userid'=>$v['id']))->delete();
                    M('myzc')->where(array('userid'=>$v['id']))->delete();
                    M('mytx')->where(array('userid'=>$v['id']))->delete();
                    M('mycz')->where(array('userid'=>$v['id']))->delete();
                    M('finance')->where(array('userid'=>$v['id']))->delete();
    
                    M('chistory')->where(array('uid'=>$v['id']))->delete();
                    M('finance')->where(array('userid'=>$v['id']))->delete();
                    M('finance')->where(array('userid'=>$v['id']))->delete();
                    M('finance')->where(array('userid'=>$v['id']))->delete();
                    M('user')->where(array('id'=>$v['id']))->delete();
                }
            }
        }
        echo 'ok';
    }
    
    
    
    public function index()
    {
        if (!userid()) {
            //redirect('/#login');
            $this->error('请先登录');
        }

        $user = M('User')->where(array('id' => userid()))->field('id,login_token,username,header_pic,email')->find();   
        $vip_return = get_vip_fee($user['id']);
        $this->ajaxReturn(array(
            'code'  =>  200,
            'result'    =>  '登录成功！',
            'body'    =>  array(
                'ltoken'    =>  $user['login_token'],
                'username'  =>  $user['username'],
                'header_pic'  =>  $user['header_pic']?'/Upload/app/header/'.$user['header_pic']:'/Upload/app/5b800c04849e0.png',
                'vip'  =>  $vip_return[0],
                'uid'=>$user['id']
                //'email' => $user['email']
            )
        ));
        /* $this->assign('user', $user);
        $this->assign('prompt_text', D('Text')->get_content('user_index'));
        $this->display(); */
    }
    
    //获取实名认证信息-app端
    public function nameauth_new(){
        if (!userid()) {
            $this->error('请先登录');
        }
        
        $user = M('User')->where(array('id' => userid()))->field('truename,idcard,idcardauth,idcardimg1,idcardimg2,idcardimg3,idcardinfo')->find();
        
        $this->success('获取成功',$user);
    }

    public function numeauth1(){
        if (!userid()) {
            redirect('/#login');
        }

        $user = M('User')->where(array('id' => userid()))->find();

        if ($user['idcard']) {
            $user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
        }
        
        $imgstr = "";
        $imgnum=0;
        if($user['idcardimg1']){
            $img_arr = array();
            $img_arr = explode("_",$user['idcardimg1']);

            foreach($img_arr as $k=>$v){
                $imgstr = $imgstr.'<li style="height:100px;float:left;"><img style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$v.'" /></li>';
                $imgnum++;
            }
            
            
            unset($img_arr);
        }
        $allowImg = false;
        if( ($user['idcardauth']==0 && $imgnum<3) || ($user['idcardauth']==0 && $imgnum==3 && !empty($user['idcardinfo']))){
            $allowImg = true;
        }

        $this->assign('user', $user);
        $this->assign('userimg', $imgstr);
        $this->assign('imgnum', $imgnum);
        $this->assign('allowImg', $allowImg);
        
        $this->assign('prompt_text', D('Text')->get_content('user_nameauth'));
        $this->display();
    }
    
    
    public function nameauth()
    {
        if (!userid()) {
            //redirect('/#login');
            $this->error('请先登陆');
        }

        $user = M('User')->where(array('id' => userid()))->find();

        if ($user['idcard']) {
            $user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
        }
        
        $imgstr = "";
        $imgnum=0;
        if($user['idcardimg1']){
            $img_arr = array();
            $img_arr = explode("_",$user['idcardimg1']);

        /*  foreach($img_arr as $k=>$v){
                $imgstr = $imgstr.'<li style="height:100px;float:left;"><img style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$v.'" /></li>';
                $imgnum++;
            }*/
            $imgstr = '<li  style="height:100px;float:left;"><img class="img-all" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg1'].'" /></li>';
            $imgstr = $imgstr.'<li  style="height:100px;float:left;"><img class="img-all1" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg2'].'" /></li>';
            $imgstr = $imgstr.'<li style="height:100px;float:left;"><img class="img-all2" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg3'].'" /></li>';

            unset($img_arr);
        }
        $allowImg = false;
        if( ($user['idcardauth']==0 && $imgnum<3) || ($user['idcardauth']==0 && $imgnum==3 && !empty($user['idcardinfo']))){
            $allowImg = true;
        }

        $this->assign('user', $user);
        $this->assign('userimg', $imgstr);
        $this->assign('imgnum', $imgnum);
        $this->assign('allowImg', $allowImg);
        
        $this->assign('prompt_text', D('Text')->get_content('user_nameauth'));
        $this->display();
    }
    
    //注册实名认证
    public function realname(){
        
        if (!userid()) {
            redirect('/#login');
        }
        $user = M('User')->where('id = '.$_SESSION['userId'])->find();
        
        //判断是否设置过支付方式
        if(!($user['ali_img'] || $user['weixin_img'])){
            $user_bank = M('user_bank')->where(array('userid'=>$user['id']))->getField('bankcard');
            if($user_bank){
                $user['is_bindbank'] = 1;//绑定过
            }else{
                $user['is_bindbank'] = 2;//未绑定
            }
        }else{
            $user['is_bindbank'] = 1;//绑定过
        }
        //
        
        $this->assign('user',$user);
        $this->display();
    }
    //生成谷歌验证码
    public function googleverify(){     
        $ga=new \Home\Controller\GoogleAuthenticatorController();
        $moble=M('User')->where('id='.userid())->getField('moble'); 
        $google=$ga->createSecret($moble);  
        if($google=="error"){
        $google=$ga->createSecret($moble);      
        }
        return $google; 
    }
    
    //验证码页面
    public function googleauth(){
        
        if (!userid()) {
            redirect('/#login');
        }
        $gstatus=M('User')->where('id='.userid())->getField('google'); //是否通过谷歌验证码
        $this->assign('status',$gstatus);
        $this->display();
    }
    //抓取图片
    public function curl_file_get_contents($durl){
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $durl);
       curl_setopt($ch, CURLOPT_TIMEOUT, 2);
       curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
       curl_setopt($ch, CURLOPT_REFERER,_REFERER_);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $r = curl_exec($ch);
       curl_close($ch);
       return $r;
     }
    //重置谷歌验证码页面
    public function googlerelieve ()
    {       
        if (!userid()) {
            redirect('/#login');
        }               
        $gstatus=M('User')->where('id='.userid())->getField('google');//是否绑定谷歌验证码
        if($gstatus==1){        
        $google=$this->googleverify();
        $this->assign('goolekey',$google[0]['secret']);
        $url=$google[0]['QRCodeGoogleUrl'];
        $imgurl=substr($url,0,4).substr($url,5);
        $a = $this->curl_file_get_contents($imgurl);
        $pashname='./Upload/verifyimg/'.rand(10000,99999).time().".jpg";    
        file_put_contents($pashname, $a);
        $this->assign('orc',$pashname);     
        $this->display();
        }else{
            $this->redirect("/user/googleauths");
            exit;           
        }
    }
    //取消绑定谷歌验证码
    public function googlecancel(){
        if (!userid()) {
            redirect('/#login');
        }
        if(IS_AJAX){
            $id=userid();
            $code=I('post.verify'); 
            $status=check_google($code,$id);                            //验证谷歌验证码
            if($status=="error"){
                $this->ajaxReturn(array('status'=>0,'msg'=>"谷歌验证码错误!"));
                exit;
            }else{
                $data['id']=$id;
                $data['google']=0;  //0未绑定，1绑定
                $data['secret']=""; 
                $data['check']=1; 
                $result=M('User')->save($data);
                if($result!==false){
                    $this->ajaxReturn(array('status'=>1,'msg'=>"谷歌验证码解除绑定成功!"));    
                }   
            }
        return; 
        }   
    }
    //重置绑定谷歌验证码
    public function googlereset(){
        
        if (!userid()) {
            redirect('/#login');
        }
        if(IS_AJAX){            
            $id=userid();
            $code=I('post.verify');
            $password=I('post.password');
            $key=I('post.key');
            if($key==""||$key==null){
                $this->ajaxReturn(array('status'=>0,'msg'=>"请刷新页面重新生成谷歌验证码"));
                exit;
            }
            $ga=new \Home\Controller\GoogleAuthenticatorController();   
            $status=$ga->verifyCode($code,$key);//验证验证码正确性
            if($status=="error"){
                $this->ajaxReturn(array('status'=>0,'msg'=>"谷歌验证码错误!"));
                exit;
            }else{          
                $paypassword=M('User')->where('id='.$id)->getField('paypassword');  
                if(md5($password)==$paypassword){
                    $data['google']=1;
                    $data['secret']=$key;
                    $data['id']=$id;
                    $data['check']=2;       //1手机验证2谷歌验证
                    M('User')->save($data);             
                    $this->ajaxReturn(array('status'=>1,'msg'=>"谷歌验证码重置绑定成功!"));                    
                }else{                  
                    $this->ajaxReturn(array('status'=>0,'msg'=>"资金密码输入错误!"));
                    exit;   
                }               
            }   
            return;
        }   
    }
        
    //生成谷歌验证码
    public function googleauths(){
        
    if (!userid()) {
            //redirect('/#login');
            $this->error('请先登陆');
        }       
        $google=$this->googleverify();          
        $this->success('获取成功',$google[0]['secret']);
        //$this->assign('goolekey',$google[0]['secret']);       
        
        /*$url=$google[0]['QRCodeGoogleUrl'];
        $imgurl=substr($url,0,4).substr($url,5);
        $a = $this->curl_file_get_contents($imgurl);
        $pashname='./Upload/verifyimg/'.rand(10000,99999).time().".jpg";    
        file_put_contents($pashname, $a);
        $this->assign('orc',$pashname); 
        $this->display();*/
    }
    
    //获取数据绑定谷歌验证码
    public function goolesave(){
        
        if (!userid()) {
            //redirect('/#login');
            $this->error('请先登陆');
        }
        //if(IS_AJAX){          
        $id=userid();
        $code=I('post.verify');
        $password=I('post.password');
        $key=I('post.key');
        if($key==""||$key==null){           
            $this->ajaxReturn(array('status'=>0,'msg'=>"请刷新页面重新生成谷歌验证码"));
            exit;
        }
        $gstatus=M('User')->where('id='.$id)->getField('google');//是否绑定谷歌验证码
        if($gstatus==1){        
            $this->ajaxReturn(array('status'=>0,'msg'=>"已经绑定谷歌验证码"));
            exit;
        }
        $ga=new \Home\Controller\GoogleAuthenticatorController();   
        $status=$ga->verifyCode($code,$key);//验证验证码正确性
        if($status=="error"){
            $this->ajaxReturn(array('status'=>0,'msg'=>"谷歌验证码错误!"));
            exit;
        }else{          
                $paypassword=M('User')->where('id='.$id)->getField('paypassword');  
                if(md5($password)==$paypassword){
                    $data['google']=1;
                    $data['secret']=$key;
                    $data['id']=$id;
                    M('User')->save($data);             
                    $this->ajaxReturn(array('status'=>1,'msg'=>"谷歌验证码绑定成功!"));              
                }else{                  
                    $this->ajaxReturn(array('status'=>0,'msg'=>"资金密码输入错误!"));
                    exit;   
                }                           
        }
        return;
        //} 
    }   
    //高级认证
    public function senior(){
            if (!userid()) {
            redirect('/#login');
        }

        $user = M('User')->where(array('id' => userid()))->find();
//      var_dump($user);die;
        $imgstr = "";
    /*  $senior_img = explode(',' , $user['senior_img']);
//      var_dump($senior_img);die;
        foreach($senior_img as $k=>$v){
            var_dump($v);
            $imgstr = '<li  style="height:100px;float:left;"><img class="img-all" style="height:100px;margin-right:20px;" src="/Upload/senior/'.$v.'" /></li>';
        }*/
//      echo $imgstr;die;

        if ($user['idcard']) {
            $user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
        }
        
        
        $imgnum=0;
        if($user['idcardimg1']){
            $img_arr = array();
            $img_arr = explode("_",$user['idcardimg1']);

        /*  foreach($img_arr as $k=>$v){
                $imgstr = $imgstr.'<li style="height:100px;float:left;"><img style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$v.'" /></li>';
                $imgnum++;
            }
            $imgstr = '<li  style="height:100px;float:left;"><img class="img-all" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg1'].'" /></li>';
            $imgstr = $imgstr.'<li  style="height:100px;float:left;"><img class="img-all1" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg2'].'" /></li>';
            $imgstr = $imgstr.'<li style="height:100px;float:left;"><img class="img-all2" style="height:100px;margin-right:20px;" src="/Upload/idcard/'.$user['idcardimg3'].'" /></li>';*/

            unset($img_arr);
        }
        $allowImg = false;
        if( ($user['idcardauth']==0 && $imgnum<3) || ($user['idcardauth']==0 && $imgnum==3 && !empty($user['idcardinfo']))){
            $allowImg = true;
        }
        //通过验证
        $renz=M('user')->where('id='.userid())->getField('senior_past');
        $this->assign('renz',$renz);
        $this->assign('user', $user);
        $this->assign('userimg', $imgstr);
        $this->assign('imgnum', $imgnum);
        $this->assign('allowImg', $allowImg);
        
        $this->assign('prompt_text', D('Text')->get_content('user_nameauth'));
        $this->display();
        
    }
    public function imgup(){
       // var_dump($_FILES);die;
      if($_FILES){
          $upload = new \Think\Upload();
          $upload->rootPath = './Upload/senior/';
          foreach($_FILES as $k=>$v){
              foreach($v['tmp_name'] as $k1=>$v1){
                  $name =  $upload->saveName = time().'_'.mt_rand();
               //   var_dump($name);
                    $info = $upload->upload();
                   
                   if(!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                    }else{// 上传成功 获取上传文件信息
                     echo $info['savepath'].$info['savename'];
                      foreach($info as $k2=>$v2){
                        $info_name .= $v2['savepath'].$v2['savename'].",";
                       //$s =+  $v2['savename'].',';
                       $time=time();
                        M('User')->where(array('id' => userid()))->save(array('senior_img' => $info_name,'senior_past' => 0,'senior_time'=>$time));
                        }
                    }
              }
               //     $name1 = $v['photo']['name'];
                  //  $time = time().rand(100000, 999999);
                  //  $n= $time;
          }
      }
    }
    //高级认证图片上传
    public function senior_upimg(){
        // var_dump($_FILES);die;
         if($_FILES['inputfile1']["tmp_name"]){
            $result=$this->upload_senior("inputfile1");
            // var_dump($result);die;
            if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('senior_img1' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
        if($_FILES['inputfile2']["tmp_name"]){
            $result=$this->upload_senior("inputfile2");
            if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('senior_img2' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
        if($_FILES['inputfile3']["tmp_name"]){
            $result=$this->upload_senior("inputfile3");
            if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('senior_img3' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
         if($_FILES['inputfile4']["tmp_name"]){
            $result=$this->upload_senior("inputfile4");
            if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('senior_img4' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
    }
    
    protected function upload_senior($name){
        $upload = new \Think\Upload();
        $upload->maxSize = 20971520;
        $upload->exts = array('jpg', 'png');
        $upload->rootPath = './Upload/idcard/';
        $upload->autoSub = false;
        $info = $upload->uploadOne($_FILES[$name]);
        if(!$info){
            return "error";
            exit();
        }else{
            return $info['savename'];
        }
    }
    //变更上传状态
    public function senior_s(){
        // var_dump($_POST);die;
        $data['senior_time'] = time();
        $data['senior_past'] = 1;
        $user_info = M('user')->where('id='.$_SESSION['userId'])->save($data);
        if($user_info){
            $this->success('提交成功');
        }else{
            $this->error('提交失败');
        }
    }
    //高级认证重新上传
    public function senior_cx(){
        if (!userid()) {
            redirect('/#login');
        }
        $url = M('user')->where('id='.$_SESSION['userId'])->find();
        $url1 = './Upload/idcard/'.$url['senior_img1'];
        $url2 = './Upload/idcard/'.$url['senior_img2'];
        $url3 = './Upload/idcard/'.$url['senior_img3'];
        $url4 = './Upload/idcard/'.$url['senior_img4'];
        
        if(file_exists($url1) && file_exists($url2) && file_exists($url3) && file_exists($url4)){
            // echo $url1;die;
            unlink($url1);
            unlink($url2);
            unlink($url3);
            unlink($url4);
            M('user')->where('id='.$_SESSION['userId'])->save(array('senior_img1'=>'','senior_img2'=>'','senior_img3'=>'','senior_img4'=>'', 'senior_time'=>'' ,'senior_past'=>''));
            $this->success('操作成功');
        }else{
            $this->success('未找到原图片路径');
        }
    }
    
    //身份认证重新上传
    public function realname_cx(){
        if (!userid()) {
            redirect('/#login');
        }
        $url = M('user')->where('id='.$_SESSION['userId'])->find();
        $url1 = './Upload/idcard/'.$url['idcardimg1'];
        $url2 = './Upload/idcard/'.$url['idcardimg2'];
        $url3 = './Upload/idcard/'.$url['idcardimg3'];
        
        if(file_exists($url1) && file_exists($url2) && file_exists($url3) ){
            // echo $url1;die;
            unlink($url1);
            unlink($url2);
            unlink($url3);
            M('user')->where('id='.$_SESSION['userId'])->save(array('idcardimg1'=>'','idcardimg2'=>'','idcardimg3'=>'' ,'idcardauth'=>'0'));
            $this->success('操作成功');
        }else{
            $this->success('未找到原图片路径');
        }
    }
    

    public function password()
    {
        if (!userid()) {
            redirect('/#login');
        }

        $this->assign('prompt_text', D('Text')->get_content('user_password'));
        $this->display();
    }

    public function uppassword($oldpassword, $newpassword, $repassword,$moble_verify)
    {
        if (!userid()) {
            $this->error('请先登录！');
        }
        
        if (!session('real_moble')) {
            $this->error('验证码已失效！');
        }

        if ($moble_verify != session('real_moble')) {
            $this->error('手机验证码错误！');
        }else{
            session('real_moble',null);
        }

        if (!check($oldpassword, 'password')) {
            $this->error('旧登录密码格式错误！');
        }

        if (!check($newpassword, 'password')) {
            $this->error('新登录密码格式错误！');
        }

        if ($newpassword != $repassword) {
            $this->error('确认新密码错误！');
        }

        $password = M('User')->where(array('id' => userid()))->getField('password');

        if (md5($oldpassword) != $password) {
            $this->error('旧登录密码错误！');
        }

        $rs = M('User')->where(array('id' => userid()))->save(array('password' => md5($newpassword)));

        if ($rs!==FALSE) {
            $this->success('修改成功');
        }
        else {
            $this->error('修改失败');
        }
    }
    
    
    public function uppassword_zhisucom($oldpassword="", $newpassword="",$repassword="")
    {
        if (!userid()) {
             $this->error('请先登录！');
            //$this->ajaxReturn(array('code'=>1002));
        }

        if ($oldpassword == $newpassword) {
            $this->error('新修改的密码和原密码一样！');
        }
        if (!check($oldpassword, 'password')) {
            $this->error('旧登录密码格式错误！');
            // $this->ajaxReturn(array('code'=>1034));
        }

        if (!check($newpassword, 'password')) {
            $this->error('新登录密码格式错误！');
            // $this->ajaxReturn(array('code'=>1035));
        }

        if ($newpassword != $repassword) {
            $this->error('确认新密码错误！');
            // $this->ajaxReturn(array('code'=>1025));
        }

        $password = M('User')->where(array('id' => userid()))->getField('password');

        if (md5($oldpassword) != $password) {
            $this->error('旧登录密码错误！');
            // $this->ajaxReturn(array('code'=>1034));
        }
        $paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

        if(md5($newpassword) == $paypassword){
            $this->error("新密码不能和交易密码一样");
        }
        
        
        
        $rs = M('User')->where(array('id' => userid()))->save(array('password' => md5($newpassword)));

        if (!($rs===false)) {
            $this->success('修改成功');
            // $this->ajaxReturn(array('code'=>1012));
        }
        else {
            $this->error('修改失败');
            // $this->ajaxReturn(array('code'=>1013));
        }
    }
    
    
    

    public function paypassword()
    {
        if (!userid()) {
            redirect('/#login');
        }
        
        
        $user = M('User')->where(array('id' => userid()))->find();
        $this->assign('user', $user);
        
        $this->assign('prompt_text', D('Text')->get_content('user_paypassword'));
        $this->display();
    }

    
    
    public function uppaypassword_zhisucom($oldpaypassword, $newpaypassword, $repaypassword)
    {
        //var_dump($_POST);die;
        if (!userid()) {
            $this->error('请先登录！');
        }


        if (!check($oldpaypassword, 'password')) {
            $this->error('旧资金密码格式错误！');
        }

        if (!check($newpaypassword, 'password')) {
            $this->error('新资金密码格式错误！');
        }

        if ($newpaypassword != $repaypassword) {
            $this->error('确认新密码错误！');
        }

        $user = M('User')->where(array('id' => userid()))->find();

        if (md5($oldpaypassword) != $user['paypassword']) {
            $this->error('旧资金密码错误！');
        }

        if (md5($newpaypassword) == $user['password']) {
            $this->error('资金密码不能和登录密码相同！');
        }

        $rs = M('User')->where(array('id' => userid()))->save(array('paypassword' => md5($newpaypassword)));

        if (!($rs===false)) {
            $this->success('修改成功');
        }
        else {
            $this->error('修改失败');
        }
    }
    
    
    
    
    
    
    
    
    public function uppaypassword($oldpaypassword, $newpaypassword, $repaypassword, $moble_verify)
    {
        if (!userid()) {
            $this->error('请先登录！');
        }

        if (!session('real_moble')) {
            $this->error('验证码已失效！');
        }

        if ($moble_verify != session('real_moble')) {
            $this->error('手机验证码错误！');
        }else{
            session('real_moble',null);
        }

        if (!check($oldpaypassword, 'password')) {
            $this->error('旧交易密码格式错误！');
        }

        if (!check($newpaypassword, 'password')) {
            $this->error('新交易密码格式错误！');
        }

        if ($newpaypassword != $repaypassword) {
            $this->error('确认新密码错误！');
        }

        $user = M('User')->where(array('id' => userid()))->find();

        if (md5($oldpaypassword) != $user['paypassword']) {
            $this->error('旧交易密码错误！');
        }

        if (md5($newpaypassword) == $user['password']) {
            $this->error('交易密码不能和登录密码相同！');
        }

        $rs = M('User')->where(array('id' => userid()))->save(array('paypassword' => md5($newpaypassword)));

        if ($rs) {
            $this->success('修改成功');
        }
        else {
            $this->error('修改失败');
        }
    }

    public function ga()
    {
        if (empty($_POST)) {
            if (!userid()) {
                redirect('/#login');
            }

            $this->assign('prompt_text', D('Text')->get_content('user_ga'));
            $user = M('User')->where(array('id' => userid()))->find();
            $is_ga = ($user['ga'] ? 1 : 0);
            $this->assign('is_ga', $is_ga);

            if (!$is_ga) {
                $ga = new \Common\Ext\GoogleAuthenticator();
                $secret = $ga->createSecret();
                session('secret', $secret);
                $this->assign('Asecret', $secret);
                $qrCodeUrl = $ga->getQRCodeGoogleUrl($user['username'] . '%20-%20' . $_SERVER['HTTP_HOST'], $secret);
                $this->assign('qrCodeUrl', $qrCodeUrl);
                $this->display();
            }
            else {
                $arr = explode('|', $user['ga']);
                $this->assign('ga_login', $arr[1]);
                $this->assign('ga_transfer', $arr[2]);
                $this->display();
            }
        }
        else {
            if (!userid()) {
                $this->error('登录已经失效,请重新登录!');
            }

            $delete = '';
            $gacode = trim(I('ga'));
            $type = trim(I('type'));
            $ga_login = (I('ga_login') == false ? 0 : 1);
            $ga_transfer = (I('ga_transfer') == false ? 0 : 1);

            if (!$gacode) {
                $this->error('请输入验证码!');
            }

            if ($type == 'add') {
                $secret = session('secret');

                if (!$secret) {
                    $this->error('验证码已经失效,请刷新网页!');
                }
            }
            else if (($type == 'update') || ($type == 'delete')) {
                $user = M('User')->where('id = ' . userid())->find();

                if (!$user['ga']) {
                    $this->error('还未设置谷歌验证码!');
                }

                $arr = explode('|', $user['ga']);
                $secret = $arr[0];
                $delete = ($type == 'delete' ? 1 : 0);
            }
            else {
                $this->error('操作未定义');
            }

            $ga = new \Common\Ext\GoogleAuthenticator();

            if ($ga->verifyCode($secret, $gacode, 1)) {
                $ga_val = ($delete == '' ? $secret . '|' . $ga_login . '|' . $ga_transfer : '');
                M('User')->save(array('id' => userid(), 'ga' => $ga_val));
                $this->success('操作成功');
            }
            else {
                $this->error('验证失败');
            }
        }
    }

    public function moble()
    {
        if (!userid()) {
            redirect('/#login');
        }

        $user = M('User')->where(array('id' => userid()))->find();

        //if ($user['moble']) {
            //$user['moble'] = substr_replace($user['moble'], '****', 3, 4);
        //}

        $this->assign('user', $user);
        $this->assign('prompt_text', D('Text')->get_content('user_moble'));
        $this->display();
    }

    public function upmoble($moble, $moble_verify)
    {
        if (!userid()) {
            $this->error('您没有登录请先登录！');
        }

        if (!check($moble, 'moble')) {
            $this->error('手机号码格式错误！');
        }

        if (!check($moble_verify, 'd')) {
            $this->error('短信验证码格式错误！');
        }

        if ($moble_verify != session('real_verify')) {
            $this->error('短信验证码错误！');
        }

        if (M('User')->where(array('moble' => $moble))->find()) {
            $this->error('手机号码已存在！');
        }

        $rs = M('User')->where(array('id' => userid()))->save(array('moble' => $moble, 'mobletime' => time()));

        if ($rs) {
            $this->success('手机认证成功！');
        }
        else {
            $this->error('手机认证失败！');
        }
    }
    
    
    
    public function upmoble_zhisucom($moble_new="", $moble_verify_new="")
    {
        if (!userid()) {
            $this->error('您没有登录请先登录！');
        }

        if (!check($moble_new, 'moble')) {
            $this->error('手机号码格式错误！');
        }

        if (!check($moble_verify_new, 'd')) {
            $this->error('短信验证码格式错误！');
        }

        if ($moble_verify_new != session('real_verify')) {
            $this->error('短信验证码错误！');
        }

        if (M('User')->where(array('moble' => $moble_new))->find()) {
            $this->error('手机号码已存在！');
        }

        $rs = M('User')->where(array('id' => userid()))->save(array('moble' => $moble_new,'username'=>$moble_new, 'mobletime' => time()));

        if (!($rs===false)) {
            $this->success('手机绑定成功！');
        }
        else {
            $this->error('手机绑定失败！');
        }
    }
    
    
    
    
    
    
    
    

    public function alipay()
    {
        if (!userid()) {
            redirect('/#login');
        }

        D('User')->check_update();
        $this->assign('prompt_text', D('Text')->get_content('user_alipay'));
        $user = M('User')->where(array('id' => userid()))->find();
        $this->assign('user', $user);
        $this->display();
    }

    public function upalipay($alipay = NULL, $paypassword = NULL)
    {
        if (!userid()) {
            $this->error('您没有登录请先登录！');
        }

        if (!check($alipay, 'moble')) {
            if (!check($alipay, 'email')) {
                $this->error('支付宝账号格式错误！');
            }
        }

        if (!check($paypassword, 'password')) {
            $this->error('交易密码格式错误！');
        }

        $user = M('User')->where(array('id' => userid()))->find();

        if (md5($paypassword) != $user['paypassword']) {
            $this->error('交易密码错误！');
        }

        $rs = M('User')->where(array('id' => userid()))->save(array('alipay' => $alipay));

        if ($rs) {
            $this->success('支付宝认证成功！');
        }
        else {
            $this->error('支付宝认证失败！');
        }
    }

    public function tpwdset()
    {
        if (!userid()) {
            redirect('/#login');
        }

        $user = M('User')->where(array('id' => userid()))->find();
        $this->assign('prompt_text', D('Text')->get_content('user_tpwdset'));
        $this->assign('user', $user);
        $this->display();
    }

    public function tpwdsetting()
    {
        if (userid()) {
            $tpwdsetting = M('User')->where(array('id' => userid()))->getField('tpwdsetting');
            exit($tpwdsetting);
        }
    }

    public function uptpwdsetting()
    {
        if (!userid()) {
            $this->error('请先登录！');
            // $this->ajaxReturn(array('code'=>1002));
        }
        $paypassword = I('post.paypassword');
        $tpwdsetting=1;
        if (!check($oldpaypassword, 'password')) {
            $this->error('旧交易密码格式错误！');
        }
        
        if (!check($paypassword, 'password')) {
            $this->error('交易密码格式错误！');
            // $this->ajaxReturn(array('code'=>1005));
        }

        if (($tpwdsetting != 1) && ($tpwdsetting != 2) && ($tpwdsetting != 3)) {
            $this->error('选项错误！' . $tpwdsetting);
            // $this->ajaxReturn(array('code'=>1004));
        }

        $user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

        if (md5($paypassword) != $user_paypassword) {
            $this->error('交易密码错误！');
        } 
        if (md5($oldpaypassword) != $user_paypassword) {
            $this->error('旧交易密码错误！');
        }

        $savedata['paypassword'] = md5($paypassword);
        $savedata['tpwdsetting'] = $tpwdsetting;
        //$rs = M('User')->where(array('id' => userid()))->save(array('tpwdsetting' => $tpwdsetting));
        $rs = M('User')->where(array('id' => userid()))->save($savedata);

        if (!($rs===false)) {
            $this->success('操作成功！');
            // $this->ajaxReturn(array('code'=>1012));
        }
        else {
            $this->error('操作失败！');
            // $this->ajaxReturn(array('code'=>1013));
        }
    }
    
    
    //设置交易 密码
    public function set_transpwd()
    {
        $password = I('post.password');
        $repassword = I('post.repassword');
        $nickname = I('post.nickname') ? I('post.nickname') : '';
        $tpwdsetting=1;
        if (!check($password, 'password')) {
            $this->error('交易密码格式错误,密码加数字结合！');
            // $this->ajaxReturn(array('code'=>1005));
        }
        if($password != $repassword)
        {
            $this->error('重复密码错误');
        }
        $user = M('User')->where(array('id'=>userid()))->field('password,paypassword')->find();
        if($user['password'] == md5($password))
        {
            $this->error('不能与登录密码一样');
        }
        $savedata['paypassword'] = md5($password);
        $savedata['tpwdsetting'] = $tpwdsetting;
        $savedata['nickname'] = $nickname;
        $rs = M('User')->where(array('id' => userid()))->save($savedata);
        
        if (!($rs===false)) {
            $this->success('操作成功！');
            // $this->ajaxReturn(array('code'=>1012));
        }
        else {
            $this->error('操作失败！');
            // $this->ajaxReturn(array('code'=>1013));
        }
    }
    
    //修改交易密码
    public function save_transpwd()
    {
        $password = I('post.password');
        $repassword = I('post.repassword');
        $code = trim(I('post.code'))>0 ? trim(I('post.code')) : 1;
        $code_type = I('post.code_type');
        
        $tpwdsetting=1;
        if (!check($password, 'password')) {
            $this->error('交易密码格式错误！');
            // $this->ajaxReturn(array('code'=>1005));
        }
        
        if($password != $repassword)
        {
            $this->error('重复密码错误');
        }
        $user = M('User')->where(array('id'=>userid()))->field('password,paypassword,username')->find();
        if($user['password'] == md5($password))
        {
            $this->error('不能与登录密码一样');
        }
        if($user['paypassword'] == md5($password))
        {
            $this->error('不能与原密码一样');
        }
        //$code_log = M('Code_log')->where(array('username'=>$user['username'],'type'=>$code_type))->order('id desc')->find();
        if($code != 1)
        {
            $code_log = M('Code_log')->where(array('username'=>$user['username'],'type'=>$code_type))->order('id desc')->find();
            if($code_log)
            {
                if($code_log['addtime'] + 300 < time())
                {
                    $this->error('验证码已过期');
                }
                if($code_log['code'] != $code)
                {
                    $this->error('验证码错误');
                }
            }
            else
            {
                $this->error('请获取验证码');
            }
        }
        $savedata['paypassword'] = md5($password);
        $savedata['tpwdsetting'] = $tpwdsetting;
        
        $rs = M('User')->where(array('id' => userid()))->save($savedata);
        if (!($rs===false)) {
            $this->success('操作成功！');
            // $this->ajaxReturn(array('code'=>1012));
        }
        else {
            $this->error('操作失败！');
            // $this->ajaxReturn(array('code'=>1013));
        }
    }

    public function bank()
    {
        if (!userid()) {
            redirect('/#login');
        }

        $UserBankType = M('UserBankType')->where(array('status' => 1))->order('id desc')->select();
        $this->assign('UserBankType', $UserBankType);
        $truename = M('User')->where(array('id' => userid()))->getField('truename');
        $this->assign('truename', $truename);
        //$UserBank = M('UserBank')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->limit(1)->select();
        $UserBank = M('UserBank')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();
        
        $this->assign('UserBank', $UserBank);
        $this->assign('prompt_text', D('Text')->get_content('user_bank'));
        $this->display();
    }

    public function upbank($name, $bank, $bankprov, $bankcity, $bankaddr, $bankcard, $paypassword)
    {
        if (!userid()) {
            redirect('/#login');
        }

        if (!check($name, 'a')) {
            $this->error('备注名称格式错误！');
        }

        if (!check($bank, 'a')) {
            $this->error('开户银行格式错误！');
        } 
        
        if (!check($bankprov, 'c')) {
            $this->error('开户省市格式错误！');
        }

        if (!check($bankcity, 'c')) {
            $this->error('开户省市格式错误2！');
        }

        if (!check($bankaddr, 'a')) {
            $this->error('开户行地址格式错误！');
        }

        if (!check($bankcard, 'd')) {
            $this->error('银行账号格式错误！');
        }

        if (!check($paypassword, 'password')) {
            $this->error('交易密码格式错误！');
        }

        $user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

        if (md5($paypassword) != $user_paypassword) {
            $this->error('交易密码错误！');
        }

        if (!M('UserBankType')->where(array('title' => $bank))->find()) {
            $this->error('开户银行错误！');
        } 

        $userBank = M('UserBank')->where(array('userid' => userid()))->select();

        foreach ($userBank as $k => $v) {
            if ($v['name'] == $name) {
                $this->error('请不要使用相同的备注名称！');
            }

            if ($v['bankcard'] == $bankcard) {
                $this->error('银行卡号已存在！');
            }
        } 

        if (10 <= count($userBank)) {
            $this->error('每个用户最多只能添加10个银行卡账户！');
        }

        if (M('UserBank')->add(array('userid' => userid(), 'name' => $name, 'bank' => $bank, 'bankprov' => $bankprov, 'bankcity' => $bankcity, 'bankaddr' => $bankaddr, 'bankcard' => $bankcard, 'addtime' => time(), 'status' => 1))) {
            $this->success('银行添加成功！');
        }
        else {
            $this->error('银行添加失败！');
        }
    }

    public function delbank($id, $paypassword)
    {

        if (!userid()) {
            redirect('/#login');
        }

        if (!check($paypassword, 'password')) {
            $this->error('交易密码格式错误！');
        }

        if (!check($id, 'd')) {
            $this->error('参数错误！');
        }

        $user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

        if (md5($paypassword) != $user_paypassword) {
            $this->error('交易密码错误！');
        }

        if (!M('UserBank')->where(array('userid' => userid(), 'id' => $id))->find()) {
            $this->error('非法访问！');
        }
        else if (M('UserBank')->where(array('userid' => userid(), 'id' => $id))->delete()) {
            $this->success('删除成功！');
        }
        else {
            $this->error('删除失败！');
        }
    }

    public function qianbao($coin = NULL)
    {
        if (!userid()) {
            redirect('/#login');
        }

        $Coin = M('Coin')->where(array(
            'status' => 1,
            'name'   => array('neq', 'cny')
            ))->select();

        if (!$coin) {
            $coin = $Coin[0]['name'];
        }

        $this->assign('xnb', $coin);

        foreach ($Coin as $k => $v) {
            $coin_list[$v['name']] = $v;
        }

        $this->assign('coin_list', $coin_list);
        $userQianbaoList = M('UserQianbao')->where(array('userid' => userid(), 'status' => 1, 'coinname' => $coin))->order('id desc')->select();
        $this->assign('userQianbaoList', $userQianbaoList);
        $this->assign('prompt_text', D('Text')->get_content('user_qianbao'));
        $this->display();
    }

    //提现地址列表
    public function tx_list(){
        if (!userid()) {
            redirect('/#login');
        }
        $coin = I('post.coin');
        $qianbao = M('UserQianbao')->where(array('userid'=>userid(),'coinname'=>$coin,'status'=>1))->select();
        if($qianbao){
            $this->ajaxReturn(array('code'=>200,'msg'=>$qianbao));
        }else{
            $this->ajaxReturn(array('code'=>404,'msg'=>'获取失败'));
        }
    }

    public function upqianbao()
    {
        if (!userid()) {
            // redirect('/#login');
            $this->ajaxReturn(array('code'=>1002));
        }
        $coin = I('post.coin');
        $name = I('post.name');
        $addr = I('post.addr');
        // $paypassword = I('post.paypassword');
        if (!check($name, 'a')) {
            // $this->error('备注名称格式错误！');
            //$this->ajaxReturn(array('code'=>1004));
        }

        if (!check($addr, 'dw')) {
            // $this->error('钱包地址格式错误！');
            //$this->ajaxReturn(array('code'=>1004));
        }

        /*if (!check($paypassword, 'password')) {
            $this->error('交易密码格式错误！');
        }

        $user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

        if (md5($paypassword) != $user_paypassword) {
            $this->error('交易密码错误！');
        }*/

        if (!M('Coin')->where(array('name' => $coin))->find()) {
            // $this->error('币种错误！');
            $this->ajaxReturn(array('code'=>1004));
        }

        $userQianbao = M('UserQianbao')->where(array('userid' => userid(), 'coinname' => $coin))->select();

        foreach ($userQianbao as $k => $v) {
            if ($v['name'] == $name) {
                // $this->error('请不要使用相同的钱包标识！');
                $this->ajaxReturn(array('code'=>1004));
            }

            if ($v['addr'] == $addr) {
                // $this->error('钱包地址已存在！');
                //$this->ajaxReturn(array('code'=>1004));
            }
        }

        if (10 <= count($userQianbao)) {
            // $this->error('每个人最多只能添加10个地址！');
            $this->ajaxReturn(array('code'=>1036));
        }

        if (M('UserQianbao')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'coinname' => $coin, 'addtime' => time(), 'status' => 1))) {
            // $this->success('添加成功！');
            $this->ajaxReturn(array('code'=>1012,'msg'=>'添加成功！'));
        }
        else {
            // $this->error('添加失败！');
            $this->ajaxReturn(array('code'=>1013,'msg'=>'添加失败！'));
        }
    }

    public function delqianbao($id, $paypassword)
    {
        if (!userid()) {
            redirect('/#login');
        }

        if (!check($paypassword, 'password')) {
            $this->error('交易密码格式错误！');
        }

        if (!check($id, 'd')) {
            $this->error('参数错误！');
        }

        $user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

        if (md5($paypassword) != $user_paypassword) {
            $this->error('交易密码错误！');
        }

        if (!M('UserQianbao')->where(array('userid' => userid(), 'id' => $id))->find()) {
            $this->error('非法访问！');
        }
        else if (M('UserQianbao')->where(array('userid' => userid(), 'id' => $id))->delete()) {
            $this->success('删除成功！');
        }
        else {
            $this->error('删除失败！');
        }
    }

    public function goods()
    {
        if (!userid()) {
            redirect('/#login');
        }

        $userGoodsList = M('UserGoods')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();

        foreach ($userGoodsList as $k => $v) {
            $userGoodsList[$k]['moble'] = substr_replace($v['moble'], '****', 3, 4);
            $userGoodsList[$k]['idcard'] = substr_replace($v['idcard'], '********', 6, 8);
        }

        $this->assign('userGoodsList', $userGoodsList);
        $this->assign('prompt_text', D('Text')->get_content('user_goods'));
        $this->display();
    }

    public function upgoods($name, $truename, $idcard, $moble, $addr, $paypassword)
    {
        if (!userid()) {
            redirect('/#login');
        }

        if (!check($name, 'a')) {
            $this->error('备注名称格式错误！');
        }

        if (!check($truename, 'truename')) {
            $this->error('联系姓名格式错误！');
        }

        if (!check($idcard, 'idcard')) {
            $this->error('身份证号格式错误！');
        }

        if (!check($moble, 'moble')) {
            $this->error('联系电话格式错误！');
        }

        if (!check($addr, 'a')) {
            $this->error('联系地址格式错误！');
        }

        $user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

        if (md5($paypassword) != $user_paypassword) {
            $this->error('交易密码错误！');
        }

        $userGoods = M('UserGoods')->where(array('userid' => userid()))->select();

        foreach ($userGoods as $k => $v) {
            if ($v['name'] == $name) {
                $this->error('请不要使用相同的地址标识！');
            }
        }

        if (10 <= count($userGoods)) {
            $this->error('每个人最多只能添加10个地址！');
        }

        if (M('UserGoods')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'idcard' => $idcard, 'truename' => $truename, 'moble' => $moble, 'addtime' => time(), 'status' => 1))) {
            $this->success('添加成功！');
        }
        else {
            $this->error('添加失败！');
        }
    }
    
    
    
    
    public function upgoods_zhisucom($name="", $truename="", $idcard="", $moble="", $addr="", $paypassword="",$prov="",$city="")
    {
        if (!userid()) {
            redirect('/#login');
        }

        if (!check($name, 'a')) {
            $this->error('备注名称格式错误！');
        }

        if (!check($truename, 'truename')) {
            $this->error('联系姓名格式错误！');
        }

        if (!check($moble, 'moble')) {
            $this->error('联系电话格式错误！');
        }

        if (!check($addr, 'a')) {
            $this->error('联系地址格式错误！');
        }

        if (!check($prov, 'a')) {
            $this->error('省份填写错误！');
        }
        if (!check($city, 'a')) {
            $this->error('城市填写错误！');
        }       
        
        $user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

        if (md5($paypassword) != $user_paypassword) {
            $this->error('交易密码错误！');
        }

        $userGoods = M('UserGoods')->where(array('userid' => userid()))->select();

        foreach ($userGoods as $k => $v) {
            if ($v['name'] == $name) {
                $this->error('请不要使用相同的地址标识！');
            }
        }

        if(10 <= count($userGoods)) {
            $this->error('每个人最多只能添加10个地址！');
        }

        if(M('UserGoods')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'prov' => $prov,'city'=>$city, 'truename' => $truename, 'moble' => $moble, 'addtime' => time(), 'status' => 1))) {
            $this->success('添加成功！');
        }
        else {
            $this->error('添加失败！');
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    public function delgoods($id, $paypassword)
    {
        if (!userid()) {
            redirect('/#login');
        }

        if (!check($paypassword, 'password')) {
            $this->error('交易密码格式错误！');
        }

        if (!check($id, 'd')) {
            $this->error('参数错误！');
        }

        $user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

        if (md5($paypassword) != $user_paypassword) {
            $this->error('交易密码错误！');
        }

        if (!M('UserGoods')->where(array('userid' => userid(), 'id' => $id))->find()) {
            $this->error('非法访问！');
        }
        else if (M('UserGoods')->where(array('userid' => userid(), 'id' => $id))->delete()) {
            $this->success('删除成功！');
        }
        else {
            $this->error('删除失败！');
        }
    }

    public function log()
    {
        if (!userid()) {
            redirect('/#login');
        }

        $where['status'] = array('egt', 0);
        $where['userid'] = userid();
        $Model = M('UserLog');
        $count = $Model->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('prompt_text', D('Text')->get_content('user_log'));
        $this->display();
    }

    public function install()
    {
    }
    
    
    //工单币种列表
    public function coinlist(){
         $this->ajaxReturn(array(
             'code'    =>  200,
             'result'  =>   '获取成功',
             'body'    =>  C('coin_on')
         ));
    }
    
    //提交工单
    public function worklist(){
         // var_dump($_SESSION);die;
         //$list = M('worklist')->where('close_id=0 and send_id='.$_SESSION['userId'])->order('send_time desc')->select();
         $list = M('worklist')->where('send_id='.userid())->order('send_time desc')->select();
         // var_dump($list);die;
         foreach($list as $k=>$v)
         {
         	$url = PC_URL;
			$pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
			$list[$k]['reply_content'] = preg_replace($pregRule, '<img src="'.$url.'${1}" style="max-width:100%">', $v['reply_content']);
           if($v['send_img1']){
           	$list[$k]['send_img1'] = PC_URL.$v['send_img1'];
           }
           $list[$k]['send_time'] = date('Y-m-d H:i:s',$v['send_time']);
           
         }
         //$this->assign('list',$list);
         $this->ajaxReturn(array(
             'code'   =>  200,
             'result' =>  '获取成功',
             'body'   =>  array(
                 'list'   =>  $list
             )
         ));
        $this->display();
    }

    //处理提交工单图片
    public function worklist_img(){
         /* var_dump($_FILES);
         var_dump($_POST);
          die;*/
         if (!userid()) {
            $this->error('请登录');
        }
         
        if ($_FILES)
        {
          for($i=1;$i<4;$i++){
              $name = $_FILES["filename$i"]['name'];
              if($name){
                  $time = time();
                  $n = $this->getRandCode().$name;
                  $data["send_img$i"] = '/Upload/worklist_img/'.$n;
                  $URL='./Upload/worklist_img/'.$n;
                  //上传图片
                  $a=move_uploaded_file($_FILES["filename$i"]['tmp_name'], $URL);
              }
              
              //$this->success($URL1);
          }
        }
        
        if(!($_POST['ticket_text'] || ($data['send_img1'] || $data['send_img2'] || $data['send_img3']))){
            $this->error('内容和图片不能同时为空');
            //$this->ajaxReturn(array('code'=>1051));
        }
        $user = M('User')->where('id = '.userid())->find();
        // var_dump($user);die;
        if($_POST){
            $data['send_id'] = userid();
            $data['send_time'] = time();
            $data['send_content'] = $_POST['ticket_text'];
            $data['send_user'] = $user['username'];
            /* $data['send_img1'] = $n1?'/Upload/worklist_img/'.$n1:'';
            $data['send_img2'] = $n2?'/Upload/worklist_img/'.$n2:'';
            $data['send_img3'] = $n3?'/Upload/worklist_img/'.$n3:''; */
            $data['status'] = 0;
            $data['send_cate'] = 0;
            /*$data['currency'] = $_POST['currency'];
            $data['trans_id'] = $_POST['depo_txid'];*/
            $data['worklist_id'] = $this->worklist_code();
            $list = M('worklist')->add($data);
            if($list){
               $this->success('提交成功');
                //$this->ajaxReturn(array('code'=>1012));
            }else{
               $this->error('提交失败');
                //$this->ajaxReturn(array('code'=>1013));
            }
        }
     
    }
  
  public function worklistimg()
  {
  	
  }
    
    //处理提交工单图片
    public function worklist_send(){
         
         if (!userid()) {
           //$this->error('请先登陆');
            $this->ajaxReturn(array(
             'code'   =>  404,
             'result' =>  '请先登陆'
         ));
        }
        

if($_FILES['filename']["tmp_name"]){
            $result=$this->upload2("filename");
            // var_dump($result);die;
            if($result!="error"){
                $data['send_img1'] = PC_URL. '/Upload/worklist_img/'.$result;
             // $this->ajaxReturn($data);
            }else{
              // $this->error('提交失败');
              $this->ajaxReturn(array(
             'code'   =>  404,
             'result' =>  '提交失败'
         ));
            }
        }


        $user = M('User')->where(array('login_token'=>$_POST['ltoken']))->find();
        // var_dump($user);die;
        if($_POST){
            $data['send_id'] = userid();
            $data['send_time'] = time();
            $data['send_content'] = $_POST['ticket_text'];
            $data['send_user'] = $_POST['username'];
            $data['status'] = 0;
            $data['send_cate'] = 0;
            $data['worklist_id'] = $this->worklist_code();
            $list = M('worklist')->add($data);
            if($list){
               // $this->success('提交成功');
              $this->ajaxReturn(array(
             'code'   =>  200,
             'result' =>  '提交成功'
         ));
            }else{
               $this->ajaxReturn(array(
             'code'   =>  404,
             'result' =>  '提交失败'
         ));
            }
        }
     
    }
    public function imgUser_g(){
        // var_dump($_FILES);die;
        //上传用户身份证
        /*if (!userid()) {
            echo "nologin";
        }*/
        
        if($_FILES['inputfile1']["tmp_name"]){
            $result=$this->upload2("inputfile1");
            // var_dump($result);die;
            if($result!="error"){
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
        if($_FILES['inputfile2']["tmp_name"]){
            $result=$this->upload2("inputfile2");
            if($result!="error"){
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
        if($_FILES['inputfile3']["tmp_name"]){
            $result=$this->upload2("inputfile3");
            if($result!="error"){
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
        }
    }
    
    protected function upload2($name){
        $upload = new \Think\Upload();
        $upload->maxSize = 20971520;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath = './Upload/worklist_img/';
        $upload->autoSub = false;
        $info = $upload->uploadOne($_FILES[$name]);
        if(!$info){
            return "error";
            exit();
        }else{
            return $info['savename'];
        }
    }
    
    //关闭工单
    public function close_down(){
        // var_dump($_POST);die;
        $data['close_id'] = 1;
        $where['worklist_id'] = $_POST['dan'];
        $info = M('worklist')->where($where)->save($data);
        if($info){
            $this->success('关闭成功');
        }else{
            $this->error('关闭失败');
        }
    }
    
    
    //随机字符串(上传图片命名)
    public function getRandCode()
    {
        $charts = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz0123456789";
        $max = strlen($charts);
        $noncestr = "";
        for($i = 0; $i < 16; $i++)
        {
            $noncestr .= $charts[mt_rand(0, $max)];
        }


        return $noncestr;
    }
    //生成工单号
    public function worklist_code(){
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J','K','M');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
    
    //邮箱绑定
    public function email(){
      $user_info = M('user')->where('id='.$_SESSION['userId'])->find();
      //var_dump($_SESSION);die;
      $this->assign('user_info',$user_info);
        $this->display();
    }

    //验证邮箱
    public function yz_email(){
        // var_dump($_POST);die;
        if($_POST){
            $where['email'] = $_POST['email'];
            $userinfo = M('user')->where($where)->find();
            if($userinfo){
                $this->error('该邮箱已存在');
            }
        }
    }
  //邮箱绑定提交
  public function email_bind(){
    //var_dump($_POST);die;
    $email  = trim(I('post.email'));
    $yzm = trim(I('post.yzm'));
    if(!$email){
        // $this->error('邮箱不能为空');
        $this->ajaxReturn(array('code'=>1002));
    }
    if(!check($email,'email')){
        // $this->error('邮箱格式不正确');
        $this->ajaxReturn(array('code'=>1053));
    }
    /*if(session('real_verify') != $yzm){
        $this->error('验证码不正确');
    }*/
    $where['email'] = $email;
    $user_email = M('user')->where($where)->getField('id');
    if($user_email){
        // $this->error('邮箱已存在');
        $this->ajaxReturn(array('code'=>1054));
    }
    
    $data['email'] = $email;
    $email_info = M('user')->where(array('id'=>userid()))->save($data);
    if($email_info){
        // $this->success('绑定成功');
        $this->ajaxReturn(array('code'=>1012));
    }else{
        // $this->error('绑定失败');
        $this->ajaxReturn(array('code'=>1013));
    }
    
  }
  
  //支付设置
  public function zhifushezhi(){
      if (!userid()) {
            $this->error('请先登陆');
        }
      $info = M('user_bank')->where('userid='.userid())->select();
      //$userinfo = M('user')->where('id = '.userid())->find();
      // var_dump($userinfo);die;
      //$this->assign('userinfo',$userinfo);
      //$this->assign('info',$info);
      $this->success('获取成功',$info);
      $this->display();
  }
  
  //支付宝，微信显示-app显示
  public function zhifushezhi_aliw(){
      if (!userid()) {
          $this->error('请先登陆');
      }
      $userinfo = M('user')->where('id = '.userid())->field('ddpay,ddpayname,weixin,weixin_name,weixin_img,ali_img')->find();
      // var_dump($userinfo);die;
      //$this->assign('userinfo',$userinfo);
      //$this->assign('info',$info);
      $this->success('获取成功',$userinfo);
      $this->display();
  }
  
  //绑定银行卡
  public function bind_bank_old(){
      // var_dump($_POST);die;
       if (!userid()) {
            // $this->error('请先登陆');
        $this->ajaxReturn(array('code'=>1002));
        }
        $user = M('user')->where('id='.userid())->find();
      if($_POST){
        $bank = M('user_bank')->where('bankcard='.$_POST['bank_addr'])->find();
        if($bank){
            // $this->error('银行卡号已存在！');
            $this->ajaxReturn(array('code'=>1038));
        }
        
        if (!check($_POST['bank_addr'], 'd')) {
            // $this->error('银行账号格式错误！');
            $this->ajaxReturn(array('code'=>1039));
        }
        
        /*if(md5($_POST['fund_pwd_bank']) != $user['paypassword']){
            $this->error('资金密码错误');
        }*/
        $save = M('user_bank')->add(array('userid'=>$user['id'],'bank'=>$_POST['bank_name'],'bankuname'=>$_POST['bank_user'],'name'=>$user['truename'],'bankaddr'=>$_POST['bank_branch'],'bankcard'=>$_POST['bank_addr'],'addtime'=>time(),'sort'=>0,'status'=>1));
        if($save){
            // $this->success('绑定成功');
            $this->ajaxReturn(array('code'=>1012));
        }else{
            // $this->error('绑定失败');
            $this->ajaxReturn(array('code'=>1013));
        }
      }else{
            // $this->error('绑定失败');
        $this->ajaxReturn(array('code'=>1012));
        }
  }
  
  
  //绑定银行卡
  public function bind_bank()
  {
      if(!userid())
      {
          $this->error('请登录');
      }
      
      $bankname = I('post.bankname');
      $bankcard = I('post.bankcard');
      $address = I('post.address');
      $paypassword = I('post.paypassword');
    $truename = I('post.truename');
      if (!check($bankcard, 'd')) 
      {
            $this->error('银行账号格式错误！');
            //$this->ajaxReturn(array('code'=>1039));
      }
      $bank = M('user_bank')->where('bankcard='.$bankcard)->find();
      if($bank){
            $this->error('银行卡号已存在！');
            // $this->ajaxReturn(array('code'=>1038));
        }
      $user = M('User')->where(array('id'=>userid()))->field('id,paypassword,truename')->find();
      if(md5($paypassword) != $user['paypassword'])
      {
          $this->error('交易密码错误');
      }
      
      $bind_bank = M('User_bank')->add(array('userid'=>$user['id'],'bank'=>$bankname,'bankuname'=>$truename,'name'=>$truename,'bankaddr'=>$address,'bankcard'=>$bankcard,'addtime'=>time(),'sort'=>0,'status'=>1));
      if($bind_bank)
      {
          $this->success('绑定成功');
      }
      else
      {
          $this->error('绑定失败');
      }
  }
  
  
   public function save_bank()
  {
      if(!userid())
      {
          $this->error('请登录');
      }
      
      $bankname = I('post.bankname');
      $bankcard = I('post.bankcard');
      $address = I('post.address');
      $paypassword = I('post.paypassword');
    $truename = I('post.truename');
      if (!check($bankcard, 'd')) 
      {
            $this->error('银行账号格式错误！');
            //$this->ajaxReturn(array('code'=>1039));
      }
      $bank = M('user_bank')->where('bankcard='.$bankcard)->find();
      if($bank){
            $this->error('银行卡号已存在！');
            // $this->ajaxReturn(array('code'=>1038));
        }
      $user = M('User')->where(array('id'=>userid()))->field('id,paypassword,truename')->find();
      if(md5($paypassword) != $user['paypassword'])
      {
          $this->error('交易密码错误');
      }
      
      $bind_bank = M('User_bank')->where(array('userid'=>$user['id']))->save(array('bank'=>$bankname,'bankuname'=>$truename,'name'=>$truename,'bankaddr'=>$address,'bankcard'=>$bankcard,'addtime'=>time(),'sort'=>0,'status'=>1));
      if($bind_bank)
      {
          $this->success('绑定成功');
      }
      else
      {
          $this->error('绑定失败');
      }
  }

  
  
  //删除绑定的银行卡记录
  public function del_bind_bank(){
      if (!userid()) {
          $this->error('请先登陆');
      }
      $user = M('user')->where('id='.userid())->find();
      if($_POST){
          $id = trim(I('post.bank_id'));
          if (!check($id, 'd')) {
              $this->error('记录id错误！');
          }
  
          $bank = M('user_bank')->where('id='.$id)->find();
          if(!$bank){
              $this->error('绑卡记录不存在！');
          }
  
          $save = M('user_bank')->where(array('id'=>$id,'userid'=>userid()))->delete();
          if($save!==FALSE){
              $result['code'] = 200;
              $result['result'] = '解绑成功';
          }else{
              $result['code'] = 204;
              $result['result'] = '解绑失败';
          }
      }else{
          $result['code'] = 204;
          $result['result'] = '参数不全';
      }
      $this->ajaxReturn($result);
  }

  public function img_up()
  {
    
    if(!empty($_FILES['file']))
    {
        $exename = $this->getExeName($_FILES['file']['name']);
        $imageSavePath = uniqid().'.'.$exename;
        var_dump($imageSavePath);die;
        if($move_uploaded_file($_FILES['file']['tmp_name'],$imageSavePath))
        {
            echo $imageSavePath;
        }
    }
  }

  public function getExeName($filename)
  {
    $pathinfo = pathinfo($filename);
    return strtolower($pathinfo['extension']);
  }


  
  //绑定支付宝
  public function bind_ali(){
      
      if (!userid()) {
               //$this->success(userid());
             $this->error('请先登陆');
        //$this->ajaxReturn(array('code'=>1002));
        }
        //var_dump($_REQUEST);die;
        /*var_dump($_POST);die;
        echo "<pre/>";
        var_dump($_FILES);die;*/
      //var_dump($_POST);die;
      $user = M('user')->where('id='.userid())->find();
      if($_POST){
        if(md5($_POST['fund_pwd_ali'])!=$user['paypassword']){
              $this->error('资金密码错误');
        }
        if ($_FILES['file']['tmp_name']) {
                $result = $this->upload_ali($_FILES['file']);
                if($result!="error"){
                    $savedata['ali_img'] = $result;
                    //M('User')->where(array('id' => userid()))->save(array('ali_img' => $result));
                    //$this->ajaxReturn($result);
                }else{
                     $this->error("图片上传出错");
                    //$this->ajaxReturn(array('code'=>1041));
                }
        }else{
             $this->error("请上传图片");
            //$this->ajaxReturn(array('code'=>1042));
        }
        $savedata['ddpay'] = $_POST['ali_name'];
        $savedata['ddpayname'] = $_POST['ddpayname'];
        // $savedata['ddmemo'] = $_POST['ali_memo'];
        //array('ddpay'=>$_POST['ali_name'],'ddpayname'=>$_POST['pay_name'],'ddmemo'=>$_POST['ali_memo']);
        $ali = M('user')->where('id='.userid())->save($savedata);
        if($ali){
             $this->success('绑定成功');
            //$this->ajaxReturn(array('code'=>1012));
        }else{
             $this->error('绑定失败');
            //$this->ajaxReturn(array('code'=>1013));
        }
      }else{
             $this->error('绑定失败');
        //$this->ajaxReturn(array('code'=>1013));
        }
  }
  //上传支付宝照片(暂不使用)
  public function ali_img(){
    // var_dump($_FILES);die;
    if ($_FILES['up-img-zfb']['tmp_name']) {
        $result = $this->upload_ali('up-img-zfb');
        if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('ali_img' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
    }
  }
  protected function upload_ali($file){
        $upload = new \Think\Upload();
        $upload->maxSize = 20971520;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath = './Upload/zhifu/';
        $upload->autoSub = false;
        //$info = $upload->uploadOne($_FILES);
        $info = $upload->uploadOne($file);
        if(!$info){
            //return "error";
            $this->error('图片上传出错');
            exit();
        }else{
            return $info['savename'];
        }
    }
  //绑定微信
  public function weixin(){
      //var_dump($_POST);die;
       if (!userid()) {
            //redirect('/#login');
            $this->error('请先登陆');
        //$this->ajaxReturn(array('code'=>1002));
        }
        $user = M('user')->where('id='.userid())->find();
        if($_POST){
            if(md5($_POST['fund_pwd_wei'])!=$user['paypassword']){
              $this->error('资金密码错误');
            }
            
            if ($_FILES['file']['tmp_name']) {
                    $result = $this->upload_ali($_FILES['file']);
                    if($result!="error"){
                        
                        $savedata['weixin_img'] = $result;
                       
                        //M('User')->where(array('id' => userid()))->save(array('weixin_img' => $result));
                        //$this->ajaxReturn($result);
                    }else{
                        $this->error("图片上传失败");
                       //  $this->ajaxReturn(array('code'=>1041));
                    }
            }
            
            $savedata['weixin'] = $_POST['wei_nameid'];
            $savedata['weixin_name'] = $_POST['weixin_name'];
            $savedata['weixin_memo'] = $_POST['wei_memo'];
            //array('weixin'=>$_POST['wei_nameid'],'weixin_name'=>$_POST['wei_name'],'weixin_memo'=>$_POST['wei_memo'])
            $wei = M('user')->where('id='.userid())->save($savedata);
            if($wei){
                 $this->success('绑定成功');
                //$this->ajaxReturn(array('code'=>1012));
            }else{
                 $this->error('绑定失败');
                //$this->ajaxReturn(array('code'=>1013));
            }
      }else{
             $this->error('绑定失败');
        //$this->ajaxReturn(array('code'=>1013));
        }
  }
  //微信照片
  public function weixin_img(){
    if ($_FILES['up-img-wx']['tmp_name']) {
        $result = $this->upload_ali('up-img-wx');
        if($result!="error"){
                M('User')->where(array('id' => userid()))->save(array('weixin_img' => $result));
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn("error");
            }
    }
  }
 

 //是否实名
  public function is_shiming(){
    if(!userid()){
          $this->error('请先登陆');
      }
      $uid = userid();
      // $uid =7;
      $truename = M('User')->where(array('id'=>$uid))->getField('idcardauth');
      if(!empty($truename)){
        $this->ajaxReturn(array('code'=>200));
      }else{
        $this->ajaxReturn(array('code'=>404));
      }
  }
  //更换头像
  public function edit_headerpic(){
      if(!userid()){
          $this->error('请先登陆');
      }
      //echo '<pre>';
      //print_r($_FILES);exit;
      if ($_FILES['file']['tmp_name']) {
            $upload = new \Think\Upload();
            $upload->maxSize = 20971520;//2048000;20971520
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = './Upload/app/header/';
            $upload->autoSub = false;
            $info = $upload->uploadOne($_FILES['file']);
            if(!$info){
                $this->error('图片上传出错');
                exit();
            }else{
                $result =  $info['savename'];
                M('User')->where(array('id' => userid()))->save(array('header_pic' => $result));
                $this->ajaxReturn($result);
            }
      }else{
          $this->error('请上传头像');
      }
  }

  //添加支付方式的姓名
  public function user_name(){
    if(!userid()){
          $this->error('请先登陆');
      }
    $user = M('User')->where(array('id'=>userid()))->getField('truename');
    $this->ajaxReturn(array('code'=>200,'msg'=>$user));
  }


  //关于我们
  public function about_we(){
    $info = M("Article")->where(array('type'=>'info','status'=>1))->find();

    $this->ajaxReturn(array('code'=>200,'info'=>$info));
  }


  //资产折合BTC
  public function compromise(){
    $uid = userid();
    // $uid = 1;
    $coin = M('Coin')->where('status=1')->select();
    // dump($coin);die;
    foreach ($coin as $k => $v) {
        if($v['name'] != 'btc'){
            $user_coin1 = M('User_coin')->where(array('userid'=>$uid))->getField($v['name']);
            $user_coin2 = M('User_coin')->where(array('userid'=>$uid))->getField($v['name'].'d');
            $user_coin3 = M('User_coin')->where(array('userid'=>$uid))->getField('gg'.$v['name']);
            $user_coin = $user_coin1 + $user_coin2 + $user_coin3;
            if($user_coin>0){
                $market = M('Market')->where(array('name'=>$v['name'].'_btc'))->getField('new_price');
                if($market > 0){
                    $sum = $user_coin / $market;
                    // echo $sum."<br/>";
                    $btc_sum1 += $sum;
                }
                
            }
        }else{
            $btc_coin1 = M('User_coin')->where(array('userid'=>$uid))->getField('btc');
            $btc_coin2 = M('User_coin')->where(array('userid'=>$uid))->getField('btcd');
            $btc_coin3 = M('User_coin')->where(array('userid'=>$uid))->getField('ggbtc');
            $btc_coin = $btc_coin1 + $btc_coin2 + $btc_coin3;
        }
    }
    $btc_sum = round($btc_sum1 + $btc_coin,6);#折合btc后的总量
    $market_cny = M('Market')->where(array('name'=>'btc_cny'))->getField('new_price');
    $cny = round($btc_sum * $market_cny,6);
    // echo $cny."<br/>";
    $this->ajaxReturn(array('code'=>200,'cny'=>$cny,'btc_sum'=>$btc_sum));
  }


  //账户资产
  public function zh_zichan(){
    $uid = userid();
    // $uid = 1;
    $coin = M('Coin')->where('status=1')->select();
    foreach ($coin as $k => $v) {#bb账户
        if($v['name'] != 'btc'){
            $user_coin1 = M('User_coin')->where(array('userid'=>$uid))->getField($v['name']);
            $user_coin2 = M('User_coin')->where(array('userid'=>$uid))->getField($v['name'].'d');
            $user_coin = $user_coin1 + $user_coin2;
            if($user_coin>0){
                $market = M('Market')->where(array('name'=>$v['name'].'_btc'))->getField('new_price');
                if($market > 0){
                    $sum = $user_coin / $market;
                    // echo $sum."<br/>";
                    $btc_sum1 += $sum;
                }
                
            }
        }else{
            $btc_coin1 = M('User_coin')->where(array('userid'=>$uid))->getField('btc');
            $btc_coin2 = M('User_coin')->where(array('userid'=>$uid))->getField('btcd');
            $btc_coin = $btc_coin1 + $btc_coin2;
        }
    }
    $btc_sum = round($btc_sum1 + $btc_coin,6);#折合btc后的总量
    $market_cny = M('Market')->where(array('name'=>'btc_cny'))->getField('new_price');
    $cny = round($btc_sum * $market_cny,6);

    foreach ($coin as $k1 => $v1) {#gg账户
        if($v1['name'] != 'btc'){
            $gg_coin = M('User_coin')->where(array('userid'=>$uid))->getField('gg'.$v1['name']);
            if($gg_coin>0){
                $market = M('Market')->where(array('name'=>$v1['name'].'_btc'))->getField('new_price');
                if($market > 0){
                    $ggsum = $gg_coin / $market;
                    // echo $sum."<br/>";
                    $ggbtc_sum1 += $ggsum;
                }
                
            }
        }else{
            $ggbtc_coin = M('User_coin')->where(array('userid'=>$uid))->getField('btc');
        }
    }
    $ggbtc_sum = round($ggbtc_sum1 + $ggbtc_coin,6);#折合btc后的总量
    $market_cny1 = M('Market')->where(array('name'=>'btc_cny'))->getField('new_price');
    $ggcny = round($ggbtc_sum * $market_cny1,6);
    $info = array();
    $info['btc_sum'] = $btc_sum;
    $info['cny'] = $cny;
    $info['ggbtc_sum'] = $ggbtc_sum;
    $info['ggcny'] = $ggcny;
    $this->ajaxReturn(array('code'=>200,'info'=>$info));
  }

  //币种资产
  public function bz_zichan(){
    $uid = userid();
    // $uid = 1;
    $coin = M('Coin')->where('status=1')->field('name')->select();
    foreach ($coin as $k => $v) {
        $user_coin1 = M('User_coin')->where(array('userid'=>$uid))->getField($v['name']);
        $user_coin2 = M('User_coin')->where(array('userid'=>$uid))->getField($v['name'].'d');
        $coin[$k]['ky'] = $user_coin1;
        $coin[$k]['zj'] = $user_coin1 + $user_coin2; 
    }
    // dump($coin);
    $this->ajaxReturn(array('code'=>200,'info'=>$coin));
  }
  
  
  
  public function upregister3()
    {
        $truename = I('post.truename');
        $idcard = I('post.idcard');
        if (!check($truename, 'truename')) {
            // $this->error('真实姓名格式错误！');
            $this->ajaxReturn(array('code'=>1032));
        }

        if (!check($idcard, 'idcard')) {
            // $this->error('身份证号格式错误！');
            $this->ajaxReturn(array('code'=>1033));
        }

        if (!$_POST['ltoken']) {
            // $this->error('非法访问！');
            $this->ajaxReturn(array('code'=>1004));
        }
        $info = M('User')->where(array('id' => userid()))->save(array('truename' => $truename, 'idcard' => $idcard,'idcardauth'=>1));

        if($info) {
            // $this->success('认证成功！');
            $this->ajaxReturn(array('code'=>1012));
        }
        else{
            // $this->error('认证失败！');
            $this->ajaxReturn(array('code'=>1013));
        }
    }
    
    //实名认证
    public function check_truename()
    {
        $truename = I('post.truename');
        $idcard = I('post.idcard');
        $type = in_array(I('post.type'),array('1','2'))?I('post.type'):$this->error('证件类型错误');
        if (!check($truename, 'truename')) {
            $this->error('真实姓名格式错误！');
            //$this->ajaxReturn(array('code'=>1032));
        }

        if (!check($idcard, 'idcard')) {
            $this->error('身份证号格式错误！');
            //$this->ajaxReturn(array('code'=>1033));
        }

        if (!$_POST['ltoken']) {
            $this->error('请登录');
            //$this->ajaxReturn(array('code'=>1004));
        }
        
        $info = M('User')->where(array('id' => userid()))->save(array('truename' => $truename, 'idcard' => $idcard,'idcardauth'=>1,'is_idcard'=>$type));

        if($info) {
            $this->success('认证成功！');
            // $this->ajaxReturn(array('code'=>1012));
        }
        else{
            $this->error('认证失败！');
            // $this->ajaxReturn(array('code'=>1013));
        }
        
    }
    
    
    //昵称
    public function nickname(){
        if(!userid()){
            // $this->error('请先登录');
            $this->ajaxReturn(array('code'=>'1002'));
        }
        $uid = userid();
        $nickname = I('post.nickname');
        if(strlen($nickname)>40){
            $this->ajaxReturn(array('code'=>1031,'info'=>'昵称格式错误'));
        }
        $info = M('User')->where(array('id'=>$uid))->save(array('nickname'=>$nickname));
        if($info){
            $this->ajaxReturn(array('code'=>1012,'info'=>'设置成功'));
        }else{
            $this->ajaxReturn(array('code'=>1013,'info'=>'设置失败'));
        }
    }
    
    public function nicknames(){
        $uid = userid();
        $info = M('User')->where(array('id'=>$uid))->getField('nickname');
        $this->ajaxReturn(array('code'=>200,'info'=>$info));
    }
    
    //支付宝信息
    public function mmp_zhifu(){
        $uid = userid();
        $info = M('user')->where(array('id'=>$uid))->field('ali_img,ddpay,ddpayname')->find();
        $info['ali_img'] = '/Upload/zhifu/'.$info['ali_img'];
        $this->ajaxReturn(array('code'=>200,'info'=>$info));
    }
    
    //威信信息
    public function mmp_weixin(){
        $uid = userid();
        $info = M('user')->where(array('id'=>$uid))->field('weixin,weixin_img,weixin_name')->find();
        $info['weixin_img'] = '/Upload/zhifu/'.$info['weixin_img'];
        $this->ajaxReturn(array('code'=>200,'info'=>$info));
    }
    
    //银行卡信息
    public function mmp_bank(){
        $uid = userid();
        $info = M('User_bank')->where(array('userid'=>$uid))->find();
        $this->ajaxReturn(array('code'=>200,'info'=>$info));
    }
    
    //反馈祥情
    public function fk_detail(){
        if(!userid()){
            $this->error('请登录');
        }
        $uid = userid();
        $id = I('post.id');
        $info = M('worklist')->where(array('id'=>$id))->find();
      	$info['send_time'] = date('Y-m-d H:i:s',$info['send_time']);
        if($info){
            $this->ajaxReturn(array('code'=>200,'info'=>$info));
        }else{
            $this->ajaxReturn(array('code'=>404));
        }
    }
    
    //是否邦定邮箱
    public function is_emails(){
        $uid = userid();
        $info = M('user')->where(array('id'=>$uid))->find();
        if($info['email']){
            $this->ajaxReturn(array('code'=>200,'info'=>$info['email']));
        }
    }
    
    
    # 版本检测
    public function get_app_version($version)
    {
        $config=M('config')->field('app_version,app_url')->where('id=1')->find();
        $version=I('post.version');
        // $version= '1.0.0';
        //dump($version);die;
        if($version==$config['app_version']){
            $this->ajaxReturn(['status'=>'ok']);
        }else{
            $this->ajaxReturn(['status'=>'error','data'=>$config['app_url']]);
        }
    }

    //分享邀请码
    public function invits(){
        $uid = userid();
        $ltoken = I('post.ltoken');
        $info1 = M('User')->where(array('login_token'=>$ltoken))->getField('invit');
        //$info1 = M('User')->where(array('id'=>$uid))->getField('invit');
        $app_url = M('Config')->where('id=1')->getField('app_url');
        $info['invit'] = $info1;
        $info['app_url'] = $app_url;
        $this->ajaxReturn(array('code'=>200,'info'=>$info));
    }



    # 邀请记录
    public function invit_detail(){
        // $uid  = userid();
        $ltoken = I('post.ltoken');
        // $ltoken = 'dffb359335be105d9ed743cd0fafeb45';
        $uid = M('User')->where(array('login_token'=>$ltoken))->getField('id');
        $invit_info = M('User')->where(array('invit_1'=>$uid))->field('username,addtime')->select();
        $jiangli = M('finance')->where(array('userid'=>$uid,'nameid'=>406))->field('coinname,fee,remark,addtime')->select();
        $this->ajaxReturn(array('code'=>200,'invit'=>$invit_info,'jiangli'=>$jiangli));
    }
    
    #昵称
    public function nick_name(){
        $ltoken = I('post.ltoken');
        $nickname = M('User')->where(array('login_token'=>$ltoken))->getField('nickname');
        $this->ajaxReturn(array('code'=>200,'nickname'=>$nickname));
    }
    
    //绑定手机号
    public function set_mobile()
    {
        $mobile = I('post.mobile');
        $code = I('post.code')?I('post.code'):1;
        $type = 1;
        
        if (!check($mobile, 'moble')) 
        {
            $this->error('手机号格式错误！');
            //$this->ajaxReturn(array('code'=>'1026'));
        }
        if($code != 1)
        {
            $code_log = M('Code_log')->where(array('username'=>$mobile,'type'=>$type))->order('id desc')->find();
            if($code_log)
            {
                if($code_log['addtime'] + 300 < time())
                {
                    $this->error('验证码已过期');
                }
                if($code_log['code'] != $code)
                {
                    $this->error('验证码错误');
                }
            }
            else
            {
                $this->error('请获取验证码');
            }
        }
        
        $user = M('User')->where(array('id'=>userid()))->save(array('moble'=>$mobile,'mobletime'=>time()));
        if($user)
        {
            $this->success('设置成功');
        }
        else
        {
            $this->error('设置失败');
        }
    }
    
    
    //更换手机号第一步
    public function save_mobile1()
    {
        $mobile = I('post.mobile');
        $code = I('post.code')?I('post.code'):1;
        $type = 1;
        
        if (!check($mobile, 'moble')) 
        {
            $this->error('手机号格式错误！');
            //$this->ajaxReturn(array('code'=>'1026'));
        }
        if($code != 1)
        {
            $code_log = M('Code_log')->where(array('username'=>$mobile,'type'=>$type))->order('id desc')->find();
            if($code_log)
            {
                if($code_log['addtime'] + 300 < time())
                {
                    $this->error('验证码已过期');
                }
                if($code_log['code'] != $code)
                {
                    $this->error('验证码错误');
                }
            }
            else
            {
                $this->error('请获取验证码');
            }
        }
        
        $this->success();
    }
    
    //更换手机号第二步
    public function save_mobile2()
    {
        $mobile = I('post.mobile');
        $code = I('post.code')?I('post.code'):1;
        $type = 1;
        
        if (!check($mobile, 'moble')) 
        {
            $this->error('手机号格式错误！');
            //$this->ajaxReturn(array('code'=>'1026'));
        }
        if($code != 1)
        {
            $code_log = M('Code_log')->where(array('username'=>$mobile,'type'=>$type))->order('id desc')->find();
            if($code_log)
            {
                if($code_log['addtime'] + 300 < time())
                {
                    $this->error('验证码已过期');
                }
                if($code_log['code'] != $code)
                {
                    $this->error('验证码错误');
                }
            }
            else
            {
                $this->error('请获取验证码');
            }
        }
        
        $user = M('User')->where(array('id'=>userid()))->save(array('moble'=>$mobile,'mobletime'=>time()));
        if($user)
        {
            $this->success('设置成功');
        }
        else
        {
            $this->error('设置失败');
        }
    }
    
    //绑定邮箱
    public function set_email()
    {
        $email = I('post.email');
        $code = I('post.code')?I('post.code'):1;
        $type = 2; 
        
        if(!check($email,'eamil'))
        {
            $this->error('邮箱格式错误！');
        }
        
        if($code != 1)
        {
            $code_log = M('Code_log')->where(array('username'=>$email,'type'=>$type))->order('id desc')->find();
            if($code_log)
            {
                if($code_log['addtime'] + 300 < time())
                {
                    $this->error('验证码已过期');
                }
                if($code_log['code'] != $code)
                {
                    $this->error('验证码错误');
                }
            }
            else
            {
                $this->error('请获取验证码');
            }
        }
        
        $user = M('User')->where(array('id'=>userid()))->save(array('email'=>$email));
        if($user)
        {
            $this->success('设置成功');
        }
        else
        {
            $this->error('设置失败');
        }
    }
    
    //更换邮箱第一步
    public function save_email1()
    {
        $email = I('post.email');
        $code = I('post.code')?I('post.code'):1;
        $type = 2;
        
        if(!check($email,'eamil'))
        {
            $this->error('邮箱格式错误！');
        }
        if($code != 1)
        {
            $code_log = M('Code_log')->where(array('username'=>$email,'type'=>$type))->order('id desc')->find();
            if($code_log)
            {
                if($code_log['addtime'] + 300 < time())
                {
                    $this->error('验证码已过期');
                }
                if($code_log['code'] != $code)
                {
                    $this->error('验证码错误');
                }
            }
            else
            {
                $this->error('请获取验证码');
            }
        }
        
        $this->success();
    }
    
    //更换邮箱第二步
    public function save_email2()
    {
        $email = I('post.email');
        $code = I('post.code')?I('post.code'):1;
        $type = 2; 
        
        if(!check($email,'eamil'))
        {
            $this->error('邮箱格式错误！');
        }
        
        if($code != 1)
        {
            $code_log = M('Code_log')->where(array('username'=>$email,'type'=>$type))->order('id desc')->find();
            if($code_log)
            {
                if($code_log['addtime'] + 300 < time())
                {
                    $this->error('验证码已过期');
                }
                if($code_log['code'] != $code)
                {
                    $this->error('验证码错误');
                }
            }
            else
            {
                $this->error('请获取验证码');
            }
        }
        
        $user = M('User')->where(array('id'=>userid()))->save(array('email'=>$email));
        if($user)
        {
            $this->success('设置成功');
        }
        else
        {
            $this->error('设置失败');
        }
    }
    
    //帮助中心列表
    public function help_center_list()
    {
        $info = M('Article')->where(array('type'=>'help','status'=>1))->order('addtime desc')->field('id,title,title_e,title_en')->select();
        $this->success($info);
    }
    
    //帮助中心详情
    public function help_center_detail()
    {
        $id = I('post.id');
        $info = M('Article')->where(array('id'=>$id,'status'=>1))->field('id,title,title_e,title_en,content,content_e,content_en')->find();
        $this->success($info);
    }


    //用户状态集合
    public function user_status()
    {
        $userid = userid();
        $user = M('User')->where(array('id'=>$userid))->find();
        $status = array();
        $status['is_paypwd'] = !empty($user['paypassword']) ? 1 : 0;#是否设置交易密码
        /*是否实名认证*/
        if($user['idcardauth'] == 0)
        {
            $status['is_shiming'] = 0;
        }
        elseif ($user['idcardauth'] == 1) 
        {
            $status['is_shiming'] = 1;
        }
        elseif ($user['idcardauth'] == 2) 
        {
            $status['truename'] = $user['truename'];
            $status['idcard'] = $user['idcard'];
            $status['is_shiming'] = 2;
        }
        /*是否实名认证结束*/

        /*是否高级认证*/
        if($user['senior_past'] == 0)
        {
            $status['senior_past'] = 0;
        }
        elseif ($user['senior_past'] == 1) 
        {
            $status['senior_past'] = 1;
        }
        elseif ($user['senior_past'] == 2) 
        {
            $status['senior_past'] = 2;
        }
        /*是否高级认证结束*/

        /*是否绑定手机号*/
        $status['is_mobile'] = !empty($user['moble']) ? 1 : 0;
        /*是否绑定手机号结束*/

        /*是否绑定邮箱*/
        $status['is_email'] = !empty($user['email']) ? 1 : 0;
        /*是否绑定邮箱结束*/

        /*是否绑定银行卡*/
        $bank = M('user_bank')->where(array('userid'=>userid()))->field('id')->find();
        $status['is_bank'] = $bank['id'] > 0 ? 1 : 0;
        /*是否绑定银行卡结束*/

        /*是否绑定支付宝*/
        $status['is_ali_pay'] = !empty($user['ddpay']) ? 1 : 0;
        /*是否绑定支付宝结束*/

        /*是否绑定微信*/
        $status['is_weixin'] = !empty($user['weixin']) ? 1 : 0;
        /*是否绑定微信*/
      
        /*商家状态 */
        $status['shoper'] = !empty($user['shoper']) ? $user['shoper'] : 0;
        /*商家状态 */
        $this->success($status);

    }
  
  
  //商家缴纳保证金页面
  public function shop_bzj()
  {
    $config = M('Config')->where("id=1")->field('bzj_addr,bzj_num,bzj_coin')->find();
    $user = M('User')->where(array('id'=>userid()))->field('id,moble,truename')->find();
    $config['uid'] = $user['id'];
    $config['moble'] = $user['moble'];
    $config['truename'] = $user['truename'];
    $this->success($config);
  }

  public function shop_bzj_ac()
  {
    $userid = userid();
    $user = M('User')->where(array('id'=>$userid))->find();
    $data = array();
    $data['userid'] = $user['id'];
    $data['truename'] = $user['truename'];
    $data['moble'] = $user['moble'];
    $data['weixin'] = I('post.weixin');
    $data['content'] = I('post.content');
    $data['idcard'] = $user['idcard'];
    $data['status'] = 1;
    $data['shop_time'] = time();
    M('User')->where(array('id'=>$userid))->save(array('shoper'=>1));
    $res = M('User_shop')->add($data);
    if($res)
    {
        $this->success('申请成功');
    }
    else
    {
        $this->error('申请失败');
    }
  }
  
  
  //东洲用到的amfc_usdt价格
  public function amfc_jeff()
  {
    $res = M('Market')->where(array('name'=>"amfc_jeff"))->getField('new_price');
    $res = $res > 0 ? $res : 0;
    $this->ajaxReturn(array('code'=>200,'new_price'=>$res));
  }
    
}

?>