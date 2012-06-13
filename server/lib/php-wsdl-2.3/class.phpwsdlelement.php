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

PhpWsdl::RegisterHook('InterpretKeywordpw_elementHook','internal','PhpWsdlElement::InterpretElement');

/**
 * An element of a complex type
 * 
 * @author Andreas Zimmermann, wan24.de
 */
class PhpWsdlElement extends PhpWsdlParam{
	/**
	 * Can the value be NULL?
	 * 
	 * @var boolean
	 */
	public $NillAble=true;
	/**
	 * Minimum number of elements
	 * 
	 * @var int
	 */
	public $MinOccurs=1;
	/**
	 * Maximum number of elements
	 * 
	 * @var int|string
	 */
	public $MaxOccurs=1;
	
	/**
	 * Constructor
	 * 
	 * @param string $name The name
	 * @param string $type The type name
	 * @param array $settings Optional the settings hash array (default: NULL)
	 */
	public function PhpWsdlElement($name,$type,$settings=null){
		PhpWsdl::Debug('New complex type element '.$name);
		parent::PhpWsdlParam($name,$type,$settings);
		$this->NillAble=!in_array($type,PhpWsdl::$NonNillable);
		if(!is_null($settings)){
			if(isset($settings['nillable']))
				$this->NillAble=$settings['nillable']=='1'||$settings['nillable']=='true';
			if(isset($settings['minoccurs']))
				$this->MinOccurs=$settings['minoccurs'];
			if(isset($settings['maxoccurs']))
				$this->MaxOccurs=$settings['maxoccurs'];
		}
	}
	
	/**
	 * Create the WSDL
	 * 
	 * @param PhpWsdl $pw The PhpWsdl object
	 * @return string The WSDL
	 */
	public function CreateElement($pw){
		PhpWsdl::Debug('Create WSDL definition for element '.$this->Name);
		$res='<s:element minOccurs="'.$this->MinOccurs.'" maxOccurs="'.$this->MaxOccurs.'" nillable="'.(($this->NillAble)?'true':'false').'" name="'.$this->Name.'" type="';
		$res.=PhpWsdl::TranslateType($this->Type).'"';
		if($pw->IncludeDocs&&!$pw->Optimize&&!is_null($this->Docs)){
			$res.='>'."\n";
			$res.='<s:annotation>'."\n";
			$res.='<s:documentation><![CDATA['.$this->Docs.']]></s:documentation>'."\n";
			$res.='</s:annotation>'."\n";
			$res.='</s:element>';
		}else{
			$res.=' />';
		}
		return $res;
	}
	
	/**
	 * Create the HTML documentation for a complex type element
	 * 
	 * @param array $data
	 */
	public function CreateElementHtml($data){
		PhpWsdl::Debug('CreateElementHtml for '.$data['element']->Name);
		$res=&$data['res'];
		$e=&$data['element'];
		if(in_array($e->Type,PhpWsdl::$BasicTypes)){
			$res[]='<li><span class="blue">'.$e->Type.'</span> <span class="bold">'.$e->Name.'</span>';
		}else{
			$res[]='<li><a href="#'.$e->Type.'"><span class="lightBlue">'.$e->Type.'</span></a> <span class="bold">'.$e->Name.'</span>';
		}
		$o=sizeof($res)-1;
		$temp=Array(
			'nillable = <span class="blue">'.(($e->NillAble)?'true':'false').'</span>',
			'minoccurs = <span class="blue">'.$e->MinOccurs.'</span>',
			'maxoccurs = <span class="blue">'.$e->MaxOccurs.'</span>',
		);
		$res[$o].=' ('.implode(', ',$temp).')';
		if(!is_null($e->Docs))
			$res[$o].='<br><span class="normal">'.nl2br(htmlentities($e->Docs)).'</span>';
		$res[$o].='</li>';
		PhpWsdl::CallHook(
			'CreateElementHtmlHook',
			$data
		);
	}
	
	/**
	 * Interpret a element keyword
	 * 
	 * @param array $data The parser data
	 * @return boolean Response
	 */
	public static function InterpretElement($data){
		$info=explode(' ',$data['keyword'][1],3);
		if(sizeof($info)<2)
			return true;
		$name=substr($info[1],1);
		if(substr($name,strlen($name)-1,1)==';')
			$name=substr($name,0,strlen($name)-1);
		PhpWsdl::Debug('Interpret element '.$name);
		if($data['server']->ParseDocs)
			if(sizeof($info)>2)
				$data['settings']['docs']=trim($info[2]);
		$data['elements'][]=new PhpWsdlElement($name,$info[0],$data['settings']);
		$data['settings']=Array();
		return false;
	}
}
