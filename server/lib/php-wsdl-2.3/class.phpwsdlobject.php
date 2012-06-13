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

/**
 * PhpWsdl element object base
 * 
 * @author Andreas Zimmermann, wan24.de
 */
class PhpWsdlObject{
	/**
	 * The GUID
	 * 
	 * @var string
	 */
	public $GUID;
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
	 * Other settings
	 * 
	 * @var array
	 */
	public $Settings=null;
	
	/**
	 * Constructor
	 * 
	 * @param string $name The name
	 * @param array $settings Settings array (default: NULL)
	 */
	public function PhpWsdlObject($name,$settings=null){
		$this->GUID=uniqid();
		PhpWsdl::Debug('New PhpWsdlObject "'.$name.'" with GUID "'.$this->GUID.'"');
		$this->Name=$name;
		if(!is_null($settings)){
			if(isset($settings['docs']))
				$this->Docs=$settings['docs'];
			if(isset($settings['settings']))
				$this->Settings=$settings['settings'];
		}
	}
}
