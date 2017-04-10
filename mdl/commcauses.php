<?php
 
if(!defined('DOKU_INC')) die();

require_once 'factory.php';
require_once 'commcause.php';

class BEZ_mdl_Commcauses extends BEZ_mdl_Factory {
	private $select_query;
	
	public function __construct($model) {
		parent::__construct($model);
        $this->select_query =
                    "SELECT commcauses.id, commcauses.issue, commcauses.datetime,
                            commcauses.reporter, commcauses.type, commcauses.content,
                            commcauses.content_cache, issues.coordinator,
                            (SELECT COUNT(*) FROM tasks
                                WHERE tasks.cause = commcauses.id) AS tasks_count
                    FROM commcauses JOIN issues ON commcauses.issue = issues.id";
	}

	
	public function get_one($id) {
		if ($this->auth->get_level() < 5) {
			throw new Exception('no permission');
		}
		
		$q = $this->select_query . ' WHERE commcauses.id = ?';
		$sth = $this->model->db->prepare($q);
		$sth->execute(array($id));
		
		$commcause = $sth->fetchObject("BEZ_mdl_Commcause",
					array($this->model));
        
        if ($commcause === false) {
            throw new Exception('there is no commcause with id: '.$id);
        }
		
		return $commcause;
	}
	
	protected $filter_field_map = array(
		'type' 		=> 'commcauses.type',
        'issue'     => 'commcauses.issue'
	);
	
	public function get_all($filters) {
		if ($this->auth->get_level() < 5) {
			throw new Exception('no permission');
		}
		
		//filters is issue
		if (!is_array($filters)) {
			$filters = array('issue' => $filters);
		}
		
		list($where_q, $execute) = $this->build_where($filters);
		
		$q = $this->select_query . ' ' . $where_q;
		
		$sth = $this->model->db->prepare($q);
		$sth->setFetchMode(PDO::FETCH_CLASS, "BEZ_mdl_Commcause",
				array($this->model));

		$sth->execute($execute);
		return $sth;
	}
	
	public function get_causes_ids($issue) {
		if ($this->auth->get_level() < 5) {
			throw new Exception('no permission');
		}
		
		$q = "SELECT commcauses.id
				FROM commcauses JOIN issues ON commcauses.issue = issues.id
				WHERE issue = ? AND commcauses.type != '0'";
		
		$sth = $this->model->db->prepare($q);
		$sth->setFetchMode(PDO::FETCH_NUM);

		$sth->execute(array($issue));
		$arr = $sth->fetchAll();
		
		return array_map(function($elm) { return $elm[0]; }, $arr);
	}
	
	public function create_object($defaults) {
		$issue_id = $defaults['issue'];
		$issue = $this->model->issues->get_one($issue_id);
		
		$coordinator = $issue->coordinator;
		$defaults['coordinator'] = $coordinator;

		$commcause = new BEZ_mdl_Commcause($this->model, $defaults);
		return $commcause;
	}
	
	public function delete($obj) {
		if ($obj->get_level() >= 15 ||
			$obj->reporter === $this->auth->get_user()) {
            if ($obj->tasks_count > 0) {
                throw new Exception('cannot remove commcause with assigned tasks');
            }
			$this->delete_from_db($obj->id);
		} else {
			throw new Exception('no permission');
		}
		
	}
}
