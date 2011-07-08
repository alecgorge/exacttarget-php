<?php
/**
 * @author Alec Gorge
 * @year 2011
 * 
 * @link http://wiki.memberlandingpages.com/030_Developer_Documentation/040_XML_API/ExactTarget_XML_API_Technical_Reference/Constructing_Your_Calling_Application
 * 
 * $et = new ExactTarget('user', 'pass');
 * 
 * var_dump($et->call(array(
 *     'system_name' => 'list',
 *     'action' => 'retrieve',
 *     'search_type' => 'listid',
 *     'search_value' => '123456',
 *     'search_value2' => ''
 * )));
 * 
 */
class ExactTarget {
	private $xml_url = "https://api.dc1.exacttarget.com/integrate.aspx";
	private $std_args = array(
		'qf' => 'xml'
	);
	private $request_mode_post = true;
	private $username;
	private $password;
	
	public function __construct ($username, $password, $usePost = true) {
		$this->request_mode_post = $usePost;
		$this->username = $username;
		$this->password = $password;
	}
	
	public function call(array $systemArr) {
		return $this->makeRequest($systemArr);
	}
	
	private function makeRequest(array $xml_payload) {
		$args = array_merge($this->std_args, array(
			'xml' => $this->makeXMLPayload($xml_payload);
		));
		$url = $this->xml_url . ($this->request_mode_post ? "" : "?".$this->encodeFields($args));
		
		$c = curl_init($url);
		if($this->request_mode_post) {
			curl_setopt($c, CURLOPT_POST, $this->request_mode_post);
			curl_setopt($c, CURLOPT_POSTFIELDS, $this->encodeFields($args));
		}
		
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($c);
		curl_close($c);
		
		return $result;	
	}
	
	private function encodeFields(array $args) {
		$r = array();
		foreach($args as $k => $v) {
			$r[] = sprintf("%s=%s", $k, rawurlencode($v));
		}
		return implode('&', $r);
	}
	
	private function makeXMLPayload(array $xml_payload) {
		$system = $this->arrayToXML($xml_payload, 'system');
		return <<<XML
<?xml version="1.0"?>
<exacttarget>
	<authorization>
		<username>{$this->username}</username>
		<password>{$this->password}</password>
	</authorization>
	$system
</exacttarget>

XML;
	}
	
	private function arrayToXML(array $arr, $rootTagName = 'root') {
		$xml = new SimpleXMLElement('<'.$rootTagName.'/>');
	    foreach ($arr as $k => $v) {
	        is_array($v)
	            ? array_to_xml($v, $xml->addChild($k))
	            : $xml->addChild($k, $v);
	    }
	    return $xml->asXML();
	}
}