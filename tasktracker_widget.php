<?php

$baseDir = dirname(__FILE__);

$baseDir."<br/>"; 

require_once $baseDir."/includes/config.php";
require_once $baseDir ."/includes/session.php";


/*
function login( $password ) {
		global $user;
		
		require_once '../classes/authenticator.class.php';

		$auth =& getauth('ldap');

		$auth->fallback = false;
		
		if (!$auth->authenticate($user, $password)) {
			return false;
		}
	
		return true;
}
*/
//Programmvariablen
$log="";
$i=0;
$j=0;
$isreport = false;

$file_content="<?xml version='1.0' encoding='UTF-8'?>"; //Datei-Inhalt
//String fuer Systeminfos
$info_msg_1="<info>";
$info_msg_2="<info>";
$info_updated_tasks="<updated>";
//XML-Inhalt - Projektdaten
$xml_content="";
$report="";

//Beutzernamen und Password
$user = utf8_decode($_GET['user']);
$pw = $_GET['pw'];
$ldap = $_GET['ldap'];

//Varialbeln fuer Datenbankoperationen



if(isset($_GET['sl'])){
	
	$host = $dPconfig['dbhost'];
	$port = $_GET['port'];
	$db = $dPconfig['dbname'];
	$dbuser = $dPconfig['dbuser'];
	$dbpw = $dPconfig['dbpass'];
	
}else{
	$host = $_GET['host'];
	$port = $_GET['port'];
	$db = $_GET['db'];
	$dbuser = $_GET['dbuser'];
	$dbpw = $_GET['dbpw'];
}


//Filtervariablen
$projectStatus = $_GET['ps'];
$taskFilter = $_GET['tf'];
$projectShowInactiv = $_GET['sip'];

//Variablen fuer Task-Daten (ids,startzeiten,endzeiten,arbeitszeit,notizen)
$tasks = array();
$tasks_ids = array();
$tasks_percents = array();
$tasks_dates = array();
$tasks_times = array();
$tasks_notes = array();

//Variablen für mySQL-Anfragen
$con_id;
$query1="";
$query2="";
$query3="";
$query4="";
$result1;
$result2;
$result3;
$result_row1;
$result_row2;
$result_row3;

$user_id = 0;
$task_id = 0;
$last_project_id = 0;


function report($user_id,$user){

	$zeit = time(); // Aktuelle Zeit in Sekunden
	$datum = getdate($zeit);

	if($datum[mon] < 10) $datum[mon] = "0".$datum[mon];
	if($datum[mday] < 10) $datum[mday] = "0".$datum[mday];

	$query = "SELECT `task_log_name`, `task_log_hours`, `task_log_description`, `task_log_date` FROM `task_log` WHERE `task_log_creator` =".$user_id."  ORDER BY `task_log_date`";
	$result = mysql_query($query);

	$report .= "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>" .
			"<html>" .
			"<head>" .
			"<title>Daily report of ".$user." at ".$datum[mday].".".$datum[mon].".".$datum[year]."</title>" .
			"</head>" .
			"<body>" .
			"<h2>Daily report of ".$user." at ".$datum[mday].".".$datum[mon].".".$datum[year]."</h2>" .
			"<table border=1>" .
			"<tr><th width='300' >Task</th><th width='60'>Time</th><th width='40'>Duration</th><th width='500'>Notes</th></th>";

			//als XML?
			//header("Content-type: application/vnd-ms-excel");
			//header("Content-Disposition: attachment; filename=export.xls");

			while($result_row = mysql_fetch_array($result)){

				//Datumsformat in Datenbank 2005-10-19 13:55:01
				$db_taskdate = substr($result_row['task_log_date'], 0, 10);
				$db_tasktime = substr($result_row['task_log_date'], 11, 8);
				$act_date = $datum[year]."-".$datum[mon]."-".$datum[mday];

				//Zeit aus dotproject umrechnen in Normalzeit
				$task_timearray = explode(".",$result_row['task_log_hours']);
				$hrs = $task_timearray[0];
				$min  = strval(round(intval($task_timearray[1])*(3/5)));
				if($min < 10) $min = "0".$min;

				$result_row['task_log_hours'] = $hrs.".".$min;


				if($db_taskdate == $act_date){
					$report .= "<tr>";
					$report .= "<td valign='top'>".$result_row['task_log_name']."</td>";
					$report .= "<td valign='top' align='right'>".$db_tasktime."</td>";
					$report .= "<td valign='top' align='right'>".$result_row['task_log_hours']."</td>";
					$report .= "<td valign='top'>".$result_row['task_log_description']."</td>";
					$report .= "<tr>";
				}
			}

	$report .=	"</tr>" .
			"</table>" .
			"</body>" .
			"</html>";

	return $report;
	mysql_free_result($result);

}

//----------------------------------- Programm --------------------------------------------------------------------------


//Benutzer
if($user ==""){
	$info_msg_1.="0001-0"; //Geben Sie Ihren Benutzernamen an!
	$info_msg_2.="0001-1"; //Rechtsklick - 'Einstellungen' - 'Benutzer'
}else{

		/*
		if( !login ($pw) ){
			$info_msg_1.= "0006-0";	//Benutzernamen / Passwort prüfen!
			$info_msg_2.= "0006-1";	//Rechtsklick - 'Einstellungen' - 'Benutzer'
		}
		else 
		{	
		*/
			//Datenbank verbinden
			if(!$con_id = @mysql_connect("$host:$port",$dbuser,$dbpw)) {
				$err_no = mysql_errno();
				$log .="mySQL-Fehlernummer: ".$err_no."\nErrormsg.: ".mysql_error()."";
				switch($err_no){
					case 2005:
						$info_msg_1.= "0002-0";	//Unbekannter mySQL-Server Host!
						$info_msg_2.= "0002-1";	//Host: '".$host."'
						break;
					case 2013:
						$info_msg_1.= "0002-0";	//Unbekannter mySQL-Server Host!
						$info_msg_2.= "0002-1";	//Host: '".$host."'
						break;
					case 2003:
						$info_msg_1.= "0003-0";	//Keine Verbindung zum mySQL-Server!
						$info_msg_2.= "0003-1";	//Status \ Port prüfen: '".$port."'
						break;
					case 1045:
						$info_msg_1.= "0004-0";	//Zugriff auf Datenbank verweigert!
						$info_msg_2.= "0004-1";	//für Nutzer: '".$dbuser."'@'".$host."'
						break;
					default:
						$info_msg_1.="";
						$info_msg_2.="";
				}
			}
			else if(!@mysql_select_db($db,$con_id)){
				$info_msg_1.= "0005-0";	//Datenbank exisitiert nicht!
				$info_msg_2.= "0005-1";	//Datenbank: ".$db
			}else {
				//------------------------------------Neue Projekt-Task-Daten aus Datenbank holen--------------------------------------------------------------
				//user-id
				
				//-------
				$query1="SELECT `user_id` FROM `users` WHERE `user_username`='".$user."' AND `user_password`='".$pw."'";
				$result1=mysql_query($query1,$con_id);
		
				if(mysql_num_rows($result1)==0){
					$info_msg_1.= "0006-0";	//Benutzernamen / Passwort prüfen!
					$info_msg_2.= "0006-1";	//Rechtsklick - 'Einstellungen' - 'Benutzer'
		
					mysql_free_result($result1);
				}else {
				//-------	
					while($result_row1 = mysql_fetch_array($result1)){
						$user_id = $result_row1['user_id'];
					}
		
					//--------------------------- Zeit, Notizen in Datenbank schreiben/updaten ----------------------------------
					if(isset($_GET['ids'])){
						$tasks = explode("]|[",$_GET['tasks']);
						$tasks_ids = explode("]|[",$_GET['ids']);
						$tasks_percents = explode("]|[",$_GET['percents']);
						$tasks_times = explode("]|[",$_GET['times']);
						$tasks_dates = explode("]|[",$_GET['dates']);
						$tasks_notes = explode("]|[",$_GET['notes']);
		
		
						if(sizeof($tasks_ids)!=0){
							for($j=0;$j < sizeof($tasks_ids);$j++){
									if($tasks_ids[$j] !=0){
		
										//Zeit an dotproject anpassen
										$timearray = explode(".",$tasks_times[$j]);
										$hrs = $timearray[0];
										$min = intval($timearray[1]);
										
										$log .="minuten erst:".$min;
										
										$min  = strval(round($min*(5/3)));
										$log .="\nminuten danach:".$min;
										
										if($min <10)
											$tasks_times[$j] = $hrs.".0".$min;
										else	$tasks_times[$j] = $hrs.".".$min;
										
										$log .="\nZeit in dotProject-format umgerechnet:".$tasks_times[$j];
										
										
										$query3 ="INSERT INTO `task_log` (`task_log_task`, `task_log_name`, `task_log_description`, `task_log_creator`, `task_log_hours`, `task_log_date`)".
												"VALUES (".$tasks_ids[$j].", '".$tasks[$j]."', '".utf8_decode($tasks_notes[$j])."', ".$user_id.", ".$tasks_times[$j].", '".$tasks_dates[$j]."')";
		
										if($tasks_percents[$j] != ""){
											$query4 ="UPDATE `tasks` SET `task_percent_complete`=".$tasks_percents[$j]."  WHERE `task_id`=".$tasks_ids[$j]."";
											mysql_query($query4);
										}
		
										if(mysql_query($query3) == true){
											//Tasks-Ids übergeben die in Datenbank aktualisiert wurden
											$info_updated_tasks .="<id>".$tasks_ids[$j]."</id>";
										}
									}
							}
						}
					}
					//---------------------------------- Report erstellen --------------------------------------------------
		
		
					if(isset($_GET['report'])){
						$report = report($user_id,$user);
						$isreport = true;
					}
					else $isreport = false;
		
		
					$select = 	"p.project_id, p.project_name,p.project_description, p.project_status,p.project_start_date,p.project_end_date,p.project_actual_end_date,p.project_company,t.task_id,t.task_name,t.task_description,t.task_percent_complete";
					$tables = "user_tasks AS ut JOIN tasks AS t JOIN projects AS p";
					$order_by = "ORDER BY p.project_name,t.task_name";
					$join_condition = "ON (ut.user_id = ".$user_id." AND ut.task_id = t.task_id)";
		
					//Spqziele Query-Bedingungen
		
					//Tasksfilter = 1 -> Tasks mit 100% ausfiltern
					if($taskFilter == 1)
						$task_percent_complete ="AND task_percent_complete < 100";
					else $task_percent_complete = "";
					//Projektstatus 'x' -> alle Tasks anzeigen
					if($projectStatus != 'x')
						$project_status = "AND p.project_status =".$projectStatus;
					else  $project_status ="";
					//Inktive Projekte nicht anzeigen -> $projectShowInactiv == 0
					if($projectShowInactiv == 0)
						$project_activ = "AND p.project_active = 1"; //nur aktive Projekte auslesen
					else $project_activ = "";
		
					#$project_running = " AND p.project_status > 0 AND p.project_status < 7 ";
					
					$query2 = "SELECT ".$select." FROM ".$tables." ".$join_condition." AND t.task_project = p.project_id ".$project_status." ".$task_percent_complete." ".$project_activ." ".$order_by;
					#echo ".". $query2;
					$result2 = mysql_query($query2);
		
					if(mysql_num_rows($result2) == 0){
						$info_msg_1 .= "0007-0";	//Keine Tasks vorhanden!
						$info_msg_2 .= "0007-1";	//getProjectStatus
					}else{
		
						$xml_content .="\n<projects>";
		
		
						//Inhalt aus Datenbank holen und in String schreiben
						while($result_row2 = mysql_fetch_array($result2)){
							//Ueberpruefen ob neues Projekt beginnt
							if($last_project_id != $result_row2['project_id']){
								//letzte Projekt beenden
								if($last_project_id != 0) $xml_content .="\n</item>";
								//Letze Projektname sichern
								$last_project_id = $result_row2['project_id'];
								
								$start_date=$result_row2['project_start_date']; 
								$end_date=$result_row2['project_end_date'];
								$act_end_date=$result_row2['project_actual_end_date'];
		
								if($start_date !="" && $start_date !="0000-00-00 00:00:00")
								$start_date=substr($start_date,8,2).".".substr($start_date,5,2).".".substr($start_date,2,2);
								else $start_date="";
								
								if($end_date !="" && $end_date !="0000-00-00 00:00:00")
								$end_date=substr($end_date,8,2).".".substr($end_date,5,2).".".substr($end_date,2,2);
								else $end_date="";
								
								if($act_end_date !="" && $act_end_date !="0000-00-00 00:00:00")
								$act_end_date=substr($act_end_date,8,2).".".substr($act_end_date,5,2).".".substr($act_end_date,2,2);
								else $act_end_date="-";
								
								$query5 = "SELECT `company_name` FROM `companies` WHERE `company_id`=".$result_row2['project_company'];
								$result5 = mysql_query($query5);
								$result_row5 = mysql_fetch_array($result5);
								
								//Projektnamen schreiben
								$xml_content .= "\n<item>\n<company>".$result_row5['company_name']."</company>";
								$xml_content .= "\n<project>";
								$xml_content .= $result_row2['project_name'];
								$xml_content .= "</project>";
								$xml_content .= "<pid>".$result_row2['project_id']."</pid>";
								$xml_content .= "\n<startdate>".$start_date."</startdate>";
								$xml_content .= "\n<enddate>".$end_date."</enddate>";
								$xml_content .= "\n<actenddate>".$act_end_date."</actenddate>";
								
								$xml_content .= "\n<state>".$result_row2['project_status']."</state>";
								$xml_content .= "\n<pdescription>".$result_row2['project_description']."</pdescription>";
							}
							//Tasknamen schreiben
							$xml_content .="\n<task>\n<id>".$result_row2['task_id']."</id>\n<name>".$result_row2['task_name']."</name>" .
										   "\n<percent>".$result_row2['task_percent_complete']."</percent>\n" .
										   "\n<tdescription>".$result_row2['task_description']."</tdescription></task>";
						}
						$xml_content .="\n</item>\n</projects>";
					}
					mysql_free_result($result1);
					mysql_free_result($result2);
		}
	}
}


$info_msg_1 .= "</info>";
$info_msg_2 .= "</info>";
$info_updated_tasks .= "</updated>";

if($isreport == false)
	$file_content .= "\n<content>\n".$info_msg_1."\n".$info_msg_2."\n".$info_updated_tasks.utf8_encode($xml_content).utf8_encode($report)."\n</content>";
else $file_content .= utf8_encode($report);

echo $file_content;

//Logfile zum Testen

$fp = fopen ("log_server.txt","w");
fwrite($fp,$log);
fclose($fp);

?>