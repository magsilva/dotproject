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
 * Base class for ExceptionHandler.
 */
require_once('IssueHandler.class.php');

/**
 * Required by the exception logging feature. This is from the PEAR
 * Log module.
 */
require_once('Log.php');

/**
 * Handle exceptions in the application.
 * 
 * Sets the default exception handler and handle any exception not caught by a
 * try/catch block. Every exception caught is registered in a log file or
 * database (that's why the dependency to PEAR's Log package).
 * 
 * This class implements the Singleton pattern. An instance can be created only
 * by means of the {@link instance()} method.
 * 
 * @todo Email the system report (IssueHandler->getReport())
 * @todo Log the system report (IssueHandler->getReport())
 * 
 * @package FailureHandler
 * @author Marco Aurelio Graciotto Silva
 * @license GPL
 * @since November/2006
 */
class ExceptionHandler extends IssueHandler
{
	/**
	 * The PEAR Log instance, used to register all the exceptions caught by
	 * the exception handler.
	 * 
	 * This variable is set by the constructor and cannot be modified after
	 * that. Actually, it shouldn't be static, but, as the exception handling
	 * function must be static and it uses this var, it's required that $log
	 * be static too.
	 * 
	 * @var Log
	 */
	private static $log;
	
	/**
	 * Holds the unique instance of this class (Singleton pattern implementation).
	 * 
	 * @var ExceptionHandler
	 */
	private static $instance;
	
	/**
	 * The log filename.
	 * @var string
	 */
	private $filename;
	
	/**
	 * Create a new instance of the ExceptionHandler and change the default
	 * exception handler.
	 * 
	 * The application's default exception handler is set to {@link
	 * handle_exception}. The log mechanism is initialized, registering all
	 * the exceptions into a file.
	 * 
	 * The exception logging format is as follows:
	 * Date + 'TEST' + Error level + exception message + stack trace
	 * 
	 * Example:
	 * <pre>
	 * Nov 08 14:30:17 TEST [info] exception 'Exception' with message 'Dummy'
	 * in /home/msilva/Projects/ideais/dotproject/tests/ExceptionHandlerExample.php:23
	 * Stack trace:
	 * #0 {main}
	 * <pre>
	 *
	 * @param string $filename The log filename. If a relative filename is given,
	 * the base dir will the be one this file (ExceptinHandler.class.php) is
	 * located. This argument is optional. So, if not supplied, the filename will
	 * be set to 'out.log'.  
	 */
	private function __construct($filename = null)
	{
		// The exception_handler must be defined before calling set_exception_handler().
		set_exception_handler(array( 'ExceptionHandler', 'handle_exception' ));

		$this->filename = ExceptionHandler::generate_absolute_log_filename($filename);
		$ident = 'TEST';
		$conf = array();
		/*
		 * Available error levels:
		 * 
		 * PEAR_LOG_EMERG   (0) = System is unusable
		 * PEAR_LOG_ALERT   (1) = Immediate action required
		 * PEAR_LOG_CRIT    (2) = Critical conditions
		 * PEAR_LOG_ERR     (3) = Error conditions
		 * PEAR_LOG_WARNING (4) = Warning conditions
		 * PEAR_LOG_NOTICE  (5) = Normal but significant
		 * PEAR_LOG_INFO    (6) = Informational
		 * PEAR_LOG_DEBUG   (7) = Debug-level messages
		 * PEAR_LOG_ALL     (8) = All messages
		 * PEAR_LOG_NONE    (9) = No message
		 */
		$level = PEAR_LOG_DEBUG;
		self::$log = &Log::factory('file', $this->filename, $ident, $conf, $level);
	}

	/**
	 * compile the absolute filename for a log filename.
	 * 
	 * @param string $filename The log filename. If a relative filename is given,
	 * the base dir will the be one this file (ExceptinHandler.class.php) is
	 * located. This argument is optional. So, if not supplied, the filename will
	 * be set to 'out.log'.
	 * @return string The absolute log filename.
	 */
	public static function generate_absolute_log_filename($filename = null)
	{
		if ($filename == null) {
			$filename = dirname(__FILE__) . '/out.log';
		} else {
			if ($filename{0} != '/') {
				$filename = dirname(__FILE__) . '/' . $filename;
			}
		}
		
		return $filename;
	}
	
	/**
	 * Get the log filename.
	 * 
	 * @return string The log filename.
	 */
	public function get_log_filename()
	{
		return $this->filename;
	}
	
	
	/**
	 * Restore the previous error handling function and destroy the static
	 * objects.
	 */
	public function __destruct()
	{
		restore_exception_handler();
		self::$instance = null;
		self::$log = null;
	}

	/**
	 * This handler function needs to accept one parameter, which will be the
	 * exception object that was thrown. Execution will stop after the
	 * exception_handler is called.
	 * 
	 * The exception is registered into a file.
	 */
	public static function handle_exception($exception)
	{
		self::$log->log($exception->__toString());
	}
	
	/**
	 * Create the ExceptionHandler instance. This implements the Singleton
	 * pattern.
	 * 
	 * @param string $filename The log filename. If a relative filename is given,
	 * the base dir will the be one this file (ExceptinHandler.class.php) is
	 * located. This argument is optional. So, if not supplied, the filename will
	 * be set to 'out.log'.
	 * 
	 * @throws UserException If there is an active ExceptionHandler and this filename
	 * argument is different from the one of the running instance, an UserException
	 * will be thrown. 
	 */
	public static function instance($filename = null)
	{
		if (self::$instance == null) {
			self::$instance = new ExceptionHandler($filename);
		} else {
			if (ExceptionHandler::generate_absolute_log_filename($filename) != self::$instance->get_log_filename()) {
				throw new UserException('Cannot set the log filename for an active ExceptionHandler');
			}
		}
		return self::$instance;
	}
}

?>