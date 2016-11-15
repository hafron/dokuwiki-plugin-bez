<?php
 
if(!defined('DOKU_INC')) die();

require_once 'factory.php';
require_once 'task.php';

class BEZ_mdl_Tasks extends BEZ_mdl_Factory {
	private $select_query;
	
	public function __construct($model) {
		parent::__construct($model);
		$this->select_query = "SELECT tasks.*,
						tasktypes.".$this->model->lang_code." AS tasktype_string,
						(CASE	WHEN tasks.issue IS NULL THEN '3'
								WHEN tasks.cause IS NULL OR tasks.cause = '' THEN '0'
								WHEN causes.potential = 0 THEN '1'
								ELSE '2' END) AS action,
						(CASE WHEN tasks.issue IS NULL THEN tasktypes.coordinator 
							  ELSE issues.coordinator END) AS coordinator,
						tasktypes.coordinator AS program_coordinator
						FROM tasks
							LEFT JOIN tasktypes ON tasks.tasktype = tasktypes.id
							LEFT JOIN causes ON tasks.cause = causes.id
							LEFT JOIN issues ON tasks.issue = issues.id";
	}
						
	public function get_one($id) {
		if ($this->auth->get_level() < 5) {
			throw new Exception('BEZ_mdl_Tasks: no permission to get_one()');
		}
		
		$q = $this->select_query.' WHERE tasks.id = ?';
			
		$sth = $this->model->db->prepare($q);
		$sth->execute(array($id));
			
		$task = $sth->fetchObject("BEZ_mdl_Task",
					array($this->model));
				
		return $task;
	}
	
	public function get_all() {
		if ($this->auth->get_level() < 5) {
			throw new Exception('BEZ_mdl_Tasks: no permission to get_all()');
		}
					
		$sth = $this->model->db->prepare($this->select_query);
		
		$sth->setFetchMode(PDO::FETCH_CLASS, "BEZ_mdl_Task",
				array($this->model));
				
		$sth->execute();
					
		return $sth;
	}
	
	public function create_object($defaults) {
		if (isset($defaults['issue'])) {
			$defaults['coordinator'] =
				$this->model->issues->get_one($defaults['issue'])->coordinator;
			if (isset($defaults['tasktype'])) {
				$defaults['program_coordinator'] =
					$this->model->tasktypes->get_one($defaults['tasktype'])->coordinator;
			} else {
				$defaults['program_coordinator'] = $defaults['coordinator'];
			}
		} elseif (isset($defaults['tasktype'])) {
			$defaults['coordinator'] = $defaults['program_coordinator'] = 
					$this->model->tasktypes->get_one($defaults['tasktype'])->coordinator;
		}

		$task = new BEZ_mdl_Task($this->model, $defaults);
		return $task;
	}
}