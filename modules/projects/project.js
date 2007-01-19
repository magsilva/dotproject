function setColor(color)
{
	var f = document.editFrm;
	if (color) {
		f.project_color_identifier.value = color;
	}
	// test.style.background = f.project_color_identifier.value;
	//fix for mozilla: does this work with ie? opera ok.
	document.getElementById('test').style.background = '#' + f.project_color_identifier.value;
}

function setShort()
{
	var f = document.editFrm;
	var x = 10;
	if (f.project_name.value.length < 11) {
		x = f.project_name.value.length;
	}
	if (f.project_short_name.value.length == 0) {
		f.project_short_name.value = f.project_name.value.substr(0,x);
	}
}

var calendarField = '';
var calWin = null;

function popCalendar(field)
{
	calendarField = field;
	idate = eval( 'document.editFrm.project_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=280, height=250, scollbars=false' );
}

/**
 * @param string Input date in the format YYYYMMDD
 *@param string Formatted date
 */
function setCalendar(idate, fdate)
{
	fld_date = eval('document.editFrm.project_' + calendarField);
	fld_fdate = eval('document.editFrm.' + calendarField);
	fld_date.value = idate;
	fld_fdate.value = fdate;

	// set end date automatically with start date if start date is after end date
	if (calendarField == 'start_date') {
		if( document.editFrm.end_date.value < idate) {
			document.editFrm.project_end_date.value = idate;
			document.editFrm.end_date.value = fdate;
		}
	}
}

function submitIt(projectValidName, projectsColor, projectsBadCompany) {
	var f = document.editFrm;
	var msg = '';

	if (f.project_name.value.length < 3) {
		msg += "\n" + projectsValidName;
		f.project_name.focus();
	}
	if (f.project_color_identifier.value.length < 3) {
		msg += "\n" + projectsColor;
		f.project_color_identifier.focus();
	}
	if (f.project_company.options[f.project_company.selectedIndex].value < 1) {
		msg += "\n" + projectsBadCompany;
		f.project_name.focus();
	}
	if (msg.length < 1) {
		f.submit();
	} else {
		alert(msg);
	}
}

function popContacts() {
	window.open('./index.php?m=public&a=contact_selector&dialog=1&call_back=setContacts&selected_contacts_id='+selected_contacts_id, 'contacts','height=600,width=400,resizable,scrollbars=yes');
}

function setContacts(contact_id_string){
	if(!contact_id_string){
		contact_id_string = "";
	}
	document.editFrm.project_contacts.value = contact_id_string;
	selected_contacts_id = contact_id_string;
}


function popDepartment() {
	var f = document.editFrm;
	var url = './index.php?m=public&a=selector&dialog=1&callback=setDepartment&table=departments&company_id='
            + f.project_company.options[f.project_company.selectedIndex].value
            + '&dept_id='
            + selected_departments_id;
	//prompt('',url);
	window.open(url,'dept','left=50,top=50,height=250,width=400,resizable');
	// window.open('./index.php?m=public&a=selector&dialog=1&call_back=setDepartment&selected_contacts_id='+selected_contacts_id, 'contacts','height=600,width=400,resizable,scrollbars=yes');
}

function setDepartment(department_id_string) {
	if(!department_id_string){
		department_id_string = "";
	}
	document.editFrm.project_departments.value = department_id_string;
	selected_departments_id = department_id_string;
}