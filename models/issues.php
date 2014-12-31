<?php
include_once DOKU_PLUGIN."bez/models/connect.php";
include_once DOKU_PLUGIN."bez/models/entities.php";
include_once DOKU_PLUGIN."bez/models/issuetypes.php";
include_once DOKU_PLUGIN."bez/models/states.php";
include_once DOKU_PLUGIN."bez/models/users.php";

class Issues extends Connect {
	private $coord_special = array('-proposal', '-rejected');
	public function __construct() {
		global $errors;
		parent::__construct();
		$q = <<<EOM
CREATE TABLE IF NOT EXISTS issues (
	id INT(11) NOT NULL AUTO_INCREMENT,
	title CHAR(100) NOT NULL,
	description TEXT NOT NULL,
	state INT(11) NOT NULL,
	opinion TEXT NULL,
	type INT(11) NOT NULL,
	entity CHAR(100) NOT NULL,
	coordinator CHAR(100) NULL,
	reporter CHAR(100) NOT NULL,
	date INT(11) NOT NULL,
	close_date INT(11) NULL,

	PRIMARY KEY (id)
)
EOM;
	$this->errquery($q);
	}
	public function validate($post)
	{
		global $bezlang, $errors;

		$title_max = 100;
		$description_max = 65000;

		$isstyo = new Issuetypes();
		if ( ! array_key_exists((int)$post['type'], $isstyo->get())) {
			$errors['type'] = $bezlang['vald_type_required'];
		} 
		$data['type'] = (int)$post['type'];

		$ento = new Entities();
		if ( ! in_array($post['entity'], $ento->get_list())) {
			$errors['entity'] = $bezlang['vald_entity_required'];
		} 
		$data['entity'] = $post['entity'];

		/*Jeżeli nie jesteśmy adminem, to jeżeli chodzi o koordynatorów nie mamy nic do gadania*/
		/*Koordynator nie jest wymagany*/
		$usro = new Users();
		if ($this->helper->user_admin()) {
				if (!in_array($post['coordinator'], $usro->nicks()) && !in_array($post['coordinator'], $this->coord_special))
					$errors['coordinator'] = $bezlang['vald_coordinator_required'];

			$data['coordinator'] = $post['coordinator'];
		}

		$post['title'] = trim($post['title']);
		if (strlen($post['title']) == 0) {
			$errors['title'] = $bezlang['vald_title_required'];
		} elseif (strlen($post['title']) > $title_max) {
			$errors['title'] = str_replace('%d', $title_max, $bezlang['vald_title_too_long']);
		} elseif( ! preg_match('/^[[:alnum:] \-,.]*$/ui', $post['title'])) {
			$errors['title'] = $bezlang['vald_title_wrong_chars'];
		} 
		$data['title'] = $post['title'];

		$post['description'] = trim($post['description']);
		if (strlen($post['description']) == 0) {
			$errors['description'] = $bezlang['vald_desc_required'];
		} else if (strlen($post['description']) > $description_max) {
			$errors['description'] = str_replace('%d', $description_max, $bezlang['vald_desc_too_long']);
		} 
		$data['description'] = $post['description'];

		/*zmienamy status tylko w przypadku edycji*/
		/*oraz gdy istnieje koordynator*/
		if (array_key_exists('state', $post) && in_array($data['coordinator'], $usro->nicks())) {
			$post['state'] = (int)$post['state'];
			$stato = new States();
			if (!array_key_exists($post['state'], $stato->get()))
				$errors['state'] = $bezlang['vald_state_required'];

			$data['state'] = $post['state'];
		}

		/*Przyczyna zamknięcia*/
		if (array_key_exists('opinion', $post) && $data['state'] == 1) {
			$opinion_max = 65000;
			if (strlen($post['opinion']) == 0) {
				$errors['opinion'] = $bezlang['vald_opinion_required'];
			} else if (strlen($post['opinion']) > $opinion_max) {
				$errors['opinion'] = str_replace('%d', $opinion_max, $bezlang['vald_opinion_too_long']);
			} 
			$data['opinion'] = $post['opinion'];
		}

		return $data;
	}

	public function add($post, $data=array()) {
		if ($this->helper->user_editor()) {
			$from_user = $this->validate($post);
			$data = array_merge($data, $from_user);
			$this->errinsert($data, 'issues');
		}
	}

	public function update($post, $data, $id) {
		global $INFO;
		$issue = $this->get_clean($id);
		if ($this->helper->user_admin() || $issue['coordinator'] == $INFO['client']) {
			$from_user = $this->validate($post);
			$data = array_merge($data, $from_user);
			$this->errupdate($data, 'issues', $id);
		}
	}

	public function join($a) {
		$stao = new States();
		$a['state'] = $stao->name($a['state']);

		$isstyo = new Issuetypes();
		$a['type'] = $isstyo->name($a['type']);

		$usro = new Users();
		$a['reporter'] = $usro->name($a['reporter']);

		if (!in_array($a['coordinator'], $this->coord_special))
			$a['coordinator'] = $usro->name($a['coordinator']);

		$a['date'] = (int)$a['date'];
		return $a;
	}

	public function get_clean($id) {
		global $bezlang, $errors;
		if ($this->helper->user_viewer()) {
			$id = (int) $id;

			$a = $this->fetch_assoc("SELECT * FROM issues WHERE id=$id");
			if (count($a) == 0) {
				$errors[] = $bezlang['error_issue_id_not_specifed'];
				return array();
			}
			$a = $a[0];
			return $a;
		}
	}

	public function get_by_days() {
		if (!$this->helper->user_viewer()) return false;

		$res = $this->fetch_assoc("SELECT * FROM issues ORDER BY date DESC");
		$create = $this->sort_by_days($res, 'date');
		foreach ($create as $day => $issues)
			foreach ($issues as $ik => $issue)
				$create[$day][$ik]['class'] = 'issue_created';

		$res2 = $this->fetch_assoc("SELECT * FROM issues WHERE close_date != NULL ORDER BY close_date DESC");
		$close = $this->sort_by_days($res2, 'close_date');
		foreach ($close as $day => $issues)
			foreach ($issues as $ik => $issue) {
				$close[$day][$ik]['class'] = 'issue_closed';
				$close[$day][$ik]['date'] = $close[$day][$ik]['close_date'];
			}

		return $this->helper->days_array_merge($create, $close, 'date');
	}

	public function get($id) {
		global $bezlang, $errors;
		if ($this->helper->user_viewer()) {

			$a = $this->get_clean($id);
			if ($a == array())
				return $array;
			
			$a = $this->join($a);

			return $a;
		}
	}
}

