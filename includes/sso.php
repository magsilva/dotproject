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

define('SSO_GET_PARAMETER_NAME', 'openid_name');

/**
 * Cookie name.
 */                         
define('SSO_COOKIE_NAME', 'secret');

/**
 * Default cookie timeout.
 */
define('SSO_COOKIE_TIMEOUT', time() + (60 * 60 * 24 * 7));		

define('SSO_ACTION_LOGIN', 'login');
define('SSO_ACTION_LOGOUT', 'logout');

define('SSO_IMAGE_ERROR', 'images/log-error.gif');
define('SSO_IMAGE_OK', 'images/log-notice.gif');

function get_image_mimetype($filename)
{
	$image_type = exif_imagetype($filename);
	return image_type_to_mime_type($image_type);
}

function sso_detect_action($content)
{
	return sso_detect_action_openid($content);
}

function sso_detect_action_openid($content)
{
	 $url = parse_url($content);
	 
	 if ($url !== FALSE && ! empty($url['scheme']) && ! empty($url['host'])) {
        if (in_array($url['scheme'], array('http', 'https', 'xri'))) {
        	return SSO_ACTION_LOGIN;
        }
	 }
	 return SSO_ACTION_LOGOUT;
}

function sso_login_openid($content)
{
	setcookie(SSO_COOKIE_NAME, $content);
	$link = SSO_IMAGE_OK; 
	return $link;
}

function sso_logout_openid()
{
	setcookie(SSO_COOKIE_NAME, '', time() - 3600);
	$link = SSO_IMAGE_OK; 
	return $link;
}

function sso_process()
{
	$content = $_REQUEST[SSO_GET_PARAMETER_NAME];
	$action = '';
	if (! empty($content)) {
		$action = sso_detect_action($content);
	}
	
	switch ($action) {
		case 'login':
			$link = sso_login_openid($content);
			break;
		case 'logout':
			$link = sso_logout_openid();
			break;
		default:
			$link = SSO_IMAGE_ERROR;
	} 
	header('Content-Type: ' . get_image_mimetype($link));
	readfile($link);	
}

?>
