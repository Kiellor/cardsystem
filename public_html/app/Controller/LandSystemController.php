<?php
App::uses('CakeEmail', 'Network/Email');

class LandSystemController extends AppController {

	var $uses = array('Character','Player','User','Action','TurnAction','LandAdmin','Land','Settlement','Improvement','SettlementImprovement','Turn','SettlementValues','SettlementValuesAdjusted','SettlementBuilding','LandTrades','SettlementMessages');

	public function isAuthorized($user) {

		$results = $this->loadLandsImpl(AuthComponent::user('player_id'));
		if(count($results) > 0) {
			if (in_array($this->action, array('index','loadCharacters','loadLands','loadSettlements','loadLandValues',
				'savetradegoals','loadTurns','addBuilding','removeBuilding','changePriority','disableBuilding','enableBuilding','setManualStatus','manualbuild'))) {
				return true;
			}
		}

		if(AuthComponent::user('role_landadmin')) {
			return true;
		}

		return parent::isAuthorized($user);
	}

	public function trades() {

	}

	public function graph($settlement_id = 1) {
		$this->set('settlement_id',$settlement_id);
	}

	public function index() {
		//$this->render('war');
		$this->set('view_as_player_id','-1');
	}

	public function graphdata($settlement_id = 1) {

		$db = $this->SettlementValues->getDataSource();
		$results = $db->query('Select e.name as name, sv.public_order, sv.health, sv.happiness, sv.population, sv.gold, sv.stone, sv.lumber, sv.goods, sv.food from settlement_values sv LEFT OUTER JOIN turns t on sv.turn_id = t.id LEFT OUTER JOIN events e on t.after_event_id = e.id where settlement_id = ?',array($settlement_id));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function populationTotals() {

		$db = $this->SettlementValues->getDataSource();
		$results = $db->query('Select e.name as name, sum(sv.population) as population from settlement_values sv LEFT OUTER JOIN turns t on sv.turn_id = t.id LEFT OUTER JOIN events e on t.after_event_id = e.id group by t.id');

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}


	public function graphdetails($settlement_id = 1) {
		$results = $this->Settlement->find('first',array(
			'conditions' => array('Settlement.id' => $settlement_id),
			'contain' => array('Land')
		));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function viewAs($player_id = null) {
		if($player_id != null) {
			$this->set('view_as_player_id',$player_id);
		} else {
			$this->set('view_as_player_id',AuthComponent::user('player_id'));
		}
		$this->render('index');
	}

	// admin only
	public function loadPlayers() {
		$this->loadPlayersImpl();
	}


	public function loadCharacters() {
		$results = $this->loadCharactersImpl(AuthComponent::user('player_id'));
	
		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}
	
	public function loadLands() {
		$results = $this->loadLandsImpl(AuthComponent::user('player_id'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}
	
	public function loadSettlements() {
		$results = $this->loadSettlementsImpl(AuthComponent::user('player_id'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	// admin only
	public function loadCharactersAs($player_id = null) {
		$results = $this->loadCharactersImpl($player_id);
	
		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}
	
	// admin only
	public function loadLandsAs($player_id = null) {
		$results = $this->loadLandsImpl($player_id);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}
	
	// admin only
	public function loadSettlementsAs($player_id = null) {
		$results = $this->loadSettlementsImpl($player_id);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	
	private function loadPlayersImpl() {	
		$results = $this->LandAdmin->find('all',array(
			'order'=>array('Land.id')
		));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}
	
	private function loadCharactersImpl($id) {	
		return $this->Character->find('all',array(
			'conditions' => array('Character.player_id' => $id)
		));
	}

	public function loadLandsAdmin() {
		$currentTurn = $this->getCurrentTurnId();
		$prevTurn = $this->getPreviousTurnId(1);

		$ta = $this->TurnAction->find('all',array(
			'conditions' => array('TurnAction.turn_id' => $currentTurn),
			'contain' => array('Action')
		));
		$sv = $this->SettlementValues->find('all',array(
			'conditions' => array('SettlementValues.turn_id' => $prevTurn, 'Settlement.active' => 1),
			'contain' => array('Settlement' => array('Land','SettlementImprovement' => 'Improvement', 'SettlementBuilding' => 'Improvement'))
		));

		$data = array( 'TurnAction' => $ta, 'SettlementValues' => $sv );

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	private function loadLandsImpl($player_id = null) {
		if($player_id == null) {
			$player_id = AuthComponent::user('player_id');
		}
		return $this->LandAdmin->find('all',array(
				'conditions' => array('Character.player_id' => $player_id),
				'contain' => array(
					'Land',
					'Character' => array(
						'fields' => array('name','cardnumber','id','player_id')
					)
				) 
				
		));
	}

	public function saveAllMessages() {
		$data = $this->request->input('json_decode',true);

		$messages = $data['messages'];

		$keys = array_keys($messages);

		$turnid = $messages[$keys[0]]['turn_id'];
		$this->SettlementMessages->deleteAll(array('SettlementMessages.turn_id' => $turnid));

		foreach($keys as $key) {
			$this->SettlementMessages->create();
			$this->SettlementMessages->set(array(
				'turn_id' => $turnid,
				'land_id' => $messages[$key]['land_id'],
				'settlement_id' => $messages[$key]['settlement_id'],
				'order' => $messages[$key]['order'],
				'message' => $messages[$key]['message']
			));
			$this->SettlementMessages->save();
		}

		$this->set('ajax',json_encode($messages));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadTurnMessages($land_id, $turnid) {
		$messages = $this->SettlementMessages->find('all',array(
			'conditions' => array(
				'SettlementMessages.turn_id' => $turnid,
				'SettlementMessages.land_id' => $land_id
			)
		));

		$this->set('ajax',json_encode($messages));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function saveAllTrades() {
		$data = $this->request->input('json_decode',true);

		$trades = $data['trades'];
		$taxes = $data['taxes'];

		$keys = array_keys($trades);

		$turnid = $trades[$keys[0]]['turnid'];
		$this->LandTrades->deleteAll(array('LandTrades.turn_id' => $turnid, 'LandTrades.is_tax' => 0));

		foreach($keys as $key) {
			$line = $trades[$key]['from'] . " traded ";

			if(intval($trades[$key]['traded']['gold']) < 0) { $line = $line . (intval($trades[$key]['traded']['gold']) * -1) . ' gold '; }
			if(intval($trades[$key]['traded']['food']) < 0) { $line = $line . (intval($trades[$key]['traded']['food']) * -1) . ' food '; }
			if(intval($trades[$key]['traded']['stone']) < 0) { $line = $line . (intval($trades[$key]['traded']['stone']) * -1) . ' stone '; }
			if(intval($trades[$key]['traded']['lumber']) < 0) { $line = $line . (intval($trades[$key]['traded']['lumber']) * -1) . ' lumber '; }
			if(intval($trades[$key]['traded']['goods']) < 0) { $line = $line . (intval($trades[$key]['traded']['goods']) * -1) . ' goods '; }
			
			$line = $line . " to " . $trades[$key]['to'] . " for ";

			if(intval($trades[$key]['traded']['gold']) > 0) { $line = $line . intval($trades[$key]['traded']['gold']) . ' gold '; }
			if(intval($trades[$key]['traded']['food']) > 0) { $line = $line . intval($trades[$key]['traded']['food']) . ' food '; }
			if(intval($trades[$key]['traded']['stone']) > 0) { $line = $line . intval($trades[$key]['traded']['stone']) . ' stone '; }
			if(intval($trades[$key]['traded']['lumber']) > 0) { $line = $line . intval($trades[$key]['traded']['lumber']) . ' lumber '; }
			if(intval($trades[$key]['traded']['goods']) > 0) { $line = $line . intval($trades[$key]['traded']['goods']) . ' goods '; }

			$line = preg_replace('/\s+/', ' ',$line);

			$this->LandTrades->create();
			$this->LandTrades->set(array(
				'land_id' => $trades[$key]['fromid'],
				'turn_id' => $turnid,
				'trade_line' => $line,
				'is_tax' => 0
			));
			$this->LandTrades->save();
		}


		$keys = array_keys($taxes);
		$this->LandTrades->deleteAll(array('LandTrades.turn_id' => $turnid, 'LandTrades.is_tax' => 1));

		foreach($keys as $key) {
			$line = $taxes[$key]['name'] . " generated " . $taxes[$key]['amount'] . " gold in taxes";

			$this->LandTrades->create();
			$this->LandTrades->set(array(
				'land_id' => 1,
				'turn_id' => $turnid,
				'trade_line' => $line,
				'is_tax' => 1
			));
			$this->LandTrades->save();
		}

		$this->set('ajax',json_encode($trades));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function saveManualValues() {
		$allsettlements = $this->request->input('json_decode',true);

		if(count($allsettlements) > 0) {
	  		$this->SettlementValuesAdjusted->deleteAll(array('SettlementValuesAdjusted.id > ' => 0), false);

	  		foreach($allsettlements as $data) {
		  		$this->SettlementValuesAdjusted->create();
		  		$this->SettlementValuesAdjusted->set(array(
		  			'land_id' => 			$data['SettlementValues']['land_id'],
		  			'settlement_id' => 		$data['SettlementValues']['settlement_id'],
		  			'happiness' => 			$data['SettlementValues']['happiness'],
		  			'population' => 		$data['SettlementValues']['population'],
		  			'public_order' => 		$data['SettlementValues']['public_order'],
		  			'health' => 			$data['SettlementValues']['health'],
		  			'military' => 			$data['SettlementValues']['military'],
		  			'criminal' => 			$data['SettlementValues']['criminal'],
		  			'wildlands' => 			$data['SettlementValues']['wildlands'],
		  			'gold' =>	 			$data['SettlementValues']['gold'],
		  			'food' => 				$data['SettlementValues']['food'],
		  			'stone' => 				$data['SettlementValues']['stone'],
		  			'lumber' => 			$data['SettlementValues']['lumber'],
		  			'goods' => 				$data['SettlementValues']['goods']
		  		));
		  		$this->SettlementValuesAdjusted->save();
		  	}
		}

		$this->set('ajax',json_encode($allsettlements));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadManualValues() {
		$results = $this->SettlementValuesAdjusted->find('all');

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');		
	}

	public function saveAllSettlementValues() {
  		$allsettlements = $this->request->input('json_decode',true);

  		if(count($allsettlements) > 0) {
  			$turnid = $allsettlements[0]['turn_id'];

	  		$this->SettlementValues->deleteAll(array('SettlementValues.turn_id' => $turnid));

	  		foreach($allsettlements as $data) {
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
		  			'gold' =>	 			$data['gold_dist'],
		  			'food' => 				$data['food_dist'],
		  			'stone' => 				$data['stone_dist'],
		  			'lumber' => 			$data['lumber_dist'],
		  			'goods' => 				$data['goods_dist'],
		  			'gold_goal' =>	 		$data['gold_goal'],
		  			'food_goal' => 			$data['food_goal'],
		  			'stone_goal' => 		$data['stone_goal'],
		  			'lumber_goal' => 		$data['lumber_goal'],
		  			'goods_goal' => 		$data['goods_goal']
		  		));
		  		$this->SettlementValues->save();

		  		foreach($data['Settlement']['SettlementBuilding'] as $building) {
		  			$this->SettlementBuilding->save($building);
		  			
		  			if($building['completed'] == $turnid ) {
		  				$this->SettlementImprovement->deleteAll(array('SettlementImprovement.building_id' => $building['id']));

		  				$this->SettlementImprovement->create();
		  				$this->SettlementImprovement->set(array(
		  					'building_id' =>		$building['id'],
		  					'improvement_id' => 	$building['improvement_id'],
		  					'land_id' => 			$data['land_id'],
		  					'settlement_id' => 		$data['settlement_id'],
		  					'completed' =>			$data['turn_id']
		  				));
		  				$this->SettlementImprovement->save();
		  			}

		  			if($building['completed'] < ($turnid - 2)) {
		  				$this->SettlementBuilding->deleteAll(array('SettlementBuilding.id' => $building.id));
		  			}
		  		}

		  		foreach($data['Settlement']['SettlementImprovement'] as $building) {
		 			if($building['actions_repairing'] > 0) {
			  			$this->SettlementImprovement->id = $building['id'];
			  			$this->SettlementImprovement->set(array(
			  				'actions_to_repair' => $building['actions_to_repair']
			  			));
			  			$this->SettlementImprovement->save();
			  		}
		  		}
		  	}
	  	}

  		$this->set('ajax',json_encode($allsettlements));
		$this->layout = 'ajax';
		$this->render('ajax');
	}


	private function getPreviousTurnId($land_id) {
		$currentTurn = $this->getCurrentTurnId();

		$db = $this->TurnAction->getDataSource();
		$results = $db->query('SELECT max(turn_id) as turn_id from settlement_values where turn_id < ? and land_id = ?',array($currentTurn, $land_id));
		
		return $results[0][0]['turn_id'];
	}

	private function getCurrentTurn() {
		$db = $this->TurnAction->getDataSource();
		$results = $db->fetchAll('SELECT t.id, t.status, t.started, t.ended from turns t where id in (SELECT max(id) from turns)');
		
		return $results[0];
	}

	private function getCurrentTurnId() {
		$results = $this->getCurrentTurn();
		return $results['t']['id'];
	}

	public function loadTurns() {
		$allTurns = $this->Turn->find('all',array(
			'contain' => array('Event'),
			'conditions' => array('Turn.status > ' => 1),
			'order' => array('Turn.id DESC')
		));

		$this->set('ajax',json_encode($allTurns));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadLandValues($land_id, $turn_id = null) {

		if($turn_id == null) {
			$currTurn = $this->getCurrentTurnId($land_id);		
		} else {
			$currTurn = $turn_id;
		}

		$sv = $this->SettlementValues->find('all',array(
			'conditions' => array('SettlementValues.land_id' => $land_id, 'SettlementValues.turn_id' => $currTurn),
			'contain' => array(
				'Settlement' => array('SettlementBuilding' => array('Improvement'),'SettlementImprovement' => array('Improvement','Business'))),
			'order' => array('SettlementValues.population DESC')
		));

		$this->set('ajax',json_encode($sv));
		$this->layout = 'ajax';
		$this->render('ajax');		
	}

	public function loadTradeValues($land_id, $turn_id = null) {
		if($turn_id == null) {
			$currTurn = $this->getCurrentTurnId($land_id);		
		} else {
			$currTurn = $turn_id;
		}

		$trades = $this->LandTrades->find('all',array(
			'conditions' => array('LandTrades.land_id' => $land_id, 'LandTrades.turn_id' => $currTurn)
		));

		$this->set('ajax',json_encode($trades));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function saveimprovement() {
		$data = $this->request->input('json_decode',true);

		$this->Improvement->id = $data['Improvement']['id'];
		$this->Improvement->set(array(
			'sort' => $data['Improvement']['sort'],
			'name' => $data['Improvement']['name'],
			'description' => $data['Improvement']['description'],
			'turns_needed' => $data['Improvement']['turns_needed'],
			'stone_needed' => $data['Improvement']['stone_needed'],
			'wood_needed' => $data['Improvement']['wood_needed'],
			'gold_needed' => $data['Improvement']['gold_needed'],
			'labor_needed' => $data['Improvement']['labor_needed'],
			'goods_needed' => $data['Improvement']['goods_needed'],
			'maint_gold' => $data['Improvement']['maint_gold'],
			'maint_stone' => $data['Improvement']['maint_stone'],
			'maint_lumber' => $data['Improvement']['maint_lumber'],
			'maint_labor' => $data['Improvement']['maint_labor'],
			'maint_goods' => $data['Improvement']['maint_goods'],
			'population' => $data['Improvement']['population'],
			'public_order' => $data['Improvement']['public_order'],
			'health' => $data['Improvement']['health'],
			'happiness' => $data['Improvement']['happiness'],
			'food' => $data['Improvement']['food'],
			'food_storage' => $data['Improvement']['food_storage'],
			'gold' => $data['Improvement']['gold'],
			'goods' => $data['Improvement']['goods'],
			'stone' => $data['Improvement']['stone'],
			'lumber' => $data['Improvement']['lumber'],
			'military' => $data['Improvement']['military'],
			'wildlands' => $data['Improvement']['wildlands'],
			'taxes' => $data['Improvement']['taxes']
			));
		$this->Improvement->save();

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');	
	}

	public function savetradegoals() {
  		$data = $this->request->input('json_decode',true);

  		foreach($data as $settlement) {
	  		$this->SettlementValues->id = $settlement['SettlementValues']['id'];
  			$this->SettlementValues->set(array(
	  			'gold_goal' =>	 	$settlement['SettlementValues']['gold_goal'],
	  			'food_goal' => 		$settlement['SettlementValues']['food_goal'],
	  			'stone_goal' => 	$settlement['SettlementValues']['stone_goal'],
	  			'lumber_goal' => 	$settlement['SettlementValues']['lumber_goal'],
	  			'goods_goal' => 	$settlement['SettlementValues']['goods_goal']
	  		));
	  		$this->SettlementValues->save();
	  	}

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function addBuilding() {
		$data = $this->request->input('json_decode',true);

  		$this->SettlementBuilding->create();
		$this->SettlementBuilding->set(array(
  			'improvement_id' =>	 	$data['improvement_id'],
  			'settlement_id' => 		$data['settlement_id'],
  			'turns_left' => 		$data['turns_left'],
  			'max_labor' => 			$data['max_labor'],
  			'labor_left' => 		$data['labor_left'],
  			'priority' =>			$data['priority']
  		));
  		$this->SettlementBuilding->save();

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function disableBuilding() {
		$data = $this->request->input('json_decode',true);

  		$this->SettlementImprovement->id = $data['id'];
		$this->SettlementImprovement->set('active',0);
  		$this->SettlementImprovement->save();

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function enableBuilding() {
		$data = $this->request->input('json_decode',true);

  		$this->SettlementImprovement->id = $data['id'];
		$this->SettlementImprovement->set('active',1);
  		$this->SettlementImprovement->save();

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function removeBuilding() {
		$data = $this->request->input('json_decode',true);

		$this->SettlementBuilding->delete($data['id']);

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function changePriority() {
		$data = $this->request->input('json_decode',true);

		$this->SettlementBuilding->id = $data['id'];
		$this->SettlementBuilding->set('priority',$data['priority']);
  		$this->SettlementBuilding->save();

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function setManualStatus() {
		$data = $this->request->input('json_decode',true);

		$this->SettlementBuilding->id = $data['id'];
		$this->SettlementBuilding->set('manualbuild',$data['manualbuild']);
  		$this->SettlementBuilding->save();

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function manuallyStartBuilding() {
		$data = $this->request->input('json_decode',true);

		$this->SettlementBuilding->id = $data['SettlementBuilding']['id'];
		$this->SettlementBuilding->set(array(
			'manualbuild' => $data['SettlementBuilding']['manualbuild'],
			'paidfor' => $data['SettlementBuilding']['paidfor']
		));
  		$this->SettlementBuilding->save();

  		$this->SettlementValues->id = $data['SettlementValues']['id'];
		$this->SettlementValues->set(array(
			'gold' => $data['SettlementValues']['gold'],
			'stone' => $data['SettlementValues']['stone'],
			'lumber' => $data['SettlementValues']['lumber'],
			'goods' => $data['SettlementValues']['goods']
		));
  		$this->SettlementValues->save();

  		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}
}
?>