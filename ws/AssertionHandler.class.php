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

class AssertionHandler extends IssueHandler
{
	private $assert_active;
	
	private $assert_warning;
	
	private $assert_bail;
	
	private $assert_quiet_eval;
	
	private $assert_handler;
	
	public function __construct()
	{
		// Enable assertions.
		$this->assert_active = assert_options(ASSERT_ACTIVE, 1);
		// Disable the issue of a warning for each failed assertion.
		$this->assert_warning = assert_options(ASSERT_WARNING, 0);
		// Do not exit() the script after a failed assertion.
		$this->assert_bail = assert_options(ASSERT_BAIL, 0);
		// Disable error_reporting during assertion expression evaluation.
		$this->assert_quiet_eval = assert_options(ASSERT_QUIET_EVAL, 1);

		// Set the function called whenever an assertion fails.
		$this->assert_handler = assert_options(ASSERT_CALLBACK, array( &$this, 'assertion_handler'));
	}

	public function __destruct()
	{
		parent::__destruct();
		
		// Restore assert options.
		assert_options(ASSERT_ACTIVE, $this->assert_active);
		assert_options(ASSERT_WARNING, $this->assert_warning);
		assert_options(ASSERT_BAIL, $this->assert_bail);
		assert_options(ASSERT_QUIET_EVAL, $this->assert_quiet_eval);
		assert_options(ASSERT_CALLBACK, $this->assert_handler);
	}
		
	/**
	 * The assert_options() function and/or ASSERT_CALLBACK configuration
	 * directive allow a callback function to be set to handle failed
	 * assertions.
	 * 
	 * Assert() callbacks are particularly useful for building automated test
	 * suites because they allow you to easily capture the code passed to the
	 * assertion, along with information on where the assertion was made. While
	 * this information can be captured via other methods, using assertions
	 * makes it much faster and easier!
	 * 
	 * @param $file The file the assertion failed in.
	 * @param $line The line the assertion failed on.
	 * @param $code Contain the expression that failed (if any - literal values
	 * such as 1 or "two" will not be passed via this argument)
	 */
	public function assertionHandler($file, $line, $code)
	{
		$context = $this->createReport();
		throw new AssertException($file, $line, $code, $context);
	}
}

?>