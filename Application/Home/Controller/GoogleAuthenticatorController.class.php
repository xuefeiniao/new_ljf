<?php
namespace Home\Controller;

use \Org\Util\GoogleAuthenticator;
use \Think\Controller;

    /**
    *       调用方法
    *       use Home\Controller\GoogleAuthenticatorController;
    * 		$ga=new GoogleAuthenticatorController();
    * 
    * 		$data=$ga->verifyCode('00165','7CRGRUKJL6ZV62A5');
    * 		var_dump($data);die;
    */

class GoogleAuthenticatorController extends Controller
{
    protected $GoogleAuth;
    protected $Secret;
    protected $QRCodeGoogleUrl;
    protected $UID;

    public function __construct()
    {
        $this->GoogleAuth=new GoogleAuthenticator;

    }
    /**
     * 获取秘钥
     */
    public function createSecret($UID)
    {
        $secret=$this->GoogleAuth->createSecret();

        $this->Secret=$secret;
        $this->UID=$UID;

        return  $secret?$this->getQRCodeGoogleUrl():'error';
    }
     /**
     * 获取二维码
     */
    public function getQRCodeGoogleUrl()
    {

        $QRCodeGoogleUrl=$this->GoogleAuth->getQRCodeGoogleUrl($this->UID,$this->Secret,"finsys.net");

        $this->QRCodeGoogleUrl=$QRCodeGoogleUrl;

        return $QRCodeGoogleUrl?$this->backCreate():'error';

        
    }
    /**
     * 返回创建信息
     */
    public function backCreate()
    {
        
        return array(['secret'=>$this->Secret,'QRCodeGoogleUrl'=>$this->QRCodeGoogleUrl]);

    }

    // /**
    //  * 获取验证码
    //  */
    // public function getCode($secret)
    // {

    //     return $secret?$this->GoogleAuth->getCode($secret):'error';

    // }

    /**
     * 验证验证码
     */

    public function verifyCode($inCode,$secret)
    {
       $checkResult=$this->GoogleAuth->verifyCode($secret, $inCode, 2);

       return $checkResult?'ok':'error';
    }

}