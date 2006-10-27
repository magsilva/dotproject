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

class BaseException extends Exception
{
	protected $context;
	
	/**
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
    function __construct($errno, $errstr, $errfile, $errline, $errcontext)
    {
    	$this->code = $errno;
    	$this->message = $errstr;
    	$this->file = $errfile;
    	$this->line = $errline;
    	$this->context = $errcontext;
    }

	final function getContext()
	{
		return $this->context;
	}
	
	public function __toString()
	{
		return parent::__toString() . '\n' . $this->context;
	}
}


class SystemException extends BaseException
{
}

class UserException extends BaseException
{
}

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