<?php

if(basename($_SERVER['SCRIPT_FILENAME'])==basename(__FILE__))
	exit;

/**
 * This demo webservice shows you how to work with PhpWsdl
 * 
 * @service SoapDemo
 */
class SoapDemo{
	/**
	 * Get a complex type object
	 * 
	 * @return ComplexTypeDemo The object
	 */
	public function GetComplexType(){
		return new ComplexTypeDemo();
	}
	
	/**
	 * Print an object
	 * 
	 * @param ComplexTypeDemo $obj The object
	 * @return string The result of print_r
	 */
	public function PrintComplexType($obj){
		return utf8_encode($this->PrintVariable($obj));
	}
	
	/**
	 * Print an array of objects
	 * 
	 * @param ComplexTypeDemoArray $arr A ComplexTypeDemo array
	 * @return stringArray The results of print_r
	 */
	public function ComplexTypeArrayDemo($arr){
		$res=Array();
		$i=-1;
		$len=sizeof($arr);
		while(++$i<$len)
			$res[]=$this->PrintVariable($arr[$i]);
		return $res;
	}
	
	/**
	 * Say hello demo
	 * 
	 * @param string $name Some name (or an empty string)
	 * @return string Response string
	 */
	public function SayHello($name=null){
		$name=utf8_decode($name);// Because a string parameter may be UTF-8 encoded...
		if($name=='')
			$name='unknown';
		return utf8_encode('Hello '.$name.'!');// Because a string return value should by UTF-8 encoded...
	}

	/**
	 * This method has no parameters and no return value, but it is visible in WSDL, too
	 */
	public function DemoMethod(){
	}
	
	/**
	 * This method should not be visible in WSDL - but notice:
	 * If the PHP SoapServer doesn't know the WSDL, this method is still accessable for SOAP requests!
	 * 
	 * @ignore
	 * @param unknown_type $var
	 * @return string
	 */
	public function PrintVariable($var){
		return print_r($var,true);
	}
}
