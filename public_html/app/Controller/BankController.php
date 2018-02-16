<?php

class BankController extends AppController {

	var $uses = array('Character','CharacterDeposit','Business','BusinessDeposit','BusinessPartner',
		'Land','Settlement','Improvement','SettlementImprovement');

	public function isAuthorized($user) {
		if(in_array($this->action, array('myBusinesses','loadmycharacters'))) {
			return true;
		}

		if ($user['role_banking']) {
			return true;
		}

	    return parent::isAuthorized($user);
	}

	public function index() {
    }

    public function interest() {
    }

    public function companies() {
    }

    public function generateledgers() {
    }

    public function myBusinesses() {
    }

    public function addbusiness() {
    }

	private function loadmycharactersImpl($player_id = null) {
		$this->Character->Behaviors->load('Containable');

		$results = $this->Character->find('all',array(
			'fields' => array('id','name','cardnumber','cset_id','active'),
			'conditions' => array(
				'Player.id' => $player_id,
				'Character.cset_id' => 1,
				'Character.active' => 1),
			'contain' => array('Player', 'Business' => array(
				'SettlementImprovement' => array('Settlement', 'Land'),
				'BusinessPartner' => array('Character')))
		));

		$db = $this->Character->getDataSource();

		foreach($results as $key1 => $char) {
			foreach($char['Business'] as $key2 => $row) {
				$line = $this->getCompanyDeposits($row['id']);

				$results[$key1]['Business'][$key2]['Ledger'] = $line;
			}
		}

		return $results;
	}

	public function loadmycharacters() {
		$results = $this->loadmycharactersImpl(AuthComponent::user('player_id'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadmycharacterdetail($character_id) {

	}

    public function loadCharacters() {
    	$event_id = $this->getCurrentEventId();

		$results = $this->Character->query("
			select c.id, c.name, c.cardnumber, sum(value) as Character__bank_balance from character_deposits cd left outer join characters c on c.id = cd.character_id where c.cset_id = 1 and c.active = 1 and (cd.event_id != ? OR (cd.event_id = ? AND cd.is_interest = 0)) group by c.id order by Character__bank_balance desc",array($event_id,$event_id));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function savePartner() {
		$data = $this->request->input('json_decode',true);

		$this->BusinessPartner->id = $data['partner']['id'];
		$this->BusinessPartner->set(array(
			'position' => $data['partner']['position'],
			'gold_limit' => $data['partner']['gold_limit'],
			'commodities_limit' => $data['partner']['commodities_limit']
			));
		$this->BusinessPartner->save();

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function deletePartner() {
		$data = $this->request->input('json_decode',true);

		$this->BusinessPartner->delete($data['partner']['id']);

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function addPartner() {
		$data = $this->request->input('json_decode',true);

		$this->BusinessPartner->create();
		$this->BusinessPartner->set(array(
			'business_id' => $data['partner']['business_id'],
			'character_id' => $data['partner']['character_id'],
			'position' => $data['partner']['position'],
			'gold_limit' => $data['partner']['gold_limit'],
			'commodities_limit' => $data['partner']['commodities_limit']
			));
		$this->BusinessPartner->save();	

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function getCharacters() {
		$db = $this->Character->getDataSource();

		$data = $db->query('Select id, cardnumber, name from characters where cset_id = 1 order by cardnumber');

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function saveInterest() {
		$event_id = $this->getCurrentEventId();
		$data = $this->request->input('json_decode',true);


		$this->CharacterDeposit->deleteAll(array('CharacterDeposit.event_id' => $event_id, 'CharacterDeposit.is_interest' => true),false);

		foreach($data as $row) {
			$this->CharacterDeposit->create();
			$this->CharacterDeposit->set(array(
				'character_id' => $row['id'],
				'event_id' => $event_id,
				'value' => $row['interest'],
				'is_interest' => 1
			));
			$this->CharacterDeposit->save();
		}

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	private function getCurrentEventId() {
		$db = $this->CharacterDeposit->getDataSource();
		$results = $db->fetchAll('SELECT e.id  from events e where id in (SELECT max(id) from events)');
		
		return $results[0]['e']['id'];
	}


	public function loadCompanies() {
		$results = $this->Business->find('all',array('order' => 'Business.name'));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	private function getCompanyOverview($businessid) {
		return $this->Business->find('first',array('conditions' => array('Business.id' => $businessid)));
	}

	private function getCompanyDeposits($businessid) {
		$results = $this->BusinessDeposit->query(
			"select c.name, c.cardnumber, c.id, sum(gold) as BusinessDeposit__Gold_total, sum(luxury) as BusinessDeposit__Luxury_total, sum(durable) as BusinessDeposit__Durable_total, sum(wearable) as BusinessDeposit__Wearable_total, sum(consumable) as BusinessDeposit__Consumable_total from business_deposits BusinessDeposit left outer join characters c on c.id = BusinessDeposit.character_id where BusinessDeposit.business_id = ? group by c.id", array($businessid));

		return $results;
	}

	private function getCompanyDepositsTotal($businessid) {
		$results = $this->BusinessDeposit->query(
			"select sum(gold) as BusinessDeposit__Gold_total, sum(luxury) as BusinessDeposit__Luxury_total, sum(durable) as BusinessDeposit__Durable_total, sum(wearable) as BusinessDeposit__Wearable_total, sum(consumable) as BusinessDeposit__Consumable_total from business_deposits BusinessDeposit left outer join characters c on c.id = BusinessDeposit.character_id where BusinessDeposit.business_id = ? ", array($businessid));

		return $results;
	}

	public function makeCompanyDeposit() {
		$data = $this->request->input('json_decode',true);
		$event_id = $this->getCurrentEventId();

		$this->BusinessDeposit->create();
		$this->BusinessDeposit->set(array(
			'business_id' => $data['business']['Business']['id'],
			'character_id' => $data['character']['id'],
			'event_id' => $event_id,
			'gold' => $data['gold_total'],
			'luxury' => $data['luxury_total'],
			'durable' => $data['durable_total'],
			'consumable' => $data['consumable_total'],
			'wearable' => $data['wearable_total'],
			'is_interest' => 0,
			'turn_action_id' => 0
		));
		$this->BusinessDeposit->save();

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function makeCompanyDepositFromAction() {
		$data = $this->request->input('json_decode',true);
		$event_id = $this->getCurrentEventId();

		if($data['turn_action_id'] != null && $data['turn_action_id'] != 0) {
			$this->BusinessDeposit->deleteAll(array('BusinessDeposit.turn_action_id' => $data['turn_action_id']));
		}

		$this->BusinessDeposit->create();
		$this->BusinessDeposit->set(array(
			'business_id' => $data['business'],
			'character_id' => $data['character'],
			'event_id' => $event_id,
			'gold' => $data['gold_total'],
			'luxury' => $data['luxury_total'],
			'durable' => $data['durable_total'],
			'consumable' => $data['consumable_total'],
			'wearable' => $data['wearable_total'],
			'is_interest' => 0,
			'turn_action_id' => $data['turn_action_id']
		));
		$this->BusinessDeposit->save();

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadCompanyDetails() {
		$data = $this->request->input('json_decode',true);

		$results = $this->getCompanyDeposits($data['Business']['id']);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function generateCompanyLedger($businessid = null) {

		$details = $this->getCompanyOverview($businessid);
		$deposits = $this->getCompanyDepositsTotal($businessid);

		$this->set('details',$details);
		$this->set('deposits',$deposits);

		App::import('Vendor', 'Fpdf', array('file' => 'fpdf/fpdf.php'));
		$this->layout = 'pdf'; //this will use the pdf.ctp layout

		$this->response->type('pdf');

		$this->filename = str_replace(" ","_",$details['Business']['name']);
		$this->filename = str_replace("The_","",$this->filename);

		$this->set('filename',$this->filename);
		$this->set('fpdf', new FPDF('P','mm','Letter'));

		$this->render('ledgerpdf');
	}

	public function generateCompanyLedgerDebug($businessid = null) {

		$details = $this->getCompanyOverview($businessid);
		$deposits = $this->getCompanyDepositsTotal($businessid);

		$this->set('details',$details);
		$this->set('deposits',$deposits);

		$this->filename = sprintf("%04d_%s",$businessid,str_replace(" ","_",$details['Business']['name']));
		$this->set('filename',$this->filename);

		$this->layout = 'ajax';
	}

	public function addLedger() {
		$data = $this->request->input('json_decode',true);

		$this->Business->create();
		$this->Business->set('name', $data['name']);
		$this->Business->save();

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadimprovements() {
		$results = $this->Improvement->find('all', array(
			'order' => array('Improvement.sort','Improvement.name'),
			'conditions' => array('Improvement.sort' => 'Business')
		));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadlands() {
		$results = $this->Land->find('all',array('conditions' => array('Land.baronial' => 1)));

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

}
?>