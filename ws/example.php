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

require_once("dotproject.class.php");
require_once("user.class.php");

$dot = new DotProject();
$user = new User();
$user->login("admin", "admin");
require_once($baseDir . "/classes/date.class.php");

// prepare time period for 'events'
$dd = 11;
$mm = 07;
$yy = 2006;
$startPeriod = new CDate(Date_calc::beginOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$startPeriod->setTime(0, 0, 0);
$startPeriod->subtractSeconds( 1 );
$dd = 11;
$mm = 08;
$yy = 2006;
$endPeriod = new CDate(Date_calc::endOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$endPeriod->setTime(23, 59, 59);
 
$company_id = 1;
$tasks = $user->getTasksForPeriod($startPeriod, $endPeriod, $company_id);


include_once('task.dao.php');
foreach ($tasks as $task) {
	$taskDAO = new TaskDAO($task);
	var_dump($taskDAO);
}

?>
