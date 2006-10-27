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

require_once('IssueHandler.class.php');
require_once('Exception.class.php');
require_once('Log.php');

/**
 * Sets the default exception handler if an exception is not caught within a
 * try/catch block.
 * 
 * Every exception caught is registered in a log file or database (that's why
 * the dependency to PEAR's Log package).
 */
class ExceptionHandler extends IssueHandler
{
	private static $log;
	
	/**
	 * @param $filename The file where the records will be made. If a relative
	 * filename is set, the base dir will the be one this file is located.
	 * This argument is optional. So, if not supplied, the filename will be set
	 * to 'out.log'.  
	 */
	public function __construct($filename = null)
	{
		// The exception_handler must be defined before calling set_exception_handler().
		set_exception_handler(array( 'ExceptionHandler', 'handle_exception' ));

		if ($filename == null) {
			$filename = dirname(__FILE__) . '/out.log';
		} else {
			if ($filename{0} != '/') {
				$filename = dirname(__FILE__) . '/' . $filename;
			}
		}
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
		self::$log = &Log::factory('file', $filename, $ident, $conf, $level);
	}
	
	public function __destruct()
	{
		parent::__destruct();
		restore_exception_handler();
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
}

?>