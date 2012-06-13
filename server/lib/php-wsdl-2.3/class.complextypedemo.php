<?php

if(basename($_SERVER['SCRIPT_FILENAME'])==basename(__FILE__))
	exit;

// The @pw_element and @pw_complex are non-standard keywords for documentaion 
// I had to define to support those complex types for WSDL generation. The  
// "pw" stands for "PhpWsdl". But who cares?

/**
 * This is how to define a complex type f.e. - the class ComplexTypeDemo doesn't need to exists, 
 * but it would make it easier for you to return that complex type from a method
 *
 * @pw_element string $StringA A string with a value
 * @pw_element string $StringB A string with a NULL value
 * @pw_set nillable=false The next element can't be NULL
 * @pw_element int $Integer An integer
 * @pw_set nillable=false The next element can't be NULL
 * @pw_element boolean $Boolean A boolean
 * @pw_complex ComplexTypeDemo The complex type name definition
 */
class ComplexTypeDemo{
	public $StringA='String A';
	public $StringB=null;
	public $Integer=123;
	public $Boolean=true;
}

// You can also create array types as complex type. Here for the string type and the ComplexTypeDemo complex type.
// As you can see you simply need to add "Array" to the name of the type. Not one line of code.

/**
 * @pw_complex stringArray A string array type
 */
/**
 * @pw_complex ComplexTypeDemoArray An array of ComplexTypeDemo
 */

// But you may also create an array without any name restrictions. To use the arrayOfInt[] finally, use the type 
// name without the "[]" (that's only required for parsing the correct target type)

/**
 * @pw_complex arrayOfInt[] int An int array type
 */
