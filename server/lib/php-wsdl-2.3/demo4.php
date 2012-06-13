<?php

// A quick and dirty SOAP server example

ini_set('soap.wsdl_cache_enabled',0);	// Disable caching in PHP
$PhpWsdlAutoRun=true;					// With this global variable PhpWsdl will autorun in quick mode, too
require_once('class.phpwsdl.php');

// In quick mode you can specify the class filename(s) of your webservice 
// optional parameter, if required.
//PhpWsdl::RunQuickMode();// -> Don't waste my time - just run!

class SoapDemo{
	/**
	 * Say hello to...
	 * 
	 * @param string $name A name
	 * @return string Response
	 */
	public function SayHello($name){
		$name=utf8_decode($name);
		if($name=='')
			$name='unknown';
		return utf8_encode('Hello '.$name.'!');
	}
}
