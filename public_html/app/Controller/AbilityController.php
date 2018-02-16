<?php
class AbilityController extends AppController {

	var $uses = array('Ability','ListAbility','AbilityRule','Event','CharacterAbility');

	public function beforeFilter() {
        parent::beforeFilter();

		$this->Auth->allow('viewratios');
    }

	public function isAuthorized($user) {
		if ($user['role_listwrangler']) {
			return true;
		}

		if ($user['role_cards']) {
		 	return true;
		}

		return parent::isAuthorized($user);
	}

   	public function edit($id) {

		if ($this->request->is('post')) {

			if ($this->Ability->save($this->request->data)) {
				$this->Session->setFlash('The ability has been updated.');
				$this->redirect('/ability');
			} else {
				$this->Session->setFlash('Unable to update the ability.');
			}
		}

		$this->set('ability', $this->Ability->findById($id));

		$this->supportingInfo();
	}

  	public function saveinline() {

  		$data = $this->request->input('json_decode',true);

  		$this->set('value',$data);

  		$ability = $this->Ability->findById($data['id']);

  		if(!$ability) {
  			throw new NotFoundException(__('Invalid ability !'.$data['id'].'!!'.$data['name'].'!!'.$data['display'].'!'));
  		}

  		$this->Ability->id = $data['id'];
  		$this->Ability->saveField('ability_name',$data['name']);
  		$this->Ability->saveField('display_name',$data['display']);
  		$this->Ability->saveField('Ratio',$data['ratio']);
  		$this->layout = 'ajax';
    }

   public function index() {

		if ($this->request->is('post')) {
			$this->Ability->create();
			if ($this->Ability->save($this->request->data)) {

				if(isset($this->request->data['Other']['addtooptionlist'])) {
					$this->ListAbility->create();
					$this->ListAbility->set(array(
						'elist_id' => $this->request->data['Other']['addtooptionlist'],
						'ability_id' => $this->Ability->id,
						'build_cost' => 0
					));
			    	$this->ListAbility->save();
				}

				$this->Session->setFlash('The ability has been added.');
				$this->redirect('/ability');
			} else {
				$this->Session->setFlash('Unable to add the ability.');
			}
		} else {
			$db = $this->Ability->getDataSource();
			// Get list of abilities that have options
			$results = $db->fetchAll(
				'SELECT  id, uses_option_list from abilities where uses_option_list > 0',
				array()
			);
			$options = array();
			foreach ($results as $row) {
				$options[$row['abilities']['id']] = $row['abilities']['uses_option_list'];
			}

			$this->set('abilities_with_options',$options);

			$this->set('abilities', $this->Ability->find('list',array('fields'=>array('id','ability_name'))));


			$this->supportingInfo();
		}
   }

   public function viewratios() {

   		$db = $this->Ability->getDataSource();

		// Get list of all abilities
		$abilities = $db->fetchAll(
			'SELECT a.id, a.display_name as display, a.ability_name as ability, a.ratio as ratio, at.name as type, ag.name as grp, ag.sorting_name from abilities a LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where a.deprecated = 0 and a.ratio > 0 and a.abilitygroup_id not in (25,26,21,65,22,24,39,58,51,56,49,50,43) order by ag.sorting_name asc, concat(a.sort_after_name,a.ability_name) asc'
		);

   		$this->set('abilities',$abilities);
   }

   public function viewall($orphans = 0) {

   		$db = $this->Ability->getDataSource();

		if($orphans == 1) {
			// Get list of all abilities that nobody and no lists have
			$abilities = $db->fetchAll(
				'SELECT a.id, a.display_name as display, a.ability_name as ability, a.ratio as ratio, at.name as type, ag.name as grp, ag.sorting_name from abilities a LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where a.id not in (select distinct ability_id from list_abilities) and a.id not in (select distinct ability_id from character_abilities) order by ag.sorting_name asc, concat(a.sort_after_name,a.ability_name) asc'
			);
		} else {
			// Get list of all abilities
			$abilities = $db->fetchAll(
				'SELECT a.id, a.display_name as display, a.ability_name as ability, a.ratio as ratio, at.name as type, ag.name as grp, ag.sorting_name from abilities a LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where a.deprecated = 0 order by ag.sorting_name asc, concat(a.sort_after_name,a.ability_name) asc'
			);
		}

   		$this->set('abilities',$abilities);
   }

   public function loadrules($ability_id) {

   		$db = $this->Ability->getDataSource();

	   	$rules = $db->fetchAll(
			'SELECT a.id, a.name, a.description from ability_rules a where a.id = ?',
			array($ability_id)
		);

		$this->set('rules',$rules);
		$this->layout = 'popup';
   }

   public function abilityfinder($cset_id = 1) {
   		$this->set('cset_id',$cset_id);
   }

   public function loadabilities() {

   		$db = $this->Ability->getDataSource();

   		$abilities = $db->fetchAll(
				'SELECT a.id, a.ability_name, a.uses_option_list FROM abilities a where a.deprecated = 0'
		);

   		$this->set('ajax',json_encode($abilities));
   		$this->layout = 'ajax';
   		$this->render('ajax');
   }

   public function loadoptionlist($list_id) {
   		$db = $this->Ability->getDataSource();

   		$abilities = $db->fetchAll(
				'SELECT a.id, a.ability_name FROM abilities a where a.id in (select la.ability_id from list_abilities la where la.elist_id = ?) order by a.ability_name',array($list_id)
		);

   		$this->set('ajax',json_encode($abilities));
   		$this->layout = 'ajax';
   		$this->render('ajax');
   }

   public function loaddeprecated() {
   		$db = $this->Ability->getDataSource();

   		$abilities = $db->fetchAll(
				'SELECT a.id, a.ability_name FROM abilities a where a.deprecated = 1'
		);

   		$this->set('ajax',json_encode($abilities));
   		$this->layout = 'ajax';
   		$this->render('ajax');
   }

   public function loadlists() {

   		$db = $this->Ability->getDataSource();

   		$abilities = $db->fetchAll(
				'SELECT a.id, a.ability_name FROM abilities a where a.deprecated = 0 and a.abilitygroup_id in (21,22,65)'
		);

   		$this->set('ajax',json_encode($abilities));
   		$this->layout = 'ajax';
   		$this->render('ajax');
   }

   public function whohasimpl($ability_id, $active = 0, $cset_id = 1) {

   		$db = $this->Ability->getDataSource();

   		if($active == 2) {
   			$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

   			$insert = "c.id in (select character_id from character_attendance where event_id = " . $event['Event']['id']. ") and";
   		} else if($active == 1) {
   			$insert = "c.cset_id = ".$cset_id." and c.active = 1 and";
   		} else {
   			$insert = "c.cset_id = ".$cset_id." and";
   		}

   		$sql = "SELECT total, name, cardnumber from (SELECT sum(ca.quantity) as total, c.name, c.cardnumber from characters c LEFT OUTER JOIN character_abilities ca on ca.character_id = c.id where ".$insert." ca.ability_id = ? or ca.abilityoption_id = ? group by c.cardnumber) a where a.total > 0 order by a.total desc, a.name asc";

   		$characters = $db->fetchAll($sql,array($ability_id,$ability_id));

   		return $characters;
   	}

   public function whohas($ability_id, $active = 0, $cset_id = 1) {
   		$characters = $this->whohasimpl($ability_id, $active, $cset_id);

   		$this->set('ajax',json_encode($characters));
   		$this->layout = 'ajax';
   		$this->render('ajax');
   }

   public function whohasall($active = 0, $cset_id = 1) {
   		$data = $this->request->input('json_decode',true);

   		$characters = array();
   		$total = array();
   		foreach($data as $row) {
			$results = $this->whohasimpl($row['a']['id'], $active, $cset_id);

			$total[$row['a']['ability_name']] = count($results);

			foreach($results as $c) {
				if(!array_key_exists($c['a']['cardnumber'],$characters)) {
					$characters[$c['a']['cardnumber']] = array("cardnumber" => $c['a']['cardnumber'], "name" => $c['a']['name'], "abilities" => array());
				}

				$characters[$c['a']['cardnumber']]['abilities'][] = array("name" => $row['a']['ability_name'], "count" => $c['a']['total']);
			}
   		}

   		$chars = array();

   		foreach($characters as $cardnumber => $character) {
   			$chars[] = $character;
   		}

   		$results = array("totals" => $total, "characters" => $chars);

   		$this->set('ajax',json_encode($results));
   		$this->layout = 'ajax';
   		$this->render('ajax');
   }

   private function supportingInfo() {
   		$db = $this->Ability->getDataSource();

   		// Get list of ability types
		$results = $db->fetchAll(
			'SELECT  id, sorting_name from ability_types order by sorting_name',
			array()
		);
		$abtypes = array();
		foreach ($results as $row) {
			$abtypes[$row['ability_types']['id']] = $row['ability_types']['sorting_name'];
		}

		$this->set('ability_types', $abtypes);


		// Get list of ability groups
		$results = $db->fetchAll(
			'SELECT  id, sorting_name from ability_groups order by sorting_name',
			array()
		);
		$abgroups = array();
		foreach ($results as $row) {
			$abgroups[$row['ability_groups']['id']] = $row['ability_groups']['sorting_name'];
		}

		$this->set('ability_groups', $abgroups);


		// Get list of lists
		$results = $db->fetchAll(
			'SELECT  id, list_name from elists where list_type_id != 4',
			array()
		);
		$elists = array();
		$elists[0] = "-none-";
		foreach ($results as $row) {
			$elists[$row['elists']['id']] = $row['elists']['list_name'];
		}

		$this->set('all_lists',$elists);

		// Get list of option lists
		$results = $db->fetchAll(
			'SELECT  id, list_name from elists where list_type_id = 4',
			array()
		);
		$optionlists = array();
		$optionlists[0] = "-none-";
		foreach ($results as $row) {
			$optionlists[$row['elists']['id']] = $row['elists']['list_name'];
		}

		$this->set('option_lists',$optionlists);
   }

   public function replaceall() {
   		$data = $this->request->input('json_decode',true);

   		$ability_id = $data['replace']['a']['id'];

		$db = $this->Ability->getDataSource();

		$sql = "SELECT ca.id, ca.ability_id, ca.abilityoption_id from character_abilities ca where ca.ability_id = ? or ca.abilityoption_id = ?";

   		$results = $db->fetchAll($sql,array($ability_id,$ability_id));

		foreach($results as $row) {
			$this->CharacterAbility->id = intval($row['ca']['id']);

			if($data['option'] != null) {
				$this->CharacterAbility->set(array(
					'ability_id' => $data['target']['a']['id'],
					'abilityoption_id' => $data['option']['a']['id']
				));
				$results['action'] = "Full Replace";
			} else if($row['ca']['ability_id'] == $ability_id) {
				$this->CharacterAbility->set('ability_id',$data['target']['a']['id']);
				$results['action'] = "Skill Replace";
			} else if($row['ca']['abilityoption_id'] ==  $ability_id) {
				$this->CharacterAbility->set('abilityoption_id',$data['target']['a']['id']);
				$results['action'] = "Option Replace";
			}
			$this->CharacterAbility->save();
		}

		$this->set('ajax',json_encode($results));
   		$this->layout = 'ajax';
   		$this->render('ajax');
   }

   public function deleteall() {
		$data = $this->request->input('json_decode',true);

   		$ability_id = $data['deleteme']['a']['id'];

   		$this->CharacterAbility->deleteAll(array('CharacterAbility.ability_id' => $ability_id));
   		$this->CharacterAbility->deleteAll(array('CharacterAbility.abilityoption_id' => $ability_id));
   			
   		$this->set('ajax',json_encode($data));
   		$this->layout = 'ajax';
   		$this->render('ajax');
   }

}
?>