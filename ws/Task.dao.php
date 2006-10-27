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
class TaskDAO
{
	public $id;
	public $name;
	public $description;
	public $startDate;
	public $endDate;
	public $estimatedDuration;
	public $actualDuration;
	public $project;
	public $priority;
	public $creator;
	public $responsibles;
	public $contacts;
	public $resources;
	public $dependencies;
     
	/**
	 * Initialize a Data Access Object (DAO) for a task. We may get the data
	 * from the database (direct access) or from a CTask object.
	 * 
	 * @arg $task If an integer, load from database. Otherwise, load
	 * from the object.
	 */
	function TaskDAO($task = null)
	{
		if ($task != null) {
			if (is_int($task)) {
				$this->loadFromDatabase($task);
			} else if (is_array($task)) {
				$this->loadFromArray($task);
			} else {
				$this->loadFromObject($task);
			}
		}
	}
	
	/**
	 * Read the task's data from the database.
	 */
	function loadFromDatabase($task_id)
	{
		$dotproject = new DotProject();
		$db = $dotproject->connectToDatabase();
		$stmt = $db->Prepare('select * from tasks where task_id=?');
		$rs = $db->Execute($stmt, $task_id);
		$task = $rs->fields;
		
		$this->id = $task['task_id'];
		$this->name = $task['task_name'];
		$this->description = $task['task_description'];
		$this->startDate = $task['task_start_date'];
		$this->endDate = $task['task_end_date'];
		$this->estimatedDuration = $task['task_duration'];
		$this->actualDuration = $task['task_hours_worked'];
		// TODO: Implement the ProjectDAO and put the project's name here.
		// $this->project = new ProjectDAO($task['task_project']);
		
		// TODO: Implement the PriorityDAO and put the priority's name here.
		// $this->project = new PriorityDAO($task['task_priority']);

		// TODO: Implement the UserDAO and put the person's name here.
		$this->creator = $task['task_creator'];
		
		$this->responsibles = array();
		$this->responsibles[] = $task['task_owner'];
		
		if ($task->contacts != null) {
			$this->concacts = array();
			foreach ($task->task_contacts as $contact) {
				// TODO: Implement the ContactDAO
				// $contactDAO = new ConcactDAO($contact);
				// $this->concacts[] = $contactDAO;
			}
		}
		/*
		 * Check if the resource module is enabled. If yes, grab the resources for
		 * the task and populate $this->resources. Something like this:
		 * 
		 * $resources  = array();
		 * $this->resources = array();
		 * //code here
		 * if (! empty($resources)) {
		 *	foreach	 ($resources as $resource) {
		 *		$this->resources[] = new ResourceDAO($resource);
		 * 	}
		 * }
		 */ 
		// TODO: Use the task_id?
		/*
		if ($task->task_parent != null) {
			$this->dependencies = array();
			$this->dependencies[] = $task->task_parent;
		}
		*/
	}


	/**
	 * Read the task's data from a array.
	 */
	function loadFromArray($task)
	{	
		$this->id = $task['task_id'];
		$this->name = $task['task_name'];
		$this->description = $task['task_description'];
		$this->startDate = $task['task_start_date'];
		$this->endDate = $task['task_end_date'];
		$this->estimatedDuration = $task['task_duration'];
		$this->actualDuration = $task['task_hours_worked'];
		if (isset($task['project_name'])) {
			// $this->project = new ProjectDAO($task['project_name']);
		}
		if (isset($task['task_priority'])) {
			// $this->priority = new PriorityDAO($task['task_priority']);
		}
		$this->responsibles = array();
		$this->responsibles[] = $task->task_creator;
		if (isset($task['contacts'])) {
			$this->contacts = array();
			foreach ($task['task_contacts'] as $contact) {
				$contactDAO = new ConcactDAO($contact);
				$this->concacts[] = $contactDAO;
			}
		}
		/*
		 * Check if the resource module is enabled. If yes, grab the resources for
		 * the task and populate $this->resources. Something like this:
		 * 
		 * $resources  = array();
		 * $this->resources = array();
		 * //code here
		 * if (! empty($resources)) {
		 *	foreach	 ($resources as $resource) {
		 *		$this->resources[] = new ResourceDAO($resource);
		 * 	}
		 * }
		 */ 
		if (isset($task['task_parent'])) {
			$this->dependencies = array();
			$this->dependencies[] = new TaskDAO($task['task_parent']);
		}
	}

	
	/**
	 * Read the task's data from a CTask object.
	 */
	function loadFromObject($task)
	{	
		$this->id = $task->task_id;
		$this->name = $task->task_name;
		$this->description = $task->task_description;
		$this->startDate = $task->task_start_date;
		$this->endDate = $task->task_end_date;
		$this->estimatedDuration = $task->task_duration;
		$this->actualDuration = $task->task_hours_worked;
		if ($task->task_project != null) {
			// $this->project = new ProjectDAO($task->task_project);
		}
		if ($task->task_priority != null) {
			// $this->priority = new PriorityDAO($task->task_priority);
		}
		$this->responsibles = array();
		$this->responsibles[] = $task->task_creator;
		if ($task->contacts != null) {
			$this->concacts = array();
			foreach ($task->task_contacts as $contact) {
				$contactDAO = new ConcactDAO($contact);
				$this->concacts[] = $contactDAO;
			}
		}
		/*
		 * Check if the resource module is enabled. If yes, grab the resources for
		 * the task and populate $this->resources. Something like this:
		 * 
		 * $resources  = array();
		 * $this->resources = array();
		 * //code here
		 * if (! empty($resources)) {
		 *	foreach	 ($resources as $resource) {
		 *		$this->resources[] = new ResourceDAO($resource);
		 * 	}
		 * }
		 */ 
		if ($task->task_parent != null) {
			$this->dependencies = array();
			$this->dependencies[] = $task->task_parent;
		}
	}
}

?>
