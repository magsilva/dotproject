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

function dotproject_initialize()
{
	global $baseDir;
	global $dPconfig;
	
	$dPconfig = array();
	if (is_file("$baseDir/includes/config.php")) {
		require_once("$baseDir/includes/config.php");
	}
	// throw exception or sinalize error if the file does not exist.
}


function user_initialize()
{
	global $AppUI;
	global $baseDir;
	global $dPconfig;
	
	require_once("$baseDir/includes/session.php");

	// manage the session variable(s)
	dPsessionStart(array('AppUI'));
	
	// check if session has previously been initialised
	if (!isset( $_SESSION['AppUI'] ) || isset($_REQUEST['logout'])) {
    	if (isset($_REQUEST['logout']) && isset($_SESSION['AppUI']->user_id)) {
			$AppUI =& $_SESSION['AppUI'];
			$user_id = $AppUI->user_id;
			addHistory('login', $AppUI->user_id, 'logout', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
    	}
		$_SESSION['AppUI'] = new CAppUI;
	}	
	$AppUI = $_SESSION['AppUI'];
	$last_insert_id =$AppUI->last_insert_id;
	
	// load default preferences if not logged in
	if ($AppUI->doLogin()) {
		$AppUI->loadPrefs( 0 );
	}
}

function user_login($username, $password)
{
	global $AppUI;
		
	$ok = $AppUI->login( $username, $password );
	if (!$ok) {
		$AppUI->setMsg( 'Login Failed');
	} else {
	    // Register login in user_acces_log
	    $AppUI->registerLogin();
	}
    addHistory('login', $AppUI->user_id, 'login', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
}

function initialize()
{
	dotproject_initialize();
	dotproject_initialize_user();
}


function getTasksForPeriod( $start_date, $end_date, $company_id=0 )
{
	global $AppUI;
	global $baseDir;
	
	// convert to default db time stamp
	$db_start = $start_date->format( FMT_DATETIME_MYSQL );
	$db_end = $end_date->format( FMT_DATETIME_MYSQL );
	
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
	
	var_dump($AppUI->user_id);
	
	$deny = $obj->getDeniedRecords( $AppUI->user_id );
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
 
?>
