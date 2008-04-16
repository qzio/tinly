<?php
// commenet block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: tcurl.php		                              |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel.hansson@gmail.com>              |
// +--------------------------------------------------------+
//
//  }}}
class tcurl {
	// properties {{{
	// base url to the server running bo, i.e http://tycoon.metaboli.net:3003
	protected $base_url;
	// the http user to use at login
	protected $http_user;
	// the http password to use at login
	protected $http_passwd;
	// error message, accessible through getError()
	protected $error;
	// the response from get/post, accessible through getRespons()
	protected $response;
	// just a variable to hold default cURL options.
	protected $default_options;
	// headers from response
	protected $headers = array();
	// /path/to/temp_file
	protected $tmp_file;
	// debug string
	protected $debug = '';
	// }}}
	// __construct(base_url,http_user,http_passwd) {{{
	public function __construct($base_url,$http_user = '',$http_passwd = '')
	{
		if (empty($base_url)) {
			die('you cannot use the TcURL without correct parameters...dying');
		}
		$this->base_url = $base_url;
		$this->http_user = $http_user;
		$this->http_passwd = $http_passwd;

		// set default curl options
		$this->default_options = array(
			CURLOPT_RETURNTRANSFER  => true,
			CURLOPT_USERPWD => $this->http_user.':'.$this->http_passwd,
			CURLOPT_HEADERFUNCTION => array(&$this,'readHeader'), // get the http headers to $this->headers array.
			CURLOPT_HTTPHEADER => array('Expect:'), // lighttpd needs this. 
			CURLOPT_HEADER => 0,
			CURLOPT_USERAGENT => 'tcurl',
		);

		// this file is used when writing xml file for PUT actions
      if (function_exists('sys_get_temp_dir')) {
		   $this->tmp_file = tempnam(sys_get_temp_dir(),'TcURL_stuff_');
      } else {
         $this->tmp_file = tempnam('/tmp','TcURL_stuff_');
      }
	}
	// }}}
	
	
	// base methods
	// delete(resource_url) {{{
	public function delete($resource_url)
	{
		$ch = curl_init();
		$options = $this->default_options + array(
			CURLOPT_URL =>	$this->base_url.$resource_url,
			CURLOPT_CUSTOMREQUEST => 'DELETE',
		);
		curl_setopt_array($ch,$options);
		$this->response = curl_exec($ch);
		$this->error = curl_error($ch);
		curl_close($ch);
		return (empty($this->error)) ? true : false;
	}
	// }}}
	// put(resource_url,xml_file_name) {{{
	public function put($resource_url,$xml_file_name)
	{
		$ch = curl_init();
		$xml_file_handle = fopen($xml_file_name,'r');
		$options = $this->default_options + array(
			CURLOPT_URL => $this->base_url.$resource_url,
			CURLOPT_PUT => true,
			CURLOPT_INFILE => $xml_file_handle,
			CURLOPT_INFILESIZE => filesize($xml_file_name),
		);
		curl_setopt_array($ch,$options);
		$this->response = curl_exec($ch);
		$this->error = curl_error($ch);
		curl_close($ch);
		return (empty($this->error)) ? true : false;
	}
	// }}}
	// get(resource_url) {{{
	public function get($resource_url,$force_with_headers = false)
	{
		// empty out old data
		$ch = null;
		$this->error = '';
		$this->response = '';
		$this->headers = array();

		$ch = curl_init();
		$options = $this->default_options + array(
			CURLOPT_URL =>	$this->base_url.$resource_url,

		);
		if ($force_with_headers) {
			$options[CURLOPT_HEADER] = true;
		}
		curl_setopt_array($ch,$options);
		$this->response = curl_exec($ch);
		$this->error = curl_error($ch);
		curl_close($ch);
		/*if (!empty($this->error)) {
			echo ('will perform get on '.$options[CURLOPT_URL].'<br/>');
			echo 'got headers: <pre>';
			print_r($this->getHeaders());
			echo '</pre>';
			echo 'response:<pre>';
			var_dump($this->response);
			echo '</pre>';
			die ('<br/>failed with error:('.$this->error.')<br/>');
		}*/
		
		return (empty($this->error)) ? true : false;
	}
	// }}}
	// post(resource_url,param_string) {{{
	public function post($resource_url,$post_string = '')
	{
		if (empty($post_string)) return false;
		$ch = curl_init();
		$options = $this->default_options + array(
			CURLOPT_URL =>	$this->base_url.$resource_url,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $post_string,
		);
		curl_setopt_array($ch,$options);
		$this->response = curl_exec($ch);
		$this->error = curl_error($ch);
		curl_close($ch);
		return (empty($this->error)) ? true : false;
	}
	// }}}
	// readHeaders() {{{
	protected function readHeader($ch,$header)
	{
		// split on ":" making a assoc array
		$pos = strpos($header,':');
		if ($pos !== false) {
			$this->headers[trim(substr($header,0,$pos))] = trim(substr($header,($pos+1)));

		// fallback
		} else {
			$this->headers[(count($this->headers)+1)] = $header;
		}

		// !important, return bytes read, curl will fail otherwise
		return strlen($header);
	}
	// }}}
	
	// get respons/error/headers. and more getters
	// getError() {{{
	public function getError()
	{
		return $this->error;
	}
	// }}}
	// getResponse {{{
	public function getResponse()
	{
		return $this->response;
	}
	// }}}
	// getHeaders() {{{
	public function getHeaders()
	{
		return $this->headers;
	}
	// }}}
	// getDebug() {{{
	public function getDebug()
	{
		return $this->debug;
	}
	// }}}
	// getBaseUrl() {{{
	public function getBaseUrl()
	{
		return $this->base_url;
	}
	// }}}
	
	// shortcut methods
	// putXML(resource_url,xml_string = '') {{{
	public function putXML($resource_url,$xml_string = '')
	{
		file_put_contents($this->tmp_file,$xml_string);
		$r = $this->put($resource_url,$this->tmp_file);
		unlink($this->tmp_file);
		return $r;
	}
	// }}}
	// putArrayAsXML(resource_url,p = array()) {{{
	public function putArrayAsXML($resource_url,$p = array())
	{
		$xml_string = $this->encodeXMLFromArray($p);
		$r = $this->putXML($resource_url,$xml_string);
		return $r;
	}
	// }}}
	// postArrayAsXML(resorce_url,$p = array()) {{{
	public function postArrayAsXML($resource_url,$p = array())
	{
		if (!is_array($p) || empty($p)) return false;
		$xml = $this->encodeXMLFromArray($p);
		return $this->post($resource_url,$xml);
	}
	// }}}
	// postAsXML(resorce_url,$p = array()) (DEPRECATED!) {{{
	public function postAsXML($resource_url,$p = array())
	{
		echo "DONT USE THIS METHOD!";
		if (!is_array($p) || empty($p)) return false;

		$xml = $this->encodeXMLFromArray($p);

		return $this->post($resource_url,$xml);

		/*

		$ch = curl_init();
		$options = $this->default_options + array(
			CURLOPT_URL =>	$this->base_url.$resource_url,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $xml,
			CURLOPT_HEADER => true,
		);
		curl_setopt_array($ch,$options);
		$this->response = curl_exec($ch);
		$this->error = curl_error($ch);
		curl_close($ch);
		$this->debug .= 'postasxml->'.$resource_url.' r: '.$this->response.'e('.$this->error.')h('.print_r($this->headers,true).')';
		return (empty($this->error)) ? true : false;
		 */
	}
	// }}}
	// postArray(resource_url,$p) (deprecated, use postXML insteed) {{{
	public function postArray($resource_url,$p = array())
	{
		if (!is_array($p) || empty($p)) return false;
		$post_string = $this->makePostString($p);
		return $this->post($resource_url,$post_string);
	}
	// }}}
	
	// helpers
	// makePostString($p = array() {{{
	public function makePostString($p = array(),$parent = '')
	{
		if (!is_array($p) || empty($p)) return false;

		$r = '';
		foreach($p as $key => $val) {
			$k = (!empty($parent)) ? $parent.'['.$key.']' : $key;

			// if val is array, make recursive call
			if(is_array($val) || is_object($val)) {
				$r .= $this->makePostString($val,$k);

			// if val is string, set 
			} else {
				$r .= '&'.$k.'='.urlencode($val);
			}

		}
		$r = ltrim($r,'&');
		return $r;
	}
	// }}}
	// encodeXMLFromArray {{{
	public function encodeXMLFromArray($p = array(),$inner = false)
	{
		if(!is_array($p) || empty($p)) return false;

		$r = ($inner === true) ? '' : '<?xml version="1.0" encoding="UTF-8"?>';
		foreach($p as $key => $val) {
			$pre = '<'.urlencode($key).'>';
			$suf = '</'.urlencode($key).'>';
			if (is_string($val)) $r .= $pre.urlencode($val).$suf;
			else if(is_array($val)) $r .= $pre.$this->encodeXMLFromArray($val,true).$suf;
		}
		return $r;
	}
	// }}}
}
?>
