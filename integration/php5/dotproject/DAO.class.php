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


interface iDAO
{
	public function create();
	public function read();
	public function update();
	public function delete();
	public function export();
	
	protected function __setClass();
	protected function __initializeApplication();
	protected function __initializeDatabase();
	protected function __initializeXML();
}

class DAO implements iDAO
{
	protected $class;

	public function create()
	{
	}
	
	/**
	 * Read the object data to a Data Access Object (DAO). We may get the data
	 * from the database (direct access), from a DotProject's object or from
	 * an array.
	 * 
	 * @arg $task If an integer, load from database. Otherwise, load
	 * from the object.
	 */
	public function read($key = null)
	{
		if ($key == null) {
			throw new UserException();
		}
			
		if (is_int($key)) {
			$this->__loadFromDatabase($key);
		} else if (is_array($key)) {
			$this->__loadFromArray($key);
		} else if (is_object($key)) {
			$this->__loadFromObject($key);
		} else {
			throw new UserException();
		}
	}
	
	/**
	 * Read the task's data from the database.
	 */
	private function __loadFromDatabase($object_id)
	{
		__initializeDatabase();
		
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
	public function __loadFromArray($object_data)
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
	public function __loadFromObject($object)
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
	
	/**
	 * Transform the object into a XML document.
	 */
	public function export()
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


require_once("../classes/dotproject.class.php");

?>