<?php
/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Copyright (C) 2006 Marco Aur�lio Graciotto Silva <magsilva@gmail.com>
*/


require_once("../classes/dotproject.class.php");
include_once('DAOMapper.class.php');


require_once('AssertionHandler.class.php');
require_once('ErrorHandler.class.php');
require_once('ExceptionHandler.class.php');

class DPWebService
{
	private static $instance;
	
	private $exception_handler;
	
	private $assertion_handler;
	
	private $error_handler;
	
	private function __construct()
	{
		// Whether to warn when arguments are passed by reference at function call time.
		// This method is deprecated and is likely to be unsupported in future versions
		// of PHP/Zend. The encouraged method of specifying which arguments should be
		// passed by reference is in the function declaration.
		// ini_set('allow_call_time_pass_reference', 0);

		// Enable compatibility mode with Zend Engine 1 (PHP 4). It affects the cloning,
		// casting (objects with no properties cast to FALSE or 0), and comparing of
		// objects. In this mode, objects are passed by value instead of reference by default. 
		// ini_set('zend.ze1_compatibility_mode', 1);

		// When turned on, PHP will examine the data read by fgets() and file() to see if
		// it is using Unix, MS-Dos or Macintosh line-ending conventions. This enables PHP
		// to interoperate with Macintosh systems, but defaults to Off, as there is a very
		// small performance penalty when detecting the EOL conventions for the first
		// line, and also because people using carriage-returns as item separators under
		// Unix systems would experience non-backwards-compatible behaviour. 
		ini_set('auto_detect_line_endings', 1);
		
    	// The new error format contains a reference to a page describing the error or 
    	// function causing the error. Additional you have to set docref_ext to match
    	// the file extensions of your copy.
    	// TODO: Set the docref_root.
		ini_set('docref_root', "http://br2.php.net/manual/en/" );
		ini_set('docref_ext', '.php');

		ini_set("soap.wsdl_cache_enabled", "0");
		ini_set("session.auto_start", "0");
		ini_set("default_socket_timeout", "30");
		
		$this->exception_handler = new ExceptionHandler('dp-ws.log');
		$this->error_handler = new ErrorHandler();
		$this->assertion_handler = new AssertionHandler();
	}
	
	private function __destruct()
	{
		$this->exception_handler = new ExceptionHandler();
		$this->error_handler = new ErrorHandler();
		$this->assertion_handler = new AssertionHandler();
	}

	public static function instance()
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}

}


class DotprojectWS
{
	public function ping()
	{
		return TRUE;
	}

	public function GetTask($id)
	{
		return TaskDAO($id);
	}
}


$dPWebService = DPWebService::instance();

// session_start();
$mapper = new DAOMapper();
$classmap = $mapper->getMapping();

$server = new SoapServer("dotproject.wsdl", array('classmap' => $classmap));
$server->setClass("DotprojectWS");
$server->setPersistence(SOAP_PERSISTENCE_SESSION);
$server->handle();

?>
