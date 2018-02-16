<?php
class AttendanceController extends AppController {

	var $uses = array('Character', 'Player', 'Event','PlayerAttendance','CharacterAttendance');

	public function isAuthorized($user) {


    	if(AuthComponent::user('role_staff')) {
            return true;
	    }
	   
    	if(AuthComponent::user('role_cards')) {
            return true;
	    }

    	if(AuthComponent::user('role_newplayermarshal')) {
            return true;
	    }

	    if(AuthComponent::user('role_logistics')) {
	    	return true;
	    }

	    return parent::isAuthorized($user);
	}

	public function index() {
	}

	public function outstanding() {
	}

	public function loadAttendees() {
		$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

		$results = $this->PlayerAttendance->find('all',array('conditions' => array(
			'Event.id' => $event['Event']['id']
		)));

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadPlayers() {
		$result = $this->Player->find('all',array('order' => array('Player.name')));

		$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadEvent() {
		$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

		$this->set('ajax',json_encode($event));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadAttendance($player_id) {
		$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

		$playerattend = $this->PlayerAttendance->find('first',array('conditions' => array(
			'Player.id' => $player_id,
			'Event.id' => $event['Event']['id']
		)));

		$charattend = $this->CharacterAttendance->find('all',array('conditions' => array(
			'Player.id' => $player_id,
			'Event.id' => $event['Event']['id']
		)));

		$result = new stdClass();
    	$result->pa = $playerattend;
    	$result->ca = $charattend;

		$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function undoAttendance() {
		$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

		$data = $this->request->input('json_decode',true);
		$data['event'] = $event;

		$this->PlayerAttendance->deleteAll(array('Event.id' => $event['Event']['id'], 'Player.id' => $data['player']['Player']['id']));
		$this->CharacterAttendance->deleteAll(array('Event.id' => $event['Event']['id'], 'Player.id' => $data['player']['Player']['id']));

		$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function cardGivenOut() {
		$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

		$data = $this->request->input('json_decode',true);
		$data['event'] = $event;


		$result = $this->PlayerAttendance->find('first',array('conditions' => array(
			'Event.id' => $event['Event']['id'], 
			'Player.id' => $data['player']['Player']['id']
		)));

		if($result == null) {
			$this->PlayerAttendance->create();
			$this->PlayerAttendance->set(array(
				'player_id' =>  $data['player']['Player']['id'],
				'event_id' => $event['Event']['id'],
				'arrival' => date('c')
			));
			$this->PlayerAttendance->save();
		}

		if(isset($data['character'])) {
			$result = $this->CharacterAttendance->find('first',array('conditions' => array(
				'Event.id' => $event['Event']['id'], 
				'Character.id' => $data['character']['id']
			)));

			if($result == null) {
				$db = $this->Character->getDataSource();

		    	$level = $db->fetchAll(
					'SELECT sum(ca.quantity * ca.build_spent) as tot from character_abilities ca where ca.character_id = ?', array($data['character']['id'])
				);

				$three = $db->fetchAll(
					'SELECT sum(ca.quantity * ca.build_spent) as tot from character_abilities ca where ca.character_id = ? and ca.ability_id in (select id from abilities where ratio = 3.0)', array($data['character']['id'])
				);

				$two = $db->fetchAll(
					'SELECT sum(ca.quantity * ca.build_spent) as tot from character_abilities ca where ca.character_id = ? and ca.ability_id in (select id from abilities where ratio = 2.0)', array($data['character']['id'])
				);

				$one = $db->fetchAll(
					'SELECT sum(ca.quantity * ca.build_spent) as tot from character_abilities ca where ca.character_id = ? and ca.ability_id in (select id from abilities where ratio = 1.0)', array($data['character']['id'])
				);

				$bodypoints = $this->newbody($data['character']['id']);

				$this->CharacterAttendance->create();
				$this->CharacterAttendance->set(array(
					'player_id' =>  $data['player']['Player']['id'],
					'character_id' => $data['character']['id'],
					'event_id' => $event['Event']['id'], 
					'card_returned' => 0,
					'card_updated' => 0,
					'level' => $level[0][0]['tot'] / 10,
					'three' => $three[0][0]['tot'] / 10,
					'two'   => $two[0][0]['tot'] / 10,
					'one'   => $one[0][0]['tot'] / 10,
					'body'  => $bodypoints
				));
				$this->CharacterAttendance->save();

				$result = $this->CharacterAttendance->find('first',array('conditions' => array(
					'Event.id' => $event['Event']['id'], 
					'Character.id' => $data['character']['id']
				)));
			}
		}

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function cardTurnedIn() {
		$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

		$data = $this->request->input('json_decode',true);
		$data['event'] = $event;

		$result = $this->PlayerAttendance->find('first',array('conditions' => array(
			'Event.id' => $event['Event']['id'], 
			'Player.id' => $data['player']['Player']['id']
		)));

		if($result != null) {
			$this->PlayerAttendance->id = $result['PlayerAttendance']['id'];
			$this->PlayerAttendance->set(array(
				'departure' => date('c')
			));
			$this->PlayerAttendance->save();
		}

		$result = $this->CharacterAttendance->find('first',array('conditions' => array(
			'Event.id' => $event['Event']['id'], 
			'Character.id' => $data['character']['id']
		)));

		if($result != null) {
			
			$this->CharacterAttendance->id = $result['CharacterAttendance']['id'];
			$this->CharacterAttendance->set(array(
				'card_returned' => 1
			));
			$this->CharacterAttendance->save();

			$result = $this->CharacterAttendance->find('first',array('conditions' => array(
				'Event.id' => $event['Event']['id'], 
				'Character.id' => $data['character']['id']
			)));
		}

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function eventAttendance($event_id = null) {
		if($event_id == null) {
			$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

			$event_id = $event['Event']['id'];
		}

		$db = $this->CharacterAttendance->getDataSource();

		$levels = $db->fetchAll('Select avg(level) as lvl, avg(body) as body from character_attendance where card_returned = 0 and event_id = ?',array($event_id));

		$three = $db->fetchAll('Select avg(three) as three from character_attendance where card_returned = 0 and event_id = ? and three >= 5',array($event_id));
		$two = $db->fetchAll('Select avg(two) as two from character_attendance where card_returned = 0 and event_id = ? and two >= 5',array($event_id));
		$one = $db->fetchAll('Select avg(one) as one from character_attendance where card_returned = 0 and event_id = ? and one >= 5',array($event_id));

		$players = $db->fetchAll('Select count(id) as cnt from player_attendance where event_id = ?',array($event_id));

		$median_array = $db->fetchAll('Select level from character_attendance where card_returned = 0 and event_id = ? order by level',array($event_id));

		$median = $median_array[floor(count($median_array) / 2)]['character_attendance']['level'];

		$median_body = $db->fetchAll('Select body from character_attendance where card_returned = 0 and event_id = ? order by body',array($event_id));

		$medbody = $median_body[floor(count($median_body) / 2)]['character_attendance']['body'];

		$result = new stdClass();
    	$result->cnt = $players[0][0]['cnt'];
    	$result->lvl = $levels[0][0]['lvl'];
    	$result->three = $three[0][0]['three'];
    	$result->two = $two[0][0]['two'];
    	$result->one = $one[0][0]['one'];
    	$result->body = $levels[0][0]['body'];
    	$result->medbody = $medbody;
    	$result->median = $median;

		$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadOutstanding() {
		$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

		$result = $this->CharacterAttendance->find('all',array('conditions' => array(
			'Event.id' => $event['Event']['id'],
			'CharacterAttendance.card_updated' => 0
		)));

		$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function waiverReceived() {
		$data = $this->request->input('json_decode',true);

		$this->Player->id = $data['player']['Player']['id'];
		$this->Player->set(array(
			'has_waiver' => 1
		));
		$this->Player->save();

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function ageChecked() {
		$data = $this->request->input('json_decode',true);

		$this->Player->id = $data['player']['Player']['id'];
		$this->Player->set(array(
			'is_not_minor' => 1
		));
		$this->Player->save();

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
	}



	private function newbody($character_id) {

		$db = $this->Character->getDataSource();

		// Get starting body and Racial Modifier
		$results = $db->fetchAll(
			'SELECT a.BP as total, a.Ratio as racialmod FROM abilities a LEFT OUTER JOIN character_abilities ca on a.id = ca.ability_id WHERE a.abilitygroup_id = 23 and ca.character_id = ?',
			array($character_id)
		);

		$startingbody = $results[0]['a']['total'];
		$bodytotal = $startingbody;
		$racialmod = $results[0]['a']['racialmod'];

		// Calculate a characters unique ratio
		$ratioresults = $db->fetchAll(
			'SELECT sum(ca.quantity * ca.build_spent * a.ratio * 10) / sum(ca.quantity * ca.build_spent) as total FROM character_abilities ca LEFT OUTER JOIN abilities a on a.id = ca.ability_id WHERE a.ratio > 0 and ca.character_id = ?',
			array($character_id)
		);

		$ratioraw = $ratioresults[0][0]['total'];
		$ratio = $ratioraw * $racialmod;

		// working with 10s for rounding precision
		if($ratio < 10) {
			$ratio = 10;
		}
		if($ratioraw < 10) {
			$ratioraw = 10;
		}

		// Round up to the nearest tenth
		$ratio = ceil($ratio) / 10;
		$ratioraw = ceil($ratioraw) / 10;

		// Calculate the AP multiplier for this character
		if($ratioraw >= 2.5) {
			$armormod = 3;
		} else if($ratioraw >= 1.5) {
			$armormod = 2;
		} else {
			$armormod = 1;
		}

		// Factor in the Psionicist limitation
		// Check to see if the character is a Psionicist (fixed 1:1 ratio)
		$results = $db->fetchAll(
			'SELECT sum(ca.quantity) as total FROM character_abilities ca LEFT OUTER JOIN abilities a on a.id = ca.ability_id WHERE a.ability_name = "Psionicist (B)" and ca.character_id = ?',
			array($character_id)
		);

		$psion = $results[0][0]['total'];
		$this->set('psion',$psion);
		if($psion >= 1) {
			$ratio = 1;
			$ratioraw = 1;
			$armormod = 1;
		}

		// How much build has been spend on body
		$buildonbody = $db->fetchAll(
			'SELECT COALESCE(sum(ca.quantity * ca.build_spent),0) as total FROM character_abilities ca WHERE ca.ability_id in (21032,21033,21034) and ca.character_id = ?',
			array($character_id)
		);

		$bodybuild = $buildonbody[0][0]['total'];

		$count = 0;
		$cost = 1;
		$ranks[0]['ratio'] = $ratio + 1;
		$ranks[0]['build'] = 0;
		$ranks[0]['body'] = 0;
		$ranks[0]['cost'] = 1;

		// Compute the first 15 with the boost
		while($bodybuild > 0 && $count < 15) {
			$bodytotal += $ratio + 1;
			$count++;
			$bodybuild--;

			$ranks[0]['build']++;
			$ranks[0]['body'] += $ratio + 1;
		}

		// Round the body total up after each Rank
		$bodytotal = ceil($bodytotal);

		if($bodybuild > 0) {
			// Compute the rest
			$count = 0;
			$cost = 1;

			$ranks[$cost]['ratio'] = $ratio;
			$ranks[$cost]['build'] = 0;
			$ranks[$cost]['body'] = 0;
			$ranks[$cost]['cost'] = 1;

			while($bodybuild >= $cost) {
				$ranks[$cost]['build'] += $cost;
				$ranks[$cost]['body'] += $ratio;
				$bodybuild -= $cost;
				$bodytotal += $ratio;

				$count++;
				if($count >= 15) {
					$count = 0;
					$cost++;
					// Round the body total up after each Rank
					$bodytotal = ceil($bodytotal);

					if($bodybuild > 0) {
						$ranks[$cost]['ratio'] = $ratio;
						$ranks[$cost]['build'] = 0;
						$ranks[$cost]['body'] = 0;
						$ranks[$cost]['cost'] = $cost;
					}
				}
			}
		}

		return $bodytotal;
    }

    

}
?>