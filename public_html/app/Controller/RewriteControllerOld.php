<?php
class RewriteController extends AppController {

	var $uses = array('Character', 'RewriteProposal', 'Ability', 'Player', 'Elist','CharacterAbility');

	public function isAuthorized($user) {
	    // A user can view their own characters
	    if (in_array($this->action, array('index','worksheet','propose','viewproposal','reset','event'))) {

	    	$cardnumber = $this->params['pass'][0];
			$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
			if (!$character) {
				throw new NotFoundException(__('Invalid character'));
			}
	        if ($this->Character->isOwnedBySelf($character['Character']['player_id'])) {
	            return true;
	        }
	    }

	    return parent::isAuthorized($user);
	}

	public function index($cardnumber = null) {
		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$this->set('character',$character);

		$db = $this->Character->getDataSource();

		// Get list of proposed abilities for this character
		$charabilities = $db->fetchAll(
			'SELECT sum(quantity) as qty, a.id, a.opens_list_id, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, ao.display_name as opt_disp, ca.abilityoption_id as option_id, "other" as type, ag.name as grp, ca.build_spent as build_spent, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP from rewrite_proposals ca LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN abilities ao on ca.abilityoption_id = ao.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where ag.stackable = 1 and ca.character_id = ? group by a.display_name, a.ability_name, ao.ability_name, ag.name, ca.build_spent order by ag.sorting_name asc, at.sorting_name, concat(a.sort_after_name,a.ability_name), ao.ability_name asc, build_spent',
			array($id)
		);

		if(count($charabilities) > 0) {
			$this->redirect('/rewrite/viewproposal/'.$cardnumber);
		} else {
			$this->redirect('/rewrite/worksheet/'.$cardnumber);
		}

    }

    public function pending() {
   		$db = $this->Character->getDataSource();

		// Get list of characters that have proposed rewrites
		$characters = $db->fetchAll(
			'SELECT distinct rp.character_id, c.name, c.cardnumber from rewrite_proposals rp LEFT OUTER JOIN characters c on c.id = rp.character_id'
		);

		$this->set('characters',$characters);
		$this->set('debug',$characters);
    }



    public function worksheet($cardnumber = null) {

		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$lists = array();
		$otherlists = array();

		$this->set('character',$character);

		$db = $this->Character->getDataSource();

		$player_id = $character['Player']['id'];

		$results = $db->fetchAll(
			'SELECT COALESCE(SUM(ca.quantity * ca.build_spent),0) as build from character_abilities ca where ca.character_id = ?'
			,array($id)
		);

		$build_spent = $results[0][0]['build'];
		if($build_spent == null) {
			$build_spent = 0;
		}
		$this->set('build_spent',$build_spent);

		$results = $db->fetchAll(
			'SELECT COALESCE(sum(base + service + bought + roleplay_build + lifestyle + other),0) as total from character_buildpoints bp where bp.character_id = ?',
			array($id)
		);
		$this->set('build_earned',$results[0][0]['total']);

		// Get list of all abilities for this character
		$charabilities = $db->fetchAll(
			'SELECT sum(quantity) as qty, a.id, a.opens_list_id, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, ao.display_name as opt_disp, ca.abilityoption_id as option_id, "other" as type, ag.name as grp, ca.build_spent as build_spent, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP from character_abilities ca LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN abilities ao on ca.abilityoption_id = ao.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where ag.stackable = 1 and ca.character_id = ? and a.deprecated = 1 group by a.display_name, a.ability_name, ao.ability_name, ag.name, ca.build_spent order by ag.sorting_name asc, at.sorting_name, concat(a.sort_after_name,a.ability_name), ao.ability_name asc, build_spent',
			array($id)
		);

		$abilitygroups = $db->fetchAll(
			'SELECT ag.name as grp from ability_groups ag where ag.stackable = 1 order by ag.sorting_name asc'
		);

		$results = $db->fetchAll(
			'SELECT e.name, ca.quantity as qty, a.deprecated, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, at.name as type, ca.id as caid, ca.build_spent as build_spent from character_abilities ca LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN abilities ao on ca.abilityoption_id = ao.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN events e on ca.event_id = e.id where ca.character_id = ? order by created desc',
			array($id)
		);

		$this->set('character_abilities',$this->Character->adjustAbilities($charabilities, false, $abilitygroups));


		// Get the master and heroic lists
		$results = $db->fetchAll('SELECT a.ability_name, a.id, a.opens_list_id from abilities a where a.opens_list_id in (select id from elists where list_type_id = 12) and deprecated = 0');
		$this->set('masterlists',$results);

		$results = $db->fetchAll('SELECT a.ability_name, a.id, a.opens_list_id from abilities a where a.opens_list_id in (select id from elists where list_type_id = 13) and deprecated = 0');
		$this->set('heroiclists',$results);



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
		$this->set('abilities_no_options', $this->Ability->find('list',array('fields'=>array('id','ability_name'),'conditions'=>array('Ability.abilitytype_id != '=>'25'))));
    }

	public function reset($cardnumber = null) {
		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$this->RewriteProposal->deleteAll(array('RewriteProposal.character_id' => $id), false);

		$this->redirect('/rewrite/worksheet/'.$cardnumber);
	}

    public function propose() {
		if ($this->request->is('post')) {

			$abilities = array();
			$other = array();
			foreach(array_keys($this->request->data) as $key) {
				// find the last '_' character and pull out the row number element, build an array of skills from this

				$pos = strrpos($key,'_');
				if($pos > 0) {
					$rownum = substr($key,$pos+1);
					$rowkey = substr($key,0,$pos);

					if( !array_key_exists($rownum,$abilities) ) {
						$abilities[$rownum] = array();
					}

					$abilities[$rownum][$rowkey] = $this->request->data[$key];
				} else {
					$other[$key] = $this->request->data[$key];
				}
			}

			$this->Character->id = $other['characterid'];

			foreach($abilities as $row) {
				switch($row['input_type']) {

				case 'skill' :
					if($row['quantity'] != 0) {
						$this->RewriteProposal->create();

						$this->RewriteProposal->set(array(
							'character_id' => $other['characterid'],
							'ability_id' => $row['ability_id'],
							'build_spent' => $row['build_spent'],
							'abilityoption_id' => $row['option_id'],
							'quantity' => $row['quantity']
						));
						$this->RewriteProposal->save();
					}
					break;
				}
			}

			$this->redirect('/rewrite/viewproposal/'.$other['cardnumber']);
	   	}
    }

    public function viewproposal($cardnumber = null) {

		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$this->set('character',$character);

		$db = $this->Character->getDataSource();

		// Get list of proposed abilities for this character
		$charabilities = $db->fetchAll(
			'SELECT sum(quantity) as qty, a.id, a.opens_list_id, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, ao.display_name as opt_disp, ca.abilityoption_id as option_id, "other" as type, ag.name as grp, ca.build_spent as build_spent, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP from rewrite_proposals ca LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN abilities ao on ca.abilityoption_id = ao.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where ag.stackable = 1 and ca.character_id = ? group by a.display_name, a.ability_name, ao.ability_name, ag.name, ca.build_spent order by ag.sorting_name asc, at.sorting_name, concat(a.sort_after_name,a.ability_name), ao.ability_name asc, build_spent',
			array($id)
		);

		$abilitygroups = $db->fetchAll(
			'SELECT ag.name as grp from ability_groups ag where ag.stackable = 1 order by ag.sorting_name asc'
		);

		$this->set('character_abilities',$this->Character->adjustAbilities($charabilities, false, $abilitygroups));
	}

	public function accept($cardnumber = null) {

		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$this->set('character',$character);

		$db = $this->Character->getDataSource();

		$lastevent = $db->fetchAll('SELECT max(id) as id from events');

		// Get list of proposed abilities for this character
		$charabilities = $db->fetchAll(
			'SELECT sum(quantity) as qty, rp.ability_id,  rp.abilityoption_id, rp.build_spent from rewrite_proposals rp where rp.character_id = ? group by rp.ability_id, rp.abilityoption_id, rp.build_spent',
			array($id)
		);

		foreach($charabilities as $row) {
			$this->CharacterAbility->create();
			$this->CharacterAbility->set(array(
				'character_id' => $id,
				'ability_id' => $row['rp']['ability_id'],
				'build_spent' => $row['rp']['build_spent'],
				'event_id' => $lastevent[0][0]['id'],
				'abilityoption_id' => $row['rp']['abilityoption_id'],
				'quantity' => $row[0]['qty']
			));
			$this->CharacterAbility->save();
		}

		$this->RewriteProposal->deleteAll(array('RewriteProposal.character_id' => $id), false);
		$this->redirect('/characters/view/'.$cardnumber);
	}

    private function getIntValue($array, $key, $default) {
    	return intval($this->getValue($array, $key, $default));
    }

    private function getFloatValue($array, $key, $default) {
    	return floatval($this->getValue($array, $key, $default));
    }

    private function getValue($array, $key, $default) {
    	if(array_key_exists($key,$array)) {
    		if($array[$key] == null) {
    			return $default;
    		}
    		return $array[$key];
    	} else {
    		return $default;
    	}
    }

    public function getoptionslist($id = null) {
    	$db = $this->Character->getDataSource();

    	$results = $db->fetchAll(
					'SELECT id, ability_name from abilities where id in (select ability_id from list_abilities where elist_id = ?) order by ability_name asc',
					array($id)
				);

		$options = array();
		foreach ($results as $row) {
			$options[$row['abilities']['id']] = $row['abilities']['ability_name'];
		}


		$this->layout = 'ajax';
    	$this->set('options',$options);
    }

    public function getlistabilities($id = null) {
		if (!$id) {
			throw new NotFoundException(__('Invalid elist'));
		}

		$db = $this->Elist->getDataSource();

		$results = $db->fetchAll(
			'SELECT a.id, ao.id, a.display_name, a.ability_name, la.build_cost, at.name as type, ag.name as grp, ao.ability_name as opt, ao.display_name as opt_disp from abilities a LEFT JOIN list_abilities la on a.id = la.ability_id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id LEFT OUTER JOIN abilities ao on la.abilityoption_id = ao.id where la.elist_id = ? order by ag.sorting_name asc, at.sorting_name, concat(a.sort_after_name,a.ability_name)',
			array($id)
		);

		$this->set('abilities', $results);
		$this->layout = 'ajax';
    }

}
?>
