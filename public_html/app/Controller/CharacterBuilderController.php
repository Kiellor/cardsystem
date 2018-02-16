<?php
App::uses('CakeEmail', 'Network/Email');

class CharacterBuilderController extends AppController {

	var $uses = array('Character','Player','User','CharacterAbility','CharacterBuildpoints');

	public function beforeFilter() {
        parent::beforeFilter();

		$this->Auth->allow('index','printable','getRaces','getReligions','getCommonRacialAbilities','getProfessionsForRace','getOptionList','getAbilitiesForProfession','getSubListAbilities','sendemail');
    }

	public function isAuthorized($user) {

		// Common functions
		if (in_array($this->action, array('index','printable','getRaces','getReligions','getCommonRacialAbilities','getProfessionsForRace','getOptionList','getAbilitiesForProfession','getSubListAbilities','sendemail'))) {
	        return true;
	    }

	    if(in_array($this->action, array('waiting','tracking','import'))) {
	    	if(AuthComponent::user('role_cards')) {
	            return true;
		    }
		    if(AuthComponent::user('role_newplayermarshal')) {
		    	return true;
		    }
	    }

	    return parent::isAuthorized($user);
	}

    public function construction() {
    	$this->render('underconstruction');
    }

    public function index($token = null) {
    	//$this->set('auth', json_encode($this->Auth));

    	$this->set('clientIP',$this->request->clientIp());

    	if($token != null) {
    		$this->set('token',$token);
	   		$this->set('printing',false);

	   		if(AuthComponent::user('role_cards')) {
		   		$this->set('isAdmin',true);
		    } else if(AuthComponent::user('role_newplayermarshal')) {
		    	$this->set('isAdmin',true);
		    } else if(AuthComponent::user('role_admin')) {
		   		$this->set('isAdmin',true);
		   	} else {
			   	$this->set('isAdmin',false);
		   	}

		   	$this->set('redirect',false);
    	} else {
    		$chars = 'abcdefghijklmnopqrstuvwxyz';
    		$result = '';
    		for ($i = 0; $i < 2; $i++) {
    			$result .= $chars[rand(1,26)];
    		}

    		$chars = '0123456789';
    		for ($i = 0; $i < 6; $i++) {
    			$result .= $chars[rand(1,10)];
    		}
    		
    		$this->set('token',$result);
    		$this->set('redirect',true);
    		//return $this->redirect("/character_builder/index/".$result);
    	}

    	$this->layout = 'nomenu';
    }

    public function waiting() {

    }

    public function tracking() {

    }

    public function cleanup() {

    }

    public function printable($token) {
   		$this->set('token',$token);
   		$this->set('printing',true);
   		$this->set('isAdmin',false);
    	$this->layout = 'mobile';
    	$this->render('index');
    }

    public function indexdev($token = null) {
		//$this->set('auth', json_encode($this->Auth));

    	$this->set('clientIP',$this->request->clientIp());

    	if($token != null) {
    		$this->set('token',$token);
	   		$this->set('printing',false);

	   		if(AuthComponent::user('role_cards')) {
		   		$this->set('isAdmin',true);
		    } else if(AuthComponent::user('role_newplayermarshal')) {
		    	$this->set('isAdmin',true);
		    } else if(AuthComponent::user('role_admin')) {
		   		$this->set('isAdmin',true);
		   	} else {
			   	$this->set('isAdmin',false);
		   	}

		   	$this->set('redirect',false);
    	} else {
    		$chars = 'abcdefghijklmnopqrstuvwxyz';
    		$result = '';
    		for ($i = 0; $i < 2; $i++) {
    			$result .= $chars[rand(1,26)];
    		}

    		$chars = '0123456789';
    		for ($i = 0; $i < 6; $i++) {
    			$result .= $chars[rand(1,10)];
    		}
    		
    		$this->set('token',$result);
    		$this->set('redirect',true);
    		//return $this->redirect("/character_builder/index/".$result);
    	}

    	$this->layout = 'nomenu';
	}

    public function sendemail() {
    	$data = $this->request->input('json_decode',true);

		$emailconfig = Configure::read('CharacterBuilder.submit');

    	$email = new CakeEmail();
		$email->config($emailconfig['config']);
		$email->to("cardissues@knightrealms.com");
		$email->cc("development@knightrealms.com");
		$email->from("noreply@knightrealms.com");
		$email->subject(sprintf("%s (%s)",$emailconfig['subject'],$data['token']));
		$email->send(sprintf($emailconfig['body'],$data['token']));

		$this->set('ajax',json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');			
    }

    public function import() {
		$importSuccess = false;

		$data = $this->request->input('json_decode',true);
		$character = $data['character'];
		$abilities = $data['abilities'];

		$importTo = $this->Character->find('first', array('conditions' => array('Character.cardnumber' => $character['cardnumber'])));

		$db = $this->Character->getDataSource();

		if($importTo != null) {

			$character_id = $importTo['Character']['id'];

			$results = $db->fetchAll(
				'SELECT count(ca.id) as cnt from character_abilities ca where ca.character_id = ?', array($character_id)
			);

			$importSuccess = $results[0][0]['cnt'];

			if($results[0][0]['cnt'] == 0) {

				// Create Race
				$this->CharacterAbility->create();
				$this->CharacterAbility->set(array(
					'character_id' => $character_id,
					'ability_id' => $character['race']['a']['id'],
					'build_spent' => 0,
					'event_id' => 0,
					'teacher_id' => 0,
					'abilityoption_id' => 0,
					'quantity' => 1
				));
				$this->CharacterAbility->save();

				if(isset($character['religion'])) {
					// Enter Religion
					$this->CharacterAbility->create();
					$this->CharacterAbility->set(array(
						'character_id' => $character_id,
						'ability_id' => $character['religion']['a']['id'],
						'build_spent' => 0,
						'event_id' => 0,
						'teacher_id' => 0,
						'abilityoption_id' => 0,
						'quantity' => 1
					));
					$this->CharacterAbility->save();
				}

				foreach($abilities as $row) {
					$this->CharacterAbility->create();

					$option_picked = 0;
					if($row['la']['abilityoption_id'] > 0) {
						$option_picked = $row['la']['abilityoption_id'];
					} else if($row['a']['uses_option_list'] > 0) {
						$option_picked = $row['option_picked']['a']['id'];
					}

					$this->CharacterAbility->set(array(
						'character_id' => $character_id,
						'ability_id' => $row['a']['id'],
						'build_spent' => $row['la']['build_cost'],
						'event_id' => 0,
						'teacher_id' => 0,
						'abilityoption_id' => $option_picked,
						'quantity' => $row['qty']
					));
					$this->CharacterAbility->save();
				}
			}
		}

		$this->set('ajax',$importSuccess);
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function getRaces() {

    	$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT a.id, a.opens_list_id, a.ability_name, a.display_name, a.BP, a.Ratio from abilities a where a.abilitygroup_id = 23 and deprecated = 0'
		);

		$this->set('ajax', json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

    public function getReligions() {
    	$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT a.id, a.opens_list_id, a.ability_name, a.display_name, a.BP from abilities a where a.abilitygroup_id = 26'
		);

		$this->set('ajax', json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function getCommonRacialAbilities($racelistid) {
    	$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT distinct a.id, a.ability_name, a.display_name, a.cost_increase_interval, a.opens_list_id, a.uses_option_list, a.Ratio, a.abilitytype_id, a.uses, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP, la.elist_id, la.build_cost, la.prerequisites, la.sort_order, la.footnote, la.is_footnote, la.free_set, la.free_set_limit, la.abilityoption_id, alo.ability_name, ag.name, ag.sorting_name from abilities a LEFT OUTER JOIN list_abilities la on la.ability_id = a.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN abilities alo on la.abilityoption_id = alo.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where a.opens_list_id = 0 and la.build_cost > -1 and la.elist_id in (43,'.$racelistid.') order by la.sort_order'
		);

		$this->set('ajax', json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');		
	}

	public function getOptionList($option_list_id) {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT a.id, a.ability_name, a.display_name, a.cost_increase_interval, a.opens_list_id, a.uses_option_list, a.Ratio, a.abilitytype_id, a.BP from abilities a LEFT OUTER JOIN list_abilities la on la.ability_id = a.id where la.elist_id = ?',
			array($option_list_id)
		);

		$this->set('ajax', json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

    public function getProfessionsForRace($raceid) {
    	$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT a.id, a.ability_name, a.display_name, a.cost_increase_interval, a.opens_list_id, a.uses_option_list, a.Ratio, a.abilitytype_id, a.uses, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP, la.elist_id, la.build_cost, la.prerequisites, la.sort_order, la.footnote, la.is_footnote, la.free_set, la.free_set_limit, la.abilityoption_id, ag.name, ag.sorting_name from abilities a LEFT OUTER JOIN list_abilities la on la.ability_id = a.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where a.abilitygroup_id = 21 and la.elist_id in (select opens_list_id from abilities where id = ?) order by a.ability_name',
			array($raceid)
		);

		$this->set('ajax', json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

    public function getAbilitiesForProfession($list) {

    	$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT distinct a.id, a.ability_name, a.display_name, a.cost_increase_interval, a.opens_list_id, a.uses_option_list, a.Ratio, a.abilitytype_id, a.uses, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP, la.elist_id, la.build_cost, la.prerequisites, la.sort_order, la.footnote, la.is_footnote, la.free_set, la.free_set_limit, la.abilityoption_id, alo.ability_name, ag.name, ag.sorting_name from abilities a LEFT OUTER JOIN list_abilities la on la.ability_id = a.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id LEFT OUTER JOIN abilities alo on la.abilityoption_id = alo.id where la.build_cost > -1 and la.elist_id = ? order by la.sort_order', array($list)
		);

		$this->set('ajax', json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function getSubListAbilities() {
		$data = $this->request->input('json_decode',true);

    	$db = $this->Character->getDataSource();

		$allresults = array();
		foreach($data as $row) {
			$results = $db->fetchAll(
			'SELECT distinct a.id, a.ability_name, a.display_name, a.cost_increase_interval, a.opens_list_id, a.uses_option_list, a.Ratio, a.abilitytype_id, a.uses, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP, la.elist_id, la.build_cost, la.prerequisites, la.sort_order, la.footnote, la.is_footnote, la.free_set, la.free_set_limit, la.abilityoption_id, alo.ability_name, ag.name, ag.sorting_name from abilities a LEFT OUTER JOIN list_abilities la on la.ability_id = a.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id LEFT OUTER JOIN abilities alo on la.abilityoption_id = alo.id where la.build_cost > -1 and la.elist_id = ? order by la.elist_id, la.sort_order', array($row['opens_list_id'])
			);

			$item = (object) array('ability' => $row, 'results' => $results);
			
			array_push($allresults,$item);
		}

		$this->set('ajax', json_encode($allresults));
		$this->layout = 'ajax';
		$this->render('ajax');
	}
}
?>
