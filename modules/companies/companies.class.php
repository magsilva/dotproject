<?php /* COMPANIES $Id: companies.class.php,v 1.9 2004/01/29 06:30:43 ajdonnison Exp $ */
/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision: 1.9 $
*/

require_once( $AppUI->getSystemClass ('dp' ) );

/**
 *	Companies Class
 *	@todo Move the 'address' fields to a generic table
 */
class CCompany extends CDpObject {
/** @var int Primary Key */
	var $company_id = NULL;
/** @var string */
	var $company_name = NULL;

// these next fields should be ported to a generic address book
	var $company_phone1 = NULL;
	var $company_phone2 = NULL;
	var $company_fax = NULL;
	var $company_address1 = NULL;
	var $company_address2 = NULL;
	var $company_city = NULL;
	var $company_state = NULL;
	var $company_zip = NULL;
	var $company_email = NULL;

/** @var string */
	var $company_primary_url = NULL;
/** @var int */
	var $company_owner = NULL;
/** @var string */
	var $company_description = NULL;
/** @var int */
	var $company_type = null;
	
	var $company_custom = null;

	function CCompany() {
		$this->CDpObject( 'companies', 'company_id' );
	}

// overload check
	function check() {
		if ($this->company_id === NULL) {
			return 'company id is NULL';
		}
		$this->company_id = intval( $this->company_id );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		$tables[] = array( 'label' => 'Projects', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_company' );
		$tables[] = array( 'label' => 'Departments', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_company' );
		$tables[] = array( 'label' => 'Users', 'name' => 'users', 'idfield' => 'user_id', 'joinfield' => 'user_company' );
	// call the parent class method to assign the oid
		return CDpObject::canDelete( $msg, $oid, $tables );
	}
}

function getDepartmentSelectionList($company_id, $checked_array = array(), $dept_parent=0, $spaces = 0){
	global $departments_count;
	$parsed = '';

	if($departments_count < 6) $departments_count++;
	
	$q  = new DBQuery;
	$q->addTable('departments');
	$q->addQuery('dept_id, dept_name');
	$q->addWhere("dept_parent = '$dept_parent' and dept_company = '$company_id'");
	$depts_list = $q->loadHashList("dept_id");

	foreach($depts_list as $dept_id => $dept_info){
		$selected = in_array($dept_id, $checked_array) ? "selected" : "";

		$parsed .= "<option value='$dept_id' $selected>".str_repeat("&nbsp;", $spaces).$dept_info["dept_name"]."</option>";
		$parsed .= getDepartmentSelectionList($company_id, $checked_array, $dept_id, $spaces+5);
	}
	
	return $parsed;
}


?>
