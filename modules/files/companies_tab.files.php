<?php /* PROJECTS $Id: companies_tab.files.php,v 1.1.10.3 2007/03/06 00:34:41 merlinyoda Exp $ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly.');
}

GLOBAL $AppUI, $company_id, $deny, $canRead, $canEdit, $dPconfig;
require_once( $AppUI->getModuleClass( 'files' ) );
   
$cfObj = new CFileFolder();
global $allowed_folders_ary;
$allowed_folders_ary = $cfObj->getAllowedRecords($AppUI->user_id);
global $denied_folders_ary;
$denied_folders_ary = $cfObj->getDeniedRecords($AppUI->user_id);

if ( count( $allowed_folders_ary ) < $cfObj->countFolders() ) {
$limited = true;
}
if (!$limited) {
$canEdit = true;
} elseif ($limited AND array_key_exists($folder, $allowed_folders_ary)) {
$canEdit = true;
} else {
$canEdit = false;
}
$showProject = false;
require( DP_BASE_DIR . '/modules/files/folders_table.php' );
?>
