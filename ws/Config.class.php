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

require_once(dirname(__FILE__) . '/Exception.class.php');

/**
 * Configuration file.
 * 
 * This class implements the Singleton pattern.
 */
class Config
{
	/**
	 * Single instance of the class.
	 */
    private static $instance;
	
	/**
	 * Database	 configuration for CoTeia.
	 */
	public $dbdriver;
	public $dbhost;
	public $dbname;
	public $dbuser;
	public $dbpass;

	public function __construct()
	{
		if (file_exists(dirname(__FILE__) . '/../includes/config.php')) {
			include_once(dirname(__FILE__) . '/../includes/config.php');
		} else {
			throw UserException('Configuration file could not be found.');
		}
		
		$this->dbdriver = $dPconfig['dbtype'];
		$this->dbhost = $dPconfig['dbhost'];
		$this->dbname = $dPconfig['dbname'];
		$this->dbuser = $dPconfig['dbuser'];
		$this->dbpass = $dPconfig['dbpass'];
		
		if (empty($this->dbdriver) || empty($this->dbhost) || empty($this->dbname)) {
			throw UserException('Configuration file is not valid.');		
		}
	}

	/**
	 * The singleton method.
	 */
	public static function instance() 
	{
		if (! isset( self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
    
	/**
	 * Prevent users to clone the instance.
	 */
	public function __clone()
	{
		throw UserException('Clone is not allowed.');		
	}
}

?>