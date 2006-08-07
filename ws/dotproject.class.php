<?php
/*
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 * 
 * Copyright (C) 2006 magsilva
 */

global $AppUI;
global $baseDir;
global $dPconfig;
$baseDir = dirname(__FILE__) . "/..";

class DotProject
{
	// __construct()
	function DotProject()
	{
		global $baseDir;
		global $dPconfig;
	
		$dPconfig = array();
		if (is_file("$baseDir/includes/config.php")) {
			require_once("$baseDir/includes/config.php");
		}
		// throw exception or sinalize error if the file does not exist.
	}
}

?>
