<?php

/*
PhpWsdl - Generate WSDL from PHP
Copyright (C) 2011  Andreas Zimmermann, wan24.de 

This program is free software; you can redistribute it and/or modify it under 
the terms of the GNU General Public License as published by the Free Software 
Foundation; either version 3 of the License, or (at your option) any later 
version. 

This program is distributed in the hope that it will be useful, but WITHOUT 
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. 

You should have received a copy of the GNU General Public License along with 
this program; if not, see <http://www.gnu.org/licenses/>.
*/

if(basename($_SERVER['SCRIPT_FILENAME'])==basename(__FILE__))
	exit;

// This proxy can handle missing parameters with NULL values. This will only 
// work, if the PhpWsdl class doesn't promote the WSDL to the SoapServer. But 
// then returning complex types won't be easy anymore: You have to encode the 
// return value with PHPs SoapVar object by yourself.

class PhpWsdlProxy{
	public function __call($method,$param){
		if(PhpWsdl::$Debugging)
			PhpWsdl::Debug('Proxy call method '.$method.': '.print_r($param,true));
		PhpWsdl::$ProxyServer->CreateWsdl();
		$m=PhpWsdl::$ProxyServer->GetMethod($method);
		if(is_null($m))
			throw(new SoapFault('MissingMethod','Method "'.$method.'" not found'));
		// Try to fix the missing parameters issue if the SoapServer is not running in WSDL mode
		if(!PhpWsdl::$UseProxyWsdl){
			$pLen=sizeof($m->Param);
			$temp=sizeof($param);
			if($pLen!=$temp){
				PhpWsdl::Debug('Wrong parameter count ('.$temp.'/'.$pLen.')');
				$req=new DOMDocument();
				if($req->loadXml(file_get_contents('php://input'))){
					$x=new DOMXPath($req);
					$temp=$param;
					$param=Array();
					$pos=0;// Current index in the received parameter array
					$i=-1;
					while(++$i<$pLen){
						$p=$m->Param[$i];
						if($x->query("/*[local-name()='Envelope']/*[local-name()='Body']/*[local-name()='".$m->Name."']/*[local-name()='".$p->Name."']")->length>0){
							PhpWsdl::Debug('Parameter "'.$p->Name.'" was received');
							$param[]=$temp[$pos];
							$pos++;
						}else{
							PhpWsdl::Debug('Parameter "'.$p->Name.'" was missing');
							$param[]=null;
						}
					}
				}else{
					PhpWsdl::Debug('Could not parse SOAP request XML');
				}
			}
		}
		// Prepare the method call
		$call=($m->IsGlobal)
			?$method					// Global method
			:Array(						// Class method
				PhpWsdl::$ProxyObject,
				$method
			);
		// Call the target method and return the response
		PhpWsdl::Debug('Call the target method');
		return (sizeof($param)<1)
			?call_user_func(
					$call
				)
			:call_user_func_array(
					$call,
					$param
				);
	}
}
