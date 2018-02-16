<?php
App::uses('CakeEmail', 'Network/Email');

class PersonalActionController extends AppController {

	var $uses = array('Character','Player','User','Action','DefaultAction','TurnAction','Land','Settlement','SettlementValues','Improvement','SettlementImprovement','Turn','Business','LandAction','CharacterDeposit','BusinessDeposit','Event','SettlementPlot');

	public function isAuthorized($user) {

		// Allow only admins to access this list of actions
		if (in_array($this->action, array('loadCompanies','manageold','saveimprovement','viewMap','index2','emailresults','endturn','newturn','reopenturn','finalizeturn','unfinalizeturn','manage','manage2','viewAs','getAllCurrentActionsForSettlement','loadcharactersas','getCurrentActionsAs','saveSettlementNotes','saveactionresults','saveactionresults2','loadDefaultActions'))) {
			if ($user['role_landadmin']) {
				return true;
			}
			return parent::isAuthorized($user);
		}

		// All other actions are allowed to all logged in users
		return true;
	}

	public function index() {

	}

	public function index2() {

	}

	public function loadDefaultActions() {
		$current_turn_id = $this->getCurrentTurnId();

		$db = $this->TurnAction->getDataSource();

		$db->query('insert into turn_actions (land_id, settlement_id, business_id, improvement_id, action_id, turn_id, character_id, target, comments, is_default) select land_id, settlement_id, business_id, improvement_id, action_id, ? as turn_id, character_id, target, comments, 1 as is_default from default_actions where character_id not in (select character_id from turn_actions where turn_id = ?) and character_id in (select id from characters where cset_id = 1 and active = 1)',array($current_turn_id,$current_turn_id));

		$db->query('insert into turn_actions (land_id, settlement_id, business_id, improvement_id, action_id, turn_id, character_id, target, comments, is_default) select land_id, settlement_id, business_id, id as improvement_id, 27 as action_id, ? as turn_id, 0 as character_id, "" as target, "no action" as comments, 1 as is_default from settlement_improvements WHERE business_id > 0 and id not in (select improvement_id from turn_actions where turn_id = ?)',array($current_turn_id,$current_turn_id));


		$this->Turn->id = $current_turn_id;
		$this->Turn->saveField('defaults_loaded',1);

		$results = $this->getCurrentTurn();

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function viewMap($settlement_id) {
		$this->set('settlement_id',$settlement_id);

		$this->layout = 'angular';
	}

	public function loadPlots($settlement_id) {
		$result = $this->SettlementPlot->find('all',array(
			'conditions' => array('SettlementPlot.settlement_id' => $settlement_id)
			));

		$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');	
	}

	public function viewAs($player_id = null) {
		if($player_id != null) {
			$this->set('player_id',$player_id);
			$this->set('admin','true');
		}
		$this->render('index');
	}

	public function manage() {

	}

	public function manageold() {

	}

	public function manage2() {
		$this->layout = 'angular';
	}


	public function getCharacterDetails($character_id) {

		$retval = array();
		$current_turn_id = $this->getCurrentTurnId();
		
		$results = $this->Character->query("SELECT coalesce(sum(quantity),0) as total from character_abilities where ability_id = 3019 and character_id = ?",array($character_id));

		if($results[0][0]['total'] > 0) {
			$retval['civil_service'] = 1;
		} else {
			$retval['civil_service'] = 0;
		}

		$results = $this->Character->query("SELECT coalesce(sum(quantity),0) as total from character_abilities where ability_id = 3061 and character_id = ?",array($character_id));

		if($results[0][0]['total'] > 0) {
			$retval['quadrivium'] = 1;
		} else {
			$retval['quadrivium'] = 0;
		}

		$actions = $this->TurnAction->find('all',array(
			'conditions' => array('Character.id' => $character_id, 'Turn.id' => $current_turn_id),
			'order' => array('TurnAction.action_number')
		));

		$retval['current_actions'] = $actions;

		$this->set('ajax',json_encode($retval));
		$this->layout = 'ajax';
		$this->render('ajax');	
	}

	private function loadcharactersImpl($player_id = null) {
		$results = $this->Character->find('all',array('conditions' => array(
			'Player.id' => $player_id,
			'Character.cset_id' => 1,
			'Character.active' => 1
		)));

		foreach($results as &$row) {
			$actions = $this->Character->query("SELECT coalesce(sum(quantity),0) as total from character_abilities where ability_id = 3019 and character_id = ?",array($row['Character']['id']));

			$row['Character']['action_points'] = $actions[0][0]['total'] + 1;			
		}


		return $results;
	}

	public function loadcharactersas($player_id = null) {
		$results = $this->loadcharactersImpl($player_id);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadcharacters() {
		$results = $this->loadcharactersImpl(AuthComponent::user('player_id'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadbusinessactions($character_id = null) {
		$results = $this->Action->find('all',array('conditions' => array('Action.active' => '1', 'Action.business' => '1')));

		$payload = array(
			'actions' => $results
		);

		$this->set('ajax',json_encode($payload));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadactions($character_id = null, $settlement_id = null) {
		$settlement = $this->Settlement->find('all',array('conditions' => array('Settlement.id' => $settlement_id)));

		if($settlement[0]['Land']['baronial'] == 1) {
			$results = $this->Action->find('all',array(
				'conditions' => array('Action.active' => '1', 'Action.business' => '0'),
				'order' => array('Action.negative')
			));
		}

		$landactions = $this->LandAction->find('all',array(
			'conditions' => array('LandAction.land_id' => $settlement[0]['Land']['id'])
		));

		foreach($landactions as $row) {
			$results[] = $row;
		}

		$payload = array(
			'character' => $character_id, 
			'settlement' => $settlement_id,
			'actions' => $results,
			// 'landactions' => $landactions
		);

		$this->set('ajax',json_encode($payload));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadlands() {
		$results = $this->Land->find('all');

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadbusinessimprovements($character_id = null, $business_id = null) {
		$results = $this->SettlementImprovement->find('all',array('conditions' => array(
			'Business.id' => $business_id
		)));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadimprovements() {
		$results = $this->Improvement->find('all', array('order' => array('Improvement.sort','Improvement.turns_needed','Improvement.name')));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadsettlement($settlement_id) {
		$results = $this->Settlement->find('first',array('conditions' => array('Settlement.id' => $settlement_id)));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadlandvalues($land_id) {
		$currentTurn = $this->getCurrentTurnId();
		if($land_id == -1) {
			$sv = $this->SettlementValues->find('all',array('order' => array('Land.id', 'SettlementValues.population desc'),'conditions' => array('SettlementValues.turn_id' => $currentTurn)));			
		} else {
			$sv = $this->SettlementValues->find('all',array('order' => array('SettlementValues.population desc'),'conditions' => array('SettlementValues.land_id' => $land_id, 'SettlementValues.turn_id' => $currentTurn)));
		}
		
		$results = array('SettlementValues' => $sv);
		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');		
	}

	public function loadsettlementvalues($settlement_id) {
		$previousTurn = $this->getPreviousTurn($settlement_id);
		$sv = $this->SettlementValues->find('first',array('conditions' => array('SettlementValues.settlement_id' => $settlement_id, 'SettlementValues.turn_id' => $previousTurn[0]['turn_id'])));
		
		$results = array('SettlementValues' => $sv['SettlementValues']);
		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function savesettlementvalues() {
  		$data = $this->request->input('json_decode',true);

  		$this->SettlementValues->deleteAll(array('SettlementValues.turn_id' => $data['turn_id'], 'SettlementValues.settlement_id' => $data['settlement_id']));

  		$this->SettlementValues->create();
  		$this->SettlementValues->set(array(
  			'land_id' => 			$data['land_id'],
  			'settlement_id' => 		$data['settlement_id'],
  			'turn_id' => 			$data['turn_id'],
  			'happiness' => 			$data['happiness'],
  			'population' => 		$data['population'],
  			'public_order' => 		$data['public_order'],
  			'health' => 			$data['health'],
  			'military' => 			$data['military'],
  			'military_effect' => 	$data['military_effect'],
  			'criminal' => 			$data['criminal'],
  			'wildlands' => 			$data['wildlands'],
  			'gold' =>	 			$data['gold'],
  			'food' => 				$data['food'],
  			'stone' => 				$data['stone'],
  			'lumber' => 			$data['lumber'],
  			'goods' => 				$data['goods'],
  			'gold_goal' =>	 		$data['gold_goal'],
  			'food_goal' => 			$data['food_goal'],
  			'stone_goal' => 		$data['stone_goal'],
  			'lumber_goal' => 		$data['lumber_goal'],
  			'goods_goal' => 		$data['goods_goal']
  		));
  		$this->SettlementValues->save();

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function saveSettlementNotes() {
  		$data = $this->request->input('json_decode',true);

  		$this->Settlement->id = $data['id'];
  		$this->Settlement->set(array(
  			'current_events' => 	$data['current_events'],
  			'staff_notes' => 		$data['staff_notes']
  		));
  		$this->Settlement->save();

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function savesettlement($settlement_id) {
  		$data = $this->request->input('json_decode',true);

  		foreach($data['SettlementImprovement'] as $imp) {
  			$this->SettlementImprovement->id = $imp['id'];
  			$this->SettlementImprovement->set(array(
			'stone_needed' => $imp['stone_needed'],
			'wood_needed' => $imp['wood_needed'],
			'gold_needed' => $imp['gold_needed'],
			'labor_needed' => $imp['labor_needed'],
			'goods_needed' => $imp['goods_needed'],
			'turns_needed' => $imp['turns_needed'],
			'operational_efficiency' => $imp['operational_efficiency']
			));
			$this->SettlementImprovement->save();
  		}

		$results = $this->Settlement->find('first',array('conditions' => array('Settlement.id' => $data['Settlement']['id'])));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function addimprovement() {
  		$data = $this->request->input('json_decode',true);

		$this->SettlementImprovement->create();
		$this->SettlementImprovement->set(array(
			'improvement_id' => $data['Improvement']['id'],
			'land_id' => $data['Settlement']['land_id'],
			'settlement_id' => $data['Settlement']['id'],
			'stone_needed' => $data['Improvement']['stone_needed'],
			'wood_needed' => $data['Improvement']['wood_needed'],
			'gold_needed' => $data['Improvement']['gold_needed'],
			'labor_needed' => $data['Improvement']['labor_needed'],
			'goods_needed' => $data['Improvement']['goods_needed'],
			'turns_needed' => $data['Improvement']['turns_needed'],
			'operational_efficiency' => 0
			));
		$this->SettlementImprovement->save();

		$results = $this->Settlement->find('first',array('conditions' => array('Settlement.id' => $data['Settlement']['id'])));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function debug() {
		$results = $this->getPreviousTurn(1);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	private function getPreviousTurn($settlement_id) {
		$currentTurn = $this->getCurrentTurnId();

		$db = $this->TurnAction->getDataSource();
		$results = $db->query('SELECT max(turn_id) as turn_id from settlement_values where turn_id < ? and settlement_id = ?',array($currentTurn, $settlement_id));
		
		return $results[0];
	}

	private function `) {
		$db = $this->TurnAction->getDataSource();
		$results = $db->fetchAll('SELECT t.id, t.status, t.started, t.ended, t.defaults_loaded from turns t where id in (SELECT max(id) from turns)');
		
		return $results[0];
	}

	private function getCurrentEventId() {
		$db = $this->Event->getDataSource();
		$results = $db->query('SELECT max(id) from events');
		
		return $results[0][0]['max(id)'];
	}


	private function getCurrentTurnId() {
		$results = $this->getCurrentTurn();
		return $results['t']['id'];
	}

	public function loadturn() {
		$results = $this->getCurrentTurn();

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');		
	}

	private function getMaxEventId() {
		$db = $this->TurnAction->getDataSource();
		$results = $db->fetchAll('SELECT max(id) as id from events');
		
		return $results[0][0]['id'];
	}


	public function newturn() {
		$this->Turn->create();
		$this->Turn->set(array(
			'after_event_id' => $this->getMaxEventId(),
			'started' => date('c')
		));
		$this->Turn->save();

		$results = $this->getCurrentTurn();

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function reopenturn() {
		$results = $this->getCurrentTurn();

		$this->Turn->id = $results['t']['id'];
		$this->Turn->saveField('status',0);

		$results = $this->getCurrentTurn();

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function endturn() {
		$results = $this->getCurrentTurn();

		$this->Turn->id = $results['t']['id'];
		$this->Turn->saveField('status',1);
		$this->Turn->saveField('ended',date('c'));

		$results = $this->getCurrentTurn();

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function finalizeturn() {
		$results = $this->getCurrentTurn();

		$this->Turn->id = $results['t']['id'];
		$this->Turn->saveField('status',2);

		$results = $this->getCurrentTurn();

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function unfinalizeturn() {
		$results = $this->getCurrentTurn();

		$this->Turn->id = $results['t']['id'];
		$this->Turn->saveField('status',1);

		$results = $this->getCurrentTurn();

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function saveTurnActions() {
		$data = $this->request->input('json_decode',true);

		$turn_id = $this->getCurrentTurnId();

		$this->TurnAction->deleteAll(array('TurnAction.character_id' => $data['character_id'], 'TurnAction.turn_id' => $turn_id));

		$this->TurnAction->create();
		$this->TurnAction->set(array(
			'land_id' => $data['action1']['selectedLand'],
			'settlement_id' => $data['action1']['selectedSettlement'],
			'business_id' => $data['action1']['selectedBusiness'],
			'improvement_id' => $data['action1']['selectedBI'],
			'action_id' => $data['action1']['action_id'],
			'turn_id' => $turn_id,
			'character_id' => $data['character_id'],
			'target' => $data['action1']['target'],
			'comments' => $data['action1']['comment'],
			'action_number' => '1'
		));
		if($data['action1']['character_id'] > 0) {
			$this->TurnAction->set(array('emailed' => 0));
		} else {
			$this->TurnAction->set(array('emailed' => 1));
		}

		$this->TurnAction->save();

		if(isset($data['action2']['action_id'])) {
			$this->TurnAction->create();
			$this->TurnAction->set(array(
				'land_id' => $data['action2']['selectedLand'],
				'settlement_id' => $data['action2']['selectedSettlement'],
				'business_id' => $data['action2']['selectedBusiness'],
				'improvement_id' => $data['action2']['selectedBI'],
				'action_id' => $data['action2']['action_id'],
				'turn_id' => $turn_id,
				'character_id' => $data['character_id'],
				'target' => $data['action2']['target'],
				'comments' => $data['action2']['comment'],
				'action_number' => '2'
			));
			if($data['action2']['character_id'] > 0) {
				$this->TurnAction->set(array('emailed' => 0));
			} else {
				$this->TurnAction->set(array('emailed' => 1));
			}

			$this->TurnAction->save();
		}

		// set up default actions
		if(isset($data['makedefault']) && $data['makedefault'] == true) {
			$this->DefaultAction->deleteAll(array('DefaultAction.character_id' => $data['character_id']));

			if($data['action1']['negative'] == 0) {
				$this->DefaultAction->create();
				$this->DefaultAction->set(array(
					'land_id' => $data['action1']['selectedLand'],
					'settlement_id' => $data['action1']['selectedSettlement'],
					'business_id' => $data['action1']['selectedBusiness'],
					'improvement_id' => $data['action1']['selectedBI'],
					'action_id' => $data['action1']['action_id'],
					'character_id' => $data['character_id'],
					'target' => $data['action1']['target'],
					'comments' => $data['action1']['comment'],
					'action_number' => '1'
				));

				$this->DefaultAction->save();
			}

		 	if(isset($data['action2']['action_id']) && $data['action2']['negative'] == 0) {
				$this->DefaultAction->create();
				$this->DefaultAction->set(array(
					'land_id' => $data['action2']['selectedLand'],
					'settlement_id' => $data['action2']['selectedSettlement'],
					'business_id' => $data['action2']['selectedBusiness'],
					'improvement_id' => $data['action2']['selectedBI'],
					'action_id' => $data['action2']['action_id'],
					'character_id' => $data['character_id'],
					'target' => $data['action2']['target'],
					'comments' => $data['action2']['comment'],
					'action_number' => '2'
				));

				$this->DefaultAction->save();
			}
		}

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function addTurnAction() {
		$data = $this->request->input('json_decode',true);

		$turn_id = $this->getCurrentTurnId();

		$this->TurnAction->create();

		if($data['SettlementImprovement'] != 0) {
			$this->TurnAction->set(array(
				'land_id' => $data['SettlementImprovement']['land_id'],
				'settlement_id' => $data['SettlementImprovement']['settlement_id'],
				'business_id' => $data['SettlementImprovement']['business_id'],
				'improvement_id' => $data['SettlementImprovement']['id'],
				'action_id' => $data['Action']['id'],
				'turn_id' => $turn_id,
				'character_id' => $data['Character']['id'],
				'target' => $data['Target'],
				'comments' => $data['Comment']
			));
		} else {
			$this->TurnAction->set(array(
				'land_id' => $data['Settlement']['land_id'],
				'settlement_id' => $data['Settlement']['id'],
				'business_id' => 0,
				'improvement_id' => 0,
				'action_id' => $data['Action']['id'],
				'turn_id' => $turn_id,
				'character_id' => $data['Character']['id'],
				'target' => $data['Target'],
				'comments' => $data['Comment']
			));
		}
		$this->TurnAction->save();

		// Save the new Default Action
		if($data['Default'] == true) {

			$existingDefault = $this->DefaultAction->find('first', array('conditions' => array('DefaultAction.character_id' => $data['Character']['id'])));

			$this->DefaultAction->create();	
			if($data['SettlementImprovement'] != 0) {
				$this->DefaultAction->set(array(
					'land_id' => $data['SettlementImprovement']['land_id'],
					'settlement_id' => $data['SettlementImprovement']['settlement_id'],
					'business_id' => $data['SettlementImprovement']['business_id'],
					'improvement_id' => $data['SettlementImprovement']['id'],
					'action_id' => $data['Action']['id'],
					'character_id' => $data['Character']['id'],
					'target' => $data['Target'],
					'comments' => $data['Comment']
				));
			} else {
				$this->DefaultAction->set(array(
					'land_id' => $data['Settlement']['land_id'],
					'settlement_id' => $data['Settlement']['id'],
					'business_id' => 0,
					'improvement_id' => 0,
					'action_id' => $data['Action']['id'],
					'character_id' => $data['Character']['id'],
					'target' => $data['Target'],
					'comments' => $data['Comment']
				));
			}

			$this->DefaultAction->save();
		}

		$results = $this->getCurrentActionsImpl(AuthComponent::user('player_id'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');		
	}

	public function getCurrentActions() {
		$results = $this->getCurrentActionsImpl(AuthComponent::user('player_id'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');				
	}

	public function getCurrentActionsAs($player_id = null) {
		$results = $this->getCurrentActionsImpl($player_id);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');				
	}

	public function getDefaultActions() {
		$results = $this->getDefaultActionsImpl(AuthComponent::user('player_id'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');				
	}

	public function getDefaultActionsAs($player_id = null) {
		$results = $this->getDefaultActionsImpl($player_id);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');				
	}

	public function deleteAction($action_id) {
		$this->TurnAction->delete($action_id);

		$results = $this->getCurrentActionsImpl(AuthComponent::user('player_id'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');	
	}

	public function deleteDefaultAction($action_id) {
		$this->DefaultAction->delete($action_id);

		$results = $this->getDefaultActionsImpl(AuthComponent::user('player_id'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');	
	}

	private function getCurrentActionsImpl($player_id = null) {
		$turn_id = $this->getCurrentTurnId();
		$db = $this->TurnAction->getDataSource();

		$results = $db->fetchAll(
			'SELECT c.id from characters c where c.cset_id = 1 and c.active = 1 and c.player_id = ? order by c.cardnumber asc',
			array($player_id)
		);

		$character_ids = array();
		foreach($results as $row) {
			$character_ids[] = $row['c']['id'];
		}

		$results = $this->TurnAction->find('all',array(
			'conditions' => array(
				'Turn.id' => $turn_id,
				'Character.id' => $character_ids),
			'order' => array('Character.cardnumber')
		));

		return $results;		
	}

	private function getDefaultActionsImpl($player_id = null) {
		$db = $this->DefaultAction->getDataSource();

		$results = $db->fetchAll(
			'SELECT c.id from characters c where c.player_id = ? order by c.cardnumber asc',
			array($player_id)
		);

		$character_ids = array();
		foreach($results as $row) {
			$character_ids[] = $row['c']['id'];
		}

		$results = $this->DefaultAction->find('all',array(
			'conditions' => array(
				'Character.id' => $character_ids),
			'order' => array('Character.cardnumber')
		));

		return $results;		
	}

	public function saveactionresults2() {
		$event_id = $this->getCurrentEventID();

  		$data = $this->request->input('json_decode',true);

		$this->TurnAction->id = $data['TurnAction']['id'];
		$this->TurnAction->set(array(
			'result' 			=> $data['TurnAction']['result'],
			'public_order'		=> $data['TurnAction']['public_order'],
			'health'			=> $data['TurnAction']['health'],
			'happiness'			=> $data['TurnAction']['happiness'],
			'military_effect'	=> $data['TurnAction']['military_effect'],
			'wildlands'			=> $data['TurnAction']['wildlands'],
			'criminal'			=> $data['TurnAction']['criminal'],
			'result_value'		=> $data['TurnAction']['result_value']
		));
		if($data['TurnAction']['character_id'] > 0) {
			$this->TurnAction->set(array('emailed' => 0));
		} else {
			$this->TurnAction->set(array('emailed' => 1));
		}
		$this->TurnAction->save();

		$this->CharacterDeposit->deleteAll(array('CharacterDeposit.turn_action_id' => $data['TurnAction']['id']));
		$this->BusinessDeposit->deleteAll(array('BusinessDeposit.turn_action_id' => $data['TurnAction']['id']));

		if($data['TurnAction']['result_value'] != 0) {
			if($data['Action']['business'] == 1) {
				$this->BusinessDeposit->create();
				$this->BusinessDeposit->set(array(
					'business_id' 		=> $data['TurnAction']['business_id'],
					'character_id' 		=> $data['TurnAction']['character_id'],
					'event_id' 			=> $event_id,
					'gold' 				=> $data['TurnAction']['result_value'],
					'turn_action_id' 	=> $data['TurnAction']['id']
				));
				$this->BusinessDeposit->save();
			} else {
				$this->CharacterDeposit->create();
				$this->CharacterDeposit->set(array(
					'character_id' 		=> $data['TurnAction']['character_id'],
					'event_id' 			=> $event_id,
					'value' 			=> $data['TurnAction']['result_value'],
					'turn_action_id' 	=> $data['TurnAction']['id']
				));
				$this->CharacterDeposit->save();
			}
		}

		$data['TurnAction']['saved_message'] = "Saved";

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');		
	}

	// public function saveactionresults($settlement_id) {
 //  		$data = $this->request->input('json_decode',true);

 //  		foreach($data as $imp) {
 //  			$compareto = $this->TurnAction->findById($imp['TurnAction']['id']);

 //  			if(isset($imp['TurnAction']['changed']) && $imp['TurnAction']['changed'] == "1") {
	//   			$this->TurnAction->id = $imp['TurnAction']['id'];
	//   			$this->TurnAction->set(array(
	//   				'result' 			=> $imp['TurnAction']['result'],
	//   				'public_order'		=> $imp['TurnAction']['public_order'],
	//   				'health'			=> $imp['TurnAction']['health'],
	//   				'happiness'			=> $imp['TurnAction']['happiness'],
	//   				'military_effect'	=> $imp['TurnAction']['military_effect'],
	//   				'wildlands'			=> $imp['TurnAction']['wildlands'],
	//   				'criminal'			=> $imp['TurnAction']['criminal']
	//   			));
	//   			if($compareto['TurnAction']['character_id'] > 0) {
	// 	  			$this->TurnAction->set(array('emailed' => 0));
	//   			} else {
	//   				$this->TurnAction->set(array('emailed' => 1));
	//   			}
	// 			$this->TurnAction->save();
	// 		}
 //  		}

	// 	$results = $this->getAllCurrentActionsForSettlementImpl($settlement_id);

	// 	$this->set('ajax',json_encode($results));
	// 	$this->layout = 'ajax';
	// 	$this->render('ajax');		
	// }

	// public function savebusinessactionresults($settlement_id) {
 //  		$data = $this->request->input('json_decode',true);

 //  		foreach($data as $imp) {
 //  			$compareto = $this->TurnAction->findById($imp['TurnAction']['id']);

 //  			if($compareto['TurnAction']['result'] != $imp['TurnAction']['result']) {
	//   			$this->TurnAction->id = $imp['TurnAction']['id'];
	//   			$this->TurnAction->set(array('result' => $imp['TurnAction']['result']));
	//   			if($compareto['TurnAction']['character_id'] > 0) {
	// 	  			$this->TurnAction->set(array('emailed' => 0));
	//   			} else {
	//   				$this->TurnAction->set(array('emailed' => 1));
	//   			}
	// 			$this->TurnAction->save();
	// 		}
 //  		}

	// 	$results = $this->getAllCurrentBusinessActionsForSettlementImpl($settlement_id);

	// 	$this->set('ajax',json_encode($results));
	// 	$this->layout = 'ajax';
	// 	$this->render('ajax');		
	// }

	public function getPastActionsForCharacter($character_id) {
		$results = $this->TurnAction->find('all',array(
			'conditions' => array('Character.id' => $character_id),
			'order' => array('Turn.id'),
			'contain' => array(
				'Character' => array('name','id','cardnumber'), 
				'Turn' => array('id'), 
				'Settlement' => array('id','name'), 
				'Land' => array('id','name'),
				'Action' => array('id','name'),
				'Business' => array('id','name'),
				'SettlementImprovement' => array('id','name','rank','commodity','Improvement' => array('name'))
			)
		));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');		
	}

	public function getBonusesForCharacter($character_id, $list_id) {
		$db = $this->Character->getDataSource();

		$charabilities = $db->fetchAll(
			'SELECT sum(quantity) as qty, a.id, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, ao.display_name as opt_disp, ca.abilityoption_id as option_id, at.name as type, ag.name as grp, ca.build_spent as build_spent from character_abilities ca LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN abilities ao on ca.abilityoption_id = ao.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where a.id in (select ability_id from list_abilities where elist_id in(270, ?) ) and ca.character_id = ? group by a.display_name, a.ability_name, ao.ability_name, ag.name, ca.build_spent order by ag.sorting_name asc, at.sorting_name, concat(a.sort_after_name,a.ability_name), ao.ability_name asc, build_spent',
			array($list_id, $character_id)
		);

		$results = array();
		foreach($charabilities as $row) {
			if($row[0]['qty'] > 0 && $row['a']['deprecated'] == 0) {

				$qty = $row[0]['qty'];
				$ability_name = $row['a']['ability'];
				$ability_display = $row['a']['display'];
				$ability_option = $row['ao']['opt'];
				$ability_option_display = $row['ao']['opt_disp'];

				if(!$ability_display) {
					$ability_display = $ability_name;
				}

				if (strpos($ability_display,'+1') !== false) {
					$ability_display = str_replace('+1','+'.$qty,$ability_display);
				}

				if(!$ability_option_display) {
					$ability_option_display = $ability_option;
				}

				if(strlen($ability_option) > 0) {
					$ability_display = $ability_display.' '.$ability_option_display;
				}

				$results[] = $ability_display;
			}
		}

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');	
	}

	public function getAllCurrentActionsForSettlementImpl($settlement_id) {
		$turn_id = $this->getCurrentTurnId();
		$results = $this->TurnAction->find('all',array(
			'conditions' => array(
				'Turn.id' => $turn_id,
				'Settlement.id' => $settlement_id,
				'TurnAction.business_id' => 0),
			'order' => array('Action.name')
		));

		return $results;
	}

	public function getAllCurrentActionsForSettlement($settlement_id) {
		$results = $this->getAllCurrentActionsForSettlementImpl($settlement_id);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');			
	}

	public function getAllCurrentActionsForSettlementImpl2($settlement_id) {
		$turn_id = $this->getCurrentTurnId();
		$results = $this->TurnAction->find('all',array(
			'conditions' => array(
				'Turn.id' => $turn_id,
				'Settlement.id' => $settlement_id),
			'order' => array('Action.business','Action.name'),
			'contain' => array('Character' => array('name','id','cardnumber'), 
				'Turn' => array('id'), 
				'Settlement' => array('id','name'), 
				'Land' => array('id','name'),
				'Action',
				'Business' => array('id','name'),
				'SettlementImprovement' => array('id','name','rank','commodity','Improvement' => array('name'))
			)
		));

		return $results;
	}

	public function getAllCurrentActionsForSettlement2($settlement_id) {
		$results = $this->getAllCurrentActionsForSettlementImpl2($settlement_id);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');			
	}

	public function getAllCurrentBusinessActionsForSettlementImpl($settlement_id) {
		$turn_id = $this->getCurrentTurnId();
		$results = $this->TurnAction->find('all',array(
			'conditions' => array(
				'Turn.id' => $turn_id,
				'Settlement.id' => $settlement_id,
				'TurnAction.business_id !=' => 0),
			'order' => array('Action.name')
		));

		return $results;
	}

	public function getAllCurrentBusinessActionsForSettlement($settlement_id) {
		$results = $this->getAllCurrentBusinessActionsForSettlementImpl($settlement_id);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');					
	}

	public function saveimprovement() {
		$data = $this->request->input('json_decode',true);

  		$this->SettlementImprovement->id = $data['id'];
  		$this->SettlementImprovement->set(array(
  			'business_id' => 		$data['business_id'],
  			'name' => 				$data['name'],
  			'rank' => 				$data['rank'],
  			'commodity' => 			$data['commodity']
  		));
  		$this->SettlementImprovement->save();

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function emailresults() {
		$turn_id = $this->getCurrentTurnId();
		$results = $this->TurnAction->find('all',array('conditions' => array('Turn.id' => $turn_id)));

		$emailconfig = Configure::read('PersonalAction.results');

		$users = array();

		foreach($results as $row) {
			if($row['TurnAction']['emailed'] == 0) {
				$user = $this->User->find('first',array('conditions' => array(
					'User.player_id' => $row['Character']['player_id']
				)));

				$username=$user['User']['username'];

				$this->TurnAction->id = $row['TurnAction']['id'];
				$this->TurnAction->set(array('emailed' => 1));
				$this->TurnAction->save();

				$cname = $row['Character']['name'];
				$action = $row['Action']['name'];
				$settlement = $row['Settlement']['name'];
				$land = $row['Land']['name'];
				$target = $row['TurnAction']['target'];
				$comments = $row['TurnAction']['comments'];
				$result = $row['TurnAction']['result'];

		    	$email = new CakeEmail();
				$email->config($emailconfig['config']);
				$email->to($username);
				$email->from($emailconfig['from']);
				$email->subject(sprintf("%s for %s in %s, %s",$emailconfig['subject'],$cname,$land,$settlement));
				$email->send(sprintf($emailconfig['body'],$cname,$action,$settlement,$land,$target,$comments,$result));
			}
		}

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');			
	}

	public function loadCompanies() {
    	$event_id = $this->getCurrentEventId();

		$results = $this->Business->find('all',array('order' => 'Business.name'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}
}
?>