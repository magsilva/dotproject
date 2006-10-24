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

Contributions:
	Neilson 
*/


/**
 * Global variables initialization.
 * 
 * We really wouldn't like to do this, write code outside the class. However,
 * most of the included files do require some global vars, most notably the
 * 'baseDir'. So, this hack is necessary.
 * 
 * Most of this code is just copy and paste from that within the DotProject
 * class. In a near future, maybe we can just remove this code.
 */

/**
 * Load the configuration. It will setup the global variables $baseDir, $baseUrl
 * and $dPconfig (those vars are refereced all over the DotProject's code,
 * there's no way we can avoid setting them - for now).
 */
global $baseDir;
$baseDir = dirname(__FILE__) . '/..';
$baseDir = realpath($baseDir);

global $baseUrl;
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO');
if (! empty($pathInfo)) {
	$baseUrl .= str_replace('\\', '/', dirname($pathInfo));
} else {
	$baseUrl .= str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : getenv('SCRIPT_NAME')));
}

global $dPconfig;
$dPconfig = array();
clearstatcache();
if (is_file("$baseDir/includes/config.php")) {
	require_once("$baseDir/includes/config.php");
}

require_once("$baseDir/includes/db_adodb.php");
require_once("$baseDir/includes/main_functions.php");
require_once("$baseDir/classes/ui.class.php");
require_once("$baseDir/classes/permissions.class.php");
require_once("$baseDir/includes/session.php");

global $db;
$db = db_connect($dPconfig['dbtype'], $dPconfig['dbhost'], $dPconfig['dbname'], $dPconfig['dbuser'], $dPconfig['dbpass'], $dPconfig['dbpersist']);

$sql = "SELECT config_name, config_value, config_type FROM config";
$rs = $db->Execute($sql);
if ($rs) { // Won't work in install mode.
	$rsArr = $rs->GetArray();
	foreach ($rsArr as $c) {
		if ($c['config_type'] == 'checkbox') {
			$c['config_value'] = ($c['config_value'] == 'true') ? true : false;
		}
		$dPconfig["{$c['config_name']}"] = $c['config_value'];
	}
}

global $AppUI;
dPsessionStart(array('AppUI'));
// Logout
// if (isset($_GET['logout']) && isset($_SESSION['AppUI']) && isset($_SESSION['AppUI']->user_id)) {
if (isset($_GET['logout']) && isset($_SESSION['AppUI']->user_id)) {
   	$AppUI =& $_SESSION['AppUI'];
   	$user_id = $AppUI->user_id;
	$AppUI->registerLogout($user_id);
	addHistory('login', $AppUI->user_id, 'logout', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
}
// Create a new AppUI (new session before new login or after logout).
if (!isset($_SESSION['AppUI']) || isset($_GET['logout'])) {
	$_SESSION['AppUI'] = new CAppUI;
}
$AppUI =& $_SESSION['AppUI'];



/**
 * Base class for DotProject. It handles the configuration loading, the
 * database connection and so on.
 */
class DotProject
{
	var $root;

	var $url;

	var $database;

	var $config;

	var $session;
	
	/**
	 * We don't want to call clearstatcache() so often. The cacheTimeout is
	 * used to set how often we should call clearstatcache.'
	 */
	var $cacheTimeout;

	// __construct()
	/**
	 * Initialize dotProject. It will also load the configuration and connect
	 * to the database.
	 */
	function DotProject()
	{
		$this->database = null;
		$this->config = null;
		$this->root = null;
		$this->url = null;
		$this->session = null;
		$this->cacheTimeout = 30;
	}

	/**
	 * Start dotProject. Please, do not split this method. PHP is pretty messy about
	 * global vars.
	 */
/*
	function start()
	{
		$this->setupBaseDir();
		$this->defineUrl();
		$this->loadConfig();
		$this->initializeDB();
		$this->loadDefaultConfig();
		$this->initializeSession();
	}
	
	function stop()
	{
		db_close();
	}
*/

	function setupBaseDir()
	{
		/**
		 * Load the configuration. It will setup the global variables $baseDir,
		 * $baseUrl and $dPconfig (those vars are refereced all over the DotProject's
		 * code, there's no way we can avoid setting them - for now).
	 	*/
		// Set the $baseDir. As this file is saved at the 'classes' directory,
		// we must add the '..' (as DotProject expects the site's root as $baseDir').
		$this->root = dirname(__FILE__) . '/..';
		$this->root = realpath($this->root);
		
		return $this->root;
	}
 	
	/**
	 * Automatically define the base url.
	 */
	function defineUrl()
	{
		$this->url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$this->url .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
		$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO');
		if (! empty($pathInfo)) {
			$this->url .= str_replace('\\', '/', dirname($pathInfo));
		} else {
			$this->url .= str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : getenv('SCRIPT_NAME')));
		}
		
		return $this->url;
	}

		
	/* 
	 *Load the configuration.
	*/ 
	function loadConfig()
	{
		$dPconfig = array();
		$baseDir = $this->root;
		$baseUrl = $this->url;
		
		// Clear the cache only if the modification time is greater than the timeout
		// (remember that mtime is cached, that's why we need a timeout, otherwise we would never
		// run the clearstatcache() and detect modifications to the file).
		if (time() - filemtime("$this->root/includes/config.php") > $this->cacheTimeout) {
			clearstatcache();
		}
		if (is_file("$this->root/includes/config.php")) {
			require_once("$this->root/includes/config.php");
			$this->config = $dPconfig;
		} else {
			$this->config = null;
		}

		return $this->config;
	}
		
	
	/**
	* Initialize the database connection.
	 */
	function initializeDB()
	{
		$this->database = db_connect(
			$this->config['dbtype'],
			$this->config['dbhost'],
			$this->config['dbname'],
			$this->config['dbuser'],
			$this->config['dbpass'],
			$this->config['dbpersist']
		);
		
		return $this->database;
	}

	
	/*
	* Having successfully established the database connection now, we will
	* hurry up to load the system configuration details from the database.
	*/
	function loadDefaultConfig()
	{
		$sql = "SELECT config_name, config_value, config_type FROM config";
		$rs = $this->database->Execute($sql);
		if ($rs) { // Won't work in install mode.
			$rsArr = $rs->GetArray();
			foreach ($rsArr as $c) {
				if ($c['config_type'] == 'checkbox') {
					$c['config_value'] = ($c['config_value'] == 'true') ? true : false;
				}
				$this->config["{$c['config_name']}"] = $c['config_value'];
			}
		}
	}
	
	function initializeSession()
	{
		// Initialize session
		// manage the session variable(s)
		dPsessionStart(array('AppUI'));
		
		if (! isset($_SESSION['AppUI'])) {
			$_SESSION['AppUI'] = new CAppUI;
		}
		$this->session = $_SESSION['AppUI'];
		
		return $this->session;
	}

	/**
	 * Check if the DotProject is configured and connected to the database
	 * (IOW, ready to work).
	 * 
	 * @return Boolean True is ready, False otherwise.
	 */
	function isReady()
	{
		if ($this->config != null && $this->database != null) {
			return true;
		}
		return false;
	}

	/**
	* Connects to the database. Actually, just return the DB connection created at the
	* object initialization.
	*/
	function connectToDatabase()
	{
		return $this->database;
	}
}


/*
global $baseDir;
$baseDir = $dP->setupBaseDir();

global $baseUrl;
$baseUrl = $dP->defineUrl();

global $dPconfig;
$dPconfig = $dP->loadConfig();
// Redirect to install if no configuration was set
if ($dPconfig == null) {
	echo <<<END
<html>
<head>
	<meta http-equiv='refresh' content='10; URL=$baseUrl/install/index.php'>
</head>

<body>

Fatal Error. You haven not created a config file yet.
<br/><a href='./install/index.php'>Click Here To Start Installation and Create One!</a>
(You will be automatically forwarded in 10 seconds)
</body>
</html>
END;
	exit();
}

global $db;
$db = $dot->initializeDB();
$dPconfig = $dp->loadDefaultConfig();

global $CR;
$CR = "\n";

global $AppUI;
$AppUI = $dP->initializeSession();
*/

?>