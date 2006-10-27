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
 * Sets the default exception handler if an exception is not caught within a
 * try/catch block.
 */
class ExceptionHandler extends IssueHandler
{
	public function __construct()
	{
		// The exception_handler must be defined before calling set_exception_handler().
		set_exception_handler($this->handle_exception);
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
	 */
	public function handle_exception($exception)
	{
	}
}

?>