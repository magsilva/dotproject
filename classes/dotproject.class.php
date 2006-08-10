<?php

require_once(dirname(__FILE__) . '/../base.php');

class DotProject
{
	var $database;

	var $config;

	// __construct()
	/**
	 * Initialize dotProject.
	 */
	function DotProject()
	{
		$this->loadConfiguration();
		$this->connectToDatabase();
	}

	function loadConfiguration()
	{
		global $baseDir;
		global $dPconfig;
	
		clearstatcache();
		if (is_file("$baseDir/includes/config.php")) {
			require_once("$baseDir/includes/config.php");
			require_once("$baseDir/includes/db_adodb.php");
		}

		$this->config &= $dPconfig;
	}
	
	/**
	 * Initialize the database connection.
	 */
	function connectToDatabase()
	{
		global $db;	

		db_connect($this->config['dbhost'],
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
		

		$this->database = $db;

		return $db;
	}

	function isReady()
	{
		if ($this->configuration != null && $this->database != null) {
			return true;
		}
		return false;
	}
}

?>
