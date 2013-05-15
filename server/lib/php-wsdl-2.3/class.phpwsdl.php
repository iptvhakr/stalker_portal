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

// Debugging
/*PhpWsdl::$Debugging=true;// Enable debugging
PhpWsdl::$DebugFile='./cache/debug.log';// The logfile to write the debugging messages to
PhpWsdl::$DebugBackTrace=false;// Include backtrace information in debugging messages?*/

// Initialize PhpWsdl
PhpWsdl::Init();
	
// You don't require class.phpwsdlelement.php and class.phpwsdlcomplex.php, 
// as long as you don't use complex types. So you may comment those two 
// requires out.
// You may also disable loading the class.phpwsdlproxy.php, if you don't plan 
// to use the proxy class for your webservice.
require_once(dirname(__FILE__).'/class.phpwsdlformatter.php');
require_once(dirname(__FILE__).'/class.phpwsdlobject.php');
require_once(dirname(__FILE__).'/class.phpwsdlparser.php');
require_once(dirname(__FILE__).'/class.phpwsdlproxy.php');
require_once(dirname(__FILE__).'/class.phpwsdlparam.php');
require_once(dirname(__FILE__).'/class.phpwsdlmethod.php');
require_once(dirname(__FILE__).'/class.phpwsdlelement.php');
require_once(dirname(__FILE__).'/class.phpwsdlcomplex.php');

// Do things after the environment is configured
PhpWsdl::PostInit();

/**
 * PhpWsdl class
 * 
 * @author Andreas Zimmermann
 * @copyright �2011 Andreas Zimmermann, wan24.de
 * @version 2.3
 */
class PhpWsdl{
	/**
	 * The version number 
	 * 
	 * @var string
	 */
	public static $VERSION='2.3';
	/**
	 * Set this to TRUE to enable the autorun in quick mode
	 * 
	 * @var boolean
	 */
	public static $AutoRun=false;
	/**
	 * Global static configuration
	 * 
	 * @var array
	 */
	public static $Config=Array();
	/**
	 * The webservice handler object
	 * 
	 * @var object
	 */
	public static $ProxyObject=null;
	/**
	 * The current PhpWsdl server
	 * 
	 * @var PhpWsdl
	 */
	public static $ProxyServer=null;
	/**
	 * Use WSDL with the proxy
	 * 
	 * @var boolean
	 */
	public static $UseProxyWsdl=false;
	/**
	 * The name
	 * 
	 * @var string
	 */
	public $Name;
	/**
	 * Documentation
	 * 
	 * @var string
	 */
	public $Docs=null;
	/**
	 * The namespace
	 * 
	 * @var string
	 */
	public $NameSpace=null;
	/**
	 * The SOAP endpoint URI
	 * 
	 * @var string
	 */
	public $EndPoint=null;
	/**
	 * Set this to the WSDL URI, if it's different from your SOAP endpoint + "?WSDL"
	 * 
	 * @var string
	 */
	public $WsdlUri=null;
	/**
	 * Set this to the PHP URI, if it's different from your SOAP endpoint + "?PHPSOAPCLIENT"
	 * 
	 * @var string
	 */
	public $PhpUri=null;
	/**
	 * Set this to the HTML documentation URI, if it's different from your SOAP endpoint
	 * 
	 * @var string
	 */
	public $DocUri=null;
	/**
	 * The options for the PHP SoapServer
	 * Note: "actor" and "uri" will be set at runtime
	 * 
	 * @var array
	 */
	public $SoapServerOptions=null;
	/**
	 * An array of file names to parse
	 * 
	 * @var string[]
	 */
	public $Files=Array();
	/**
	 * An array of complex types
	 * 
	 * @var PhpWsdlComplex[]
	 */
	public $Types=null;
	/**
	 * An array of method
	 * 
	 * @var PhpWsdlMethod[]
	 */
	public $Methods=null;
	/**
	 * Remove tabs and line breaks?
	 * Note: Unoptimized WSDL won't be cached
	 * 
	 * @var boolean
	 */
	public $Optimize=true;
	/**
	 * UTF-8 encoded WSDL from the last CreateWsdl method call
	 * 
	 * @var string
	 */
	public $WSDL=null;
	/**
	 * UTF-8 encoded HTML from the last OutputHtml method call
	 * 
	 * @var string
	 */
	public $HTML=null;
	/**
	 * UTF-8 encoded PHP from the last OutputPhp method call
	 * 
	 * @var string
	 */
	public $PHP=null;
	/**
	 * An array of basic types (these are just some of the XSD defined types 
	 * (see http://www.w3.org/TR/2001/PR-xmlschema-2-20010330/)
	 * 
	 * @var string[]
	 */
	public static $BasicTypes=Array(
		'anyType',
		'anyURI',
		'base64Binary',
		'boolean',
		'byte',
		'date',
		'decimal',
		'double',
		'duration',
		'dateTime',
		'float',
		'gDay',
		'gMonthDay',
		'gYearMonth',
		'gYear',
		'hexBinary',
		'int',
		'integer',
		'long',
		'NOTATION',
		'number',
		'QName',
		'short',
		'string',
		'time'
	);
	/**
	 * A list of non-nillable types
	 * 
	 * @var string[]
	 */
	public static $NonNillable=Array(
		'boolean',
		'decimal',
		'double',
		'float',
		'int',
		'integer',
		'long',
		'number',
		'short'
	);
	/**
	 * Set this to a writeable folder to enable caching the WSDL in files
	 * 
	 * @var string
	 */
	public static $CacheFolder=null;
	/**
	 * Is the cache folder writeable?
	 * 
	 * @var boolean|NULL
	 */
	public static $CacheFolderWriteAble=null;
	/**
	 * The cache timeout in seconds (set to zero to disable caching, too)
	 * If you set the value to -1, the cache will never expire. Then you have 
	 * to use the PhpWsdl->TidyCache method for cleaning up the cache once 
	 * you've made changes to your webservice.
	 * 
	 * @var int
	 */
	public static $CacheTime=3600;
	/**
	 * Write even unoptimized and/or documented XML to the cache?
	 * 
	 * @var boolean
	 */
	public static $CacheAllWsdl=false;
	/**
	 * Parse documentation?
	 * 
	 * @var boolean
	 */
	public $ParseDocs=true;
	/**
	 * Include documentation tags in WSDL, if the optimizer is disabled?
	 * 
	 * @var boolean
	 */
	public $IncludeDocs=true;
	/**
	 * Force sending WSDL (has a higher priority than PhpWsdl->ForceNotOutputWsdl)
	 * 
	 * @var boolean
	 */
	public $ForceOutputWsdl=false;
	/**
	 * Force NOT sending WSDL (disable sending WSDL, has a higher priority than ?WSDL f.e.)
	 * 
	 * @var boolean
	 */
	public $ForceNotOutputWsdl=false;
	/**
	 * Force sending HTML (has a higher priority than PhpWsdl->ForceNotOutputHtml)
	 * 
	 * @var boolean
	 */
	public $ForceOutputHtml=false;
	/**
	 * Force NOT sending HTML (disable sending HTML)
	 * 
	 * @var boolean
	 */
	public $ForceNotOutputHtml=false;
	/**
	 * The headline for the HTML output or NULL to use the default
	 * 
	 * @var string
	 */
	public $HtmlHeadLine=null;
	/**
	 * Force sending PHP (has a higher priority than PhpWsdl->ForceNotOutputPhp)
	 * 
	 * @var boolean
	 */
	public $ForceOutputPhp=false;
	/**
	 * Force NOT sending PHP (disable sending PHP)
	 * 
	 * @var boolean
	 */
	public $ForceNotOutputPhp=false;
	/**
	 * Regular expression parse a class name
	 * 
	 * @var string
	 */
	public static $classRx='/^.*class\s+([^\s]+)\s*\{.*$/is';
	/**
	 * The HTML2PDF license key (see www.htmltopdf.de)
	 * 
	 * @var string
	 */
	public static $HTML2PDFLicenseKey=null;
	/**
	 * The URI to the HTML2PDF http API
	 * 
	 * @var string
	 */
	public static $HTML2PDFAPI='http://online.htmltopdf.de/';
	/**
	 * The HTML2PDF settings (only available when using a valid license key)
	 * 
	 * @var array
	 */
	public static $HTML2PDFSettings=Array();
	/**
	 * Saves if the sources have been parsed
	 * 
	 * @var boolean
	 */
	public $SourcesParsed=false;
	/**
	 * Saves if the configuration has already been determined
	 * 
	 * @var boolean
	 */
	public $ConfigurationDetermined=false;
	/**
	 * The current PHP SoapServer object
	 * 
	 * @var SoapServer
	 */
	public $SoapServer=null;
	/**
	 * Is a http Auth login required to run the SOAP server?
	 * 
	 * @var boolean
	 */
	public $RequireLogin=false;
	/**
	 * Debugging messages
	 * 
	 * @var string[]
	 */
	public static $DebugInfo=Array();
	/**
	 * En- / Disable the debugging mode
	 * 
	 * @var boolean
	 */
	public static $Debugging=false;
	/**
	 * The debug file to write to
	 * 
	 * @var string
	 */
	public static $DebugFile=null;
	/**
	 * Put backtrace information in debugging messages
	 * 
	 * @var boolean
	 */
	public static $DebugBackTrace=false;
	/**
	 * WSDL namespaces
	 * 
	 * @var array
	 */
	public static $NameSpaces=null;
	
	/**
	 * PhpWsdl constructor
	 * Note: The quick mode by giving TRUE as first parameter is deprecated and will be removed from version 3.0.
	 * Use PhpWsdl::RunQuickMode() instead
	 * 
	 * @param string|boolean $nameSpace Namespace or NULL to let PhpWsdl determine it, or TRUE to run everything by determining all configuration -> quick mode (default: NULL)
	 * @param string|string[] $endPoint Endpoint URI or NULL to let PhpWsdl determine it - or, in quick mode, the webservice class filename(s) (default: NULL)
	 * @param string $cacheFolder The folder for caching WSDL or NULL to use the systems default (default: NULL)
	 * @param string|string[] $file Filename or array of filenames or NULL (default: NULL)
	 * @param string $name Webservice name or NULL to let PhpWsdl determine it (default: NULL)
	 * @param PhpWsdlMethod[] $methods Array of methods or NULL (default: NULL)
	 * @param PhpWsdlComplex[] $types Array of complex types or NULL (default: NULL)
	 * @param boolean $outputOnRequest Output WSDL on request? (default: FALSE)
	 * @param boolean|string|object|array $runServer Run SOAP server? (default: FALSE)
	 */
	public function PhpWsdl(
		$nameSpace=null,
		$endPoint=null,
		$cacheFolder=null,
		$file=null,
		$name=null,
		$methods=null,
		$types=null,
		$outputOnRequest=false,
		$runServer=false
		){
		// Quick mode
		self::Debug('PhpWsdl constructor called');
		$quickRun=false;
		if($nameSpace===true){
			self::Debug('Quick mode detected');
			$quickRun=true;
			$nameSpace=null;
			if(!is_null($endPoint)){
				if(self::$Debugging)
					self::Debug('Filename(s): '.print_r($endPoint,true));
				$endPoint=null;
			}
		}
		// SOAP server options
		$this->SoapServerOptions=Array(
			'soap_version'	=>	SOAP_1_1|SOAP_1_2,
			'encoding'		=>	'UTF-8',
			'compression'	=>	SOAP_COMPRESSION_ACCEPT|SOAP_COMPRESSION_GZIP|9
		);
		// Optimizer settings
		$this->Optimize=!isset($_GET['readable']);// Call with "?WSDL&readable" to get human readable WSDL
		self::Debug('Optimizer is '.(($this->Optimize)?'enabled':'disabled'));
		// Cache settings
		if(!is_null($cacheFolder)){
			self::Debug('Cache folder is '.$cacheFolder);
			self::$CacheFolder=$cacheFolder;
		}
		// Namespace
		$this->NameSpace=(is_null($nameSpace))?$this->DetermineNameSpace():$nameSpace;
		self::Debug('Namespace is '.$this->NameSpace);
		// Endpoint
		$this->EndPoint=((!is_null($endPoint)))?$endPoint:$this->DetermineEndPoint();
		self::Debug('Endpoint is '.$this->EndPoint);
		// Name
		if(!is_null($name)){
			self::Debug('Name is '.$name);
			$this->Name=$name;
		}
		// Source files
		if(!is_null($file)){
			if(self::$Debugging)
				self::Debug('Filename(s): '.print_r($file,true));
			$this->Files=array_merge($this->Files,(is_array($file))?$file:Array($file));
		}
		// Methods
		$this->Methods=(!is_null($methods))?$methods:Array();
		if(sizeof($this->Methods)>0&&self::$Debugging)
			self::Debug('Methods: '.print_r($this->Methods,true));
		// Types
		$this->Types=(!is_null($types))?$types:Array();
		if(sizeof($this->Types)>0&&self::$Debugging)
			self::Debug('Types: '.print_r($this->Types,true));
		// Constructor hook
		self::CallHook(
			'ConstructorHook',
			Array(
				'server'		=>	$this,
				'output'		=>	&$outputOnRequest,
				'run'			=>	&$runServer,
				'quickmode'		=>	&$quickRun
			)
		);
		// WSDL output
		if($outputOnRequest&&!$runServer)
			$this->OutputWsdlOnRequest();
		// Run the server
		if($quickRun||$runServer)
			$this->RunServer(null,(is_bool($runServer))?null:$runServer);
	}
	
	/**
	 * Create an instance of PhpWsdl
	 * Note: The quick mode by giving TRUE as first parameter is deprecated and will be removed from version 3.0.
	 * Use PhpWsdl::RunQuickMode() instead
	 * 
	 * @param string|boolean $nameSpace Namespace or NULL to let PhpWsdl determine it, or TRUE to run everything by determining all configuration -> quick mode (default: NULL)
	 * @param string|string[] $endPoint Endpoint URI or NULL to let PhpWsdl determine it - or, in quick mode, the webservice class filename(s) (default: NULL)
	 * @param string $cacheFolder The folder for caching WSDL or NULL to use the systems default (default: NULL)
	 * @param string|string[] $file Filename or array of filenames or NULL (default: NULL)
	 * @param string $name Webservice name or NULL to let PhpWsdl determine it (default: NULL)
	 * @param PhpWsdlMethod[] $methods Array of methods or NULL (default: NULL)
	 * @param PhpWsdlComplex[] $types Array of complex types or NULL (default: NULL)
	 * @param boolean $outputOnRequest Output WSDL on request? (default: FALSE)
	 * @param boolean|string|object|array $runServer Run SOAP server? (default: FALSE)
	 * @return PhpWsdl The PhpWsdl object
	 */
	public static function CreateInstance(
		$nameSpace=null,
		$endPoint=null,
		$cacheFolder=null,
		$file=null,
		$name=null,
		$methods=null,
		$types=null,
		$outputOnRequest=false,
		$runServer=false
		){
		self::Debug('Create new PhpWsdl instance');
		$obj=null;
		self::CallHook(
			'BeforeCreateInstanceHook',
			Array(
				'server'		=>	&$obj,
				'namespace'		=>	&$nameSpace,
				'endpoint'		=>	&$endPoint,
				'cachefolder'	=>	&$cacheFolder,
				'file'			=>	&$file,
				'name'			=>	&$name,
				'methods'		=>	&$methods,
				'types'			=>	&$types,
				'outputonrequest'=>	&$outputOnRequest,
				'runserver'		=>	&$runServer
			)
		);
		if(is_null($obj))
			$obj=new PhpWsdl($nameSpace,$endPoint,$cacheFolder,$file,$name,$methods,$types,$outputOnRequest,$runServer);
		self::CallHook(
			'CreateInstanceHook',
			Array(
				'server'		=>	&$obj
			)
		);
		return $obj;
	}
	
	/**
	 * Run the PhpWsdl SOAP server in quick mode
	 * 
	 * @param string|string[] $file The webservice handler class file or a list of files or NULL (default: NULL)
	 */
	public static function RunQuickMode($file=null){
		self::Debug('Run quick mode');
		$server=self::CreateInstance();
		if(!is_null($file))
			$server->Files=(is_array($file))?$file:Array($file);
		$server->RunServer();
	}
	
	/**
	 * Determine if WSDL was requested by the client
	 * 
	 * @return boolean WSDL requested?
	 */
	public function IsWsdlRequested(){
		return $this->ForceOutputWsdl||((isset($_GET['wsdl'])||isset($_GET['WSDL']))&&!$this->ForceNotOutputWsdl);
	}
	
	/**
	 * Determine if HTML was requested by the client
	 * 
	 * @return boolean HTML requested?
	 */
	public function IsHtmlRequested(){
		return $this->ForceOutputHtml||(strlen(file_get_contents('php://input'))<1&&!$this->ForceNotOutputHtml);
	}

	/**
	 * Determine if PHP was requested by the client
	 * 
	 * @return boolean PHP requested?
	 */
	public function IsPhpRequested(){
		return $this->ForceOutputPhp||((isset($_GET['phpsoapclient'])||isset($_GET['PHPSOAPCLIENT']))&&!$this->ForceNotOutputPhp);
	}

	/**
	 * Determine if this request is a SOAP request
	 * 
	 * @return boolean
	 */
	public function IsSoapRequest(){
		return !$this->IsHtmlRequested()&&!$this->IsWsdlRequested()&&!$this->IsPhpRequested();
	}

	/**
	 * Determine if only global methods are served
	 * 
	 * @return boolean Only global methods?
	 */
	public function IsOnlyGlobal(){
		$res=true;
		$i=-1;
		$len=sizeof($this->Methods);
		while(++$i<$len)
			if(!$this->Methods[$i]->IsGlobal){
				$res=false;
				break;
			}
		return $res;
	}
	
	/**
	 * Disble caching
	 * 
	 * @param bool $allCaching Do not only set the timeout to zero? (default: TRUE)
	 */
	public static function DisableCache($allCaching=true){
		self::Debug('Disable '.(($allCaching)?'all':'this').' caching');
		if($allCaching)
			self::$CacheFolder=null;
		if(!$allCaching)
			self::$CacheTime=0;
	}
	
	/**
	 * Enable caching
	 * 
	 * @param string $folder The cache folder or NULL to use a system temporary directory (default: NULL)
	 * @param int $timeout The caching timeout in seconds or NULL to use the previous value or the default (3600) (default: NULL)
	 */
	public static function EnableCache($folder=null,$timeout=null){
		if(is_null($folder)){
			if(self::IsCacheFolderWriteAble('./cache')){
				$folder='./cache';
			}else if(self::IsCacheFolderWriteAble(dirname(__FILE__).'/cache')){
				$folder=dirname(__FILE__).'/cache';
			}else if(self::IsCacheFolderWriteAble(sys_get_temp_dir())){
				$folder=sys_get_temp_dir();
			}else{
				self::Debug('Could not find a cache folder');
			}
		}
		if(is_null($timeout))
			$timeout=(self::$CacheTime!=0)?self::$CacheTime:3600;
		self::Debug('Enable cache in folder "'.((is_null($folder))?'(none)':$folder).'" with timeout '.$timeout.' seconds');
		self::$CacheFolder=$folder;
		self::$CacheTime=$timeout;
	}
	
	/**
	 * Determine the configuration
	 * 
	 * @return boolean Succeed?
	 */
	public function DetermineConfiguration(){
		if(!is_null($this->GetWsdlFromCache()))
			return true;
		$this->ParseSource();
		$mLen=sizeof($this->Methods);
		$tLen=sizeof($this->Types);
		$fLen=sizeof($this->Files);
		if($this->ConfigurationDetermined)
			return ($mLen>0||$tLen>0)&&!is_null($this->Name);
		self::Debug('Determine configuration');
		$this->ConfigurationDetermined=true;
		$tryWebService=
			!$this->IsFileInList('class.webservice.php')&&
			(
				file_exists('class.webservice.php')||
				file_exists(dirname(__FILE__).'/class.webservice.php')
			);
		// No methods or types? Try to parse them from the current script
		if($mLen<1&&$tLen<1){
			$tryFiles=Array();
			if($tryWebService){
				if(file_exists('class.webservice.php'))
					$tryFiles[]='class.webservice.php';
				if(file_exists(dirname(__FILE__).'/class.webservice.php'))
					$tryFiles[]=dirname(__FILE__).'/class.webservice.php';
			}
			if(!$this->IsFileInList($_SERVER['SCRIPT_FILENAME']))
				$tryFiles[]=$_SERVER['SCRIPT_FILENAME'];
			$i=-1;
			$len=sizeof($tryFiles);
			while(++$i<$len){
				$file=$tryFiles[$i];
				self::Debug('Try to load objects from '.$file);
				$this->ParseSource(false,file_get_contents($file));
				$mLen=sizeof($this->Methods);
				$tLen=sizeof($this->Types);
				if($mLen<1&&$tLen<1)
					continue;
				self::Debug('Found objects, adding the file to list');
				$this->Files[]=$file;
				$fLen++;
				break;
			}
			if($mLen<1&&$tLen<1)
				return false;
		}
		// No class name? Try to parse one from the current files
		if(!is_null($this->Name))
			return true;
		$tryFiles=Array();
		if(!$this->IsFileInList($_SERVER['SCRIPT_FILENAME']))
			$tryFiles[]=$_SERVER['SCRIPT_FILENAME'];
		if($tryWebService){
			if(file_exists('class.webservice.php'))
				$tryFiles[]='class.webservice.php';
			if(file_exists(dirname(__FILE__).'/class.webservice.php'))
				$tryFiles[]=dirname(__FILE__).'/class.webservice.php';
		}
		$tryFiles=array_merge($this->Files,$tryFiles);
		$i=-1;
		$len=sizeof($tryFiles);
		while(++$i<$len){
			$file=$tryFiles[$i];
			self::Debug('Try to determine the class name from '.$file);
			$temp=file_get_contents($file);
			if(!preg_match(self::$classRx,$temp))
				continue;
			$class=preg_replace(self::$classRx,"$1",$temp);
			self::Debug('Found class name '.$class);
			break;
		}
		// No class name yet? Use the default if only global methods are present
		if(is_null($class)&&$this->IsOnlyGlobal()){
			self::Debug('Using default webservice name');
			$class='SoapWebService';
		}
		$this->Name=$class;
		return !is_null($class);
	}
	
	/**
	 * Determine the endpoint URI
	 */
	public function DetermineEndPoint(){
		return ((isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']=='on')?'https':'http').'://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
	}

	/**
	 * Determine the namespace
	 */
	public function DetermineNameSpace(){
		return 'http://'.$_SERVER['SERVER_NAME'].str_replace(basename($_SERVER['SCRIPT_NAME']),'',$_SERVER['SCRIPT_NAME']);
	}
	
	/**
	 * Determine if a file is included in the list of files
	 * 
	 * @param string $file The filename
	 * @return boolean In the list?
	 */
	public function IsFileInList($file){
		$file=preg_quote(basename($file));
		$i=-1;
		$fLen=sizeof($this->Files);
		while(++$i<$fLen)
			if(preg_match('/^(.*\/)?'.$file.'$/i',$this->Files[$i]))
				return true;
		return false;
	}
	
	/**
	 * Create the WSDL
	 * 
	 * @param boolean $reCreate Don't use the cached WSDL? (default: FALSE)
	 * @param boolean $optimize If TRUE, override the PhpWsdl->Optimizer property and force optimizing (default: FALSE)
	 * @return string The UTF-8 encoded WSDL as string
	 */
	public function CreateWsdl($reCreate=false,$optimizer=false){
		self::Debug('Create WSDL');
		// Ask the cache
		if(!$reCreate&&(self::$CacheAllWsdl||!$this->IncludeDocs||$optimizer||$this->Optimize)){
			self::Debug('Try to get WSDL from the cache');
			$wsdl=$this->GetWsdlFromCache();
			if(!is_null($wsdl)){
				self::Debug('Using cached WSDL');
				return (!$optimizer&&!$this->Optimize)?self::FormatXml($wsdl):$wsdl;
			}
		}
		// Prepare the WSDL generator
		if(!$this->DetermineConfiguration()){
			$mLen=sizeof($this->Methods);
			$tLen=sizeof($this->Types);
			if($mLen<1&&$tLen<1){
				self::Debug('No methods and types');
				throw(new Exception('No methods and no complex types are available'));
			}
			if(is_null($this->Name)){
				self::Debug('No name');
				throw(new Exception('Could not determine webservice handler class name'));
			}
		}
		$res=Array();
		// Create the XML Header
		self::CallHook(
			'CreateWsdlHeaderHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'optimizer'		=>	&$optimizer
			)
		);
		// Create types
		self::CallHook(
			'CreateWsdlTypeSchemaHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'optimizer'		=>	&$optimizer
			)
		);
		// Create messages
		self::CallHook(
			'CreateWsdlMessagesHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'optimizer'		=>	&$optimizer
			)
		);
		// Create port types
		self::CallHook(
			'CreateWsdlPortsHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'optimizer'		=>	&$optimizer
			)
		);
		// Create bindings
		self::CallHook(
			'CreateWsdlBindingsHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'optimizer'		=>	&$optimizer
			)
		);
		// Create the service
		self::CallHook(
			'CreateWsdlServiceHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'optimizer'		=>	&$optimizer
			)
		);
		// Finish the WSDL XML string
		self::CallHook(
			'CreateWsdlFooterHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'optimizer'		=>	&$optimizer
			)
		);
		// Run the optimizer
		self::CallHook(
			'CreateWsdlOptimizeHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'optimizer'		=>	&$optimizer
			)
		);
		// Fill the cache
		if(self::$CacheAllWsdl||!$this->IncludeDocs||$optimizer||$this->Optimize){
			self::Debug('Cache created WSDL');
			$this->WriteWsdlToCache(
				(
					!self::$CacheAllWsdl&&
					!$optimizer&&
					!$this->Optimize
				)
					?self::OptimizeXml($res)
					:$res
				,
				null,
				null,
				true
			);
		}
		return $this->WSDL;
	}

	/**
	 * Create header
	 * 
	 * @param array $data Data array
	 * @return boolean Response
	 */
	public static function CreateWsdlHeader($data){
		self::Debug('CreateWsdlHeader');
		$res=&$data['res'];
		$server=$data['server'];
		$res[]='<?xml version="1.0" encoding="UTF-8"?>';
		$temp=Array();
		$keys=array_keys(self::$NameSpaces);
		$i=-1;
		$len=sizeof($keys);
		while(++$i<$len)
			$temp[]='xmlns:'.$keys[$i].'="'.self::$NameSpaces[$keys[$i]].'"';
		$res[]='<wsdl:definitions xmlns:tns="'.$server->NameSpace.'" targetNamespace="'.$server->NameSpace.'" '.implode(' ',$temp).'>';
		return true;
	}
	
	/**
	 * Create type schema
	 * 
	 * @param array $data Data array
	 * @return boolean Response
	 */
	public static function CreateWsdlTypeSchema($data){
		self::Debug('CreateWsdlTypeSchema');
		$res=&$data['res'];
		$server=$data['server'];
		$tLen=sizeof($server->Types);
		if($tLen>0){
			$res[]='<wsdl:types>';
			$res[]='<s:schema targetNamespace="'.$server->NameSpace.'">';
			$i=-1;
			while(++$i<$tLen)
				$res[]=$server->Types[$i]->CreateType($server);
			self::CallHook(
				'CreateWsdlTypesHook',
				$data
			);
			$res[]='</s:schema>';
			$res[]='</wsdl:types>';
		}
		return true;
	}
	
	/**
	 * Create messages
	 * 
	 * @param array $data Data array
	 * @return boolean Response
	 */
	public static function CreateWsdlMessages($data){
		self::Debug('CreateWsdlMessages');
		$res=&$data['res'];
		$server=$data['server'];
		$i=-1;
		$mLen=sizeof($server->Methods);
		while(++$i<$mLen)
			$res[]=$server->Methods[$i]->CreateMessages($server);
		return true;
	}
	
	/**
	 * Create port types
	 * 
	 * @param array $data Data array
	 * @return boolean Response
	 */
	public static function CreateWsdlPorts($data){
		self::Debug('CreateWsdlPorts');
		$res=&$data['res'];
		$server=$data['server'];
		$res[]='<wsdl:portType name="'.$server->Name.'Soap">';
		$i=-1;
		$mLen=sizeof($server->Methods);
		while(++$i<$mLen)
			$res[]=$server->Methods[$i]->CreatePortType($server);
		self::CallHook(
			'CreateWsdlPortsAddHook',
			$data
		);
		$res[]='</wsdl:portType>';
		return true;
	}
	
	/**
	 * Create bindings
	 * 
	 * @param array $data Data array
	 * @return boolean Response
	 */
	public static function CreateWsdlBindings($data){
		self::Debug('CreateWsdlBindings');
		$res=&$data['res'];
		$server=$data['server'];
		$res[]='<wsdl:binding name="'.$server->Name.'Soap" type="tns:'.$server->Name.'Soap">';
		$res[]='<soap:binding transport="http://schemas.xmlsoap.org/soap/http" style="rpc" />';
		$i=-1;
		$mLen=sizeof($server->Methods);
		while(++$i<$mLen)
			$res[]=$server->Methods[$i]->CreateBinding($server);
		self::CallHook(
			'CreateWsdlBindingsAddHook',
			$data
		);
		$res[]='</wsdl:binding>';
		return true;
	}
	
	/**
	 * Create service port
	 * 
	 * @param array $data Data array
	 * @return boolean Response
	 */
	public static function CreateWsdlService($data){
		self::Debug('CreateWsdlService');
		$res=&$data['res'];
		$server=$data['server'];
		$res[]='<wsdl:service name="'.$server->Name.'">';
		if($server->IncludeDocs&&!$server->Optimize&&!is_null($server->Docs))
			$res[]='<wsdl:documentation><![CDATA['.$server->Docs.']]></wsdl:documentation>';
		$res[]='<wsdl:port name="'.$server->Name.'Soap" binding="tns:'.$server->Name.'Soap">';
		$res[]='<soap:address location="'.$server->EndPoint.'" />';
		$res[]='</wsdl:port>';
		self::CallHook(
			'CreateWsdlServiceAddHook',
			$data
		);
		$res[]='</wsdl:service>';
		return true;
	}
	
	/**
	 * Create footer
	 * 
	 * @param array $data Data array
	 * @return boolean Response
	 */
	public static function CreateWsdlFooter($data){
		self::Debug('CreateWsdlFooter');
		$res=&$data['res'];
		$res[]='</wsdl:definitions>';
		return true;
	}

	/**
	 * Optimize WSDL
	 * 
	 * @param array $data Data array
	 * @return boolean Response
	 */
	public static function CreateWsdlOptimize($data){
		self::Debug('CreateWsdlOptimize');
		$res=&$data['res'];
		$server=$data['server'];
		$optimizer=&$data['optimizer'];
		$res=utf8_encode(implode('',$res));
		$res=(!$optimizer&&!$server->Optimize)
			?self::FormatXml($res)
			:self::OptimizeXml($res);
		$server->WSDL=$res;
		return true;
	}
	
	/**
	 * Remove tabs and newline from XML
	 * 
	 * @param string $xml The unoptimized XML
	 * @return string The optimized XML
	 */
	public static function OptimizeXml($xml){
		self::Debug('Optimize XML');
		return preg_replace('/[\n|\t]/','',$xml);
	}
	
	/**
	 * Format XML human readable
	 * 
	 * @param string $xml The XML
	 * @return string Human readable XML
	 */
	public static function FormatXml($xml){
		self::Debug('Produce human readable XML');
		$input=fopen('data://text/plain,'.$xml,'r');
		$output=fopen('php://temp','w');
		$xf=new PhpWsdlFormatter($input,$output);
		$xf->format();
		rewind($output);
		$xml=stream_get_contents($output);
		fclose($input);
		fclose($output);
		return $xml;
	}
	
	/**
	 * Interpret the @service keyword
	 * 
	 * @param $data The parser data
	 * @return boolean Response
	 */
	public static function InterpretService($data){
		self::Debug('Interpret service');
		$server=$data['server'];
		$info=explode(' ',$data['keyword'][1],2);
		if(sizeof($info)<1){
			self::Debug('WARNING:  Invalid service definition');
			return true;
		}
		$server->Name=$info[0];
		if($server->ParseDocs&&sizeof($info)>1&&is_null($server->Docs))
			$server->Docs=$info[1];
		return true;
	}
	
	/**
	 * Parse source files for WSDL definitions in comments
	 * 
	 * @param boolean $init Empty the Methods and the Types properties? (default: FALSE)
	 * @param string $str Source string or NULL to parse the defined files (default: NULL)
	 */
	public function ParseSource($init=false,$str=null){
		self::Debug('Parse the source');
		if($init){
			self::Debug('Init methods and types');
			$this->Methods=Array();
			$this->Types=Array();
			$this->SourcesParsed=false;
		}
		if(is_null($str)){
			if($this->SourcesParsed)
				return;
			self::Debug('Load source files');
			$this->SourcesParsed=true;
			$fLen=sizeof($this->Files);
			if($fLen<1)
				return;
			// Load the source
			$src=Array();
			$i=-1;
			while(++$i<$fLen)
				$src[]=trim(file_get_contents($this->Files[$i]));
		}else{
			self::Debug('Use given string');
			$src=Array($str);
		}
		// Parse the source
		self::Debug('Run the parser');
		$parser=new PhpWsdlParser($this);
		$parser->Parse(implode("\n",$src));
	}
	
	/**
	 * Output the WSDL to the client
	 * 
	 * @param boolean $withHeaders Output XML headers? (default: TRUE)
	 */
	public function OutputWsdl($withHeaders=true){
		if(!self::CallHook(
				'OutputWsdlHook',
				Array(
					'server'		=>	$this
				)
			)
		)
			return;
		self::Debug('Output WSDL');
		if($withHeaders)
			header('Content-Type: text/xml; charset=UTF-8',true);
		echo $this->CreateWsdl();
	}

	/**
	 * Output the WSDL to the client, if requested
	 * 
	 * @param boolean $andExit Exit after sending WSDL? (default: TRUE)
	 * @return boolean Has the WSDL been sent to the client?
	 */
	public function OutputWsdlOnRequest($andExit=true){
		if(!$this->IsWsdlRequested())
			return false;
		$this->OutputWsdl();
		if($andExit){
			self::Debug('Exit script execution');
			exit;
		}
		return true;
	}
	
	/**
	 * Output the HTML to the client
	 * 
	 * @param boolean $withHeaders Send HTML headers? (default: TRUE)
	 * @param boolean $echo Print HTML (default: TRUE)
	 * @param boolean $cache Cache the result (default: TRUE);
	 * @return string The HTML
	 */
	public function OutputHtml($withHeaders=true,$echo=true,$cache=true){
		self::Debug('Output HTML');
		if(sizeof($this->Methods)<1)
			$this->CreateWsdl();
		if(!self::CallHook(
				'OutputHtmlHook',
				Array(
					'server'		=>	$this
				)
			)
		)
			return;
		// Header
		if($withHeaders)
			header('Content-Type: text/html; charset=UTF-8',true);
		$this->GetWsdlFromCache();
		if(!is_null($this->HTML)){
			if($echo)
				echo $this->HTML;
			return $this->HTML;
		}
		$res=Array();
		$res[]='<html>';
		$res[]='<head>';
		$res[]='<title>'.((is_null($this->HtmlHeadLine))?$this->Name.' interface description':nl2br(htmlentities($this->HtmlHeadLine))).'</title>';
		$res[]='<style type="text/css" media="all">';
		$res[]='body{font-family:Calibri,Arial;background-color:#fefefe;}';
		$res[]='.pre{font-family:Courier;}';
		$res[]='.normal{font-family:Calibri,Arial;}';
		$res[]='.bold{font-weight:bold;}';
		$res[]='h1,h2,h3{font-family:Verdana,Times;}';
		$res[]='h1{border-bottom:1px solid gray;}';
		$res[]='h2{border-bottom:1px solid silver;}';
		$res[]='h3{border-bottom:1px dashed silver;}';
		$res[]='a{text-decoration:none;}';
		$res[]='a:hover{text-decoration:underline;}';
		$res[]='.blue{color:#3400FF;}';
		$res[]='.lightBlue{color:#5491AF;}';
		if(!is_null(self::$HTML2PDFLicenseKey)&&self::$HTML2PDFSettings['attachments']=='1')
			$res[]='.print{display:none;}';
		self::CallHook(
			'CreateHtmlCssHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res
			)
		);
		$res[]='</style>';
		$res[]='<style type="text/css" media="print">';
		$res[]='.noprint{display:none;}';
		if(!is_null(self::$HTML2PDFLicenseKey)&&self::$HTML2PDFSettings['attachments']=='1')
			$res[]='.print{display:block;}';
		self::CallHook(
			'CreateHtmlCssPrintHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res
			)
		);
		$res[]='</style>';
		$res[]='</head>';
		$res[]='<body>';
		$types=$this->SortObjectsByName($this->Types);
		$methods=$this->SortObjectsByName($this->Methods);
		// General information
		self::CallHook(
			'CreateHtmlGeneralHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'methods'		=>	&$methods,
				'types'			=>	&$types
			)
		);
		// Index
		self::CallHook(
			'CreateHtmlIndexHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'methods'		=>	&$methods,
				'types'			=>	&$types
			)
		);
		// Complex types
		self::CallHook(
			'CreateHtmlComplexTypesHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'methods'		=>	&$methods,
				'types'			=>	&$types
			)
		);
		// Methods
		self::CallHook(
			'CreateHtmlMethodsHook',
			Array(
				'server'		=>	$this,
				'res'			=>	&$res,
				'methods'		=>	&$methods,
				'types'			=>	&$types
			)
		);
		// HTML2PDF link
		$param=Array(
			'plain'			=>	'1',
			'filename'		=>	$this->Name.'-webservices.pdf',
			'print'			=>	'1'
		);
		if(!is_null(self::$HTML2PDFLicenseKey)){
			// Use advanced HTML2PDF API
			$temp=array_merge(self::$HTML2PDFSettings,Array(
				'url'			=>	$this->GetDocUri()
			));
			if($temp['attachments']=='1'){
				$temp['attachment_1']=$this->Name.'.wsdl:'.$this->GetWsdlUri();
				$cnt=1;
				if($this->ParseDocs&&$this->IncludeDocs){
					$cnt=2;
					$temp['attachment_2']=$this->Name.'-doc.wsdl:'.$this->GetWsdlUri().'&readable';
				}
				$cnt++;
				$temp['attachment_'.$cnt]=$this->Name.'.soapclient.php:'.$this->GetPhpUri();
				self::CallHook(
					'PdfAttachmentHook',
					Array(
						'server'		=>	$this,
						'cnt'			=>	&$cnt,
						'param'			=>	&$temp,
						'res'			=>	&$res,
						'methods'		=>	&$methods,
						'types'			=>	&$types
					)
				);
			}
			$options=Array();
			$keys=array_keys($temp);
			$i=-1;
			$len=sizeof($keys);
			while(++$i<$len)
				$options[]=$keys[$i].'='.$temp[$keys[$i]];
			$options='$'.base64_encode(implode("\n",$options));
			$license=sha1(self::$HTML2PDFLicenseKey.self::$HTML2PDFLicenseKey).'-'.sha1($options.self::$HTML2PDFLicenseKey);
			$param['url']=$options;
			$param['license']=$license;
		}
		$temp=$param;
		$param=Array();
		$keys=array_keys($temp);
		$i=-1;
		$len=sizeof($keys);
		while(++$i<$len)
			$param[]=urlencode($keys[$i]).'='.urlencode($temp[$keys[$i]]);
		$pdfLink=self::$HTML2PDFAPI.'?'.implode('&',$param);
		// Footer
		$res[]='<hr>';
		//$res[]='<p><small>Powered by <a href="http://code.google.com/p/php-wsdl-creator/">PhpWsdl</a><span class="noprint"> - PDF download: <a href="'.$pdfLink.'">Download this page as PDF</a></span></small></p>';
		$res[]='</body>';
		$res[]='</html>';
		// Clean up the generated HTML and send it
		$res=implode("\n",$res);
		$res=str_replace('<br />','<br>',$res);// Because nl2br will produce XHTML (and nothing if the second parameter is FALSE!?)
		if(!self::CallHook(
				'SendHtmlHook',
				Array(
					'server'		=>	$this,
					'res'			=>	&$res
				)
			)
		)
			return;
		$res=utf8_encode($res);
		$this->HTML=$res;
		if($cache)
			$this->WriteWsdlToCache(null,null,null,true);
		if($echo)
			echo $res;
		return $res;
	}
	
	/**
	 * Create general information
	 * 
	 * @param array $data The information object
	 * @return boolean Response
	 */
	public static function CreateHtmlGeneral($data){
		self::Debug('CreateHtmlGeneral');
		$res=&$data['res'];
		$server=$data['server'];
		$res[]='<h1>'.$server->Name.' SOAP WebService interface description</h1>';
		$res[]='<p>Endpoint URI: <span class="pre">'.$server->EndPoint.'</span></p>';
		//$res[]='<p>WSDL URI: <span class="pre"><a href="'.$server->GetWsdlUri().'&readable">'.$server->GetWsdlUri().'</a></span></p>';
		$res[]='<p>WSDL URI: <span class="pre"><a href="'.$server->GetWsdlUri().'">'.$server->GetWsdlUri().'</a></span></p>';
		$res[]='<p>Readable WSDL URI: <span class="pre"><a href="'.$server->GetWsdlUri().'&readable">'.$server->GetWsdlUri().'&readable</a></span></p>';
		$res[]='<p>PHP SOAP client download URI: <span class="pre"><a href="'.$server->GetPhpUri().'">'.$server->GetPhpUri().'</a></span></p>';
		if(self::$HTML2PDFSettings['attachments']=='1'&&!is_null(self::$HTML2PDFLicenseKey))
			$res[]='<p class="print">The WSDL files and client proxy class(es) are attached to this PDF documentation.</p>';
		if(!is_null($server->Docs))
			$res[]='<p>'.nl2br(htmlentities($server->Docs)).'</p>';
		return true;
	}
	
	/**
	 * Create table of contents
	 * 
	 * @param array $data The information object
	 * @return boolean Response
	 */
	public static function CreateHtmlIndex($data){
		self::Debug('CreateHtmlIndex');
		$res=&$data['res'];
		$types=&$data['types'];
		$methods=&$data['methods'];
		$tLen=sizeof($types);
		$mLen=sizeof($methods);
		$res[]='<div class="noprint">';
		$res[]='<h2>Index</h2>';
		if($tLen>0){
			$res[]='<p>Complex types:</p>';
			$i=-1;
			$res[]='<ul>';
			while(++$i<$tLen)
				$res[]='<li><a href="#'.$types[$i]->Name.'"><span class="pre">'.$types[$i]->Name.'</span></a></li>';
			$res[]='</ul>';
		}
		if($mLen>0){
			$res[]='<p>Public methods:</p>';
			$i=-1;
			$res[]='<ul>';
			while(++$i<$mLen)
				$res[]='<li><a href="#'.$methods[$i]->Name.'"><span class="pre">'.$methods[$i]->Name.'</span></a></li>';
			$res[]='</ul>';
		}
		$res[]='</div>';
		return true;
	}
	
	/**
	 * Create method list
	 * 
	 * @param array $data The information object
	 * @return boolean Response
	 */
	public static function CreateHtmlMethods($data){
		self::Debug('CreateHtmlMethods');
		$res=&$data['res'];
		$methods=&$data['methods'];
		$mLen=sizeof($methods);
		if($mLen>0){
			$res[]='<h2>Public methods</h2>';
			$i=-1;
			while(++$i<$mLen)
				$methods[$i]->CreateMethodHtml(array_merge(
					$data,
					Array(
						'method'		=>	$methods[$i]
					)
				));
		}
		return true;
	}

	/**
	 * Create complex types list
	 * 
	 * @param array $data The information object
	 * @return boolean Response
	 */
	public static function CreateHtmlComplexTypes($data){
		self::Debug('CreateHtmlComplexTypes');
		$res=&$data['res'];
		$types=&$data['types'];
		$server=$data['server'];
		$tLen=sizeof($server->Types);
		if($tLen>0){
			$res[]='<h2>Complex types</h2>';
			$i=-1;
			while(++$i<$tLen)
				$types[$i]->CreateTypeHtml(array_merge(
					$data,
					Array(
						'type'			=>	$types[$i]
					)
				));
		}
		return true;
	}
	
	/**
	 * Sort objects by name
	 * 
	 * @param PhpWsdlComplex[]|PhpWsdlMethod[] $obj
	 * @return PhpWsdlComplex[]|PhpWsdlMethod[] Sorted objects
	 */
	public function SortObjectsByName($obj){
		self::Debug('Sort objects by name');
		$temp=Array();
		$i=-1;
		$len=sizeof($obj);
		while(++$i<$len)
			$temp[$obj[$i]->Name]=$obj[$i];
		$keys=array_keys($temp);
		sort($keys);
		$res=Array();
		$i=-1;
		while(++$i<$len)
			$res[]=$temp[$keys[$i]];
		return $res;
	}
	
	/**
	 * Output the HTML to the client, if requested
	 * 
	 * @param boolean $andExit Exit after sending HTML? (default: TRUE)
	 * @return boolean Has the HTML been sent to the client?
	 */
	public function OutputHtmlOnRequest($andExit=true){
		if(!$this->IsHtmlRequested())
			return false;
		$this->OutputHtml();
		if($andExit){
			self::Debug('Exit script execution');
			exit;
		}
		return true;
	}
	
	/**
	 * Output the PHP SOAP client source for this webservice
	 * 
	 * @param boolean $withHeaders Send text headers? (default: TRUE)
	 * @param boolean $echo Print source (default: TRUE)
	 * @param array $options Options array (default: array)
	 * @param boolean $cache Cache the result (default: TRUE);
	 * @return string PHP source
	 */
	public function OutputPhp($withHeaders=true,$echo=true,$options=Array(),$cache=true){
		self::Debug('Output PHP');
		if(sizeof($this->Methods)<1)
			$this->CreateWsdl();
		if(!self::CallHook(
				'OutputPhpHook',
				Array(
					'server'		=>	$this,
					'withHeaders'	=>	&$withHeaders,
					'echo'			=>	&$echo,
					'options'		=>	&$options
				)
			)
		)
			return '';
		// Options
		$hasOptions=sizeof(array_keys($options))>0;
		if(!isset($options['class']))
			$options['class']=$this->Name.'SoapClient';
		if(!isset($options['openphp']))
			$options['openphp']=true;
		if(!isset($options['phpclient']))
			$options['phpclient']=true;
		$data=Array(
			'server'		=>	$this,
			'withHeaders'	=>	&$withHeaders,
			'echo'			=>	&$echo,
			'options'		=>	&$options,
			'res'			=>	&$res
		);
		// Header
		if($withHeaders){
			header('Content-Type: text/plain; charset=UTF-8',true);
			header('Content-Disposition: attachment; filename='.$options['class'].'.php');
		}
		if(!$hasOptions){
			if(is_null($this->PHP))
				$this->GetWsdlFromCache();
			if(!is_null($this->PHP)){
				if($echo)
					echo $this->PHP;
				return $this->PHP;
			}
		}else if(isset($options['php'])&&!is_null($options['php'])){
			echo $options['php'];
			return $options['php'];
		}
		$res=Array();
		if($options['openphp'])
			$res[]="<?php";
		$res[]="/**";
		if(!is_null($this->Docs)){
			$res[]=" * ".implode("\n * ",explode("\n",$this->Docs));
			$res[]=" *";
		}
		$res[]=" * @service ".$options['class'];
		$res[]=" */";
		$res[]="class ".$options['class']."{";
		$res[]="\t/**";
		$res[]="\t * The WSDL URI";
		$res[]="\t *";
		$res[]="\t * @var string";
		$res[]="\t */";
		$res[]="\tpublic static \$_WsdlUri='".(!is_null($this->WsdlUri)?$this->WsdlUri:$this->EndPoint.'?wsdl')."';";
		$res[]="\t/**";
		$res[]="\t * The PHP SoapClient object";
		$res[]="\t *";
		$res[]="\t * @var object";
		$res[]="\t */";
		$res[]="\tpublic static \$_Server=null;";
		self::CallHook('BeginCreatePhpHook',$data);
		$res[]='';
		$res[]="\t/**";
		$res[]="\t * Send a SOAP request to the server";
		$res[]="\t *";
		$res[]="\t * @param string \$method The method name";
		$res[]="\t * @param array \$param The parameters";
		$res[]="\t * @return mixed The server response";
		$res[]="\t */";
		$res[]="\tpublic static function _Call(\$method,\$param){";
		if(self::CallHook('CreatePhpCallHook',$data)){
			$res[]="\t\tif(is_null(self::\$_Server))";
			if($options['phpclient']){
				$res[]="\t\t\tself::\$_Server=new SoapClient(self::\$_WsdlUri);";
				$res[]="\t\treturn self::\$_Server->__soapCall(\$method,\$param);";
			}else{
				$res[]="\t\t\tself::\$_Server=new PhpWsdlClient(self::\$_WsdlUri);";
				$res[]="\t\treturn self::\$_Server->DoRequest(\$method,\$param);";
			}
		}
		$res[]="\t}";
		// Methods
		$i=-1;
		$len=sizeof($this->Methods);
		while(++$i<$len){
			$res[]='';
			$this->Methods[$i]->CreateMethodPhp($data);
		}
		$res[]="}";
		// Types
		$i=-1;
		$len=sizeof($this->Types);
		while(++$i<$len){
			$res[]='';
			$this->Types[$i]->CreateTypePhp($data);
		}
		self::CallHook('EndCreatePhpHook',$data);
		$res=utf8_encode(implode("\n",$res));
		if(!$hasOptions){
			if(is_null($this->PHP))
				$this->PHP=$res;
			if($cache)
				$this->WriteWsdlToCache(null,null,null,true);
		}
		if($echo)
			echo $res;
		return $res;
	}
	
	/**
	 * Output the PHP SOAP client source for this webservice, if requested
	 * 
	 * @param boolean $andExit Exit after sending the PHP source?
	 * @return boolean PHP sent?
	 */
	public function OutputPhpOnRequest($andExit=true){
		if(!$this->IsPhpRequested())
			return false;
		$this->OutputPhp();
		if($andExit){
			self::Debug('Exit script execution');
			exit;
		}
		return true;
	}
	
	/**
	 * Run the PHP SoapServer
	 * 
	 * @param string $wsdlFile The WSDL file name or NULL to let PhpWsdl decide (default: NULL)
	 * @param string|object|array $class The class name to serve, the classname and class as array or NULL (default: NULL)
	 * @param boolean $andExit Exit after running the server? (default: TRUE)
	 * @param boolean $forceNoWsdl Force no WSDL usage? (default: FALSE);
	 * @return boolean Did the server run?
	 */
	public function RunServer($wsdlFile=null,$class=null,$andExit=true,$forceNoWsdl=false){
		self::Debug('Run the server');
		if($forceNoWsdl)
			self::Debug('Forced non-WSDL mode');
		if(self::CallHook(
				'BeforeRunServerHook',
				Array(
					'server'		=>	$this,
					'wsdlfile'		=>	&$wsdlFile,
					'class'			=>	&$class,
					'andexit'		=>	&$andExit,
					'forcenowsdl'	=>	&$forceNoWsdl
				)
			)
		){
			// WSDL requested?
			if($this->OutputWsdlOnRequest($andExit))
				return false;
			// PHP requested?
			if($this->OutputPhpOnRequest($andExit))
				return false;
			// HTML requested?
			if($this->OutputHtmlOnRequest($andExit))
				return false;
		}
		// Login
		$user=null;
		$password=null;
		if($this->RequireLogin){
			if(isset($_SERVER['PHP_AUTH_USER'])||isset($_SERVER['PHP_AUTH_PW'])){
				$user=(isset($_SERVER['PHP_AUTH_USER']))?$_SERVER['PHP_AUTH_USER']:null;
				$password=(isset($_SERVER['PHP_AUTH_PW']))?$_SERVER['PHP_AUTH_PW']:null;
			}
			self::Debug('Check login '.$user.':'.str_repeat('*',strlen($password)));
			if(!self::CallHook(
					'LoginHook',
					Array(
						'server'		=>	$this,
						'user'			=>	&$user,
						'password'		=>	&$password
					)
				)
			){
				self::Debug('Login required');
				header('WWW-Authenticate: Basic realm="SOAP webservice login required"');
			    header('HTTP/1.0 401 Unauthorized');
				if($andExit){
					self::Debug('Exit script execution');
					exit;
				}
			    return false;
			}
		}
		// Load the proxy
		$useProxy=false;
		if(is_array($class)){
			self::Debug('Use the proxy for '.$class[0]);
			self::$ProxyObject=$class[1];
			self::$ProxyServer=$this;
			$class=$class[0];
			$useProxy=true;
		}
		// Ensure a webservice name
		if(is_null($class)){
			self::Debug('No webservice name yet');
			if(!$this->DetermineConfiguration())
				throw(new Exception('Invalid configuration'));
			if(!is_null($this->Name))
				$class=$this->Name;
		}else if(is_string($class)){
			self::Debug('Using '.$class.' as webservice name');
			$this->Name=$class;
		}
		self::Debug('Use class '.((!is_object($class))?$class:get_class($class)));
		// Load WSDL
		if(!$forceNoWsdl&&(!$useProxy||self::$UseProxyWsdl)){
			self::Debug('Load WSDL');
			$this->CreateWsdl(false,true);
			if(is_null($wsdlFile))
				$wsdlFile=$this->GetCacheFileName();
			if(!is_null($wsdlFile))
				if(!file_exists($wsdlFile)){
					self::Debug('WSDL file "'.$wsdlFile.'" does not exists');
					$wsdlFile=null;
				}
		}
		// Load the files, if the webservice handler class doesn't exist
		if(!is_object($class))
			if(!class_exists($class)&&!$this->IsOnlyGlobal()){
				self::Debug('Try to load the webservice handler class');
				$i=-1;
				$len=sizeof($this->Files);
				while(++$i<$len){
					self::Debug('Load '.$this->Files[$i]);
					require_once($this->Files[$i]);
				}
				if(!class_exists($class)){
					// Try class.webservice.php
					if(file_exists('class.webservice.php')){
						self::Debug('Try to load class.webservice.php');
						require_once('class.webservice.php');
						if(class_exists($class))
							$this->Files[]='class.webservice.php';
					}
					if(!class_exists($class))
						if(file_exists(dirname(__FILE__).'/class.webservice.php')){
							self::Debug('Try to load '.dirname(__FILE__).'/class.webservice.php');
							require_once(dirname(__FILE__).'/class.webservice.php');
							if(class_exists($class))
								$this->Files[]=dirname(__FILE__).'/class.webservice.php';
						}
					if(!class_exists($class))
						// A handler class or object is required when using non-global methods!
						throw(new Exception('Webservice handler class not present'));
				}
			}
		// Prepare the SOAP server
		$this->SoapServer=null;
		if(self::CallHook(
				'PrepareServerHook',
				Array(
					'server'		=>	$this,
					'soapserver'	=>	&$this->SoapServer,
					'wsdlfile'		=>	&$wsdlFile,
					'class'			=>	&$class,
					'useproxy'		=>	&$useProxy,
					'forcenowsdl'	=>	&$forceNoWsdl,
					'andexit'		=>	&$andExit,
					'user'			=>	&$user,
					'password'		=>	&$password
				)
			)
		){
			self::Debug('Prepare the SOAP server');
			// WSDL file
			$wsdlFile=($forceNoWsdl||($useProxy&&!self::$UseProxyWsdl))?null:$wsdlFile;
			if(!is_null($wsdlFile)){
				self::Debug('Using WSDL file '.$wsdlFile);
			}else{
				self::Debug('No WSDL file');
			}
			// Server options
			$temp=Array(
				'actor'			=>	$this->EndPoint,
				'uri'			=>	$this->NameSpace,
			);
			$temp=array_merge($this->SoapServerOptions,$temp);
			if(self::$Debugging)
				self::Debug('Server options: '.print_r($temp,true));
			// Create the server object
			self::Debug('Creating PHP SoapServer object');
			$this->SoapServer=new SoapServer(
				$wsdlFile,
				$temp
			);
			// Set the handler class or object
			if($useProxy||!is_object($class)){
				$temp=($useProxy)?'PhpWsdlProxy':$class;
				if(!is_null($temp)){
					self::Debug('Setting server class '.$temp);
					$this->SoapServer->setClass($temp);
				}else{
					self::Debug('No server class or object');
				}
			}else{
				self::Debug('Setting server object '.get_class($class));
				$this->SoapServer->setObject($class);
			}
			// Add global methods
			$i=-1;
			$len=sizeof($this->Methods);
			while(++$i<$len)
				if($this->Methods[$i]->IsGlobal){
					self::Debug('Adding global method '.$this->Methods[$i]->Name);
					$this->SoapServer->addFunction($this->Methods[$i]->Name);
				}
		}
		// Run the SOAP server
		if(self::CallHook(
				'RunServerHook',
				Array(
					'server'		=>	$this,
					'soapserver'	=>	&$this->SoapServer,
					'wsdlfile'		=>	&$wsdlFile,
					'class'			=>	&$class,
					'useproxy'		=>	&$useProxy,
					'forcenowsdl'	=>	&$forceNoWsdl,
					'andexit'		=>	&$andExit,
					'user'			=>	&$user,
					'password'		=>	&$password
				)
			)
		){
			self::Debug('Run the SOAP server');
			$this->SoapServer->handle();
			if($andExit){
				self::Debug('Exit script execution');
				exit;
			}
		}
		return true;
	}
	
	/**
	 * Get the WSDL download URI
	 * 
	 * @return string The Uri
	 */
	public function GetWsdlUri(){
		return ((is_null($this->WsdlUri))?$this->EndPoint:$this->WsdlUri).'?wsdl';
	}
	
	/**
	 * Get the PHP download URI
	 * 
	 * @return string The URI
	 */
	public function GetPhpUri(){
		return ((is_null($this->PhpUri))?$this->EndPoint:$this->PhpUri).'?phpsoapclient';
	}
	
	/**
	 * Get the HTML documentation URI
	 * 
	 * @return string The Uri
	 */
	public function GetDocUri(){
		return (is_null($this->PhpUri))?$this->EndPoint:$this->DocUri;
	}
	
	/**
	 * Find a method
	 * 
	 * @param string $name The method name
	 * @return PhpWsdlMethod The method object or NULL
	 */
	public function GetMethod($name){
		self::Debug('Find method '.$name);
		$i=-1;
		$len=sizeof($this->Methods);
		while(++$i<$len)
			if($this->Methods[$i]->Name==$name){
				self::Debug('Found method at index '.$i);
				return $this->Methods[$i];
			}
		return null;
	}
	
	/**
	 * Find a complex type
	 * 
	 * @param string $name The type name
	 * @return PhpWsdlComplex The type object or NULL
	 */
	public function GetType($name){
		self::Debug('Find type '.$name);
		$i=-1;
		$len=sizeof($this->Types);
		while(++$i<$len)
			if($this->Types[$i]->Name==$name){
				self::Debug('Found type at index '.$i);
				return $this->Types[$i];
			}
		return null;
	}
	
	/**
	 * Get the cache filename
	 * 
	 * @param string $endpoint The endpoint URI or NULL to use the PhpWsdl->EndPoint property (default: NULL)
	 * @return string The cache filename or NULL, if caching is disabled
	 */
	public function GetCacheFileName($endpoint=null){
		$data=Array(
			'server'		=>	$this,
			'endpoint'		=>	$endpoint,
			'filename'		=>	(is_null(self::$CacheFolder))?null:self::$CacheFolder.'/'.sha1((is_null($endpoint))?$this->EndPoint:$endpoint).'.wsdl'
		);
		self::CallHook(
			'CacheFileNameHook',
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
		self::Debug('Check cache file exists '.$file);
		return file_exists($file)&&file_exists($file.'.cache');
	}
	
	/**
	 * Determine if the existing cache files are still valid
	 * 
	 * @param string $file The WSDL cache filename or NULL to use the default (default: NULL)
	 * @return boolean Valid?
	 */
	public function IsCacheValid($file=null){
		self::Debug('Check cache valid');
		if(is_null($file))
			$file=$this->GetCacheFileName();
		if(!$this->CacheFileExists($file))
			return false;
		return self::$CacheTime<0||time()-file_get_contents($file.'.cache')<=self::$CacheTime;
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
		self::Debug('Get WSDL from cache');
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
			self::Debug('Unserialize methods, types and files');
			$data=unserialize(file_get_contents($file.'.obj'));
			$this->Methods=$data['methods'];
			$this->Types=$data['types'];
			$this->Files=$data['files'];
			$this->Name=$data['name'];
			$this->Docs=$data['docs'];
			$this->HTML=$data['html'];
			$this->PHP=$data['php'];
			$this->WsdlUri=$data['wsdluri'];
			$this->PhpUri=$data['phpuri'];
			$this->DocUri=$data['docuri'];
			self::CallHook(
				'ReadCacheHook',
				Array(
					'server'		=>	$this,
					'data'			=>	&$data
				)
			);
			if($data['version']!=self::$VERSION){
				self::Debug('Could not use cache from version '.$data['version']);
				$this->Methods=Array();
				$this->Types=Array();
				$this->Files=Array();
				$this->Name=null;
				$this->Docs=null;
				$this->HTML=null;
				$this->PHP=null;
				$this->WsdlUri=null;
				$this->PhpUri=null;
				$this->DocUri=null;
				$this->WSDL=null;
				$this->TidyCacheFolder(true);
				return null;
			}
		}
		$this->ConfigurationDetermined=true;
		$this->SourcesParsed=true;
		return $this->WSDL;
	}
	
	/**
	 * Write WSDL to cache
	 * 
	 * @param string $wsdl The UTF-8 encoded WSDL string (default: NULL)
	 * @param string $endpoint The SOAP endpoint or NULL to use the default (default: NULL)
	 * @param string $file The target filename or NULL to use the default (default: NULL)
	 * @param boolean $force Force refresh (default: FALSE)
	 * @return boolean Succeed?
	 */
	public function WriteWsdlToCache($wsdl=null,$endpoint=null,$file=null,$force=false){
		self::Debug('Write WSDL to the cache');
		if(is_null($endpoint))
			$endpoint=$this->EndPoint;
		if($endpoint==$this->EndPoint&&!is_null($wsdl))
			$this->WSDL=$wsdl;
		if(is_null($wsdl)){
			if(is_null($this->WSDL)){
				self::Debug('No WSDL');
				return false;// WSDL not defined
			}
			$wsdl=$this->WSDL;
		}
		if(is_null($file)){
			$file=$this->GetCacheFileName($endpoint);
			if(is_null($file)){
				self::Debug('No cache file');
				return false;// No cache file
			}
		}
		$temp=substr($file,0,1);
		if($temp!='/'&&$temp!='.'){
			if(is_null(self::$CacheFolder)){
				self::Debug('No cache folder');
				return false;// No cache folder
			}
			$file=self::$CacheFolder.'/'.$file;
		}
		if(!$force)
			if($this->IsCacheValid($file)){
				self::Debug('Cache is still valid');
				return true;// Existing cache is still valid
			}
		self::Debug('Write to '.$file);
		if(file_put_contents($file,$wsdl)===false){
			self::Debug('Could not write to cache');
			return false;// Error writing to cache
		}
		if(file_put_contents($file.'.cache',time())===false){
			self::Debug('Could not write cache time file');
			return false;// Error writing to cache
		}
		$data=Array(
			'version'		=>	self::$VERSION,
			'methods'		=>	$this->Methods,
			'types'			=>	$this->Types,
			'files'			=>	$this->Files,
			'name'			=>	$this->Name,
			'docs'			=>	$this->Docs,
			'html'			=>	$this->HTML,
			'php'			=>	$this->PHP,
			'wsdluri'		=>	$this->WsdlUri,
			'phpuri'		=>	$this->PhpUri,
			'docuri'		=>	$this->DocUri
		);
		self::CallHook(
			'WriteCacheHook',
			Array(
				'server'		=>	$this,
				'data'			=>	&$data
			)
		);
		if(file_put_contents($file.'.obj',serialize($data))===false){
			self::Debug('Could not write serialized cache');
			return false;// Error writing to cache
		}
		return true;
	}
	
	/**
	 * Determine if the cache folder is writeable
	 * 
	 * @param string $folder The folder or NULL to use the static property CacheFolder (default: NULL)
	 * @return boolean Writeable?
	 */
	public static function IsCacheFolderWriteAble($folder=null){
		if(!is_null(self::$CacheFolderWriteAble))
			return self::$CacheFolderWriteAble;
		if(is_null($folder))
			$folder=self::$CacheFolder;
		if(is_null($folder)){
			self::$CacheFolderWriteAble=false;
			return false;
		}
		if(!is_dir($folder)){
			self::Debug('Invalid cache folder (not a directory?)');
			self::$CacheFolderWriteAble=false;
			return;
		}
		$file=uniqid();
		while(file_exists($folder.'/'.$file))
			$file=uniqid();
		$file=$folder.'/'.$file;
		$temp=uniqid();
		if(file_put_contents($file,$temp)===false){
			self::$CacheFolderWriteAble=false;
			return false;
		}
		$res=file_get_contents($file)===$temp;
		unlink($file);
		self::$CacheFolderWriteAble=$res;
		return $res;
	}
	
	/**
	 * Determine if the cache is different from the current version of your webservice handler class.
	 * Status: Untested
	 * 
	 * @return boolean Differences detected?
	 */
	public function IsCacheDifferent(){
		self::Debug('Determine if the cache is different from this instance');
		// Load the cache
		$temp=new PhpWsdl(null,$this->EndPoint);
		$temp->GetWsdlFromCache();
		if(is_null($temp->WSDL))
			return true;// Not cached yet
		// Initialize this instance
		$this->DetermineConfiguration();
		$this->ParseSource();
		// Compare the cache with this instance
		$res=serialize(
				Array(
					$this->Methods,
					$this->Types
				)
			)!=serialize(
				Array(
					$temp->Methods,
					$temp->Types
				)
			);
		self::Debug('Cache is '.(($res)?'equal':'different'));
		return $res;
	}
	
	/**
	 * Delete cache files from the cache folder
	 * 
	 * @param boolean $mineOnly Only delete the cache files for this definition? (default: FALSE)
	 * @param boolean $cleanUp Only delete the cache files that are timed out? (default: FALSE)
	 * @param string $wsdlFile The WSDL filename (default: NULL)
	 * @return string[] The deleted filenames
	 */
	public function TidyCacheFolder($mineOnly=false,$cleanUp=false,$wsdlFile=null){
		if(is_null(self::$CacheFolder))
			return Array();
		$deleted=Array();
		if($cleanUp){
			self::Debug('Cleanup cache');
		}else if($mineOnly){
			self::Debug('Clean own cache');
		}else{
			self::Debug('Clean all cache');
		}
		if($mineOnly){
			self::Debug('Delete own cache');
			$file=(is_null($wsdlFile))?$this->GetCacheFileName():$wsdlFile;
			if($cleanUp)
				if($this->IsCacheValid($file))
					return $deleted;
			if(file_exists($file))
				if(unlink($file))
					$deleted[]=$file;
			if(file_exists($file.'.cache'))
				if(unlink($file.'.cache'))
					$deleted[]=$file.'.cache';
			if(file_exists($file.'.obj'))
				if(unlink($file.'.obj'))
					$deleted[]=$file.'.obj';
			self::Debug(sizeof($deleted).' files deleted');
		}else{
			self::Debug('Delete whole cache');
			$files=glob(self::$CacheFolder.(($cleanUp)?'/*.wsdl':'/*.wsd*'));
			if($files!==false){
				$toDelete=Array();
				$i=-1;
				$len=sizeof($files);
				while(++$i<$len){
					$file=$files[$i];
					if($cleanUp){
						if(!$this->IsCacheValid($file))
							continue;
						$toDelete[]=$file;
						$toDelete[]=$file.'.cache';
						$toDelete[]=$file.'.obj';
					}else{
						if(!preg_match('/\.wsdl(\.cache|\.obj)?$/',$file))
							continue;
						if(unlink($files[$i]))
							$deleted[]=$files[$i];
					}
				}
				if($cleanUp){
					$i=-1;
					$len=sizeof($toDelete);
					while(++$i<$len)
						if(file_exists($toDelete[$i]))
							if(unlink($toDelete[$i]))
								$deleted[]=$toDelete[$i];
				}
				self::Debug(sizeof($deleted).' files deleted');
			}else{
				self::Debug('"glob" failed');
			}
		}
		return $deleted;
	}
	
	/**
	 * Translate a type name for WSDL
	 * 
	 * @param string $type The type name
	 * @return string The translates type name
	 */
	public static function TranslateType($type){
		if(!self::CallHook(
				'TranslateTypeHook',
				Array(
					'type'			=>	&$type
				)
			)
		)
			return $type;
		return ((in_array($type,self::$BasicTypes))?self::$Config['xsd']:self::$Config['tns']).':'.$type;
	}
	
	/**
	 * Call a hook function
	 * 
	 * @param string $name The hook name
	 * @param mixed $data The parameter (default: NULL)
	 * @return boolean Response
	 */
	public static function CallHook($name,$data=null){
		self::Debug('Call hook '.$name);
		if(!self::HasHookHandler($name))
			return true;
		$keys=array_keys(self::$Config['extensions'][$name]);
		$i=-1;
		$len=sizeof($keys);
		while(++$i<$len){
			self::Debug('Call '.self::$Config['extensions'][$name][$keys[$i]]);
			if(!call_user_func(self::$Config['extensions'][$name][$keys[$i]],$data)){
				self::Debug('Handler stopped hook execution');
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Register a hook
	 * 
	 * @param string $hook The hook name
	 * @param string $name The call name
	 * @param mixed $data The hook call data
	 */
	public static function RegisterHook($hook,$name,$data){
		if(!self::HasHookHandler($hook))
			self::$Config['extensions'][$hook]=Array();
		if(self::$Debugging){
			$handler=$data;
			if(is_array($handler)){
				$class=$handler[0];
				$method=$handler[1];
				if(is_object($class))
					$class=get_class($class);
				$handler=$class.'.'.$method;
			}
			self::Debug('Register hook '.$hook.' handler '.$name.': '.$handler);
		}
		self::$Config['extensions'][$hook][$name]=$data;
	}
	
	/**
	 * Unregister a hook
	 * 
	 * @param string $hook The hook name
	 * @param string $name The call name or NULL to unregister the whole hook
	 */
	public static function UnregisterHook($hook,$name=null){
		if(!self::HasHookHandler($hook))
			return;
		if(!is_null($name)){
			if(!isset(self::$Config['extensions'][$hook][$name]))
				return;
		}else{
			unset(self::$Config['extensions'][$hook]);
			return;
		}
		unset(self::$Config['extensions'][$hook][$name]);
		if(self::$Debugging)
			self::Debug('Unregister hook '.$hook.' handler '.$name);
		if(sizeof(self::$Config['extensions'][$hook])<1)
			unset(self::$Config['extensions'][$hook]);
	}
	
	/**
	 * Determine if a hook has a registered handler
	 * 
	 * @param string $hook The hook name
	 * @return boolean Has handler?
	 */
	public static function HasHookHandler($hook){
		return isset(self::$Config['extensions'][$hook]);
	}
	
	/**
	 * Add a debugging message
	 * 
	 * @param string $str The message to add to the debug protocol
	 */
	public static function Debug($str){
		if(!self::$Debugging)
			return;
		$temp=date('Y-m-d H:i:s')."\t".$str;
		if(self::$DebugBackTrace){
			$trace=debug_backtrace();
			$temp.=" ('".$trace[1]['function']."' in '".basename($trace[1]['file'])."' at line #".$trace[1]['line'].")";
		}
		self::$DebugInfo[]=$temp;
		if(!is_null(self::$DebugFile))
			if(file_put_contents(self::$DebugFile,$temp."\n",FILE_APPEND)===false){
				self::Debug('Could not write to debug file '.self::$DebugFile);
				self::$DebugFile=null;
			}
	}

	/**
	 * Initialize PhpWsdl
	 */
	public static function Init(){
		self::Debug('Init');
		// Configuration
		self::$HTML2PDFSettings=Array(
			'attachments'	=>	'1',
			'outline'		=>	'1'
		);
		self::$NameSpaces=Array(
			'soap'			=>	'http://schemas.xmlsoap.org/wsdl/soap/',
			's'				=>	'http://www.w3.org/2001/XMLSchema',
			'wsdl'			=>	'http://schemas.xmlsoap.org/wsdl/',
			'soapenc'		=>	'http://schemas.xmlsoap.org/soap/encoding/'
		);
		self::EnableCache();
		self::$Config['extensions']=Array();// A configuration space for extensions
		self::$Config['tns']='tns';			// The xmlns name for the target namespace
		self::$Config['xsd']='s';			// The xmlns name for the XSD namespace
		// Parser hooks
		self::RegisterHook('InterpretKeywordserviceHook','internal','PhpWsdl::InterpretService');
		// WSDL hooks
		self::RegisterHook('CreateWsdlHeaderHook','internal','PhpWsdl::CreateWsdlHeader');
		self::RegisterHook('CreateWsdlTypeSchemaHook','internal','PhpWsdl::CreateWsdlTypeSchema');
		self::RegisterHook('CreateWsdlMessagesHook','internal','PhpWsdl::CreateWsdlMessages');
		self::RegisterHook('CreateWsdlPortsHook','internal','PhpWsdl::CreateWsdlPorts');
		self::RegisterHook('CreateWsdlBindingsHook','internal','PhpWsdl::CreateWsdlBindings');
		self::RegisterHook('CreateWsdlServiceHook','internal','PhpWsdl::CreateWsdlService');
		self::RegisterHook('CreateWsdlFooterHook','internal','PhpWsdl::CreateWsdlFooter');
		self::RegisterHook('CreateWsdlOptimizeHook','internal','PhpWsdl::CreateWsdlOptimize');
		// HTML hooks
		self::RegisterHook('CreateHtmlGeneralHook','internal','PhpWsdl::CreateHtmlGeneral');
		self::RegisterHook('CreateHtmlIndexHook','internal','PhpWsdl::CreateHtmlIndex');
		self::RegisterHook('CreateHtmlMethodsHook','internal','PhpWsdl::CreateHtmlMethods');
		self::RegisterHook('CreateHtmlComplexTypesHook','internal','PhpWsdl::CreateHtmlComplexTypes');
		// Extensions
		self::Debug('Load extensions');
		$files=glob(dirname(__FILE__).'/'.'class.phpwsdl.*.php');
		if($files!==false){
			$i=-1;
			$len=sizeof($files);
			while(++$i<$len){
				self::Debug('Load '.$files[$i]);
				require_once($files[$i]);
			}
		}else{
			self::Debug('"glob" failed');
		}
	}

	/**
	 * Do things after the environment is configured
	 */
	public static function PostInit(){
		self::CallHook('PostInitHook');
		// Autorun
		global $PhpWsdlAutoRun;
		if(self::$AutoRun||$PhpWsdlAutoRun)
			self::RunQuickMode();
	}
}
