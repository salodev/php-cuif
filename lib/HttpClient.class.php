<?php

class HttpMessage {
	protected $_headers = array();
	protected $_body = null;
	public function addHeader($name, $value) {
		$this->_headers[] = array(
			'name' => $name,
			'value' => $value,
		);
	}
	
	public function getHeaders() {
		return $this->_headers;
	}
	
	public function getBody() {
		return $this->_body;
	}
	
	public function setBody($content) {
		$this->_body = $content;
	}
}
class HttpRequest extends HttpMessage {
	const GET    = 0;
	const POST   = 1;
	const PUT    = 2;
	const DELETE = 3;
	private $_cookies = array();
	private $_url = null;
	private $_method = HttpRequest::GET;
	
	public function __construct($url, $method = HttpRequest::GET) {
		$this->_url = $url;
		$this->_method = $method;
	}
	public function addCookie($name, $value) {
		$this->_cookies[$name] = $value;
	}
	
	public function getCookies() {
		return $this->_cookies;
	}
}

class HttpResponse extends HttpMessage {
	
}

class HttpClient {
	private $_curl = null;
	public function __construct() {
		$this->_curl = curl_init();
	}
	public function exec(HttpRequest $request) {
		$response = new HttpResponse();
		return $response;
	}
}