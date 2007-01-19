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
 * We must handle the differences between PHP 4 and 5. The object
 * model from those version is completely different and, as you know,
 * IDEAIS is developement using OO (object-oriented) features.
 * 
 * The easiest solution is to adopt completely different code for PHP 4
 * and PHP 5. The trend is that PHP 4, already obsoleted, come to desuse
 * and any important PHP application be migrated to a newer (>= 5.0.0)
 * version).
 */
 
function __autoload($class_name)
{
	if (version_compare(phpversion(), "5.0.0", ">=")) {
		require_once(dirname(__FILE__) . '/php5/' . $class_name . '.class.php');
	} else {
		require_once(dirname(__FILE__) . '/php4/' . $class_name . '.class.php');
	}	
}	
?>
