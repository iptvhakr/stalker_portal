Contents
~~~~~~~~
- Why another WSDL generator?
- How to use PhpWsdl
- The quick mode
- How to get rid of the NULL-problem
- Demonstrations
- To cache or not to cache
- Debugging
- Undocumented
- SOAP with JavaScript
- SOAP with Microsoft Visual Studio
- License
- Support
- Project homepage

Why another WSDL generator?
~~~~~~~~~~~~~~~~~~~~~~~~~~~
I started to develop my own WSDL generator for PHP because the ones I saw 
had too many disadvantages for my purposes. The main problem - and the main 
reason to make my own WSDL generator - was receiving NULL in parameters 
that leads the PHP SoapServer to throw around with "Missing parameter" 
exceptions. F.e. a C# client won't send the parameter, if its value is 
NULL. But the PHP SoapServer needs the parameter tag with 'xsi:nil="true"' to 
call a method with the correct number of parameters.

At the end the NULL-problem still exists a little bit, but I've created a 
thin, fast and ComplexType-supporting WSDL generator for my PHP webservices - 
and maybe yours?

If this isn't enough for your application, I recommend you to have a look at 
Zend.

How to use PhpWsdl
~~~~~~~~~~~~~~~~~~
To use PhpWsdl you need these classes:
- class.phpwsdl.php
	The base class for creating WSDL, running a SOAP server, or creating a PHP 
	SOAP client proxy class

- class.phpwsdlclient.php
	The base class for doing SOAP requests or creating a PHP SOAP client proxy 
	class from a SOAP webservice

- class.phpwsdlcomplex.php
	Represents a complex type

- class.phpwsdlelement.php
	Represents an element of a complex type

- class.phpwsdlformatter.php
	A PHP class to format a XML string human readable

- class.phpwsdlmethod.php
	Represents a SOAP method

- class.phpwsdlobject.php
	The parent class for PhpWsdl objects

- class.phpwsdlparam.php
	Represents a parameter or a return value of a SOAP method

- class.phpwsdlparser.php
	The PHP source code parser

- class.phpwsdlproxy.php
	A proxy webservice class to get rid of the NULL problem

You only need to include the 'class.phpwsdl.php' in your project to use 
PhpWsdl. This class will load it's depencies from the same location.

PhpWsdl enables you to mix class and global methods in one webservice, if you 
use the PhpWsdlProxy class (see demo3.php).

If you want to use my solution for transferring hash arrays with SOAP, you 
can also include 'class.phpwsdlhash.php' (not loaded by default). This file 
contains some type definitions and methods for working with hash arrays. Have 
a look inside for some documentation.

Call your webservice URI without any parameter to display a HTML description 
of the SOAP interface. Attach "?wsdl" to the URI to get the WSDL definition. 
Add "&readable" too, to get humen readable WSDL. Attach "?phpsoapclient" to 
download a PHP SOAP client proxy for your webservice.

Some classes can consume settings. Open the source code and have a look at the 
constructor to see what you can do with settings. Settings are defined with 
the @pw_set keyword in comments. An example usage for settings can be found in 
class.complextypedemo.php.

You don't have to specify the SOAP endpoint URI - PhpWsdl is able to determine 
this setting. But since I don't know your environment and your purposes, it's 
still possible to change the SOAP endpoint location per instance.

Open the demo*.php files in your PHP source editor for some code examples. At 
the Google Code project you'll find some Wikis, too.

Note: PhpWsdl can include documentation tags in WSDL. But f.e. Visual Studio 
won't use these documentations for IntelliSense. This is not a bug in PhpWsdl.

The quick mode
~~~~~~~~~~~~~~
PhpWsdl can determine most of the required configuration to run a SOAP server. 
The fastest way is:

PhpWsdl::RunQuickMode();

This example requires the webservice handler class to be in the same file. If 
the class is located in another file, you can specify the file like this:

PhpWsdl::RunQuickMode('class.soapdemo.php');

Or, if there are multiple files to parse for WSDL definitions:

PhpWsdl::RunQuickMode(Array(
	'class.soapdemo.php',
	'class.complextypedemo.php'
));

When providing more than one file, be sure the first class in the first file 
is the webservice handler class!

You even don't need to load your classes with "require_once" - let PhpWsdl do 
it for you.

But what will the quick mode do for you, what you don't know?

1. Determine a namespace based on the URI the webservice has been called
2. Determine the endpoint based on the URI the webservice has been called
3. Determine the webservice name (and handler class name) from the first 
   exported class of the running script or class.webservice.php or the list 
   of files
4. Determine a writeable cache folder
5. Load extensions, if the PHP "glob" function works
6. Load your webservice class, if it's not loaded already
7. Create the WSDL
8. Return HTML, PHP, the WSDL or configure and run a SOAP server
9. Exit the script execution

If your webservice can run without giving a file name or list to the 
constructor, you may want to try the autorun feature. There are two ways to 
enable the autorun:

1. Edit the source of class.phpwsdl.php and set the property PhpWsdl::$AutoRun 
   to TRUE to enable the autorun for all your webservices that use PhpWsdl

2. Set the global variable $PhpWsdlAutoRun to TRUE to enable the autorun for 
   the current webservice

The autorun is demonstrated in demo4/5.php.

How to get rid of the NULL-problem
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
When a SOAP client like .NET is making a SOAP request and a parameter is NULL, 
the parameters tag won't be included in the SOAP request. The PHP SoapServer 
will then throw around with "Missing parameter" exceptions or call your 
method with a wrong parameter count.

The only way I found to get rid of this problem is using a proxy class that is 
able to find missing parameters to call the target method correctly. For this 
it's important that the PHP SoapServer don't know the WSDL of your webservice.

But this solutions opens another problem: If the PHP SoapServer don't know 
your WSDL, return values won't be encoded properly. You have to encode them by 
yourself using PHPs SoapVar class. I didn't implement an encoding routine in 
the proxy yet - maybe coming soon.

To see an example how to use the proxy, please look into demo3.php.

Another solution is to work with complex types that serve the parameters. Then 
every method that supports NULL in parameters needs to use a complex type as 
the only one parameter that includes the parameters as elements.

Demonstrations
~~~~~~~~~~~~~~
This package contains some demonstrations:
- demo.php
	A basic usage demonstration
- demo2.php
	How to use PhpWsdl without PHP comment definitions of the WSDL elements
- demo3.php
	How to use the proxy to get rid of the NULL parameter problem
	How to mix class and global methods in one webservice
- demo4.php
	A quick and dirty single file usage example
- demo5.php
	A quick demonstration how to serve global methods
- demo6.php
	A quick demonstration how to use the SOAP client

Some demonstrations are using the following classes:
- class.complextypedemo.php
	How to define a complex type and array types
- class.soapdemo.php
	A simple SOAP webservice class with some test methods

If you want to test PhpWsdl online without installing it on your own server, 
you can try these URIs:

http://wan24.de/test/phpwsdl2/demo.php -> HTML documentation output & endpoint
http://wan24.de/test/phpwsdl2/demo.php?WSDL -> WSDL output
http://wan24.de/test/phpwsdl2/demo.php?PHPSOAPCLIENT -> PHP output

demo?.php are available under the same location, too. If you try the PDF 
download, you'll notice that you get other results as if you try it from your 
server. This is because I use a valid license key for the HTML2PDF API - 
PhpWsdl will then create a TOC and attach the WSDL files and a PHP SOAP client 
into the PDF, so it's very easy for you to provide your webservice fully 
documented any ready to use for other developers.

Note: Sometimes my test URIs won't work because I'm testing a newer version...

To cache or not to cache
~~~~~~~~~~~~~~~~~~~~~~~~
I recommend to use the WSDL caching feature of PhpWsdl. For this you need 
a writeable folder where PhpWsdl can write WSDL files to. Using the cache is 
much faster in an productive environment. During development you may want to 
disable caching (see the demo scripts for those two lines of code). To 
completely disable the caching feature, you need to set the static CacheFolder 
property of PhpWsdl to NULL. Without a cache folder (or when using the proxy 
class) returning complex types needs attention: Use PHPs SoapVar class to 
encode them properly.

Debugging
~~~~~~~~~
All debug messages are collected with the PhpWsdl::Debug method. All messages 
are being collected in the PhpWsdl::$DebugInfo array. But PhpWsdl can also 
write to a text log file, too.

To enable debugging, set the property PhpWsdl::$Debugging to TRUE:

PhpWsdl::$Debugging=true;

To enable writing to a log file, set the property PhpWsdl::$DebugFile to the 
location of the file:

PhpWsdl::$DebugFile='./cache/debug.log';

You can enable adding backtrace informations in the debug message by setting 
the property PhpWsdl::$DebugBackTrace to TRUE:

PhpWsdl::$DebugBackTrace=true;

Undocumented
~~~~~~~~~~~~
Things that are not yet demonstrated are:
- Adding/Removing predefined basic types in PhpWsdl::$BasicTypes
- Usage of the non-nillable types
- Hooking in PhpWsdl (see inline documentation)
- How to handle hash arrays with PhpWsdlHashArrayBuilder (see inline 
  documentation)
- How to develop extensions for an extended complex type support f.e.

SOAP with JavaScript
~~~~~~~~~~~~~~~~~~~~
For using SOAP with JavaScript I use the SOAPClient class from Matteo Casati. 
I also tested this class with the PhpWsdl demonstrations, and I was able to 
receive complex types as JavaScript object. But sending a complex type to the 
webservice failed with some constructor-problem within the SOAPClient class. 
Currently I haven't worked on a solution for this problem because I don't use 
complex types... Maybe using "anyType" can fix this problem.

If you want to use foreign SOAP webservices with your AJAX application, you 
need to use a SOAP webservice proxy. This addon is available as seperate 
download or directly from the SVN respository. The AJAX proxy is using JSON - 
have look at http://www.json.org/js.html for informations how to en- and 
decode objects to/from JSON in your JavaScript application.

SOAP with Microsoft Visual Studio
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
All tests using Microsoft Visual Studio 2010 ran without any problems. You can 
add your webservice as service or web reference to your project. Visual Studio 
will then generate proxy classes and other things for you. Earlier or later 
versions of Visual Studio or .NET should be compatible, too.

License
~~~~~~~
PhpWsdl is GPL (v3 or later) licensed per default. I offer a LGPL like license 
bundled with an individual SLA for your company, if required - contact me with 
email to schick_was_an at hotmail dot com for details.

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

See license.txt for the full GPLv3 license text.

Support
~~~~~~~
If you need help implementing PhpWsdl, I may help you with email. Contact me 
at schick_was_an at hotmail dot com. If you found an error, please report it 
at the project homepage.

Project homepage
~~~~~~~~~~~~~~~~
PhpWsdl is hosted by Google Code. The project homepage is located at

http://code.google.com/p/php-wsdl-creator/

This location should be the only source for downloads, source, Wikis and 
reported issues.

You can find my German speaking homepage here:

http://wan24.de

There you'll find some other projects and some free downloads that are maybe 
interesting for you, too.
