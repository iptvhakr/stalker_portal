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

PhpWsdl::RegisterHook('InterpretKeywordparamHook','internal','PhpWsdlParam::InterpretParam');
PhpWsdl::RegisterHook('InterpretKeywordreturnHook','internal','PhpWsdlParam::InterpretReturn');

/**
 * A parameter or return value definition for a method
 * 
 * @author Andreas Zimmermann, wan24.de
 */
class PhpWsdlParam extends PhpWsdlObject{
	/**
	 * The parameter type name
	 * 
	 * @var string
	 */
	public $Type;
	/**
	 * Documentation
	 * 
	 * @var string
	 */
	public $Docs=null;
	/**
	 * The default name for the return value object
	 * Use %method% as placeholder for the method name
	 * 
	 * @var string
	 */
	public static $DefaultReturnName='return';
	
	/**
	 * Constructor
	 * 
	 * @param string $name The name
	 * @param string $type Optional the type name (default: string)
	 * @param array $settings Optional the settings hash array (default: NULL)
	 */
	public function PhpWsdlParam($name,$type='string',$settings=null){
		PhpWsdl::Debug('New parameter '.$name);
		parent::PhpWsdlObject($name,$settings);
		$this->Type=$type;
	}
	
	/**
	 * Create the part WSDL
	 * 
	 * @param PhpWsdl $pw The PhpWsdl object
	 * @return string The WSDL
	 */
	public function CreatePart($pw){
		PhpWsdl::Debug('Create WSDL definition for parameter part '.$this->Name);
		$res='<wsdl:part name="'.$this->Name.'" type="';
		$res.=PhpWsdl::TranslateType($this->Type).'"';
		if($pw->IncludeDocs&&!$pw->Optimize&&!is_null($this->Docs)){
			$res.='>'."\n";
			$res.='<s:documentation><![CDATA['.$this->Docs.']]></s:documentation>'."\n";
			$res.='</wsdl:part>';
		}else{
			$res.=' />';
		}
		return $res;
	}
	
	/**
	 * Create parameter HTML documentation
	 * 
	 * @param array $data Some data
	 */
	public function CreateParamHtml($data){
		PhpWsdl::Debug('CreateParamHtml for '.$data['param']->Name);
		$res=&$data['res'];
		$p=&$data['param'];
		if(is_null($p->Docs))
			return;
		if(in_array($p->Type,PhpWsdl::$BasicTypes)){
			$res[]='<li class="pre"><span class="blue">'.$p->Type.'</span> <span class="bold">'.$p->Name.'</span>';
		}else{
			$res[]='<li class="pre"><a href="#'.$p->Type.'"><span class="lightBlue">'.$p->Type.'</span></a> <span class="bold">'.$p->Name.'</span>';
		}
		$res[sizeof($res)-1].='<br><span class="normal">'.nl2br(htmlentities($p->Docs)).'</span></li>';
		PhpWsdl::CallHook(
			'CreateParameterHtmlHook',
			$data
		);
	}
	
	/**
	 * Create return value HTML documentation
	 * 
	 * @param array $data Some data
	 */
	public function CreateReturnHtml($data){
		PhpWsdl::Debug('CreateReturnHtml for '.$data['method']->Return->Name);
		$res=&$data['res'];
		$m=&$data['method'];
		$res[]='<p>Return value <span class="pre">';
		$o=sizeof($res)-1;
		$type=$m->Return->Type;
		if(in_array($type,PhpWsdl::$BasicTypes)){
			$res[$o].='<span class="blue">'.$type.'</span>';
		}else{
			$res[$o].='<a href="#'.$type.'"><span class="lightBlue">'.$type.'</span></a>';
		}
		$res[$o].='</span>'.((!is_null($m->Return->Docs))?': '.nl2br(htmlentities($m->Return->Docs)):'').'</p>';
		PhpWsdl::CallHook(
			'CreateReturnHtmlHook',
			$data
		);
	}
	
	/**
	 * Interpret a parameter keyword
	 * 
	 * @param array $data The parser data
	 * @return boolean Response
	 */
	public static function InterpretParam($data){
		if($data['method']=='')
			return true;
		$info=explode(' ',$data['keyword'][1],3);
		if(sizeof($info)<2)
			return true;
		$name=substr($info[1],1);
		if(substr($name,strlen($name)-1,1)==';')
			$name=substr($name,0,strlen($name)-1);
		PhpWsdl::Debug('Interpret parameter '.$name);
		if($data['server']->ParseDocs)
			if(sizeof($info)>2)
				$data['settings']['docs']=trim($info[2]);
		$data['param'][]=new PhpWsdlParam($name,$info[0],$data['settings']);
		$data['settings']=Array();
		return false;
	}
	
	/**
	 * Interpret a return value
	 * 
	 * @param array $data The parser data
	 * @return boolean Response
	 */
	public static function InterpretReturn($data){
		if($data['method']=='')
			return true;
		$info=explode(' ',$data['keyword'][1],2);
		if(sizeof($info)<1)
			return true;
		PhpWsdl::Debug('Interpret return');
		if($data['server']->ParseDocs)
			if(sizeof($info)>1)
				$data['settings']['docs']=trim($info[1]);
		$data['return']=new PhpWsdlParam(str_replace('%method%',$data['method'],self::$DefaultReturnName),$info[0],$data['settings']);
		$data['settings']=Array();
		return false;
	}
}
