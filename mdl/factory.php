<?php
 
if(!defined('DOKU_INC')) die();

require_once 'auth.php';

abstract class BEZ_mdl_Factory {
	protected $model;
    //$auth;
	
	protected $filter_field_map = array();
	
	protected function build_where($filters=array()) {
		$execute = array();
		$where_q = array();
		foreach ($filters as $filter => $value) {
			if (isset($this->filter_field_map[$filter])) {
				$field = $this->filter_field_map[$filter];
			} else {
				$field = $filter;
			}
			
			$operator = '=';
			if (is_array($value)) {
				if ($value[0] === '!=') {
					$operator = '!=';
					$value = $value[1];
				}
			}
			
			$where_q[] = $field." $operator :$filter";
			$execute[":$filter"] = $value;
		}
		
		$where = '';
		if (count($where_q) > 0) {
			$where = ' WHERE '.implode(' AND ', $where_q);
		}	
		return array($where, $execute);
	}
	
	public function __construct($model) {
		$this->model = $model;
		$this->auth = new BEZ_mdl_Auth($this->model);
	}
	
//	public function get_level() {
//		return $this->auth->get_level();
//	}
		
	public function get_table_name() {
		$class = get_class($this);
		$exp = explode('_', $class);
		$table = lcfirst($exp[2]);
		return $table;
	}
    
	public function save($obj) {
//		if ($obj->any_errors()) {
//			return false;
//		}
		
		$set = array();
		$execute = array();
		foreach ($obj->get_columns() as $column) {
			$set[] = ":$column";
			$execute[':'.$column] = $obj->$column;
		}
				
		$query = 'REPLACE INTO '.$this->get_table_name().'
							('.implode(',', $obj->get_columns()).')
							VALUES ('.implode(',', $set).')';
									
		$sth = $this->model->db->prepare($query);
		$sth->execute($execute);

        //new object is created
        if ($obj->id === NULL) {
            $id = $this->model->db->lastInsertId();
            $obj->set_id($id);
        }
        
        //$this->model->acl->replace_acl_record($this->get_table_name(), $obj);
        
		return $id;
	}
	
	protected function delete_from_db($id) {
		$q = 'DELETE FROM '.$this->get_table_name().' WHERE id = ?';
		$sth = $this->model->db->prepare($q);
		$sth->execute(array($id));
	}
	
	public function delete($obj) {
//		if ($this->auth->get_level() < 20) {
//			return false;
//		}
        
        //if user can change id, he can delete record
        $this->model->acl->can_change($obj, 'id');
		$this->delete_from_db($obj->id);
	}
}
