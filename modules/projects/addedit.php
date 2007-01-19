<?php /* PROJECTS $Id: addedit.php,v 1.93.6.1 2006/04/06 08:35:09 cyberhorse Exp $ */
$project_id = intval(dPgetParam($_GET, 'project_id', 0));
$company_id = intval(dPgetParam($_GET, 'company_id', 0));
$contact_id = intval(dPgetParam($_GET, 'contact_id', 0));

// Check permissions for this record
$perms =& $AppUI->acl();
$canEdit = $perms->checkModuleItem($m, 'edit', $project_id);
$canAdd = $perms->checkModuleItem($m, 'add');
if (($project_id > 0 && !$canEdit) || ($project_id == 0 && !$canAdd)) {
	$AppUI->redirect( 'm=public&a=access_denied' );
}

// Get a list of permitted companies
require_once( $AppUI->getModuleClass('companies'));
$row = new CCompany();
$companies = $row->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
// TODO: Why merge this array?
$companies = arrayMerge(array('0'=>''), $companies);

// If creating a project, check the company count
if ($project_id == 0 && count($companies) < 2) {
	// TODO: Why < 2? Yeah, we merged an empty company, so this is a way to
	// garantee thatthere is, at least, one company to choose for this project.
	$AppUI->setMsg( "noCompanies", UI_MSG_ERROR, true );
	$AppUI->redirect();
}


// Pull users
$q = new DBQuery;
$q->addTable('users','u');
$q->addTable('contacts','con');
$q->addQuery('user_id');
$q->addQuery('CONCAT_WS(", ",contact_last_name,contact_first_name)');
$q->addOrder('contact_last_name');
$q->addWhere('u.user_contact = con.contact_id');
$users = $q->loadHashList();

// Load the project data (if editing) or just create an empty project.
$row = new CProject();
if ($project_id > 0 ) {
	if (! $row->load( $project_id, false )) {
		// Tried to edit an invalid project (probably does not exist).
		$AppUI->setMsg( 'Project' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect();
	}
} else if ($project != 0) {
	// Tried to edit an invalid project.
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}


// Set the project's company
if ($project_id == 0 && $company_id > 0) {
	$row->project_company = $company_id;
}

// Add in the existing company if for some reason it is disallowed
if ($project_id > 0 && !array_key_exists($row->project_company, $companies)) {
	$q = new DBQuery;
	$q->addTable('companies');
	$q->addQuery('company_name');
	$q->addWhere('companies.company_id = '.$row->project_company);
	$sql = $q->prepare();
	$q->clear();
	$companies[$row->project_company] = db_loadResult($sql);
}

// Get critical tasks (criteria: task_end_date)
$criticalTasks = ($project_id > 0) ? $row->getCriticalTasks() : NULL;

// Get ProjectPriority from sysvals
$projectPriority = dPgetSysVal( 'ProjectPriority' );

// Format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = new CDate( $row->project_start_date );
$end_date = intval( $row->project_end_date ) ? new CDate( $row->project_end_date ) : null;
$actual_end_date = intval( $criticalTasks[0]['task_end_date'] ) ? new CDate( $criticalTasks[0]['task_end_date'] ) : null;
$style = (( $actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

// Setup the title block
$ttl = $project_id > 0 ? "Edit Project" : "New Project";
$titleBlock = new CTitleBlock( $ttl, 'applet3-48.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=projects", "projects list" );
if ($project_id != 0) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$project_id", "view this project" );
}
$titleBlock->show();

// Build display list for departments
$company_id = $row->project_company;
$selected_departments = array();
if ($project_id > 0) {
	$q =& new DBQuery;
	$q->addTable('project_departments');
	$q->addQuery('department_id');
	$q->addWhere('project_id = ' . $project_id);
	$res =& $q->exec();
	for ( $res; ! $res->EOF; $res->MoveNext()) {
		$selected_departments[] = $res->fields['department_id'];
	}
	$q->clear();
}
$departments_count = 0;
$department_selection_list = getDepartmentSelectionList($company_id, $selected_departments);
if ($department_selection_list!=""){
	$department_selection_list = $AppUI->_("Departments")."<br /><select name='dept_ids[]' size='$departments_count' multiple style=''>$department_selection_list</select>";
} else {
	$department_selection_list = "<input type='button' class='button' value='".$AppUI->_("Select department...")."' onclick='javascript:popDepartment();' /><input type=\"hidden\" name=\"project_departments\"";
}

// Get contacts list
$selected_contacts = array();
if ($project_id > 0) {
	$q =& new DBQuery;
	$q->addTable('project_contacts');
	$q->addQuery('contact_id');
	$q->addWhere('project_id = ' . $project_id);
	$res =& $q->exec();
	for ( $res; ! $res->EOF; $res->MoveNext()) {
		$selected_contacts[] = $res->fields['contact_id'];
	}
	$q->clear();
}
if ($project_id == 0 && $contact_id > 0) {
	$selected_contacts[] = "$contact_id";
}
?>


<link rel="stylesheet" type="text/css" media="all" href="<?php echo $dPconfig['base_url'];?>/lib/calendar/calendar-dp.css" title="blue" />

<!-- Import functions -->
<script type="text/javascript">
var selected_contacts_id = "<?php echo implode(',', $selected_contacts); ?>";
var selected_departments_id = "<?php echo implode(',', $selected_departments); ?>";
</script>
<script type="text/javascript" src="<?php echo $dPconfig['base_url'];?>/modules/projects/project.js"></script>

<!-- import the calendar script -->
<script type="text/javascript" src="<?php echo $dPconfig['base_url'];?>/lib/calendar/calendar.js"></script>

<!-- import the language module -->
<script type="text/javascript" src="<?php echo $dPconfig['base_url'];?>/lib/calendar/lang/calendar-<?php echo $AppUI->user_locale; ?>.js" /></script>



<form name="editFrm" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
	<input type="hidden" name="project_creator" value="<?php echo $AppUI->user_id;?>" />
	<input name='project_contacts' type='hidden' value="<?php echo implode(',', $selected_contacts); ?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Name');?></td>
			<td width="100%" colspan="2">
				<input type="text" name="project_name" value="<?php echo dPformSafe( $row->project_name );?>" size="25" maxlength="50" onBlur="setShort();" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner');?></td>
			<td colspan="2">
<?php echo arraySelect( $users, 'project_owner', 'size="1" style="width:200px;" class="text"', $row->project_owner? $row->project_owner : $AppUI->user_id ) ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company');?></td>
			<td width="100%" nowrap="nowrap" colspan="2">
<?php
		echo arraySelect( $companies, 'project_company', 'class="text" size="1"', $row->project_company );
?> *</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date');?></td>
			<td nowrap="nowrap">	 <input type="hidden" name="project_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
				<input type="text" class="text" name="start_date" id="date1" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />

				<a href="#" onClick="popCalendar( 'start_date', 'start_date');">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
			<td rowspan="6" valign="top">
					<?php
						if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) {
							echo "<input type='button' class='button' value='".$AppUI->_("Select contacts...")."' onclick='javascript:popContacts();' />";
						}
						// Let's check if the actual company has departments registered
						if ($department_selection_list != "") {
					?>
						<br />
						<?php echo $department_selection_list; ?>
					<?php
						}
					?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Finish Date');?></td>
			<td nowrap="nowrap">	<input type="hidden" name="project_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
				<input type="text" class="text" name="end_date" id="date2" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />

				<a href="#" onClick="popCalendar('end_date', 'end_date');">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget');?> <?php echo $dPconfig['currency_symbol'] ?></td>
			<td>
				<input type="Text" name="project_target_budget" value="<?php echo @$row->project_target_budget;?>" maxlength="10" class="text" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1"></td>
		</tr>
<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Finish Date');?></td>
			<td nowrap="nowrap">
				<?php if ($project_id > 0) { ?>
				<?php echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id='.$criticalTasks[0]['task_id'].'">' : '';?>
				<?php echo $actual_end_date ? '<span '. $style.'>'.$actual_end_date->format( $df ).'</span>' : '-';?>
				<?php echo $actual_end_date ? '</a>' : '';?>
				<?php } else { echo $AppUI->_('Dynamically calculated');} ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Budget');?> <?php echo $dPconfig['currency_symbol'] ?></td>
			<td>
				<input type="text" name="project_actual_budget" value="<?php echo @$row->project_actual_budget;?>" size="10" maxlength="10" class="text"/>
			</td>
		</tr>
		<tr>
			<td colspan="3"><hr noshade="noshade" size="1"></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL');?></td>
			<td colspan="2">
				<input type="text" name="project_url" value='<?php echo @$row->project_url;?>' size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL');?></td>
			<td colspan="2">
				<input type="Text" name="project_demo_url" value='<?php echo @$row->project_demo_url;?>' size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" colspan="3">
			<?php
				require_once("./classes/CustomFields.class.php");
				$custom_fields = New CustomFields( $m, $a, $row->project_id, "edit" );
				$custom_fields->printHTML();
			?>
			</td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Priority' );?></td>
			<td nowrap>
				<?php echo arraySelect( $projectPriority, 'project_priority', 'size="1" class="text"', $row->project_priority, true );?> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name');?></td>
			<td colspan="3">
				<input type="text" name="project_short_name" value="<?php echo dPformSafe( @$row->project_short_name ) ;?>" size="10" maxlength="10" class="text" /> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Color Identifier');?></td>
			<td nowrap="nowrap">
				<input type="text" name="project_color_identifier" value="<?php echo (@$row->project_color_identifier) ? @$row->project_color_identifier : 'FFFFFF';?>" size="10" maxlength="6" onBlur="setColor();" class="text" /> *
			</td>
			<td nowrap="nowrap" align="right">
				<a href="#" onClick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scollbars=false');"><?php echo $AppUI->_('change color');?></a>
			</td>
			<td nowrap="nowrap">
				<span id="test" title="test" style="background:#<?php echo (@$row->project_color_identifier) ? @$row->project_color_identifier : 'FFFFFF';?>;"><a href="#" onClick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scollbars=false');"><img src="./images/shim.gif" border="1" width="40" height="20" /></a></span>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Type');?></td>
			<td colspan="3">
				<?php echo arraySelect( $ptype, 'project_type', 'size="1" class="text"', $row->project_type, true );?> *
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<table width="100%" bgcolor="#cccccc">
				<tr>
					<td><?php echo $AppUI->_('Status');?> *</td>
					<td nowrap="nowrap"><?php echo $AppUI->_('Progress');?></td>
					<td><?php echo $AppUI->_('Active');?>?</td>
				</tr>
				<tr>
					<td>
						<?php echo arraySelect( $pstatus, 'project_status', 'size="1" class="text"', $row->project_status, true ); ?>
					</td>
					<td>
						<strong><?php echo sprintf( "%.1f%%", @$row->project_percent_complete);?></strong>
					</td>
					<td>
						<input type="checkbox" value="1" name="project_active" <?php echo $row->project_active||$project_id==0 ? 'checked="checked"' : '';?> />
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<?php  
			// Retrieve projects that the user can access
			$objProject = new CProject();
			$allowedProjects = $objProject->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name' );
			
			$q  = new DBQuery;
			$q->addTable('projects', 'p');
			$q->addTable('tasks', 't');
			$q->addQuery('p.project_id, p.project_name');
			$q->addWhere('t.task_project = p.project_id');
			if ( count($allowedProjects) > 0 ) {
				$q->addWhere('(p.project_id IN (' .
				implode (',', array_keys($allowedProjects)) . '))');
			}
			$q->addOrder('p.project_name');
			
			$importList = $q->loadHashList();
			$importList = arrayMerge( array( '0'=> $AppUI->_('none') ), $importList);
		?>
		<tr>
			<td align="left" nowrap="nowrap">
				<?php   echo $AppUI->_('Import tasks from');?>:<br/>
			</td>
			<td colspan="3">
				<?php echo arraySelect( $importList, 'import_tasks_from', 'size="1" class="text"', null, false ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<?php echo $AppUI->_('Description');?><br />
				<textarea name="project_description" cols="50" rows="10" wrap="virtual" class="textarea"><?php echo dPformSafe( @$row->project_description );?></textarea>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td>
		<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel');?>" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=projects';}" />
	</td>
	<td align="right">
		<?php
			$args .= "'";
			$args .= $AppUI->_('projectsValidName', UI_OUTPUT_JS);
			$args .= "', '";
			$args .= $AppUI->_('projectsColor', UI_OUTPUT_JS);
			$args .= "', '";
			$args .= $AppUI->_('projectsBadCompany', UI_OUTPUT_JS);
			$args .= "'";
		?>
		<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt(<?php echo $args ?>);" />
	</td>
</tr>
</form>
</table>
* <?php echo $AppUI->_('requiredField');?>
