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

require_once("../classes/dotproject.class.php");
require_once("../classes/user.class.php");
require_once('../ws/Task.dao.php');
require_once($baseDir . "/classes/date.class.php");

$dot = new DotProject();
$user = new User();
$user->login("admin", "admin");

// prepare time period for 'events'
$dd = 11;
$mm = 01;
$yy = 2006;
$startPeriod = new CDate(Date_calc::beginOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$startPeriod->setTime(0, 0, 0);
$startPeriod->subtractSeconds( 1 );
$dd = 30;
$mm = 12;
$yy = 2006;
$endPeriod = new CDate(Date_calc::endOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$endPeriod->setTime(23, 59, 59);
 
$company_id = 1;
$tasks = $user->getTasksForPeriod($startPeriod, $endPeriod, $company_id);

$taskDAO = new TaskDAO(1);
var_dump($taskDAO);

/*
foreach ($tasks as $taskdata) {
	$taskDAO = new TaskDAO($taskdata);
	var_dump($taskDAO);
}
*/

?>
