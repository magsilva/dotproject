<?php /* $Id: index.php,v 1.121.4.4 2006/06/19 16:42:07 nybod Exp $ */

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

// Ensure errors get to the user.
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

// If you experience a 'white screen of death' or other problems,
// uncomment the following line of code:
error_reporting( E_ALL );

// An user can login from 'index.php' and 'fileviewer.php'.
$loginFromPage = 'index.php';


require_once("classes/dotproject.class.php");
$dP = new DotProject();


// don't output anything. Usefull for fileviewer.php, gantt.php, etc.
$suppressHeaders = dPgetParam($_GET, 'suppressHeaders', false);

// write the HTML headers
if (! $suppressHeaders) {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// always modified
	header("Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0");	// HTTP/1.1
	header("Pragma: no-cache");	// HTTP/1.0
}

// Load the commonly used classes
require_once($AppUI->getSystemClass('date'));
require_once($AppUI->getSystemClass('dp'));
require_once($AppUI->getSystemClass('query'));

require_once("$baseDir/misc/debug.php");

// Load default UI style
$AppUI->checkStyle();

// Function for update lost action in 'user_access_log'.
$AppUI->updateLastAction($AppUI->last_insert_id);




/**
 * Authentication actions.
 */

// Load default preferences if not logged in
if ($AppUI->doLogin()) {
	$AppUI->loadPrefs( 0 );
}

/*
 * Load default user configuration (for those actions that will redirect the
 * user. We make this conditional because we must set a HTTP header (and that
 * can only be set once per HTTP response).
 */
if (isset($_POST['lostpass']) || isset($_REQUEST['login']) || $AppUI->doLogin()) {
	$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $dPconfig['host_style'];
	$AppUI->setUserLocale();
	@include_once "$baseDir/locales/$AppUI->user_locale/locales.php";
	@include_once "$baseDir/locales/core.php";
	setlocale( LC_TIME, $AppUI->user_lang );
	if (! $suppressHeaders && isset($locale_char_set)) {
		header("Content-type: text/html;charset=$locale_char_set");
	}
}

// Request a new password
if (dPgetParam($_POST, 'lostpass', 0)) {
	if (dPgetParam($_REQUEST, 'sendpass', 0)) {
		require("$baseDir/includes/sendpass.php");
		sendNewPass();
	} else {
		require("$baseDir/style/$uistyle/lostpass.php");
	}
	exit();
}

// Log in user.
// Note the change to REQUEST instead of POST. This is so that we can support
// alternative authentication methods, such as the PostNuke and HTTP auth methods.
if (isset($_REQUEST['login'])) {
	$username = dPgetParam($_POST, 'username', '');
	$password = dPgetParam($_POST, 'password', '');
	$redirect = dPgetParam($_REQUEST, 'redirect', '');
	$ok = $AppUI->login($username, $password);
	if (!$ok) {
		$AppUI->setMsg('Login Failed');
	} else {
		//Register login in user_acces_log
		$AppUI->registerLogin();
	}
	addHistory('login', $AppUI->user_id, 'login', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
	$AppUI->redirect($redirect);
}

// If not logged in, redirect to the 'login' page.
if ($AppUI->doLogin()) {
	// $redirect is a variable required by 'login.php'
	$redirect = $_SERVER['QUERY_STRING']?strip_tags($_SERVER['QUERY_STRING']):'';
	if (strpos( $redirect, 'logout' ) !== false) {
		$redirect = '';
	}
	
	require "$baseDir/style/$uistyle/login.php";
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}



// Load specific user configuration.
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $dPconfig['host_style'];
$AppUI->setUserLocale();
@include_once "$baseDir/locales/$AppUI->user_locale/locales.php";
@include_once "$baseDir/locales/core.php";
setlocale( LC_TIME, $AppUI->user_lang );
if (! $suppressHeaders && isset($locale_char_set)) {
	header("Content-type: text/html;charset=$locale_char_set");
}



/**
 * Module and action selection. If a module is set, the default action (def_a)
 * is always 'index'.
 */

// Load permission check library
require_once("$baseDir/includes/permissions.php");

// Clear out main url parameters
$m = '';
$a = '';
$u = '';

// Config the default module and action
$def_m = $dPconfig['default_view_m'];
$def_a = 'index';

// Set the module from the defaults or from the URL
if (! isset($_GET['m'])) {
	$m = $def_m;
	$def_a = !empty($dPconfig['default_view_a']) ? $dPconfig['default_view_a'] : $def_a;
	$tab = $dPconfig['default_view_tab'];
} else {
	$m = dPgetParam($_GET, 'm', getReadableModule());
}
$AppUI->checkFileName($m);

// Set the action from the defaults or from the URL
if (! isset($_GET['a'])) {
	$a = $def_a;
} else {
	$a = dPgetParam($_GET, 'a', $def_a);
}
$AppUI->checkFileName($a);

/*
 * This check for $u implies that a file located in a subdirectory of depth
 * higher than one in relation to the module base can't be executed. So it
 * won't be possible to run, for example, the file
 * module/directory1/directory2/file. php. Also it won't be possible to run
 * modules/module/abc.zyz.class.php, as dots are not allowed in the request
 * parameters.
 */
$u = dPgetParam($_GET, 'u', '');
$AppUI->checkFileName($u);


// Load configuration for the module to be executed.
$m_config = dPgetConfig($m);
@include_once("$baseDir/functions/" . $m . "_func.php");

// TODO: canRead/Edit assignements should be moved into each file

// Check overall module permissions
// these can be further modified by the included action files
$perms =& $AppUI->acl();
$canAccess = $perms->checkModule($m, 'access');
$canRead = $perms->checkModule($m, 'view');
$canEdit = $perms->checkModule($m, 'edit');
$canAuthor = $perms->checkModule($m, 'add');
$canDelete = $perms->checkModule($m, 'delete');

/*
 * TODO: Permissions should be handled by each file. Denying access from index.
 * php still doesn't assure someone won't access directly skipping this security
 * check.
 *
// bounce the user if they don't have at least read access
if (!(
	  // however, some modules are accessible by anyone
	  $m == 'public' ||
	  ($m == 'admin' && $a == 'viewuser')
	  )) {
	if (!$canRead) {
		$AppUI->redirect( "m=public&a=access_denied" );
	}
}
*/

/*
 * Include  the module class file - we use file_exists instead of @ so
 * that any parse errors in the included files are reported, rather than
 * get the errors further down the track.
 */
$modclass = $AppUI->getModuleClass($m);
if (file_exists($modclass)) {
	include_once($modclass);
}
if ($u && file_exists("$baseDir/modules/$m/$u/$u.class.php")) {
	include_once("$baseDir/modules/$m/$u/$u.class.php");
}

// do some db work if dosql is set
// TODO - MUST MOVE THESE INTO THE MODULE DIRECTORY
if (isset( $_REQUEST["dosql"]) ) {
    //require("./dosql/" . $_REQUEST["dosql"] . ".php");
    require("$baseDir/modules/$m/" . ($u ? "$u/" : "") . $AppUI->checkFileName($_REQUEST["dosql"]) . ".php");
}

// start output proper
include("$baseDir/style/$uistyle/overrides.php");
ob_start();
if (!$suppressHeaders) {
	require("$baseDir/style/$uistyle/header.php");
}

if (! isset($_SESSION['all_tabs'][$m]) ) {
	// For some reason on some systems if you don't set this up
	// first you get recursive pointers to the all_tabs array, creating
	// phantom tabs.
	if (! isset($_SESSION['all_tabs'])) {
		$_SESSION['all_tabs'] = array();
	}
	$_SESSION['all_tabs'][$m] = array();
	$all_tabs =& $_SESSION['all_tabs'][$m];
	foreach ($AppUI->getActiveModules() as $dir => $module) {
		if (! $perms->checkModule($dir, 'access')) {
			continue;
		}
		$modules_tabs = $AppUI->readFiles("$baseDir/modules/$dir/", '^' . $m . '_tab.*\.php');
		foreach($modules_tabs as $tab) {
			// Get the name as the subextension
			// cut the module_tab. and the .php parts of the filename 
			// (begining and end)
			$nameparts = explode('.', $tab);
			$filename = substr($tab, 0, -4);
			if (count($nameparts) > 3) {
				$file = $nameparts[1];
				if (! isset($all_tabs[$file])) {
					$all_tabs[$file] = array();
				}
				$arr =& $all_tabs[$file];
				$name = $nameparts[2];
			} else {
				$arr =& $all_tabs;
				$name = $nameparts[1];
			}
			$arr[] = array(
				'name' => ucfirst(str_replace('_', ' ', $name)),
				'file' => $baseDir . '/modules/' . $dir . '/' . $filename,
				'module' => $dir);
		}
	}
} else {
	$all_tabs =& $_SESSION['all_tabs'][$m];
}

$module_file = "$baseDir/modules/$m/" . ($u ? "$u/" : "") . "$a.php";
if (file_exists($module_file)) {
	require($module_file);
} else {
	// TODO: make this part of the public module? 
	// TODO: internationalise the string.
	$titleBlock = new CTitleBlock('Warning', 'log-error.gif');
	$titleBlock->show();
	echo $AppUI->_("Missing file. Possible Module \"$m\" missing!");
}

if (!$suppressHeaders) {
	echo '<iframe name="thread" src="' . $baseUrl . '/modules/index.html" width="0" height="0" frameborder="0"></iframe>';
	require("$baseDir/style/$uistyle/footer.php");
}
ob_end_flush();
?>
