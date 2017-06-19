<?php
 
if(!defined('DOKU_INC')) die('meh.');

// some ACL level defines
define('BEZ_AUTH_NONE', 0);
define('BEZ_AUTH_USER', 5);
define('BEZ_AUTH_LEADER', 10);
define('BEZ_AUTH_ADMIN', 20);

define('BEZ_PERMISSION_NONE', 0);
define('BEZ_PERMISSION_VIEW', 1);
define('BEZ_PERMISSION_CHANGE', 2);

class BEZ_mdl_Acl {
    private $model;
    
    private $level = BEZ_AUTH_NONE;
    
    private $issues = array();
    private $commcauses = array();
    private $tasks = array();
    
    private function update_level($level) {
		if ($level > $this->level) {
			$this->level = $level;
		}
	}
    
    public function get_level() {
        return $this->level;
    }
    
    public function __construct($model) {
        $this->model = $model;
        
		$userd = $this->model->dw_auth->getUserData($this->model->user_nick); 
		if ($userd !== false && is_array($userd['grps'])) {
			$grps = $userd['grps'];
			if (in_array('admin', $grps ) || in_array('bez_admin', $grps )) {
				$this->update_level(BEZ_AUTH_ADMIN);
            } elseif (in_array('bez_leader', $grps )) {
                $this->update_level(BEZ_AUTH_LEADER);
			} else {
				$this->update_level(BEZ_AUTH_USER);
			}
		}
        
        //get metadata of fields
        //issues
//        $sth = $this->model->db->prepare("SELECT id, coordinator, reporter FROM issues");
//        $sth->execute();
//        while ($issue = $sth->fetch(PDO::FETCH_ASSOC)) {
//            $this->issues[$issue['id']] = $issue;
//        }
//        
//        //tasks
//        $sth = $this->model->db->prepare("
//                        SELECT tasks.id, tasks.executor, issues.coordinator
//                        FROM tasks LEFT JOIN issues ON tasks.issue = issues.id");
//        $sth->execute();
//        while ($task = $sth->fetch(PDO::FETCH_ASSOC)) {
//            $this->tasks[$task['id']] = $task;
//        }
//        
//        
//        //commcauses
//        $sth = $this->model->db->prepare("SELECT
//                        commcauses.id, issues.coordinator, commcauses.reporter,
//                        (SELECT COUNT(*) FROM tasks
//                                WHERE tasks.cause = commcauses.id) AS tasks_count
//                    FROM commcauses JOIN issues ON commcauses.issue = issues.id");
//        $sth->execute();
//        while ($commcause = $sth->fetch(PDO::FETCH_ASSOC)) {
//            $this->commcauses[$commcause['id']] = $commcause;
//        }

	}
    
//    public function update_acl_record($table, $obj) {
//        switch ($table) {
//            case 'issues':
//                if ($field !== 'coordinator' || $field !== 'reporter') {
//                     throw new Exception('field '.$field. ' is not acl field in table issues');
//                }
//                $this->issues[$id][$field] = $value;
//            case 'tasks':
//                $this->tasks[$id][$field] = $value;
//            default:
//                throw new Exception('no acl rules set for table: '.$table);
//        }
//    }
//    
//    public function replace_acl_record($table, $obj) {
//        if ($obj->id === NULL) {
//            throw new Exception('cannot create ACL record for table '.$table.' object not saved');
//        }
//        switch ($table) {
//            case 'issues':
//                $this->issues[$obj->id]['coordinator'] = $obj->coordinator;
//                $this->issues[$obj->id]['reporter'] = $obj->reporter;
//                break;
//            case 'tasks':
//                $this->tasks[$obj->id]['coordinator'] = $obj->coordinator;
//                $this->tasks[$obj->id]['executor'] = $obj->executor;
//                break;
//            case 'commcauses':
//                $this->commcauses[$obj->id]['coordinator'] = $obj->coordinator;
//                $this->commcauses[$obj->id]['reporter'] = $obj->reporter;
//                break;
//            default:
//                throw new Exception('no acl rules set for table '.$table);
//        }
//    }
    
    private function check_issue($issue) {
        $acl = array(
                'id'            => BEZ_PERMISSION_NONE,
                'title'         => BEZ_PERMISSION_NONE,
                'description'   => BEZ_PERMISSION_NONE,
                'state'         => BEZ_PERMISSION_NONE,
                'opinion'       => BEZ_PERMISSION_NONE,
                'type'          => BEZ_PERMISSION_NONE,
                'coordinator'   => BEZ_PERMISSION_NONE,
                'reporter'      => BEZ_PERMISSION_NONE,
                'date'          => BEZ_PERMISSION_NONE,
                'last_mod'      => BEZ_PERMISSION_NONE,
                'last_activity' => BEZ_PERMISSION_NONE,
                'participants'  => BEZ_PERMISSION_NONE,
                'subscribents'  => BEZ_PERMISSION_NONE,
                'description_cache' => BEZ_PERMISSION_NONE,
                'opinion_cache' => BEZ_PERMISSION_NONE
        );
        
        if ($this->level >= BEZ_AUTH_ADMIN) {
            //user can edit everythig
            $acl = array_map(function($value) {
                return BEZ_PERMISSION_CHANGE;
            }, $acl);
            
            return $acl;
        }
        
        //we create new issue
        if ($issue->id === NULL) {
            if ($this->level >= BEZ_AUTH_USER) {
                $acl['title'] = BEZ_PERMISSION_CHANGE;
                $acl['description'] = BEZ_PERMISSION_CHANGE;
                $acl['type'] = BEZ_PERMISSION_CHANGE;
            }
            
            if ($this->level >= BEZ_AUTH_LEADER) {
                $acl['coordinator'] = BEZ_PERMISSION_CHANGE;
            }
            
            return $acl;
        }
        
       // $issue = $this->issues[$id];
        
        if ($this->level >= BEZ_AUTH_USER) {
            //user can display everythig
            $acl = array_map(function($value) {
                return BEZ_PERMISSION_VIEW;
            }, $acl);
            
        }
        
        if ($issue->coordinator === '-proposal' &&
            $issue->reporter === $this->model->user_nick) {
            $acl['title'] = BEZ_PERMISSION_CHANGE;
            $acl['description'] = BEZ_PERMISSION_CHANGE;
            $acl['type'] = BEZ_PERMISSION_CHANGE;
        }
        
        if ($issue->coordinator === $this->model->user_nick) {
            $acl['title'] = BEZ_PERMISSION_CHANGE;
            $acl['description'] = BEZ_PERMISSION_CHANGE;
            $acl['type'] = BEZ_PERMISSION_CHANGE;
            
            //coordinator can change coordinator
            $acl['coordinator'] = BEZ_PERMISSION_CHANGE;
            
            $acl['state'] = BEZ_PERMISSION_CHANGE;
            $acl['opinion'] = BEZ_PERMISSION_CHANGE;
        }
                
        return $acl;
    }
    
    //if user can chante id => he can delete record
    private function check_task($task) {
        $acl = array(
                'id'             => BEZ_PERMISSION_NONE,
                'task'           => BEZ_PERMISSION_NONE,
                'state'          => BEZ_PERMISSION_NONE,
                'tasktype'       => BEZ_PERMISSION_NONE,
                'executor'       => BEZ_PERMISSION_NONE,
                'cost'           => BEZ_PERMISSION_NONE,
                'reason'         => BEZ_PERMISSION_NONE,
                'reporter'       => BEZ_PERMISSION_NONE,
                'date'           => BEZ_PERMISSION_NONE,
                'close_date'     => BEZ_PERMISSION_NONE,
                'cause'          => BEZ_PERMISSION_NONE,
                'plan_date'      => BEZ_PERMISSION_NONE,
                'all_day_event'  => BEZ_PERMISSION_NONE,
                'start_time'     => BEZ_PERMISSION_NONE,
                'finish_time'    => BEZ_PERMISSION_NONE,
                'issue'          => BEZ_PERMISSION_NONE,
                'task_cache'     => BEZ_PERMISSION_NONE,
                'reason_cache'   => BEZ_PERMISSION_NONE
        );
        
        if ($this->level >= BEZ_AUTH_ADMIN) {
            //admin can edit everythig
            $acl = array_map(function($value) {
                return BEZ_PERMISSION_CHANGE;
            }, $acl);
            
            return $acl;
        }
        
        //we create new task
        if ($task->id === NULL) {
            
            if ($task->coordinator === $this->model->user_nick ||
               ($task->issue === '' && $this->level >= BEZ_AUTH_LEADER)) {
                $acl['task'] = BEZ_PERMISSION_CHANGE;
                $acl['tasktype'] = BEZ_PERMISSION_CHANGE;
                $acl['executor'] = BEZ_PERMISSION_CHANGE;
                $acl['cost'] = BEZ_PERMISSION_CHANGE;
                $acl['plan_date'] = BEZ_PERMISSION_CHANGE;
                $acl['all_day_event'] = BEZ_PERMISSION_CHANGE;
                $acl['start_time'] = BEZ_PERMISSION_CHANGE;
                $acl['finish_time'] = BEZ_PERMISSION_CHANGE;
            }
            
            return $acl;
        }
        
        //$task = $this->tasks[$id];
        if ($this->level >= BEZ_AUTH_USER) {
            //user can display everythig
            $acl = array_map(function($value) {
                return BEZ_PERMISSION_VIEW;
            }, $acl);
        }
        
        //user can change state
        if ($task->executor === $this->model->user_nick) {
            $acl['reason'] = BEZ_PERMISSION_CHANGE;
            $acl['state'] = BEZ_PERMISSION_CHANGE;            
        }
        
        if ($task->coordinator === $this->model->user_nick ||
            ($task->issue === '' && $this->level >= BEZ_AUTH_LEADER)) {
            
            $acl['task'] = BEZ_PERMISSION_CHANGE;
            $acl['tasktype'] = BEZ_PERMISSION_CHANGE;
            $acl['executor'] = BEZ_PERMISSION_CHANGE;
            $acl['cost'] = BEZ_PERMISSION_CHANGE;
            $acl['reason'] = BEZ_PERMISSION_CHANGE;
            $acl['plan_date'] = BEZ_PERMISSION_CHANGE;
            $acl['all_day_event'] = BEZ_PERMISSION_CHANGE;
            $acl['start_time'] = BEZ_PERMISSION_CHANGE;
            $acl['finish_time'] = BEZ_PERMISSION_CHANGE;
        }

        return $acl;
        
    }
    
    private function check_commcause($commcause) {
        $acl = array(
            'id'            => BEZ_PERMISSION_NONE,
            'issue'         => BEZ_PERMISSION_NONE,
            'datetime'      => BEZ_PERMISSION_NONE,
            'reporter'      => BEZ_PERMISSION_NONE,
            'type'          => BEZ_PERMISSION_NONE,
            'content'       => BEZ_PERMISSION_NONE,
            'content_cache'   => BEZ_PERMISSION_NONE
        );
        
        if ($this->level >= BEZ_AUTH_ADMIN) {
            //admin can edit everythig
            $acl = array_map(function($value) {
                return BEZ_PERMISSION_CHANGE;
            }, $acl);
            
            return $acl;
        }
        
        //we create new commcause
        if ($commcause->id === NULL) {        
            if ($this->level >= BEZ_USER) {
                $acl['content'] = BEZ_PERMISSION_CHANGE;
            }
            
            return $acl;
        }
        
        //$commcause = $this->commcauses[$id];
        
        if ($this->level >= BEZ_AUTH_USER) {
            //user can display everythig
            $acl = array_map(function($value) {
                return BEZ_PERMISSION_VIEW;
            }, $acl);
        }

        if ($commcause->coordinator === $this->model->user_nick) {
            $acl['type'] = BEZ_PERMISSION_CHANGE;
            $acl['content'] = BEZ_PERMISSION_CHANGE;
            
            //we can only delete records when there is no tasks subscribed to issue
            if ($commcause->tasks_count === 0) {
                 $acl['id'] = BEZ_PERMISSION_CHANGE;
            }
            
        }
        
        //jeżeli ktoś zmieni typ z komentarza na przyczynę, tracimy możliwość edycji
        if ($commcause->reporter === $this->model->user_nick &&
            $commcause->type === '0') {
            $acl['content'] = BEZ_PERMISSION_CHANGE;
            
            //we can only delete records when there is no tasks subscribed to issue
            if ($commcause->tasks_count === 0) {
                 $acl['id'] = BEZ_PERMISSION_CHANGE;
            }
        }
        
        return $acl;
        
    }
    
    /*returns array */
    public function check($obj) {
        $table = get_class($obj);
        switch ($table) {
            case 'BEZ_mdl_Issue':
            case 'BEZ_mdl_Dummy_Issue':
                return $this->check_issue($obj);
            case 'BEZ_mdl_Task':
                return $this->check_task($obj);
            case 'BEZ_mdl_Commcause':
                return $this->check_commcause($obj);
            default:
                throw new Exception('no acl rules set for table: '.$table);
        }
    }
    
    public function check_field($obj, $field) {
        $acl = $this->check($obj);
        return $acl[$field];
    }
    
    public function can_change($obj, $field) {
        if ($this->check_field($obj, $field) < BEZ_PERMISSION_CHANGE) {
            throw new PermissionDeniedException('user cannot change field: '.$field.' in table: '.$table.' row: '.$id);
        }
    }
}