<?php

require_once(dirname(__FILE__) . '/../base.php');

class DotProject
{
	// __construct()
	/**
	 * Initialize dotProject.
	 */
	function DotProject()
	{
		global $baseDir;
		global $dPconfig;
	
		$dPconfig = array();
		if (is_file("$baseDir/includes/config.php")) {
			require_once("$baseDir/includes/config.php");
			require_once("$baseDir/includes/db_adodb.php");
		}
		// throw exception or sinalize error if the file does not exist.
	}
	
	/**
	 * Initialize the database connection.
	 */
	function connectToDatabase()
	{
		global $db, $dPconfig;	

		db_connect($dPconfig['dbhost'], $dPconfig['dbname'], $dPconfig['dbuser'], $dPconfig['dbpass'], $dPconfig['dbpersist']);

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
		
		return $db;
	}
}

?>