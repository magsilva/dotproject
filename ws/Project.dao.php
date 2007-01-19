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

/**
* This will look like this:
* Taken from http://br.php.net/manual/en/ref.soap.php#54955
* 
* <complexType name="MyWSDLStructure">
* <sequence>
*   <element name="MyProperty1" type="xsd:integer"/>
*   <element name="MyProperty2" type="xsd:string"/>
* </sequence>
* </complexType>
*/
class ProjectDAO
{
	public $id;
	public $company;
	public $department;
	public $name;
	public $shortName;
	public $owner;
	public $url;
	public $demoUrl;
	public $startDate;
	public $endDate;
	public $actualEndDate;
	public $status;
	public $percentComplete;
	public $colorIdentifier;
	public $description;
	public $targetBudget;
	public $actualBudget;
	public $creator;
	public $active;
	public $private;
	public $departments;
	public $contacts;
	public $priority;
	public $type;
	
	/**
	 * Initialize a Data Access Object (DAO) for a task. We may get the data
	 * from the database (direct access) or from a CProject object.
	 * 
	 * @arg $task If an integer, load from database. Otherwise, load
	 * from the object.
	 */
	function ProjectDAO($project = null)
	{
		if ($project != null) {
			if (is_int($project)) {
				$this->loadFromDatabase($project);
			} else if (is_array($project)) {
				$this->loadFromArray($project);
			} else {
				$this->loadFromObject($project);
			}
		}
	}
	
	/**
	 * Read the task's data from the database.
	 */
	function loadFromDatabase($project_id)
	{
		$dotproject = new DotProject();
		$db = $dotproject->connectToDatabase();
		$stmt = $db->Prepare('select * from projects where project_id=?');
		$rs = $db->Execute($stmt, $project_id);
		$project = $rs->fields;

		$this->id = $project['project_id'];
		$this->company = $project['project_company'];
		$this->department = $project['project_departament'];
		$this->name = $project['project_name'];
		$this->shortName = $project['project_short_name'];
		$this->owner = $project['project_owner'];
		$this->url = $project['project_url'];
		$this->demoUrl = $project['project_demo_url'];
		$this->startDate = $project['project_start_date'];
		$this->endDate = $project['project_end_date'];
		$this->actualEndDate = $project['project_actual_end_date'];
		$this->status = $project['project_status'];
		$this->percentComplete = $project['project_percent_complete'];
		$this->colorIdentifier = $project['project_color_identifier'];
		$this->description = $project['project_description'];
		$this->targetBudget = $project['project_target_budget'];
		$this->actualBudget = $project['project_actual_budget'];
		$this->creator = $project['project_creator'];
		$this->active = $project['project_active'];
		$this->private = $project['project_private'];
		
		if ($project->departaments != null) {
			$this->departaments = array();
			foreach ($project->project_departaments as $departament) {
				// TODO: Implement the DepartamentDAO
				// $departamentDAO = new DepartamentDAO($contact);
				// $this->departament[] = $departamentDAO;
			}
		}
		
		if ($project->contacts != null) {
			$this->concacts = array();
			foreach ($project->project_contacts as $contact) {
				// TODO: Implement the ContactDAO
				// $contactDAO = new ConcactDAO($contact);
				// $this->concacts[] = $contactDAO;
			}
		}
		
		$this->priority = $project['project_priority'];
		$this->type = $project['project_type'];		
	}


	/**
	 * Read the project's data from a array.
	 */
	function loadFromArray($project)
	{	
		$this->id = $project['project_id'];
		$this->company = $project['project_company'];
		$this->department = $project['project_departament'];
		$this->name = $project['project_name'];
		$this->shortName = $project['project_short_name'];
		$this->owner = $project['project_owner'];
		$this->url = $project['project_url'];
		$this->demoUrl = $project['project_demo_url'];
		$this->startDate = $project['project_start_date'];
		$this->endDate = $project['project_end_date'];
		$this->actualEndDate = $project['project_actual_end_date'];
		$this->status = $project['project_status'];
		$this->percentComplete = $project['project_percent_complete'];
		$this->colorIdentifier = $project['project_color_identifier'];
		$this->description = $project['project_description'];
		$this->targetBudget = $project['project_target_budget'];
		$this->actualBudget = $project['project_actual_budget'];
		$this->creator = $project['project_creator'];
		$this->active = $project['project_active'];
		$this->private = $project['project_private'];
		
		if ($project->departaments != null) {
			$this->departaments = array();
			foreach ($project->project_departaments as $departament) {
				// TODO: Implement the DepartamentDAO
				// $departamentDAO = new DepartamentDAO($contact);
				// $this->departament[] = $departamentDAO;
			}
		}
		
		if ($project->contacts != null) {
			$this->concacts = array();
			foreach ($project->project_contacts as $contact) {
				// TODO: Implement the ContactDAO
				// $contactDAO = new ConcactDAO($contact);
				// $this->concacts[] = $contactDAO;
			}
		}
		
		$this->priority = $project['project_priority'];
		$this->type = $project['project_type'];		
	}

	
	/**
	 * Read the project's data from a Cproject object.
	 */
	function loadFromObject($project)
	{	
		$this->id = $project->project_id;
		$this->company = $project->project_company;
		$this->department = $project->project_departament;
		$this->name = $project->project_name;
		$this->shortName = $project->project_short_name;
		$this->owner = $project->project_owner;
		$this->url = $project->project_url;
		$this->demoUrl = $project->project_demo_url;
		$this->startDate = $project->project_start_date;
		$this->endDate = $project->project_end_date;
		$this->actualEndDate = $project->project_actual_end_date;
		$this->status = $project->project_status;
		$this->percentComplete = $project->project_percent_complete;
		$this->colorIdentifier = $project->project_color_identifier;
		$this->description = $project->project_description;
		$this->targetBudget = $project->project_target_budget;
		$this->actualBudget = $project->project_actual_budget;
		$this->creator = $project->project_creator;
		$this->active = $project->project_active;
		$this->private = $project->project_private;
		
		if ($project->departaments != null) {
			$this->departaments = array();
			foreach ($project->project_departaments as $departament) {
				// TODO: Implement the DepartamentDAO
				// $departamentDAO = new DepartamentDAO($contact);
				// $this->departament[] = $departamentDAO;
			}
		}
		
		if ($project->contacts != null) {
			$this->concacts = array();
			foreach ($project->project_contacts as $contact) {
				// TODO: Implement the ContactDAO
				// $contactDAO = new ConcactDAO($contact);
				// $this->concacts[] = $contactDAO;
			}
		}
		
		$this->priority = $project->project_priority;
		$this->type = $project->project_type;		
	}
	
	
	function saveToXML()
	{
		$dom = new DOMDocument('1.0', 'utf-8');
		foreach ($this as $property) {
			$element = $dom->createElementNS('http://ideias.sf.net/schema/v1', 'ideais:' . $property, $this->{$property});
			$dom->appendChild($element);
		}

		$xml = $dom->saveXML();
		var_dump($xml);
		
		return xml;
	}
}

?>
