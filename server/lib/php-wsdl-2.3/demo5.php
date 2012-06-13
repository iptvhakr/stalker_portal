<?php

// This is an example how to use global methods in a SOAP webservice.
//
// You can't mix class and global methods within one webservice, if you're not 
// using the PhpWsdlProxy class (see demo3.php).

require_once('class.phpwsdl.php');
ini_set('soap.wsdl_cache_enabled',0);	// Disable caching in PHP
PhpWsdl::$CacheTime=0;					// Disable caching in PhpWsdl
PhpWsdlMethod::$IsGlobalDefault=true;	// Serve any method as global per default
PhpWsdl::RunQuickMode();				// Run in quick mode

/**
 * This is how to define a global method for WSDL. A setting was not required since the 
 * PhpWsdlMethod::$IsGlobalDefault property was set to TRUE so every method is handled 
 * as global method per default now
 * 
 * @return string Response
 */
function GlobalMethodDemo(){
	return utf8_encode('Response of the global method demo');
}

// If you want PhpWsdl to handle all methods as global per default, set the 
// PhpWsdlMethod::$IsGlobalDefault to TRUE. Then you don't need to set the 
// setting "global" to "1" for every method.
