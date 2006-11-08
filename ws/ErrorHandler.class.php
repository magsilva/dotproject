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
 * 
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Copyright (C) 2006 Marco Aurelio Graciotto Silva <magsilva@gmail.com>
*/

/**
 * ErrorHandler's parent class.
 */
require_once('IssueHandler.class.php');

/**
 * Classes for the exceptions thrown by the ErrorHandler class.
 */
require_once('Exception.class.php');

/**
 * The ErrorHandler transforms every error into an Exception.
 * 
 * An useful PHP's feature is the error handling mechanism ({@link
 * http://br.php.net/manual/en/ref.errorfunc.php}). Everytime an error, internal
 * or triggered intentionally by the application (using the 'trigger_error'
 * statement), takes place, a function is called to handle the error. This
 * feature is used here to wrap every error into an exception. Those exceptions
 * will be handled then be handled by the exception handler.
 * 
 * This approach, wrap errors within exceptions, is a rather useful one for PHP 5
 * applications written with a object-orientation concept. It allows an unified
 * technique to handle application's and system's errors.
 * 
 * An alternative would do the way around: catch any exception and call
 * 'trigger_error'. However, not every object-oriented language support this
 * technique, favoring the use of exceptions. That's why we do wrap errors
 * within exceptions.
 * 
 * @author Marco Aurelio Graciotto Silva
 * @license GPL
 * @since November/2006
 * @package FailureHandler
 */
class ErrorHandler extends IssueHandler
{
	/**
	 * Previous state for error level. This is set by the object's constructor
	 * and restored when garbage collected.
	 * @var int
	 */
	private $previous_error_level;
	
	/**
	 * Previous state for startup errors verbosity. This is set by the object's
	 * constructor and restored when garbage collected.
	 * @var bool
	 */
	private $previous_display_startup_errors;

	/**
	 * Previous state for repeated errors handling. This is set by the object's
	 * constructor and restored when garbage collected.
	 * @var bool
	 */
	private $previous_ignore_repeated_errors;
	
	/**
	 * Previous state for repeated errors in the same file handling. This is set
	 * by the object's constructor and restored when garbage collected.
	 * @var bool
	 */	private $previous_ignore_repeated_source;
	
	/**
	 * Previous state for user errors handling. This is set by the object's
	 * constructor and restored when garbage collected.
	 * @var bool
	 */
	private $previous_ignore_user_abort;
	
	/**
	 * Previous state for memory leaks detection and report. This is set by
	 * the object's constructor and restored when garbage collected.
	 * @var bool
	 */
	private $previous_report_memleaks;
	
	/**
	 * Previous state for error reporting using HTML. This is set by the object's
	 * constructor and restored when garbage collected.
	 * @var bool
	 */
	private $previous_html_errors;
	
	/**
	 * Previous state for and old PHP compatibility warning (actually an old
	 * PHP bug some software relied on to work properly). This is set by the
	 * object's  constructor and restored when garbage collected.
	 * @var bool
	 */
	private $previous_bug_compat_warn;
	
	/**
	 * Set error handling configuration.
	 * 
	 * Several PHP's error handling configuration are set, being as strict as
	 * possible (detecting and reporting any error found) while not sending messages
	 * to the user (console messages, etc).
	 * 
	 * The default error handling is also set to an static method from this class
	 * ({@link error_handler()}).
	 */
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
		
		// The new error format contains a reference to a page describing the error or 
		// function causing the error. Additional you have to set docref_ext to match
		// the file extensions of your copy.
		ini_set('docref_root', 'http://br2.php.net/manual/en/' );
		ini_set('docref_ext', '.php');
	}
	
	/**
	 * Restore the original error handling settings.
	 * 
	 * Restore all the settings done by the constructor. All the error
	 * handling settings are set to the value before this object initialization.
	 * The error handling is set to the one before the initialization too.
	 */
	public function __destruct()
	{
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
	
	/**
	 * Translate the error type to a string.
	 * 
	 * Translate the error type to a string (one a human being can understand).
	 * The translation is in English (no i18n for now).
	 * 
	 * @param int $errno The error code.
	 * @return string The translated error message.
	 */
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

		/*
		 * PHP 6 defined a new error type, E_RECOVERABLE_ERROR. The following
		 * code is required only when running with PHP 6.
		 */	
		if ( ! defined('E_RECOVERABLE_ERROR')) {	
			define('E_RECOVERABLE_ERROR', 0);
		}
		if (version_compare(phpversion(), '6.0.0') > 0) {
			$errortype[E_RECOVERABLE_ERROR] = 'Catchable fatal error';
		}
		
		return $errortype[$errno];
	}

	/**
	 * Create a SystemException object.
	 * 
	 * Create a SystemException object (but do not throw it). This exception will be
	 * created whenever an internal or system error is detected.
	 * 
	 * @param int $errno The error code.
	 * @param string $errstr The error message.
	 * @param string $errfile The file's name the error was found in.
	 * @param int $errline The line's number of the file the error was found in.
	 * @param array The active symbol table at the point the error occurred.
	 * 
	 * @return SystemException A SystemException object initialized with the parameters the
	 * method received.
	 */
	private function create_system_exception($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$exception = new SystemException($errno, $errstr, $errfile, $errline, $errcontext);
				
		return $exception;
	}

	/**
	 * Create an UserException object.
	 * 
	 * Create an UserException object (but do not throw it). This exception will
	 * be created whenever the application causes an error ('trigger_error()').
	 * 
	 * @param int $errno The error code.
	 * @param string $errstr The error message.
	 * @param string $errfile The file's name the error was found in.
	 * @param int $errline The line's number of the file the error was found in.
	 * @param array The active symbol table at the point the error occurred.
	 * 
	 * @return UserException A UserException object initialized with the parameters the
	 * method received.
	 */
	private function create_user_exception($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$exception = new UserException($errno, $errstr, $errfile, $errline, $errcontext);
				
		return $exception;
	}

	
	/**
	 * Handle application errors.
	 * 
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
	 * @param int $errno The first parameter, errno, contains the level of the error
	 * raised, as an integer.
	 * @param string $errstr The second parameter, errstr, contains the error message,
	 * as a string.
	 * @param string $errfile The third parameter is optional, errfile, which contains
	 * the filename that the error was raised in, as a string.
	 * @param int $errline The fourth parameter is optional, errline, which contains
	 * the line number the error was raised at, as an integer.
	 * @param array $errcontext The fifth parameter is optional, errcontext, which  is
	 * an array that points to the active symbol table at the point the error
	 * occurred. In other words, errcontext will contain an array of every
	 * variable that existed in the scope the error was triggered in. User error
	 * handler must not modify error context.
	 * 
	 * @throws SystemException Thrown on system errors.
	 * @throws UserException Thrown on applications errors (expected by the software,
	 * usually as a side effect of an 'trigger_error()' statement.
	 * 
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
		// Just to be on the safe side (the PHP specification says it will exit as
		// soon as an exception is thrown, but extra care is never too much).
		exit();
	}
}
?>