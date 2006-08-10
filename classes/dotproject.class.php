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

// Globals
$dbPconfig;
$db;
$baseDir;
$baseUrl;

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
		$this->cacheTimeout = 30;
		$this->start();
	}

	/**
	 * Start dotProject. Please, do not split this method. PHP is pretty messy about
	 * global vars.
	 */
	function start()
	{
		global $baseDir;
		global $baseUrl;
		global $dPconfig;
		
		/**
		 * Load the configuration. It will setup the global variables $baseDir,
		 * $baseUrl and $dPconfig (those vars are refereced all over the DotProject's
		 * code, there's no way we can avoid setting them - for now).
	 	*/
		// Set the $baseDir. As this file is saved at the 'classes' directory,
		// we must add the '..' (as DotProject expects the site's root as $baseDir').
		$this->root = dirname(__FILE__) . '/..';
		$this->root = realpath($this->root);
		$baseDir = $this->root;
				
		// Automatically define the base url
		$this->url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$this->url .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
		$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO');
		if (! empty($pathInfo)) {
			$this->url .= str_replace('\\', '/', dirname($pathInfo));
		} else {
			$this->url .= str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : getenv('SCRIPT_NAME')));
		}
		$baseUrl = $this->baseUrl;
				
		// Load the configuration.
		if (time() - fileatime("$this->root/includes/config.php") > $this->cacheTimeout) {
			clearstatcache();
		}
		if (is_file("$this->root/includes/config.php")) {
			require_once("$this->root/includes/config.php");
		}

		$this->config =& $dPconfig;
	
		/**
		 * Initialize the database connection.
		 */
		require_once("$this->root/includes/db_adodb.php");

		$this->database = db_connect($this->config['dbhost'],
			$this->config['dbname'],
			$this->config['dbuser'],
			$this->config['dbpass'],
			$this->config['dbpersist']
		);

		/*
		* Having successfully established the database connection now, we will
		* hurry up to load the system configuration details from the database.
		*/
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
}

?>
