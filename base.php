<?php
/* $Id: base.php,v 1.1.2.5 2007/08/04 13:37:33 cyberhorse Exp $ */

/* {{{ Copyright (c) 2003-2005 The dotProject Development Team <core-developers@dotproject.net>

    This file is part of dotProject.

    dotProject is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    dotProject is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with dotProject; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
}}} */

global $baseDir;
global $baseUrl;

$baseDir = dirname(__FILE__);

// only rely on env variables if not using a apache handler
function safe_get_env($name) 
{
	if (isset($_SERVER[$name])) {
		return $_SERVER[$name];
	} elseif (strpos(php_sapi_name(), 'apache') === false) {
		getenv($name);
	} else {
		return '';
	}
}

// automatically define the base url
$baseUrl = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$baseUrl .= safe_get_env('HTTP_HOST');
$pathInfo = safe_get_env('PATH_INFO');
if (@$pathInfo) {
  $baseUrl .= str_replace('\\','/',dirname($pathInfo));
} else {
  $baseUrl .= str_replace('\\','/', dirname(safe_get_env('SCRIPT_NAME')));
}

$baseUrl = preg_replace('#/$#D', '', $baseUrl);
// Defines to deprecate the global baseUrl/baseDir
define('DP_BASE_DIR', $baseDir);
define('DP_BASE_URL', $baseUrl);

// required includes for start-up
global $dPconfig;
$dPconfig = array();


$d = dir(dirname(__FILE__) . '/lib');
while (false !== ($entry = $d->read())) {
	if ($entry != '.' && $entry != '..') {
		$entry = $d->path . '/' .$entry;
		if (is_dir($entry)) {
			set_include_path(realpath($entry) .  PATH_SEPARATOR . get_include_path());
		}
	}
}
$d->close();

?>
