<?php

class Curl{

	private $_c = null;
	private $_url = null;
	
	public function __construct($url) {

		if( !function_exists('curl_init') ) {
			throw new Exception('Не установлен CURL.');
		}

		$this->_c = curl_init($url);
		$this->_url = $url;
		if(!$this->_c) throw new Exception('Error');

		curl_setopt($this->_c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->_c, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->_c, CURLOPT_TIMEOUT, 7);
	}

	public function __destruct() {
		curl_close($this->_c);
	}

	public function setSignature($reqString, $key){
		// echo http_build_query(array('1' => '212', '2' => '123'));
		// var_dump(basename($this->_url).$key); die();
		curl_setopt($this->_c,CURLOPT_HTTPHEADER, array(
			'Signature: '.md5($reqString . $key),
			'Content-Type: application/x-www-form-urlencode'
		));
	}

	public function setContentType($type, $charset='UTF-8'){
		// curl_setopt($this->_c,CURLOPT_HTTPHEADER, array(
		// 	'Content-Type: '.$type
		// ));
	}

	public function setPostData($params){
		curl_setopt($this->_c, CURLOPT_POST, true);
    	curl_setopt($this->_c, CURLOPT_POSTFIELDS, http_build_query($params));
	}

	public function exec(){
		return curl_exec($this->_c);
	}

	public function getError(){
		return curl_error($this->_c);
	}

	public function getInfo(){
		print_r(curl_getinfo($this->_c));
	}
}