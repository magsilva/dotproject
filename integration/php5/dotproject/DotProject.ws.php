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

Copyright (C) 2006 Marco Aurelio Graciotto Silva <magsilva@gmail.com>
*/

require_once('FailureHandler.class.php');
require_once('DAOMapper.class.php');
require_once('WebService.class.php');
require_once('../classes/dotproject.class.php');

class DPWebService extends WebService
{
	protected $exception_handler;
	
	protected $assertion_handler;
	
	protected $error_handler;
	
	protected $daomapper;
	
	public function __construct()
	{
		parent::__construct();
		
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
				
		$this->exception_handler = ExceptionHandler::instance('dp-ws.log');
		$this->error_handler = new ErrorHandler();
		$this->assertion_handler = new AssertionHandler();

		$this->daomapper = new DAOMapper();

		$this->start();
 	}
	
	/**
	 * Finalize the DotProject web service.
	 */
	public function __destruct()
	{
		parent::__destruct();
	}
	
	protected function get_wsdl()
	{ 
		return "dotproject.wsdl";
	}

	protected function get_mapping()
	{
		return $this->mapper->getMapping();
	}

	public function ping()
	{
		return TRUE;
	}

	public function GetTask($id)
	{
		return TaskDAO($id);
	}
}

$dPWebService = new DPWebService();

?>
