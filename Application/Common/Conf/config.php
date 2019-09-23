<?php

return array(
	'DB_TYPE'              => DB_TYPE,
	'DB_HOST'              => DB_HOST,
	'DB_NAME'              => DB_NAME,
	'DB_USER'              => DB_USER,
	'DB_PWD'               => DB_PWD,
	'DB_PORT'              => DB_PORT,
	'DB_PREFIX'            => 'zhisucom_',
	'ACTION_SUFFIX'        => '',
	'MULTI_MODULE'         => true,
	'MODULE_DENY_LIST'     => array('Common', 'Runtime'),
	'MODULE_ALLOW_LIST'    => array('Home', 'Admin','Wap'),
	'DEFAULT_MODULE'       => 'Home',
	'URL_CASE_INSENSITIVE' => false,
	'URL_MODEL'            => 2,
	'URL_HTML_SUFFIX'      => 'html',
	'UPDATE_PATH'          => '',
	'CLOUD_PATH'           => '',
    'HOST_IP'	=> '',
	'TMPL_CACHFILE_SUFFIX' =>'.html',
	'DATA_CACHE_TYPE'      => 'file',
	'URL_PARAMS_SAFE'	   => true,
	'URL_PARAMS_FILTER'    => true,
	'DEFAULT_FILTER'       =>'check_zhisucom,htmlspecialchars,strip_tags',
	'URL_PARAMS_FILTER_TYPE' =>'check_zhisucom,htmlspecialchars,strip_tags',
  
  
  // 配置邮件发送服务器
    'MAIL_HOST' =>'smtp.qq.com',//smtp服务器的名称
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'1356856049@qq.com',//你的邮箱名
    'MAIL_FROM' =>'1356856049@qq.com',//发件人地址
    'MAIL_FROMNAME'=>'JEFF',//发件人姓名
	'MAIL_PASSWORD' =>'kwzbwmaqxrgggbeg',//邮箱密码
    'MAIL_CHARSET' =>'utf-8',//设置邮件编码
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件
	);
?>