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

Copyright (C) 2006 Marco Aurelio Graciotto Silva <magsilva@gmail.com>
*/

class BO2XML {

    function BO2XML()
    {
    $dom = domxml_new_doc("1.0");
    $dom->create_element("id");
    $dom->append_child("id");
    
    
    doc = domxml_new_doc("1.0");
$root = $doc->create_element("HTML");
$root = $doc->append_child($root);
$head = $doc->create_element("HEAD");
$head = $root->append_child($head);
$title = $doc->create_element("TITLE");
$title = $head->append_child($title);
$text = $doc->create_text_node("This is the title");
$text = $title->append_child($text);
$doc->dump_file("/tmp/test.xml", false, true);
    
    object(adofetchobj)(26) {
  ["company_name"]=> string(18) "Ironia Corporation"
  ["user_name"]=> string(30) "Marco Aurélio Graciotto Silva"
  ["project_id"]=> string(1) "3"
  ["project_company"]=> string(1) "1"
  ["project_department"]=> string(1) "0"
  ["project_name"]=> string(11) "Teste123456"
  ["project_short_name"]=> string(10) "Teste12345"
  ["project_owner"]=> string(1) "3"
  ["project_url"]=> NULL
  ["project_demo_url"]=> NULL
  ["project_start_date"]=> string(19) "2006-12-19 00:00:00"
  ["project_end_date"]=> NULL
  ["project_actual_end_date"]=> NULL
  ["project_status"]=> string(1) "1"
  ["project_percent_complete"]=> NULL
  ["project_color_identifier"]=> string(6) "FFFFFF"
  ["project_description"]=> string(9) "raasdffga"
  ["project_target_budget"]=> string(4) "0.00"
  ["project_actual_budget"]=> string(4) "0.00"
  ["project_creator"]=> string(1) "1"
  ["project_active"]=> string(1) "1"
  ["project_private"]=> string(1) "0"
  ["project_departments"]=> NULL
  ["project_contacts"]=> NULL
  ["project_priority"]=>  string(1) "0"
  ["project_type"]=> string(1) "2"
}
    
    var $project_id = NULL;
	var $project_company = NULL;
	var $project_department = NULL;
	var $project_name = NULL;
	var $project_short_name = NULL;
	var $project_owner = NULL;
	var $project_url = NULL;
	var $project_demo_url = NULL;
	var $project_start_date = NULL;
	var $project_end_date = NULL;
	var $project_actual_end_date = NULL;
	var $project_status = NULL;
	var $project_percent_complete = NULL;
	var $project_color_identifier = NULL;
	var $project_description = NULL;
	var $project_target_budget = NULL;
	var $project_actual_budget = NULL;
	var $project_creator = NULL;
	var $project_active = NULL;
	var $project_private = NULL;
	var $project_departments= NULL;
	var $project_contacts = NULL;
	var $project_priority = NULL;
	var $project_type = NULL;
    
}
?>