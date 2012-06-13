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

// To use PhpWsdlClient, you need to load it seperatly from PhpWsdl. Or you 
// just load this class and let it autoload PhpWsdl 

require_once(dirname(__FILE__).'/class.phpwsdl.php');

/**
 * A SOAP client class for PhpWsdl
 * 
 * @author Andreas Zimmermann, wan24.de
 * @version 2.2
 */
class PhpWsdlClient{
	/**
	 * The version number 
	 * 
	 * @var string
	 */
	public static $VERSION='2.2';
	/**
	 * Parse a method
	 * 1: Type
	 * 2: Method
	 * 3: Parameters
	 * 
	 * @var string
	 */
	public static $methodRx='/^([^\s]+)\s+([^\(]+)\(([^\)]+)?\).*$/';
	/**
	 * Parse a type
	 * 1: Type
	 * 2: Name
	 * 
	 * @var string
	 */
	public static $typeRx='/^([^\s]+)\s([^\[|\{|\s]+)[\[|\{|\s].*$/s';
	/**
	 * Parse an element
	 * 1: Type
	 * 2: Name
	 * 
	 * @var string
	 */
	public static $elementRx='/^\s*([^\s]+)\s+([^\s|;]+);.*$/';
	/**
	 * The WSDL URI
	 * 
	 * @var string
	 */
	public $WsdlUri;
	/**
	 * The PHP SoapClient object
	 * 
	 * @var SoapClient
	 */
	public $Client=null;
	/**
	 * SOAP call options
	 * 
	 * @var array
	 */
	public $Options;
	/**
	 * SOAP call request headers
	 * 
	 * @var array
	 */
	public $RequestHeaders;
	/**
	 * PHP SoapClient options array
	 * 
	 * @var array
	 */
	public $ClientOptions;
	/**
	 * The webservice name
	 * 
	 * @var string
	 */
	public $ServiceName=null;
	/**
	 * The SOAP endpoint
	 * 
	 * @var string
	 */
	public $EndPoint=null;
	/**
	 * The SOAP namespace
	 * 
	 * @var string
	 */
	public $NameSpace=null;
	/**
	 * A PhpWsdl object
	 * 
	 * @var PhpWsdl
	 */
	public $Server=null;
	/**
	 * The WSDL
	 * 
	 * @var string
	 */
	public $WSDL=null;
	/**
	 * A list of warnings
	 * 
	 * @var string[]
	 */
	public $Warnings=Array();
	/**
	 * The http Auth username for fetching the WSDL (and sending SOAP requests, if PhpWsdlClient->UseSoapHttpAuth is TRUE)
	 * If you need a different login for SOAP requests, use the PhpWsdlClient->ClientOptions array fro the SOAP request 
	 * authentification values (login/password)
	 * 
	 * @var string
	 */
	public $HttpUser=null;
	/**
	 * The http Auth password
	 * 
	 * @var string
	 */
	public $HttpPassword=null;
	/**
	 * Use http Auth for SOAP requests, too? If FALSE, the login will only be used to fetch the WSDL from the given URI
	 * 
	 * @var boolean
	 */
	public $UseSoapHttpAuth=true;
	/**
	 * Enable debugging of the RAW request/response?
	 * 
	 * @var boolean
	 */
	public $Debugging=false;
	
	/**
	 * Constructor
	 * 
	 * @param string $wsdlUri The WSDL URI
	 * @param array $options SOAP call options (default: NULL)
	 * @param array $requestHeaders SOAP call request headers (default: NULL)
	 */
	public function PhpWsdlClient($wsdlUri,$options=null,$requestHeaders=null,$clientOptions=Array()){
		PhpWsdl::Debug('New PhpWsdlClient '.$wsdlUri);
		$this->WsdlUri=$wsdlUri;
		$this->Options=$options;
		$this->RequestHeaders=$requestHeaders;
		$this->ClientOptions=array_merge(
			Array(
				'soap_version'	=>	SOAP_1_1|SOAP_1_2,
				'encoding'		=>	'UTF-8',
				'compression'	=>	SOAP_COMPRESSION_ACCEPT|SOAP_COMPRESSION_GZIP|9,
				'connection_timeout'=>5
			),
			$clientOptions
		);
		$this->GetWsdlFromCache();
	}
	
	/**
	 * Fetch the WSDL (supports http Auth if CURL is enabled)
	 * 
	 * @param string $wsdlUri The WSDL URI or NULL to use the PhpWsdlClient->WaslUri property (default: NULL)
	 * @param array $options The CURL options (default: array)
	 * @return string The WSDL XML
	 */
	public function FetchWsdl($wsdlUri=null,$options=Array()){
		if(is_null($wsdlUri))
			$wsdlUri=$this->WsdlUri;
		PhpWsdl::Debug('Fetch WSDL from '.$wsdlUri);
		if(!is_file($wsdlUri)&&function_exists('curl_init')&&!is_null($this->HttpUser)){
			// Fetch with http Auth (credits to faebu :)
			PhpWsdl::Debug('Try CURL for http Auth');
            $ch=curl_init();
            $credit=($this->HttpUser.':'.$this->HttpPassword);
            curl_setopt_array($ch,array_merge(
            	Array(
            		CURLOPT_URL				=>	$wsdlUri,
            		CURLOPT_HTTPAUTH		=>	CURLAUTH_BASIC,
            		CURLOPT_USERPWD			=>	$credit,
            		CURLOPT_TIMEOUT			=>	10,
            		CURLOPT_RETURNTRANSFER	=>	1
            	),
            	$options
            ));
            $wsdl=curl_exec($ch);
            if($wsdl===false){
            	PhpWsdl::Debug('Could not fetch WSDL with CURL: '.curl_error($ch));
            	@curl_close($ch);
            }else{
	            curl_close($ch);
	            return $wsdl;
            }
		}
		// Fetch without authentification
		return file_get_contents($wsdlUri);
	}
	
	/**
	 * Do a SOAP request
	 * 
	 * @param string $method The method name
	 * @param array $param The method parameters
	 * @param array $options The call options (default: array)
	 * @param array $requestHeaders The request headers (default: array)
	 * @return mixed The server response
	 */
	public function DoRequest($method,$param,$options=Array(),$requestHeaders=Array()){
		PhpWsdl::Debug('Sending request '.$method);
		$options=array_merge($this->Options,$options);
		$requestHeaders=array_merge($this->RequestHeaders,$requestHeaders);
		$client=$this->GetClient();
		$res=$client->__soapCall($method,$param,$options,$requestHeaders);
		if(is_soap_fault($res))
			PhpWsdl::Debug('SOAP error #'.$res->faultcode.': '.$res->faultstring);
		if($this->Debugging){
			PhpWsdl::Debug('Parameters: '.print_r($param,true));
			PhpWsdl::Debug('Options: '.print_r($options,true));
			PhpWsdl::Debug('Headers: '.print_r($requestHeaders,true));
			PhpWsdl::Debug('Result: '.print_r($res,true));
			PhpWsdl::Debug('Request: '.$client->__getLastRequest());
			PhpWsdl::Debug('Request headers: '.$client->__getLastRequestHeaders());
			PhpWsdl::Debug('Response: '.$client->__getLastResponse());
			PhpWsdl::Debug('Response headers: '.$client->__getLastResponseHeaders());
		}
		return $res;
	}
	
	/**
	 * Get the PHP SoapClient object
	 * 
	 * @param string $uri The WSDL URI or NULL to use the PhpWsdlClient->WsdlUri property (default: NULL)
	 * @param array $options The additional PHP SoapClient options (default: array)
	 * @return SoapClient The PHP SoapClient object
	 */
	public function GetClient($uri=null,$options=Array()){
		if(is_null($this->Client)){
			if(is_null($uri))
				$uri=$this->WsdlUri;
			PhpWsdl::Debug('Create a PHP SoapClient object in PhpWsdlClient from '.$uri);
			if(!is_null(PhpWsdl::$CacheFolder)){
				$wsdlFile=$this->GetCacheFileName($uri);
				if(!is_null($this->GetWsdlFromCache($wsdlFile))){
					PhpWsdl::Debug('Use cached WSDL from '.$wsdlFile);
					$uri=$wsdlFile;
				}else{
					$wsdl=$this->FetchWsdl($uri);
					if($wsdl!==false)
						if($this->WriteWsdlToCache($wsdl,$uri,$wsdlFile,true)){
							PhpWsdl::Debug('Using cached WSDL from '.$wsdlFile);
							$uri=$wsdlFile;
						}
				}
			}
			if(
				!is_null($this->HttpUser)&&
				!isset($this->ClientOptions['login'])&&
				!isset($options['login'])&&
				$this->UseSoapHttpAuth
			){
				PhpWsdl::Debug('Using http Auth options');
				$options['login']=$this->HttpUser;
				$options['password']=$this->HttpPassword;
			}
			if($this->Debugging){
				PhpWsdl::Debug('Debugging enabled');
				$options['trace']=true;
			}
			$this->Client=new SoapClient($uri,array_merge(
				$this->ClientOptions,
				$options
			));
		}
		return $this->Client;
	}
	
	/**
	 * Create/Fill a PhpWsdl object from WSDL
	 * 
	 * @param PhpWsdl An existing PhpWsdl object (default: NULL)
	 * @return PhpWsdl The PhpWsdl object
	 */
	public function CreateServerFromWsdl($soap=null){
		PhpWsdl::Debug('Create/Fill a PhpWsdl object from WSDL');
		if(is_null($soap)){
			if(!is_null($this->Server)){
				PhpWsdl::Debug('Return existing object');
				return $this->Server;
			}
			$soap=PhpWsdl::CreateInstance();
		}else{
			PhpWsdl::Debug('Use existing object');
		}
		if(!is_null($soap->GetWsdlFromCache())){
			PhpWsdl::Debug('Server created from cached values');
			if(is_null($this->Server))
				$this->Server=$soap;
			return $soap;
		}
		// Configuration
		$soap->WsdlUri=$this->WsdlUri;
		$this->ParseWsdl();
		if(!is_null($this->ServiceName))
			$soap->Name=$this->ServiceName;
		if(!is_null($this->NameSpace))
			$soap->NameSpace=$this->NameSpace;
		$client=$this->GetClient();
		// Methods
		$fnc=$client->__getFunctions();
		$i=-1;
		$len=sizeof($fnc);
		while(++$i<$len){
			$f=$fnc[$i];
			list(
				$type,
				$method,
				$temp
			)=explode("\t",preg_replace(self::$methodRx,"$1\t$2\t$3",$f));
			PhpWsdl::Debug('Found method #'.$i.' '.$method);
			if(!is_null($soap->GetMethod($method))){
				$this->Warn('WARNING: Double method detected!');
				continue;
			}
			$m=new PhpWsdlMethod($method);
			$temp=explode(' ',$temp);
			$pLen=sizeof($temp);
			for($j=0;$j<$pLen-1;$j++){
				list(
					$t,
					$n
				)=Array(
					$temp[$j],
					$temp[$j+1]
				);
				PhpWsdl::Debug('Found parameter #'.$j.' '.$n.' type of '.$t);
				$m->Param[]=new PhpWsdlParam(substr($n,1),$t);
			}
			if($type!='void')
				$m->Return=new PhpWsdlParam('return',$type);
			$soap->Methods[]=$m;
		}
		// Types
		$typ=$client->__getTypes();
		$i=-1;
		$len=sizeof($typ);
		while(++$i<$len){
			$t=$typ[$i];
			list(
				$type,
				$name
			)=explode("\t",preg_replace(self::$typeRx,"$1\t$2",$t));
			PhpWsdl::Debug('Found type #'.$i.' '.$name.' type of '.$type);
			if(!is_null($soap->GetType($name))){
				$this->Warn('WARNING: Double type detected!');
				continue;
			}
			$arr=strpos($t,'[]')>-1;
			if($arr){
				PhpWsdl::Debug('Array type');
				$y=new PhpWsdlComplex($name);
				$y->Type=$type;
				$y->IsArray=true;
				$soap->Types[]=$y;
			}else if($type=='struct'){
				PhpWsdl::Debug('Complex type');
				$el=Array();
				$temp=explode("\n",$t);
				$j=0;
				$eLen=sizeof($temp)-1;
				while(++$j<$eLen){
					list(
						$p,
						$n
					)=explode("\t",preg_replace(self::$elementRx,"$1\t$2",$temp[$j]),2);
					PhpWsdl::Debug('Found element #'.$j.' '.$n.' type of '.$p);
					$el[]=new PhpWsdlElement($n,$p);
				}
				$y=new PhpWsdlComplex($name,$el);
				$y->IsArray=false;
				$soap->Types[]=$y;
			}else{
				$this->Warn('WARNING: Could not create type '.$t);
			}
		}
		if(is_null($this->Server))
			$this->Server=$soap;
		return $soap;
	}
	
	/**
	 * Parse the WSDL for webservice name, namespace and endpoint URI
	 */
	public function ParseWsdl(){
		if(!is_null($this->ServiceName)&&!is_null($this->EndPoint)&&!is_null($this->NameSpace))
			return;
		PhpWsdl::Debug('Parse WSDL');
		$wsdl=$this->WSDL;
		$writeCache=false;
		if(is_null($wsdl)){
			$wsdl=$this->FetchWsdl();
			if($wsdl===false)
				throw(new Exception('Could not fetch WSDL'));
			$writeCache=true;
		}
		$xml=new DOMDocument();
		if(!$xml->loadXML($wsdl))
			throw(new Exception('Invalid WSDL'));
		$x=new DOMXPath($xml);
		// Namespace
		$temp=$x->query("/*[local-name()='definitions']/@targetNamespace");
		if($temp->length>0){
			$temp=$temp->item(0);
			PhpWsdl::Debug('Namespace '.$temp->value);
			$this->NameSpace=$temp->value;
		}else{
			$this->Warn('WARNING: No namespace found');
		}
		// Webservice name
		$temp=$x->query("/*[local-name()='definitions']/*[local-name()='service']/@name");
		if($temp->length>0){
			$temp=$temp->item(0);
			PhpWsdl::Debug('Name '.$temp->value);
			$this->ServiceName=$temp->value;
		}else{
			$this->Warn('WARNING: No name found');
		}
		// Endpoint URI
		$temp=$x->query("/*[local-name()='definitions']/*[local-name()='service']/*[local-name()='port']/*[local-name()='address']/@location");
		if($temp->length>0){
			$temp=$temp->item(0);
			PhpWsdl::Debug('Endpoint '.$temp->value);
			$this->EndPoint=$temp->value;
		}else{
			$this->Warn('WARNING: No endpoint found');
		}
		// Caching
		$this->WriteWsdlToCache($wsdl,null,null,$writeCache);
	}
	
	/**
	 * Create a PHP SOAP client for this webservice
	 * 
	 * @param string $filename Name of the file to save the PHP code (default: NULL)
	 * @param array $options The options array for the PhpWsdl->OutputPhp method (default: array)
	 * @return string The UTF-8 encoded PHP code of the SOAP client
	 */
	public function CreatePhpSoapClient($filename=null,$options=Array()){
		$php=$this->CreateServerFromWsdl()->OutputPhp(false,false,$options);
		if(!is_null($filename))
			if(file_put_contents($filename,$php)===false)
				PhpWsdl::Debug('Could not write file '.$filename);
		return $php;
	}
	
	/**
	 * Add a warning message
	 * 
	 * @param string $str The message
	 */
	private function Warn($str){
		$this->Warnings[]=$str;
		PhpWsdl::Debug($str);
	}
	
	/**
	 * Get the cache filename
	 * 
	 * @param string $wsdluri The WSDL URI or NULL to use the PhpWsdlAjax->WsdlUri property (default: NULL)
	 * @return string The cache filename or NULL, if caching is disabled
	 */
	public function GetCacheFileName($wsdluri=null){
		$data=Array(
			'client'		=>	$this,
			'wsdluri'		=>	$wsdluri,
			'filename'		=>	(is_null(PhpWsdl::$CacheFolder))?null:PhpWsdl::$CacheFolder.'/client-'.sha1((is_null($wsdluri))?$this->WsdlUri:$wsdluri).'.wsdl'
		);
		PhpWsdl::CallHook(
			'ClientCacheFileNameHook',
			$data
		);
		return $data['filename'];
	}

	/**
	 * Determine if the cache file exists
	 * 
	 * @param string $file The WSDL cache filename or NULL to use the default (default: NULL)
	 * @return boolean Are the cache files present?
	 */
	public function CacheFileExists($file=null){
		if(is_null($file))
			$file=$this->GetCacheFileName();
		PhpWsdl::Debug('Check cache file exists '.$file);
		return file_exists($file)&&file_exists($file.'.cache');
	}
	
	/**
	 * Determine if the existing cache files are still valid
	 * 
	 * @param string $file The WSDL cache filename or NULL to use the default (default: NULL)
	 * @return boolean Valid?
	 */
	public function IsCacheValid($file=null){
		PhpWsdl::Debug('Check cache valid');
		if(is_null($file))
			$file=$this->GetCacheFileName();
		if(!$this->CacheFileExists($file))
			return false;
		return PhpWsdl::$CacheTime<0||time()-file_get_contents($file.'.cache')<=PhpWsdl::$CacheTime;
	}
	
	/**
	 * Get the WSDL from the cache
	 * 
	 * @param string $file The WSDL cache filename or NULL to use the default (default: NULL)
	 * @param boolean $force Force this even if the cache is timed out? (default: FALSE)
	 * @param boolean $nounserialize Don't unserialize the PhpWsdl* objects? (default: FALSE)
	 * @return string The cached WSDL
	 */
	public function GetWsdlFromCache($file=null,$force=false,$nounserialize=false){
		PhpWsdl::Debug('Get WSDL from cache');
		if(!is_null($this->WSDL))
			return $this->WSDL;
		if(is_null($file))
			$file=$this->GetCacheFileName();
		if(!$force){
			if(!$this->IsCacheValid($file))
				return null;
		}else if(!$this->CacheFileExists($file)){
			return null;
		}
		$this->WSDL=file_get_contents($file);
		if(!$nounserialize){
			PhpWsdl::Debug('Unserialize data');
			$data=unserialize(file_get_contents($file.'.obj'));
			$this->ServiceName=$data['servicename'];
			$this->EndPoint=$data['endpoint'];
			$this->NameSpace=$data['namespace'];
			PhpWsdl::CallHook(
				'ClientReadCacheHook',
				Array(
					'client'		=>	$this,
					'data'			=>	&$data
				)
			);
			if($data['version']!=self::$VERSION){
				PhpWsdl::Debug('Could not use cache from version '.$data['version']);
				$this->ServiceName=null;
				$this->EndPoint=null;
				$this->NameSpace=null;
				$this->WSDL=null;
				return null;
			}
		}
		return $this->WSDL;
	}
	
	/**
	 * Write WSDL to cache
	 * 
	 * @param string $wsdl The UTF-8 encoded WSDL string (default: NULL)
	 * @param string $wsdluri The SOAP WSDL URI or NULL to use the default (default: NULL)
	 * @param string $file The target filename or NULL to use the default (default: NULL)
	 * @param boolean $force Force refresh (default: FALSE)
	 * @return boolean Succeed?
	 */
	public function WriteWsdlToCache($wsdl=null,$wsdluri=null,$file=null,$force=false){
		PhpWsdl::Debug('Write WSDL to the cache');
		if(is_null($wsdluri))
			$wsdluri=$this->WsdlUri;
		if($wsdluri==$this->WsdlUri&&!is_null($wsdl))
			$this->WSDL=$wsdl;
		if(is_null($wsdl)){
			if(is_null($this->WSDL)){
				PhpWsdl::Debug('No WSDL');
				return false;// WSDL not defined
			}
			$wsdl=$this->WSDL;
		}
		if(is_null($file)){
			$file=$this->GetCacheFileName($wsdluri);
			if(is_null($file)){
				PhpWsdl::Debug('No cache file');
				return false;// No cache file
			}
		}
		$temp=substr($file,0,1);
		if($temp!='/'&&$temp!='.'){
			if(is_null(PhpWsdl::$CacheFolder)){
				PhpWsdl::Debug('No cache folder');
				return false;// No cache folder
			}
			$file=PhpWsdl::$CacheFolder.'/'.$file;
		}
		if(!$force)
			if($this->IsCacheValid($file)){
				PhpWsdl::Debug('Cache is still valid');
				return true;// Existing cache is still valid
			}
		PhpWsdl::Debug('Write to '.$file);
		if(file_put_contents($file,$wsdl)===false){
			PhpWsdl::Debug('Could not write to cache');
			return false;// Error writing to cache
		}
		if(file_put_contents($file.'.cache',time())===false){
			PhpWsdl::Debug('Could not write cache time file');
			return false;// Error writing to cache
		}
		$data=Array(
			'version'		=>	self::$VERSION,
			'servicename'	=>	$this->ServiceName,
			'endpoint'		=>	$this->EndPoint,
			'namespace'		=>	$this->NameSpace
		);
		PhpWsdl::CallHook(
			'ClientWriteCacheHook',
			Array(
				'client'		=>	$this,
				'data'			=>	&$data
			)
		);
		if(file_put_contents($file.'.obj',serialize($data))===false){
			PhpWsdl::Debug('Could not write serialized cache');
			return false;
		}
		return true;
	}
	
	/**
	 * SOAP method call proxy method
	 * 
	 * @param string $method The method name
	 * @param array $param The method parameters
	 * @return mixed The server response
	 */
	public function __call($method,$param){
		return $this->DoRequest($method,$param);
	}
}
