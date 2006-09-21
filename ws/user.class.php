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

global $AppUI;
global $baseDir;
global $dPconfig;
$baseDir = dirname(__FILE__) . "/..";

class User {

	function User()
	{
		global $AppUI;
		global $baseDir;
		global $dPconfig;
	
		require_once("$baseDir/includes/session.php");

		// manage the session variable(s)
		dPsessionStart(array('AppUI'));
	
		// check if session has been previously initialised
		if ($this->somebodyIsAuthenticated()) {
			$this->logout();
		} else {
			$_SESSION['AppUI'] = new CAppUI;
		}	
		$AppUI =& $_SESSION['AppUI'];
		
		// load default preferences if not logged in
		// TODO: Analyse the usefulness of this code and (probably) remove it.
		if ($AppUI->doLogin()) {
			$AppUI->loadPrefs(0);
		}
	}
	
	function logout()
	{
		$AppUI =& $_SESSION['AppUI'];
		$user_id = $AppUI->user_id;
		addHistory('login', $AppUI->user_id, 'logout', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
		$AppUI->registerLogout($AppUI->user_id);
	}	

	function login($username, $password)
	{
		global $AppUI;
		
		$ok = $AppUI->login($username, $password);
		if (!$ok) {
			$AppUI->setMsg('Login Failed');
		} else {
		    // Register login in user_acces_log
		    $AppUI->registerLogin();
		}
		addHistory('login', $AppUI->user_id, 'login', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
	}
	
	function somebodyIsAuthenticated()
	{
		if (!isset( $_SESSION['AppUI'] ) || isset($_REQUEST['logout'])) {
    		if (isset($_REQUEST['logout']) && isset($_SESSION['AppUI']->user_id)) {
    			return TRUE;
    		}
		}
		return FALSE;
	}

	function getTasksForPeriod($start_date, $end_date, $company_id=0)
	{
		global $AppUI;
		global $baseDir;
		
		// ou CTask::getTasksForPeriod(
	
		// convert to default db time stamp
		$db_start = $start_date->format(FMT_DATETIME_MYSQL);
		$db_end = $end_date->format(FMT_DATETIME_MYSQL);
	
		// filter tasks for not allowed projects
		$tasks_filter = '';
		require_once($baseDir . "/modules/projects/projects.class.php");
		$proj =& new CProject;
		$task_filter_where = $proj->getAllowedSQL($AppUI->user_id, 'task_project');
		if (count($task_filter_where)) {
			$tasks_filter = ' AND (' . implode(' AND ', $task_filter_where) . ")";
		}
		// assemble where clause
		$where = "task_project = project_id"
			. "\n\tAND task_status > -1"
			. "\n\tAND ("
				. "\n\t\t(task_start_date <= '$db_end' AND task_end_date >= '$db_start')"
			. "\n\t\tOR task_start_date BETWEEN '$db_start' AND '$db_end'"
			. "\n\t)"
			. "\n\t$tasks_filter";
		$where .= $company_id ? "\n\tAND project_company = '$company_id'" : '';
	
		// exclude read denied projects
		$obj = new CProject();
	
		$deny = $obj->getDeniedRecords($AppUI->user_id);
		$where .= count($deny) > 0 ? "\n\tAND task_project NOT IN (" . implode( ',', $deny ) . ')' : '';

		// get any specifically denied tasks
		require_once($baseDir . "/modules/tasks/tasks.class.php");
		$obj = new CTask();
		$allow = $obj->getAllowedSQL( $AppUI->user_id );
		$where .= count($allow) > 0 ? "\n\tAND " . implode( ' AND ', $allow ) : '';
	
		// assemble query
		$sql = "SELECT DISTINCT task_id, task_name, task_start_date, task_end_date,"
	    	. "\n\ttask_duration, task_duration_type,"
    		. "\n\tproject_color_identifier AS color,"
    		. "\n\tproject_name"
    		. "\nFROM tasks,projects,companies"
    		. "\nWHERE $where"
    		. "\nORDER BY task_start_date";
   	 
		// execute and return
		return db_loadList( $sql );
	}
} 

?>