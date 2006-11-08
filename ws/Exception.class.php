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
 * Base exception for user applications exceptions
 * 
 * Base exception for user applications exceptions. It's, actually,
 * a wrapper for PHP's exceptions, allowing us, through reflection,
 * the detection of exceptions thrown by the 'FailureHandler' package's
 * handlers.
 * 
 * @author Marco Aurelio Graciotto Silva
 * @license GPL
 * @since November/2006
 * @package FailureHandler
 */
abstract class BaseException extends Exception
{
	/**
	 * The application's context when running an exception.
	 * @var array 
	 */	
	protected $context;
	
	/**
	 * Initialize the exception.
	 * 
	 * Initialize the exception. We do not run the parent::__constructor(), so
	 * extra care is needed to fill all the exception's required attributes.
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
	*/
    function __construct($errno, $errstr, $errfile, $errline, $errcontext)
    {
    	$this->code = $errno;
    	$this->message = $errstr;
    	$this->file = $errfile;
    	$this->line = $errline;
    	$this->context = $errcontext;
    }

	/**
	 * Get the application context when thown the exception.
	 * 
	 * @return array The application context.
	 */
	final function getContext()
	{
		return $this->context;
	}
	
	/**
	 * Get a pretty print string with the exception data.
	 * 
	 * @return string The exception data.
	 */
	public function __toString()
	{
		return parent::__toString() . '\n' . $this->context;
	}
}

/**
 * System exceptions.
 * 
 * The SystemException class is just a BaseException renamed. Think of it
 * as a tagged BasedException. This is required just to discover the
 * exception's kind using the PHP reflection mechanism.
 * 
 * A SystemException is thrown whenever an internal PHP error or external
 * libraries fails. In other words, whenever an exception take place and
 * is not the application faults (but a third party), a SystemException
 * must be thrown.
 */
class SystemException extends BaseException
{
}

/**
 * User exceptions.
 * 
 * The UserException class is just a BaseException renamed. Think of it
 * as a tagged BasedException. This is required just to discover the
 * exception's kind using the PHP reflection mechanism.
 * 
 * An UserException is thrown whenever an application fails due to its own
 * fault. In other words, if it's not a {@link SystemException}, it's an
 * UserException.
 */
class UserException extends BaseException
{
}

/**
 * Assert exceptions.
 * 
 * The AssertException class is just a BaseException renamed. Think of it
 * as a tagged BasedException. This is required just to discover the
 * exception's kind using the PHP reflection mechanism.
 * 
 * An AssertException is thrown whenever an assertion fails.
 */
class AssertException extends BaseException
{
    function __construct($file, $line, $code)
    {
    	$this->code = 0;
    	$this->message = $code;
    	$this->file = $file;
    	$this->line = $line;
    	$this->context = '';
    }
}

?>