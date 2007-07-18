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

Copyright (C) 2007 Marco Aurélio Graciotto Silva <magsilva@gmail.com>
*/

require_once(dirname(__FILE__) . '/includes/sso.php');
 
if (isset($_GET['openid_name'])) {
	setcookie(OPENID_COOKIE_NAME, $_GET['openid_name']);
	$link = 'images/log-notice.gif'; 
} else {
	$link = 'images/log-error.gif'; 
}

header('Content-Type: image/gif');
readfile($link);

?>
