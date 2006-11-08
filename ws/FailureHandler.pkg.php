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

/**
 * Handle application failures.
 * 
 * Handle application failures: uncaught exceptions, failed assertions,
 * application triggered and internal PHP errors:
 * 
 *  - Uncaught exceptions: ExceptionHandler
 *  - Failed assertions: AssertionHandler
 *  - Application triggered and internal PHP errors: ErrorHandler
 * 
 * Any of these failures but uncaught exceptions is wrapped within an
 * exception and thrown. So, the application handling main responsable
 * is the ExceptionHandler class.
 * 
 * @package FailureHandler
 */

/*
 * Files that belongs to this package.
 */
require_once('AssertionHandler.class.php');
require_once('ErrorHandler.class.php');
require_once('ExceptionHandler.class.php');

/**
 * Handle application's failures.
 * 
 * @author Marco Aurelio Graciotto Silva
 * @license GPL
 * @since November/2006
 * @package FailureHandler
 */
class FailureHandler
{
	private $error_handler;
		
	private $exception_handler;
		
	private $assertion_handler;	
	

	/**
	 * Initialize the default application handling features.
	 * 
	 * Initialize the default application handling features. This will change the
	 * default assertion, error, and exception handlers. If you need a fine grained
	 * control over those handlers, include this package's files manually and
	 * initialize the handlers yourself.
	 */
	public function __construct()
	{
		$this->assertion_handler = new AssertionHandler();
		$this->error_handler = new ErrorHandler();
		$this->exception_handler = ExceptionHandler::instance();
	}
	
	public function __destruct()
	{
	}
}

$fh = new FailureHandler();
?>