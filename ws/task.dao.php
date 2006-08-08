<?

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
	public $responsibles;
	public $contacts;
	public $resources;
	public $dependencies;
     
	 // _construct()
	function TaskDAO($task)
	{
		var_dump($task);
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
