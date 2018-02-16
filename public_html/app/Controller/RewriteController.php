<?php
class RewriteController extends AppController {

	var $uses = array('Character', 'RewriteProposal', 'Ability', 'Player', 'Elist','CharacterAbility','ListAbility','Event');

	public function isAuthorized($user) {
	    // A user can view their own characters
	    if (in_array($this->action, array('index','loadProposal','loadCharacter','upgradeRace','submitProposal','deleteProposal','loadAllRaces'))) {

	    	$cardnumber = $this->params['pass'][0];
			$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
			if (!$character) {
				throw new NotFoundException(__('Invalid character'));
			}
	        if ($this->Character->isOwnedBySelf($character['Character']['player_id'])) {
	            return true;
	        }
	    }

    	if(AuthComponent::user('role_cards')) {
            return true;
	    }

	    return parent::isAuthorized($user);
	}

	public function index($cardnumber = null) {
    	$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));

		$this->set('character',$character);
		$this->set('cardnumber',$character['Character']['cardnumber']);
    }

    public function loadProposal($cardnumber = null) {
    	$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$character_id = $character['Character']['id'];

    	$result = $this->RewriteProposal->find('all',array('conditions' => array('RewriteProposal.character_id' => $character_id)));

    	$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function loadCharacter($cardnumber = null) {
    	$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$character_id = $character['Character']['id'];

    	$result = $this->Character->find('first',array('conditions' => array('Character.id' => $character_id)));

    	$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function loadAllRaces($cardnumber = null) {
    	$result = $this->Ability->find('all',array('conditions' => array('Ability.abilitytype_id' => 23, 'Ability.deprecated' => 0)));

    	$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function upgradeRace($cardnumber = null, $newlistid = null) {
    	$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$character_id = $character['Character']['id'];

    	$oldrace = $this->CharacterAbility->find('first',array(
    		'conditions' => array(
    			'CharacterAbility.character_id' => $character_id,
    			'Ability.deprecated !=' => 0,
    			'Ability.abilitygroup_id' => 23
    		)
    	));

    	$oldlist = $this->ListAbility->find('all',array(
    		'conditions' => array(
    			'ListAbility.elist_id' => $oldrace['Ability']['opens_list_id']
    		)
    	));

    	$oldabilities = array();
    	$upgradeLists = array();

    	foreach($oldlist as $key => $row) {
    		if($row['Ability']['abilitygroup_id'] == 21) {
    			
    			// Lower Lists with a cost greater than 10
    			if($row['ListAbility']['build_cost'] > 10) {
	    			$local = $this->CharacterAbility->find('all',array(
			    		'conditions' => array(
			    			'CharacterAbility.character_id' => $character_id,
			    			'CharacterAbility.ability_id' => $row['ListAbility']['ability_id'],
			    			'CharacterAbility.build_spent' => $row['ListAbility']['build_cost']
			    		), 'order' => array('Ability.sort_after_name','Ability.ability_name')
			    	));

			    	foreach($local as $key2 => $row2) {
			    		$local[$key2]['CharacterAbility']['newcost'] = 10;
			    	}

	    			$oldabilities = array_merge($oldabilities, $local);
		    	}

    		} else {

    			if($row['ListAbility']['abilityoption_id'] > 0) {
 
					$local = $this->CharacterAbility->find('all',array(
			    		'conditions' => array(
			    			'CharacterAbility.character_id' => $character_id,
			    			'CharacterAbility.ability_id' => $row['ListAbility']['ability_id'],
			    			'CharacterAbility.build_spent' => $row['ListAbility']['build_cost'],
			    			'CharacterAbility.abilityoption_id' => $row['ListAbility']['abilityoption_id']
			    		)
			    	));

			    	foreach($local as $ekey => $entry) {
			    		$local[$ekey]['abilityoption_id'] = $row['ListAbility']['abilityoption_id'];
			    	}

			    } else if($row['Ability']['uses_option_list']) {

			    	$local = $this->CharacterAbility->find('all',array(
			    		'conditions' => array(
			    			'CharacterAbility.character_id' => $character_id,
			    			'CharacterAbility.ability_id' => $row['ListAbility']['ability_id'],
			    			'CharacterAbility.build_spent' => $row['ListAbility']['build_cost']
			    		)
			    	));

	    		} else {

		    		$local = $this->CharacterAbility->find('all',array(
			    		'conditions' => array(
			    			'CharacterAbility.character_id' => $character_id,
			    			'CharacterAbility.ability_id' => $row['ListAbility']['ability_id'],
			    			'CharacterAbility.build_spent' => $row['ListAbility']['build_cost'],
			    			'CharacterAbility.abilityoption_id' => 0
			    		)
			    	));
		    	}

		    	$oldabilities = array_merge($oldabilities, $local);
		    }
    	}

    	if($newlistid == null) {
	    	$newrace = $this->Ability->find('first', array('conditions' => array('Ability.id' => $oldrace['Ability']['deprecated'])));
	    } else {
	    	$newrace = $this->Ability->find('first', array('conditions' => array('Ability.id' => $newlistid)));	    	
	    }

    	$newabilities = $this->ListAbility->find('all',array(
    		'conditions' => array(
    			'ListAbility.elist_id' => $newrace['Ability']['opens_list_id'],
    			'Ability.abilitygroup_id !=' => 21
    		), 'order' => array('Ability.sort_after_name','Ability.ability_name')
    	));

    	// Need to check old abilities against existing character lists to see if an existing racial that is going away can be picked up from a lower list

    	$db = $this->Character->getDataSource();

    	$otherlists = $db->fetchAll(
			'SELECT sum(ca.quantity) as tot, a.opens_list_id, a.id from character_abilities ca left outer join abilities a on ca.ability_id = a.id where a.opens_list_id > 0 and a.deprecated = 0 and ca.character_id = ? group by a.id', array($character_id)
		);

    	$otherListIds = array(43);
    	foreach($otherlists as $row) {
    		if($row[0]['tot'] > 0) {
	    		array_push($otherListIds, $row['a']['opens_list_id']);
	    	}
    	}

		foreach($oldabilities as $row) {
			if($row['abilityoption_id'] != 0) {
				$local = $this->ListAbility->find('all',array(
		    		'conditions' => array(
		    			'ListAbility.ability_id' => $row['Ability']['id'],
		    			'ListAbility.abilityoption_id' => $row['abilityoption_id'],
		    			'ListAbility.elist_id' => $otherListIds
		    		), 'order' => array('Ability.sort_after_name','Ability.ability_name')
		    	));
			} else {
				$local = $this->ListAbility->find('all',array(
		    		'conditions' => array(
		    			'ListAbility.ability_id' => $row['Ability']['id'],
		    			'ListAbility.elist_id' => $otherListIds
		    		), 'order' => array('Ability.sort_after_name','Ability.ability_name')
		    	));
			}

    		if(count($local) > 0) {
	    		$newabilities = array_merge($newabilities, $local);
	    	}
    	}

    	// Populate the new abilities with information about mandatory and optional ability options
    	foreach($newabilities as $key => $row) {
    		if($row['ListAbility']['abilityoption_id'] > 0) {
    			$feature = $this->Ability->find('first',array(
    				'conditions' => array('Ability.id' => $row['ListAbility']['abilityoption_id'])));
    			$newabilities[$key]['AbilityOption'] = $feature['Ability'];
    		} else if($row['Ability']['uses_option_list']) {
    			$options = $this->ListAbility->find('all', array(
    				'conditions' => array('ListAbility.elist_id' => $row['Ability']['uses_option_list']),
    				'order' => array('Ability.ability_name')));
    			$newabilities[$key]['AbilityOptionList'] = $options;
    		}
    	}

    	$result = new stdClass();
    	$result->OldRace = $oldrace;
    	$result->OldAbilities = $oldabilities;
    	$result->NewRace = $newrace;
    	$result->NewAbilities = $newabilities;

    	$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function findDeprecated($character_id = null) {
    	$result = $this->CharacterAbility->find('all',array(
    		'conditions' => array(
    			'CharacterAbility.character_id' => $character_id,
    			'Ability.deprecated !=' => 0
    		)
    	));

    	$this->set('ajax',json_encode($result));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function submitProposal($cardnumber = null) {
    	$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$character_id = $character['Character']['id'];

    	$data = $this->request->input('json_decode',true);

		$this->RewriteProposal->deleteAll(array('RewriteProposal.character_id' => $character_id));

		// Delete old Race
		$this->RewriteProposal->create();
		$this->RewriteProposal->set(array(
			'character_id' => $character_id,
			'ability_id' => $data['OldRace']['CharacterAbility']['ability_id'],
			'abilityoption_id' => 0,
			'build_spent' => 0,
			'quantity' => 1,
			'current_id' => $data['OldRace']['CharacterAbility']['id'],
			'delete' => 0,
			'newcost' => 0,
			'newability_id' => $data['NewRace']['Ability']['id'],
			'newability_display' => $data['NewRace']['Ability']['ability_name'],
			'adding' => 0
		));
		$this->RewriteProposal->save();

		foreach($data['OldAbilities'] as $row) {
			$this->RewriteProposal->create();
			if(isset($row['CharacterAbility']['newcost'])) {
				$this->RewriteProposal->set(array(
					'character_id' => $character_id,
					'ability_id' => $row['CharacterAbility']['ability_id'],
					'abilityoption_id' => $row['CharacterAbility']['abilityoption_id'],
					'build_spent' => $row['CharacterAbility']['build_spent'],
					'quantity' => $row['CharacterAbility']['quantity'],
					'current_id' => $row['CharacterAbility']['id'],
					'delete' => 0,
					'newcost' => $row['CharacterAbility']['newcost'],
					'newability_id' => 0,
					'adding' => 0
				));
			} else {
				$this->RewriteProposal->set(array(
					'character_id' => $character_id,
					'ability_id' => $row['CharacterAbility']['ability_id'],
					'abilityoption_id' => $row['CharacterAbility']['abilityoption_id'],
					'build_spent' => $row['CharacterAbility']['build_spent'],
					'quantity' => $row['CharacterAbility']['quantity'],
					'current_id' => $row['CharacterAbility']['id'],
					'delete' => 1,
					'newcost' => 0,
					'newability_id' => 0,
					'adding' => 0
				));
			}
			$this->RewriteProposal->save();
		}

		foreach($data['NewAbilities'] as $row) {
			if($row['CharacterAbility']['quantity'] > 0) {
				$this->RewriteProposal->create();
				$this->RewriteProposal->set(array(
					'character_id' => $character_id,
					'ability_id' => $row['ListAbility']['ability_id'],
					'abilityoption_id' => $row['ListAbility']['abilityoption_id'],
					'build_spent' => $row['ListAbility']['build_cost'],
					'quantity' => $row['CharacterAbility']['quantity'],
					'current_id' => 0,
					'delete' => 0,
					'newcost' => 0,
					'newability_id' => 0,
					'adding' => 1
				));
				
				if(isset($row['selectedOption']['Ability']['id'])) {
					$this->RewriteProposal->set(array(
						'abilityoption_id' => $row['selectedOption']['Ability']['id']
					));
				}

				$this->RewriteProposal->save();
			}
		}

    	$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function acceptProposal($cardnumber = null) {
		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$character_id = $character['Character']['id'];

		$event = $this->Event->find('first',array('order' => array('Event.id' => 'desc')));

    	$proposal = $this->RewriteProposal->find('all',array('conditions' => array('RewriteProposal.character_id' => $character_id)));

    	foreach($proposal as $row) {
    		if($row['RewriteProposal']['delete'] == 1) {
    			$this->CharacterAbility->deleteAll(array('CharacterAbility.id' => $row['RewriteProposal']['current_id']));
    		
    		} else if($row['RewriteProposal']['newcost'] > 0) {
				$this->CharacterAbility->id = $row['RewriteProposal']['current_id'];
				$this->CharacterAbility->set(array('build_spent' => $row['RewriteProposal']['newcost']));
				$this->CharacterAbility->save();

    		} else if($row['RewriteProposal']['newability_id'] > 0) {
    			$this->CharacterAbility->id = $row['RewriteProposal']['current_id'];
				$this->CharacterAbility->set(array('ability_id' => $row['RewriteProposal']['newability_id']));
				$this->CharacterAbility->save();

    		} else if($row['RewriteProposal']['adding'] == 1) {
    			$this->CharacterAbility->create();
    			$this->CharacterAbility->set(array(
    				'character_id' => $character_id,
    				'ability_id' => $row['RewriteProposal']['ability_id'],
    				'abilityoption_id' => $row['RewriteProposal']['abilityoption_id'],
    				'event_id' => $event['Event']['id'],
    				'build_spent' => $row['RewriteProposal']['build_spent'],
    				'quantity' => $row['RewriteProposal']['quantity']
    			));
    			$this->CharacterAbility->save();
    		}
    	}

    	$this->RewriteProposal->deleteAll(array('RewriteProposal.character_id' => $character_id));

    	$this->set('ajax',$cardnumber);
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function deleteProposal($cardnumber = null) {
    	$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$character_id = $character['Character']['id'];

		$this->RewriteProposal->deleteAll(array('RewriteProposal.character_id' => $character_id));

		$this->set('ajax',$cardnumber);
		$this->layout = 'ajax';
		$this->render('ajax');
    }

	public function listProposals() {

	}

    public function listProposalsImpl() {
    	$db = $this->Character->getDataSource();

    	$characters = $db->fetchAll(
			'SELECT c.cardnumber, c.name from characters c where c.id in (select distinct character_id from rewrite_proposals)'
		);

    	$this->set('ajax',json_encode($characters));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function listStragglers() {
    	$db = $this->Character->getDataSource();

    	$characters = $db->fetchAll(
			'SELECT c.cardnumber, c.name from characters c where c.cset_id = 2 and c.id in (select character_id from character_abilities where ability_id in (select id from abilities where deprecated > 0 and abilitytype_id = 23))'
		);

    	$this->set('ajax',json_encode($characters));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

}
?>
