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

require_once( 'PHPUnit2/Framework/TestCase.php' );

abstract class TestCase extends PHPUnit2_Framework_TestCase
{
	protected function assertEmpty($var, $message = "")
	{
		if ( ! empty($var) ) {
			self::fail($message);
		}
	}
	
	protected function assertNotEmpty($var, $message = "")
	{
		if ( empty($var) ) {
			self::fail($message);
		}
	}
	
	protected function assertAbsoluteFilename($var, $message = "")
	{
		if ($var{0} != '/') {
			self::fail($message);
		}
	}	
}

?>