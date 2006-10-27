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

Copyright (C) 2006 Marco Aurélio Graciotto Silva <magsilva@gmail.com>
*/

require_once('IssueHandler.class.php');
require_once('Exception.class.php');

/**
 * The ErrorHandler transforms every error into an Exception. There are two
 * exceptions thrown by this class: SystemException e UserException.
 */
class ErrorHandler extends IssueHandler
{
	private $previous_error_level;
	
	private $previous_display_startup_errors;

	private $previous_ignore_repeated_errors;
	
	private $previous_ignore_repeated_source;
	
	private $previous_ignore_user_abort;
	
	private $previous_report_memleaks;
	
	private $previous_html_errors;
	
	private $previous_bug_compat_warn;
	
	public function __construct()
	{
		// Report all PHP errors
		$this->previous_error_level = error_reporting(E_ALL | E_STRICT);
		
		$this->previous_display_startup_errors = ini_set('display_startup_errors', 1);
		$this->previous_ignore_repeated_errors = ini_set('ignore_repeated_errors', 1);
		$this->previous_ignore_repeated_source = ini_set('ignore_repeated_source', 0);
		$this->previous_ignore_user_abort = ini_set('ignore_user_abort', 1);
		$this->previous_report_memleaks = ini_set('report_memleaks', 1);
		$this->previous_html_errors = ini_set('html_errors', 0);
		if (ini_get('session.bug_compat_42')) {
			$this->previous_bug_compat_warn = ini_set('session.bug_compat_warn', 1);
		}
		
		// Set the function called whenever an error happens.
		set_error_handler( array( &$this, 'error_handler' ) );
	}
	
	public function __destruct()
	{
		parent::__destruct();
		
		error_reporting($this->previous_error_level);
		ini_set('display_startup_errors', $this->previous_display_startup_errors);
		ini_set('ignore_repeated_errors', $this->previous_ignore_repeated_errors);
		ini_set('ignore_repeated_source', $this->previous_ignore_repeated_source);
		ini_set('ignore_user_abort', $this->previous_ignore_user_abort);
		ini_set('report_memleaks', $this->previous_report_memleaks);
		ini_set('html_errors', $this->previous_html_errors);
		if (ini_get('session.bug_compat_42')) {
			 ini_set('session.bug_compat_warn', $this->previous_bug_compat_warn);
		}

		restore_error_handler();
	}
	
		
	public function translateErrorType($errno)
	{
		$errortype = array (
			0                    => 'Ignored error',
			E_ERROR              => 'Error',
			E_WARNING            => 'Warning',
			E_PARSE              => 'Parsing error',
			E_NOTICE             => 'Notice',
			E_CORE_ERROR         => 'Core error',
			E_CORE_WARNING       => 'Core warning',
			E_COMPILE_ERROR      => 'Compile error',
			E_COMPILE_WARNING    => 'Compile warning',
			E_USER_ERROR         => 'User error',
			E_USER_WARNING       => 'User warning',
			E_USER_NOTICE        => 'User notice',
			E_STRICT             => 'Runtime notice',
		);
		// PHP 6 subject: E_RECOVERABLE_ERRROR => 'Catchable fatal error'
		
		return $errortype[$errno];
	}

	private function create_system_exception($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$exception = new SystemException($errno, $errstr, $errfile, $errline, $errcontext);
				
		return $exception;
	}

	private function create_user_exception($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$exception = new UserException($errno, $errstr, $errfile, $errline, $errcontext);
				
		return $exception;
	}

	
	/**
	 * This function can be used for defining your own way of handling errors
	 * during runtime, for example in applications in which you need to do
	 * cleanup of data/files when a critical error happens, or when you need to
	 * trigger an error under certain conditions (using trigger_error()).
	 * 
	 * The user function needs to accept two parameters: the error code, and a
	 * string describing the error. Then there are three optional parameters
	 * that may be supplied: the filename in which the error occurred, the line
	 * number in which the error occurred, and the context in which the error
	 * occurred (an array that points to the active symbol table at the point
	 * the error occurred). The function can be shown as:
	 * 
	 * handler ( int errno, string errstr [, string errfile [, int errline [, array errcontext]]] )
	 * 
	 * @param errno The first parameter, errno, contains the level of the error
	 * raised, as an integer.
	 * @param errstr The second parameter, errstr, contains the error message,
	 * as a string.
	 * @param errfile The third parameter is optional, errfile, which contains
	 * the filename that the error was raised in, as a string.
	 * @param errline The fourth parameter is optional, errline, which contains
	 * the line number the error was raised at, as an integer.
	 * @param errcontext The fifth parameter is optional, errcontext, which  is
	 * an array that points to the active symbol table at the point the error
	 * occurred. In other words, errcontext will contain an array of every
	 * variable that existed in the scope the error was triggered in. User error
	 * handler must not modify error context.
	 */
	function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$exception = null;
		$errstr = $this->translateErrorType($errno) . ': ' . $errstr;
		$context = $this->createReport();
		
		switch ($errno) {
			case 0:
				// The 'errno' value will be 0 if the statement that caused the
				// error was prepended by the @ error-control operator.
			case E_ERROR:
				// Fatal run-time errors. These indicate errors that can not
				// be recovered from, such as a memory allocation problem. 
				// Execution of the script is halted.
			case E_WARNING:
				// Run-time warnings (non-fatal errors). Execution of the
				// script is not halted.
			case E_PARSE:
				// Compile-time parse errors. Parse errors should only be
				// generated by the parser.
			case E_NOTICE:
				// Run-time notices. Indicate that the script encountered
				// something that could indicate an error, but could also
				// happen in the normal course of running a script.
			case E_CORE_ERROR:
				// Fatal errors that occur during PHP's initial startup.
				// This is like an E_ERROR, except it is generated by the
				// core of PHP.
			case E_CORE_WARNING:
				// Warnings (non-fatal errors) that occur during PHP's
				// initial startup. This is like an E_WARNING, except it
				// is generated by the core of PHP.
			case E_COMPILE_ERROR:
				// Fatal compile-time errors. This is like an E_ERROR, except
				// it is generated by the Zend Scripting Engine.
			case E_COMPILE_WARNING:
				// Compile-time warnings (non-fatal errors). This is like an
				// E_WARNING, except it is generated by the Zend Scripting
				// Engine.
			case E_STRICT:
				// Run-time notices. Enable to have PHP suggest changes to your
				// code which will ensure the best interoperability and forward
				// compatibility of your code.
			case E_RECOVERABLE_ERROR:
				// Catchable fatal error. It indicates that a probably
				// dangerous error occured, but did not leave the Engine in an
				// unstable state. If the error is not caught by a user defined
				// handle (see also set_error_handler()), the application
				// aborts as it was an E_ERROR.
				$exception = $this->create_system_exception($errno, $errstr, $errfile, $errline, $context);
				break;

			case E_USER_ERROR:
				// User-generated error message. This is like an E_ERROR,
				// except it is generated in PHP code by using the PHP function
				// trigger_error(). 
			case E_USER_WARNING:
				// User-generated warning message. This is like an E_WARNING,
				// except it is generated in PHP code by using the PHP function
				// trigger_error(). 
			case E_USER_NOTICE:
				// User-generated notice message. This is like an E_NOTICE, 
				// except it is generated in PHP code by using the PHP function
				// trigger_error().
				$exception = $this->create_user_exception($errno, $errstr, $errfile, $errline, $context);
				break;
			default:
				$exception = $this->create_system_exception($errno, $errstr, $errfile, $errline, $context);	
		}	 

		throw $exception;
		exit();
	}
}
?>