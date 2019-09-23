<?php

class OKCoin_ApiKeyAuthentication extends OKCoin_Authentication
{
 
    public function __construct()
    {	
		$api_key="e63a571c-d87d-4e3c-92c9-fe03495d96a1";
		$secret_key="7AFBCF56CE2733CB4B2810FFEB1D0A1F";		
        $this->_apiKey = $api_key;
        $this->_apiKeySecret = $secret_key;
    }

    public function getData()
    {
        $data = new stdClass();
        $data->apiKey = $this->_apiKey;
        $data->apiKeySecret = $this->_apiKeySecret;
        return $data;
    }
}