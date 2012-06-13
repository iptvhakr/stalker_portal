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

// This is my solution to swap hash arrays between client/server. The receiver 
// has to rebuild the hash object in the way his programming language supports 
// hash arrays. It's just an example solution. To use the types defined here, 
// you have to include this file in your source using "require". This file has 
// also to be in the list of files that are parsed by PhpWsdl.
//
// A sample unserialized PHP hash array (use the "Serialize" method on this):
//
//	Array(
//		'a'	=>	'Value of a',
//		'b'	=>	'Value of b'
//	)
//
// The resulting serialized array (return this):
//
//	Array(
//		PhpWsdlHash(
//			'key'	=>	'a',
//			'value'	=>	'Value of a'
//		),
//		PhpWsdlHash(
//			'key'	=>	'b',
//			'value'	=>	'Value of b'
//		)
//	)
//
// The received serialized array (use the "Deserialize" method on this):
//
//	Array(
//		object(
//			'key'	=>	'a',
//			'value'	=>	'Value of a'
//		),
//		object(
//			'key'	=>	'b',
//			'value'	=>	'Value of b'
//		)
//	)
//
// The value type must be unique within an hash array. You can return prepared 
// types like StringHashArray, or you define your own ones. To serialize an 
// hash array to the target type, use the PhpWsdlHashArrayBuilder class:
//
// return PhpWsdlHashArrayBuilder::Serialize($yourHashVariable);
//
// To deserialize an received object:
//
// $yourHashVariable=PhpWsdlHashArrayBuilder::Deserialize($receivedHashVariable);
//
// Tip: I prefer converting hashes into an INI formatted string. The string 
// type is primitive, but it can contain complex information. In nearly every 
// programming language you can work with the INI format with predefined 
// classes - or it's very easy to write your own.

/**
 * Key/Value pair types for string value type
 * 
 * @pw_element string $key Key
 * @pw_element string $value Value
 * @pw_complex StringHash String hash array type
 */
/**
 * @pw_complex StringHashArray Array of string hash array types
 */
/**
 * Key/Value pair types for int value type
 * 
 * @pw_element string $key Key
 * @pw_element int $value Value
 * @pw_complex IntHash Int hash array type
 */
/**
 * @pw_complex IntHashArray Array of int hash array types
 */
/**
 * Key/Value pair types for boolean value type
 * 
 * @pw_element string $key Key
 * @pw_element boolean $value Value
 * @pw_complex BooleanHash Boolean hash array type
 */
/**
 * @pw_complex BooleanHashArray Array of boolean hash array types
 */

/**
 * This class contains static methods to (de)serialize hash arrays
 * 
 * @author Andreas Zimmermann, wan24.de
 */
class PhpWsdlHashArrayBuilder{
	/**
	 * Serialize an hash array
	 * 
	 * @param array $hash The PHP hash array
	 * @return PhpWsdlHash[] The serialized array
	 */
	public static function Serialize($hash){
		if(is_null($hash))
			return null;
		$res=Array();
		$keys=array_keys($hash);
		$i=-1;
		$len=sizeof($keys);
		while(++$i<$len)
			$res[]=new PhpWsdlHash($keys[$i],$hash[$keys[$i]]);
		return $res;
	}
	
	/**
	 * Deserialize an hash array
	 * 
	 * @param array $arr The serialized array
	 * @return array The deserialized PHP hash array
	 */
	public static function Deserialize($arr){
		if(is_null($arr))
			return null;
		$res=Array();
		$i=-1;
		$len=sizeof($arr);
		while(++$i<$len)
			$res[$arr[$i]->Key]=$arr[$i]->Value;
		return $res;
	}
}

/**
 * This class simply holds a key/value pair
 * 
 * @author Andreas Zimmermann, wan24.de
 */
class PhpWsdlHash{
	/**
	 * The key
	 * 
	 * @var string
	 */
	public $Key;
	/**
	 * The value
	 * 
	 * @var mixed
	 */
	public $Value;

	/**
	 * Constructor
	 * 
	 * @ignore
	 * @param string $key The key
	 * @param mixed $value The value
	 */
	public function PhpWsdlHash($key,$value){
		$this->Key=$key;
		$this->Value=$value;
	}
}
