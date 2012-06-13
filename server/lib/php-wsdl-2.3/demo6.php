<?php

// This demonstrates how to use the SOAP client

// Internally this client uses the native PHP SoapClient. The use of PhpWsdl 
// is best for doing SOAP with http Auth protected webservices or if you 
// prefer to work with a documented PHP client class that handles all the SOAP 
// for you.

if(isset($_GET['name'])){
	// Perform SOAP request
	require_once('class.phpwsdlclient.php');// All depencies are loaded here
	ini_set('soap.wsdl_cache_enabled',0);	// Disable caching in PHP
	PhpWsdl::$CacheTime=0;					// Disable caching in PhpWsdl
	$client=new PhpWsdlClient('http://wan24.de/test/phpwsdl2/demo4.php?WSDL');// The constructor has to be called with the target webservice WSDL URI
	?><html><head><title>SOAP demo</title></head><body><p><?
	echo htmlentities($client->SayHello($_GET['name']));// Perform the SOAP request and output the response

	// You could also use the PhpWsdlClient->DoRequest method
	//echo htmlentities($client->DoRequest('SayHello',Array('you')));
	
	// PhpWsdlClient can produce a documented PHP SOAP client for you
	/*$php=$client->CreatePhpSoapClient(null,Array('openphp'=>false));// $php will contain the generated PHP SOAP client source code
	eval($php);// This will load the SOAP client classes from the PHP code string
	$soapDemo=new SoapDemoSoapClient();// Create an instance of the generated class (the name of the webservice+"SoapClient" is the default class name)
	echo htmlentities($soapDemo->SayHello('you'));*/
	
	?></p></body></html><?php
}else{
	// Show HTML form
	?><html><head><title>SOAP demo</title></head><body>
<form method="get">
<p>Enter a name: <input type="text" name="name" value="<?php echo (isset($_GET['name']))?str_replace('"','&quot;',htmlentities($_GET['name'])):'you'; ?>"> <input type="submit"></p>
</form>
</body></html><?php
}

?>