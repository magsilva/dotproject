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
 * Base class for AssertionHandler.
 */
require_once('IssueHandler.class.php');

/**
 * File that defines the AssertException class (required by the
 * {@link assert_handler()}).
 */
require_once('Exception.class.php');

/**
 * Convert any assertion failure into an exception.
 * 
 * Convert any failed assertion into an exception and throw is. This is
 * achieved by implementing a new assertion handler.
 * 
 * @author Marco Aurelio Graciotto Silva
 * @license GPL
 * @since November/2006
 * @package FailureHandler
 */
class AssertionHandler extends IssueHandler
{
	/**
	 * Previous assertion checking state.
	 * @var bool
	 */
	private $assert_active;
	
	/**
	 * Previous assertion reporting state.
	 * @var bool
	 */
	private $assert_warning;
	
	/**
	 * Previous assertion bailing out state.
	 * @var bool
	 */
	private $assert_bail;
	
	/**
	 * Previous assertion quite evaluation state.
	 * @var bool
	 */
	private $assert_quiet_eval;
	
	/**
	 * Previous assertion handler function.
	 * @var mixed
	 */
	private $assert_handler;
	
	/**
	 * Initialize the AssertionHandler.
	 * 
	 * Initialize the AssertionHandler. The assertion mechanism is enabled,
	 * any action that could result into application output (verbosity) is
	 * disabled. Assertion failure bailing out is disabled (after all, the
	 * assert exception wil cause the same effect).
	 */
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
		// Restore assert options.
		assert_options(ASSERT_ACTIVE, $this->assert_active);
		assert_options(ASSERT_WARNING, $this->assert_warning);
		assert_options(ASSERT_BAIL, $this->assert_bail);
		assert_options(ASSERT_QUIET_EVAL, $this->assert_quiet_eval);
		assert_options(ASSERT_CALLBACK, $this->assert_handler);
	}
		
	/**
	 * Assertion handler.
	 * 
	 * The assert_options() function and/or ASSERT_CALLBACK configuration
	 * directive allow a callback function to be set to handle failed
	 * assertions.
	 * 
	 * @param string $file The file the assertion failed in.
	 * @param int $line The line the assertion failed on.
	 * @param mixed $code The expression that failed (if any - literal values
	 * such as 1 or "two" will not be passed via this argument)
	 * 
	 * @throws AssertException Throws the exception when ever an assertion
	 * fails.
	 */
	public function assertion_handler($file, $line, $code)
	{
		$context = $this->createReport();
		throw new AssertException($file, $line, $code, $context);
	}
}

?>