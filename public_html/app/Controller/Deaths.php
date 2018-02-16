<?php
class CharactersController extends AppController {

	var $uses = array('Character','QuestProgress','Player');

	private $filename = "unset";

	public function isAuthorized($user) {
		if (in_array($this->action, array('getnextid'))) {
			return true;
		}

	    // A user can view their own characters
	    if (in_array($this->action, array('view','viewratios','newbody','newbodyajax'))) {

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

	    if(AuthComponent::user('role_logistics')) {
	    	if(in_array($this->action, array('downloadpdf'))) {
	    		return true;
	    	}
	    }

	    if(AuthComponent::user('role_newplayermarshal')) {
	    	if(in_array($this->action, array('add','loadPlayers','actualAddCharacter','getCsets','add_new'))) {
	    		// Need to check these for NEW characters
	    		return true;
	    	}

	    	$cardnumber = $this->params['pass'][0];
			$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
			if (!$character) {
				throw new NotFoundException(__('Invalid character'));
			}
	        if ($character['Character']['new_character'] == 1) {
	            return true;
	        }
	    }

	    return parent::isAuthorized($user);
	}

	public function index($csetid = 1) {
		$this->set('csets', $this->Character->Cset->find('all',array('order' => 'Cset.id')));

		if($csetid !== null) {
			$db = $this->Character->getDataSource();

			$results = $db->fetchAll(
				'SELECT c.id, c.cardnumber, c.name from characters c where c.cset_id = ? order by c.cardnumber asc',
				array($csetid)
			);

    		$this->set('characters', $results);
    		$this->set('count', count($results));
	    }
    }

	public function tagged($tag = null, $match = 'true', $csetid = 1) {
		if($match == 'true') {
			$results = $this->Character->find('all',array('order' => 'Character.cardnumber asc', 'conditions' => array('Character.cset_id' => $csetid, 'Character.st_only_notes LIKE' => '%'.$tag.'%')));
		} else {
			$results = $this->Character->find('all',array('order' => 'Character.cardnumber asc', 'conditions' => array('Character.cset_id' => $csetid, 'Character.st_only_notes NOT LIKE' => '%'.$tag.'%')));
		}

		$this->set('characters', $results);
		$this->set('count', count($results));
    }

    public function newbodyajax($cardnumber = null) {
    	if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$db = $this->Character->getDataSource();

		// Get starting body and Racial Modifier
		$results = $db->fetchAll(
			'SELECT a.BP as total, a.Ratio as racialmod FROM abilities a LEFT OUTER JOIN character_abilities ca on a.id = ca.ability_id WHERE a.abilitygroup_id = 23 and ca.character_id = ?',
			array($id)
		);

		$startingbody = $results[0]['a']['total'];
		$bodytotal = $startingbody;
		$racialmod = $results[0]['a']['racialmod'];

		// Show the distribution of points by ratio
		$results = $db->fetchAll(
			'SELECT sum(ca.quantity * ca.build_spent) as total, a.ratio FROM character_abilities ca LEFT OUTER JOIN abilities a on a.id = ca.ability_id WHERE ca.character_id = ? group by a.ratio order by a.ratio desc',
			array($id)
		);

		$showwork = array();
		$showwork["3.0"] = 0;
		$showwork["2.0"] = 0;
		$showwork["1.0"] = 0;
		$showwork["0.0"] = 0;
		foreach($results as $work) {
			$showwork[$work['a']['ratio']] = $work[0]['total'];
		}

		// How much build has been spend on body
		$buildonbody = $db->fetchAll(
			'SELECT COALESCE(sum(ca.quantity * ca.build_spent),0) as total FROM character_abilities ca WHERE ca.ability_id in (21032,21033,21034) and ca.character_id = ?',
			array($id)
		);

		$bodybuild = $buildonbody[0][0]['total'];

		// Factor in the Psionicist limitation
		// Check to see if the character is a Psionicist (fixed 1:1 ratio)
		$results = $db->fetchAll(
			'SELECT sum(ca.quantity) as total FROM character_abilities ca LEFT OUTER JOIN abilities a on a.id = ca.ability_id WHERE a.ability_name = "Psionicist (B)" and ca.character_id = ?',
			array($id)
		);

		$psion = $results[0][0]['total'];

		$data = array(
			'startingbody' => $startingbody,
			'racialmod' => $racialmod,
			'bodybuild' => $bodybuild,
			'showwork' => $showwork,
			'psion' => $psion
		);

    	$this->set('ajax', json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

	public function newbody($cardnumber = null) {
		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$this->set('character', $character);

		$db = $this->Character->getDataSource();

		// Get starting body and Racial Modifier
		$results = $db->fetchAll(
			'SELECT a.BP as total, a.Ratio as racialmod FROM abilities a LEFT OUTER JOIN character_abilities ca on a.id = ca.ability_id WHERE a.abilitygroup_id = 23 and ca.character_id = ?',
			array($id)
		);

		$startingbody = $results[0]['a']['total'];
		$bodytotal = $startingbody;
		$racialmod = $results[0]['a']['racialmod'];
		$this->set('starting',$startingbody);
		$this->set('racialmod',$racialmod);

		// Show the distribution of points by ratio
		$showwork = $db->fetchAll(
			'SELECT sum(ca.quantity * ca.build_spent) as total, a.ratio FROM character_abilities ca LEFT OUTER JOIN abilities a on a.id = ca.ability_id WHERE ca.character_id = ? group by a.ratio order by a.ratio desc',
			array($id)
		);
		$this->set('showwork',$showwork);


		// Calculate a characters unique ratio
		$ratioresults = $db->fetchAll(
			'SELECT sum(ca.quantity * ca.build_spent * a.ratio * 10) / sum(ca.quantity * ca.build_spent) as total FROM character_abilities ca LEFT OUTER JOIN abilities a on a.id = ca.ability_id WHERE a.ratio > 0 and ca.character_id = ?',
			array($id)
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

		// Factor in the Armor Efficiency Skill if applicable
		$results = $db->fetchAll(
			'SELECT sum(ca.quantity) as total FROM character_abilities ca LEFT OUTER JOIN abilities a on a.id = ca.ability_id WHERE a.ability_name = "Armor Efficiency" and ca.character_id = ?',
			array($id)
		);

		$armorefficiency = $results[0][0]['total'];

		if($armorefficiency >= 1 && $armormod > 1) {
			$armormod += 1;
		}

		// Factor in the Psionicist limitation
		// Check to see if the character is a Psionicist (fixed 1:1 ratio)
		$results = $db->fetchAll(
			'SELECT sum(ca.quantity) as total FROM character_abilities ca LEFT OUTER JOIN abilities a on a.id = ca.ability_id WHERE a.ability_name = "Psionicist (B)" and ca.character_id = ?',
			array($id)
		);

		$psion = $results[0][0]['total'];
		$this->set('psion',$psion);
		if($psion >= 1) {
			$ratio = 1;
			$ratioraw = 1;
			$armormod = 1;
		}

		$this->set('ratio',$ratio);
		$this->set('ratioraw',$ratioraw);
		$this->set('armormod',$armormod);

		// How much build has been spend on body
		$buildonbody = $db->fetchAll(
			'SELECT COALESCE(sum(ca.quantity * ca.build_spent),0) as total FROM character_abilities ca WHERE ca.ability_id in (21032,21033,21034) and ca.character_id = ?',
			array($id)
		);

		$bodybuild = $buildonbody[0][0]['total'];
		$this->set('build',$bodybuild);


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

		$this->set('remainder',$bodybuild);
		$this->set('body',$bodytotal);
		$this->set('ranks',$ranks);
    }

	public function level($cardnumber = null, $details = 'false') {
		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$this->set('character', $character);

		$db = $this->Character->getDataSource();

		// Load the lists a character has access too

		$cardlists = $db->fetchAll(
			'SELECT  a.ability_name, a.opens_list_id, at.name, at.id from abilities a LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id where a.opens_list_id > 0 and a.id in (SELECT b.ability_id from (SELECT sum(c.quantity) as qty, c.ability_id from character_abilities c where character_id = ? group by ability_id) b where qty > 0)',
			array($id)
		);

		$lists[] = 43;
		$alllists[] = 43;
		$levels[43] = array(
			'id' => 43,
			'list' => "Commoner",
			'level' => 1,
			'build' => 0,
			'parent' => 43
		);

		// Separate the Lists into profession lists and child lists
		foreach ($cardlists as $row) {
			if($row['a']['opens_list_id'] != null) {
				// 21 is the ID of lower lists
				if($row['at']['name'] == "Lists") {
					$alllists[] = $row['a']['opens_list_id'];
					$lists[] = $row['a']['opens_list_id'];
					$levels[$row['a']['opens_list_id']] = array(
						'id' => $row['a']['opens_list_id'],
						'list' => $row['a']['ability_name'],
						'level' => 1,
						'build' => 0,
						'parent' => $row['a']['opens_list_id']
					);
				} else {
					$otherlists[] = $row['a']['opens_list_id'];
					$alllists[] = $row['a']['opens_list_id'];
				}
			}
		}

		$buildratios = $db->fetchAll(
			'SELECT l.id, l.list_name, a.BP FROM elists l left join list_abilities la on la.elist_id = l.id left join abilities a on la.ability_id = a.id where l.list_type_id = 1 and la.ability_id in (21032, 21033, 21034) and l.id in ('.implode(',',$lists).') '
		);

		foreach ($buildratios as $row) {
			$levels[$row['l']['id']]['ratio'] = $row['a']['BP'];
		}

		// Determine which child lists feed up to which profession lists
		// Get skills from the lists the character has
		if(count($lists) > 0) {
			$results = $db->fetchAll(
				'SELECT a.id, a.opens_list_id, la.elist_id, a.ability_name from abilities a JOIN list_abilities la on a.id = la.ability_id where a.opens_list_id > 0 and la.build_cost > -1 and la.elist_id in ('.implode(',',$lists).') ');
			foreach ($results as $row) {
				$levels[$row['a']['opens_list_id']] = array(
					'id' => $row['a']['opens_list_id'],
					'list' => $row['a']['ability_name'],
					'level' => 1,
					'build' => 0,
					'parent' => $row['la']['elist_id']
				);
			}
		}

		// Get list of all abilities for this character
		$charabilities = $db->fetchAll(
			'SELECT sum(quantity) as qty, ca.build_spent, la.elist_id, a.id, a.opens_list_id, a.display_name as display, a.ability_name as ability, at.name as type from character_abilities ca LEFT OUTER JOIN list_abilities la on la.ability_id = ca.ability_id LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id where ca.character_id = ? and la.elist_id in ('.implode(',',$alllists).') and (la.abilityoption_id = 0 or la.abilityoption_id = ca.abilityoption_id) group by a.display_name, a.ability_name, ca.build_spent, la.elist_id',
			array($id)
		);

		foreach($charabilities as $row) {
			if($row[0]['qty'] != 0) {
				$fromlist = $row['la']['elist_id'];

				if(array_key_exists($fromlist, $levels)) {
					$parentlist = $levels[$fromlist]['parent'];

					$levels[$parentlist]['build'] += floor($row[0]['qty'] * $row['ca']['build_spent']);
					$levels[$parentlist]['level'] = floor($levels[$parentlist]['build'] / 10) + 1;

					$levels[$parentlist]['skills'][] = $row['a']['ability']. ' :: '. $row[0]['qty'] . '@'. $row['ca']['build_spent'] .'=' . floor($row[0]['qty'] * $row['ca']['build_spent']);
				}
			}
		}

		$ratio = 0;
		$totallevels = 0;

		$otherratio = 0;
		$othercount = 0;

		foreach($levels as $level) {
			if(array_key_exists('ratio',$level)) {
				$ratio += $level['ratio'] * $level['level'];
				$totallevels += $level['level'];

				$otherratio += $level['ratio'];
				$othercount++;
			}
		}

		if($totallevels > 0) {
			$ratio = $ratio / $totallevels;
		} else {
			$ratio = 1;
		}

		if($othercount > 0) {
			$otherratio = $otherratio / $othercount;
		} else {
			$otherratio = 1;
		}

		$this->set('levels',$levels);
		$this->set('details',$details);

		$results = $db->fetchAll(
			'SELECT COALESCE(sum(base + service + bought + roleplay_build + lifestyle + other),0) as total, COALESCE(sum(roleplay_points),0) as roleplay from character_buildpoints bp where bp.character_id = ?',
			array($id)
		);
		$build_earned = $results[0][0]['total'];
		$this->set('build_earned',$build_earned);

		$results = $db->fetchAll(
			'SELECT COALESCE(SUM(ca.build_spent * ca.quantity),0) as build from character_abilities ca where ca.character_id = ?',
			array($id)
		);
		$build_spent = $results[0][0]['build'];
		$build_unspent = $build_earned - $build_spent;
		$level = floor($build_spent / 10);
		$this->set('build_spent',$build_spent);
		$this->set('build_unspent',$build_unspent);
		$this->set('charlevel',$level);
    }

    public function viewratios($cardnumber = null) {
    	$this->view($cardnumber,false,-1);
    	$this->set('showratios','true');

    	$this->render('view');
    }

    public function editpage($cardnumber = null) {
    	$this->view($cardnumber,false,-1);
    	$this->set('showpage','true');

    	$this->render('view');
    }

	public function view($cardnumber = null, $collapse = false, $page = -1) {

		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$this->set('showratios','false');

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$db = $this->Character->getDataSource();

		$player_id = $character['Player']['id'];

		$filename = sprintf("%04d_%s",$cardnumber,str_replace(" ","_",$character['Character']['name']));
		$filename = str_replace('"',"",$filename);
		$filename = str_replace("'","",$filename);
		$filename = str_replace("(","",$filename);
		$filename = str_replace(")","",$filename);
		
		$this->filename = $filename;

		$this->set('character', $character);

		$results = $db->fetchAll('SELECT p.id, p.problem_report, p.resolution_comments, p.created, p.modified, p.resolved from character_problems p where p.character_id = ? and p.hidden = 0 order by p.resolved',array($id));
		$this->set('problems',$results);

		$results = $db->fetchAll(
			'SELECT username from users where player_id = ?',
			array($character['Player']['id'])
		);
		if(count($results) == 0) {
			$this->set('email','(!! No Email on Record !!)');
			$this->set('prompt_for_email','true');
		} else {
			$this->set('email',$results[0]['users']['username']);
			$this->set('prompt_for_email','false');
		}

		$results = $db->fetchAll(
			'SELECT TRUNCATE(COALESCE(sum(value),0),0) as gold from character_deposits where character_id = ?',
			array($id)
		);
		$bank = 0 + $results[0][0]['gold'];
		$this->set('gold',$bank);

		$numevents = $character['Character']['past_event_count'];
		$this->set('numevents',$numevents);

		$results = $db->fetchAll(
			'SELECT COALESCE(sum(base + service + bought + roleplay_build + lifestyle + other),0) as total, COALESCE(sum(roleplay_points),0) as roleplay from character_buildpoints bp where bp.character_id = ?',
			array($id)
		);
		$build_earned = intval($results[0][0]['total']);
		$roleplay_points = intval($results[0][0]['roleplay']);

		$results = $db->fetchAll(
			'SELECT COALESCE(count(cc.id),0) as total from character_chronicles cc where cc.submitted = 1 and cc.approved = 1 and cc.character_id = ?',
			array($id)
		);

		$build_earned_from_history = $results[0][0]['total'];
		$build_earned += intval($build_earned_from_history);

		$this->set('build_earned',$build_earned);
		$this->set('build_earned_from_history',$build_earned_from_history);
		$this->set('roleplay_points',$roleplay_points);

		$results = $db->fetchAll(
			'SELECT ev.name, COALESCE(sum(base + service + bought + roleplay_build + lifestyle + other),0) as total from character_buildpoints bp left join events ev on ev.id = bp.event_id where bp.character_id = ? group by ev.name order by event_id desc',
			array($id)
		);
		$this->set('buildmemo',$results);

		$results = $db->fetchAll(
			'SELECT COALESCE(SUM(ca.build_spent * ca.quantity),0) as build from character_abilities ca where ca.character_id = ?',
			array($id)
		);
		$build_spent = $results[0][0]['build'];
		$build_unspent = $build_earned - $build_spent;
		$level = floor($build_spent / 10);
		$this->set('build_spent',$build_spent);
		$this->set('build_unspent',$build_unspent);
		$this->set('level',$level);

		$serviceresults = $db->fetchAll(
			'SELECT COALESCE(sum(points),0) as service from player_servicepoints sp where sp.player_id = ?',
			array($player_id)
		);

		$this->set('service_points',$serviceresults[0][0]['service']);

		if($page == -1) {
			// Get list of all abilities for this character
			$charabilities = $db->fetchAll(
				'SELECT sum(quantity) as qty, a.id, a.deprecated,a.cost_increase_interval, a.uses, a.ratio, a.opens_list_id, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, ao.display_name as opt_disp, ca.abilityoption_id as option_id, at.name as type, ag.name as grp, ca.build_spent as build_spent, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP, ca.page from character_abilities ca LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN abilities ao on ca.abilityoption_id = ao.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where ag.stackable = 1 and ca.character_id = ? group by a.display_name, a.ability_name, ao.ability_name, ag.name, ca.build_spent order by ag.sorting_name asc, concat(a.sort_after_name,a.ability_name), ao.ability_name asc, build_spent',
				array($id)
			);
		} else {
			// Get list of all abilities for this character
			$charabilities = $db->fetchAll(
				'SELECT sum(quantity) as qty, a.id, a.deprecated,a.cost_increase_interval, a.uses, a.ratio, a.opens_list_id, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, ao.display_name as opt_disp, ca.abilityoption_id as option_id, at.name as type, ag.name as grp, ca.build_spent as build_spent, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP, ca.page from character_abilities ca LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN abilities ao on ca.abilityoption_id = ao.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id where ag.stackable = 1 and ca.character_id = ? and ca.page = ? group by a.display_name, a.ability_name, ao.ability_name, ag.name, ca.build_spent order by ag.sorting_name asc, concat(a.sort_after_name,a.ability_name), ao.ability_name asc, build_spent',
				array($id,$page)
			);
		}

		$hasdeprecatedskills = false;
		foreach($charabilities as $row) {
			if($row[0]['qty'] > 0 && $row['a']['deprecated'] > 0) {
				$hasdeprecatedskills = true;
				break;
			}
		}

		if($hasdeprecatedskills || $character['Character']['allow_rewrite'] == 1) {
			$this->set('allowrewrites','true');
		} else {
			$this->set('allowrewrites','false');
		}

		$abilitygroups = $db->fetchAll(
			'SELECT ag.name as grp from ability_groups ag where ag.stackable = 1 order by ag.sorting_name asc'
		);

		$collapse_by_skill = array();

		if($collapse && $character['Character']['collapse'] == 1) {
			$this->set('collapsing', 'true');
			// Get all lists that could collapse
			$collapse = $db->fetchAll(
				'SELECT collapse_name, id from elists where collapse_name <> "" order by collapse_order asc'
			);

			foreach ($collapse as $clist) {
				$clist_id = $clist['elists']['id'];
				$results = $db->fetchAll(
					'SELECT count(*) as count from list_abilities where elist_id = ? and ability_id not in (SELECT ability_id from character_abilities where character_id = ?)',
					array($clist_id,$id)
				);

				if($results[0][0]['count'] == '0') {
					$skill_ids = $db->fetchAll(
						'SELECT la.ability_id, ca.build_spent from list_abilities la left join character_abilities ca on la.ability_id = ca.ability_id where la.elist_id = ? and ca.character_id = ?',
						array($clist_id, $id)
					);

					$collapse_total_build = 0;

					foreach ($skill_ids as $skid) {
						$collapse_total_build += $skid['ca']['build_spent'];
					}

					foreach ($skill_ids as $skid) {
						$skill_id = $skid['la']['ability_id'];

						// If the skill is already being collapsed by a higher priority collapse then just ignore it.
						if(!array_key_exists($skill_id, $collapse_by_skill)) {
							$collapse_by_skill[$skill_id] = array(
								'id' => $clist_id,
								'name' => $clist['elists']['collapse_name'],
								'cost' => $collapse_total_build
							);
						}
					}
				}
			}
		} else {
			$this->set('collapsing', 'false');
		}

		$adjusted = $this->Character->adjustAbilities($charabilities, $collapse_by_skill, $abilitygroups);

		if(count($adjusted['Religion']) == 0) {
			$this->set('religion','none');
		} else {
			$this->set('religion',$adjusted['Religion'][0]['ability']);
			unset($adjusted['Religion']);
		}

		if(count($adjusted['Race']) == 0) {
			$this->set('race','none');
		} else {
			$this->set('race',$adjusted['Race'][0]['ability']);
			unset($adjusted['Race']);
		}

		if(count($adjusted['Lists']) == 0) {
			$this->set('lists','Commoner');
			$this->set('lists_build','0');
		} else {
			$this->set('lists',$adjusted['Lists'][0]['ability']);
			$this->set('lists_all',$adjusted['Lists'][0]['all']);
			$this->set('lists_build',$adjusted['Lists'][0]['build_spent']);
			unset($adjusted['Lists']);
		}

		if($page == -1 || $page == $character['Character']['itempage']) {
			// Get the abilities from bonded items
			$results = $db->fetchAll(
						'SELECT i.id, i.name, i.slot_number from items i where i.character_id = ?',
						array($id)
					);

			$item_names = array();
			foreach ($results as $row) {
				$item_names[$row['i']['slot_number']] = $row['i']['name'];
			}

			$results = $db->fetchAll(
				'SELECT sum(ia.qty) as qty, a.display_name, a.id, a.ability_name, a.abilitygroup_id, ia.slot_number, ia.socket_number from item_abilities ia LEFT OUTER JOIN abilities a on ia.ability_id = a.id where ia.character_id = ? group by ia.slot_number, a.id, a.display_name, a.ability_name, ia.socket_number order by ia.slot_number, ia.socket_number',
				array($id)
			);
			foreach($results as $row) {
				$ability_display = $row['a']['display_name'];
				if(!$ability_display) {
					$ability_display = $row['a']['ability_name'];
				}

				if (strpos($ability_display,'+1') !== false) {
					$ability_display = str_replace('+1','+'.$row[0]['qty'],$ability_display);
				} else if($row[0]['qty'] > 1) {
					$ability_display .= ' x' . $row[0]['qty'];
				}


				$bubbles = '';

				$rowcount = 1;
				if($row['a']['abilitygroup_id'] != 56) {
					$i = $row[0]['qty'];
					while($i > 5) {
						if(strlen($bubbles) > 0) {
							$bubbles .= "\n";
						}
						$rowcount++;
						$bubbles .= ' OOOOO|OOOOO|OOOOO|OOOOO';
						$i -= 5;
					}
					if($i > 0) {
						$rowcount++;
						$subbub = '';
						while($i > 0) {
							$subbub .= 'O';
							$i--;
						}
						while(strlen($subbub) < 5) {
							$subbub .= ' ';
						}
						if(strlen($bubbles) > 0) {
							$bubbles .= "\n";
						}
						$bubbles .= ' '.$subbub.'|'.$subbub.'|'.$subbub.'|'.$subbub;
					}
				}

				$group = $item_names[$row['ia']['slot_number']];
				$socket = $row['ia']['socket_number'];

				if($socket == 0) {
					$socket = '';
				} else {
					$socket = '['.$socket.'] ';
				}

				$adjusted[$group][] = array(
					'id'			=> $row['a']['id'],
					'ability' 		=> $socket.$ability_display,
					'bubbles' 		=> $bubbles,
					'rows'			=> $rowcount,
					'bold'			=> 0
				);
			}
		}

		if($page == -1 || $page == 0) {
			// Get list of all other markings for this character
			$otherabilities = $db->fetchAll(
				'SELECT e.name, ca.quantity, a.id, a.opens_list_id, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, ao.display_name as opt_disp, ca.abilityoption_id as option_id, at.name as type, ag.name as grp, ca.build_spent as build_spent, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP from character_abilities ca LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN abilities ao on ca.abilityoption_id = ao.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id LEFT OUTER JOIN events e on ca.event_id = e.id where ag.stackable = 0 and ca.character_id = ? order by ca.event_id asc, ag.sorting_name asc, concat(a.sort_after_name,a.ability_name)',
				array($id)
			);
			foreach ($otherabilities as $otherline) {
				$qty = $otherline['ca']['quantity'];
				$type = $otherline['at']['type'];
				$group = $otherline['ag']['grp'];
				$ability_name = $otherline['a']['ability'];
				$ability_display = $otherline['a']['display'];
				$ability_id = $otherline['a']['id'];
				$event_name = $otherline['e']['name'];

				if(!$ability_display) {
					$ability_display = $ability_name;
				}

				$ability_option = $otherline['ao']['opt'];
				$ability_option_display = $otherline['ao']['opt_disp'];
				$ability_option_id = $otherline['ca']['option_id'];

				if(!$ability_option_display) {
					$ability_option_display = $ability_option;
				}

				if(strlen($ability_option) > 0) {
					$ability_display = $ability_display.' '.$ability_option_display;
				}

				preg_match('/.*%(event)%.*/',$ability_display, $matches);
				if(array_key_exists(1,$matches)) {
					$ability_display = str_replace('%event%',$event_name,$ability_display);
				}

				$adjusted[$group][] = array(
					'ability' 		=> $ability_display,
					'bubbles'		=> '',
					'rows'			=> 1
				);
			}
		}


		$this->set('character_abilities',$adjusted);
		$counts = array();
		$total = 0;
		foreach(array_keys($adjusted) as $adj_key) {
			$count = 0;
			if(is_array($adjusted[$adj_key])) {
				foreach($adjusted[$adj_key] as $item) {
					$count += $item['rows'];
					$total += $item['rows'];
				}
			}
			$counts[$adj_key] = $count;
		}
		$counts['Total'] = $total;

		$this->set('ability_group_rows',$counts);

		$careerpoints = array();

		if(array_key_exists('Career Points',$adjusted)) {
			foreach($adjusted['Career Points'] as $cpentry) {
				$careerpoints[$cpentry['type']] = $cpentry['value'];
			}
		}

		$this->set('careerpoints',$careerpoints);

		$this->set('quests',$this->getQuests($id));

		$this->newbody($cardnumber);
    }

    private function getQuests($character_id) {
        $quests = $this->QuestProgress->find('all',array(
            'conditions' => array('Character.id' => $character_id),
            'order' => array('QuestPath.id','QuestStage.quest_stage','QuestStage.stage_code'),
            'contain' => array('Character' => array('name','id','cardnumber'),'EventAdded','EventCompleted','QuestPath','QuestStage')
        ));

        $output = array();
        $item = array();

        foreach($quests as $row) {
            if( !isset($item['QuestPath']) ) {
                $item['QuestPath'] = $row['QuestPath'];
            } else if( $item['QuestPath']['id'] != $row['QuestPath']['id']) {
                $output[] = $item;
                $item = array();
                $item['QuestPath'] = $row['QuestPath'];
            }
            $item['QuestStages'][] = array(
                'QuestProgress' => $row['QuestProgress'], 
                'QuestStage' => $row['QuestStage'],
                'EventAdded' => $row['EventAdded'],
                'EventCompleted' => $row['EventCompleted'],
                'EventCollected' => $row['EventCollected'],
                'EventDropped' => $row['EventDropped']
            );
        }
        if(isset($item['QuestPath'])) {
            $output[] = $item;
        }

        return $output;
    }

    public function getQuestsDebug($character_id) {
    	$output = $this->getQuests($character_id);
        $this->set('ajax',json_encode($output));
        $this->layout = 'ajax';
        $this->render('ajax');
    }

	public function viewpdf($cardnumber = null) {

		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$filename = sprintf("%04d_%s",$cardnumber,str_replace(" ","_",$character['Character']['name']));
		$filename = str_replace('"',"",$filename);
		$filename = str_replace("'","",$filename);
		$filename = str_replace("(","",$filename);
		$filename = str_replace(")","",$filename);

		$this->viewClass = 'Media';
		// Download app/outside_webroot_dir/example.zip
		$params = array(
			'id'        => $filename.'.pdf',
			'name'      => $filename,
			'download'  => false,
			'extension' => 'pdf',
			'path'      => Configure::read('SavePDFs.directory') . DS
		);
		$this->set($params);
    }

	private function setUpdateDate($cardnumber) {
		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		$this->Character->id = $id;
		$this->Character->saveField('last_updated_date',date('c'));
	}

	private function setPrintingDate($cardnumber) {
		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		$this->Character->id = $id;
		$this->Character->saveField('last_printed_date',date('c'));
	}

	public function debugView($cardnumber = null, $collapse = true) {

		$this->view($cardnumber, $collapse, 0);

		$this->render('debug');
	}

	public function downloadpdf($cardnumber = null, $collapse = true) {

		$this->setPrintingDate($cardnumber);
		$this->view($cardnumber, $collapse, 0);

		App::import('Vendor', 'Fpdf', array('file' => 'fpdf/fpdf.php'));
		$this->layout = 'pdf'; //this will use the pdf.ctp layout

		$this->response->type('pdf');

		$this->set('filename',$this->filename);
		$this->set('fpdf', new FPDF('P','mm','Letter'));

		$this->render('downloadpdf');
	}

	public function downloadpdf_aux($cardnumber = null, $page, $collapse = true ) {

		$this->setPrintingDate($cardnumber);
		$this->view($cardnumber, $collapse, $page);

		App::import('Vendor', 'Fpdf', array('file' => 'fpdf/fpdf.php'));
		$this->layout = 'pdf'; //this will use the pdf.ctp layout

		$this->response->type('pdf');

		$this->set('filename',"page_".$page."-".$this->filename);
		$this->set('fpdf', new FPDF('P','mm','Letter'));

		$this->render('downloadpdf_aux');
	}

    public function add_old($playername = null) {
    	if ($this->request->is('post')) {
			$this->Character->create();
			if ($this->Character->save($this->request->data)) {

				$id = $this->Character->id;
				$cardnumber = $this->request->data['Character']['cardnumber'];
				$this->Session->setFlash('The character has been added.');
				$this->redirect('/cards/page1/'.$cardnumber);
			} else {
				$this->Session->setFlash('Unable to add the character.');
			}
		} else {
			$this->set('players', $this->Character->Player->find('list',array('fields'=>array('id','name'), 'order'=>array('name'))));
			$this->set('csets', $this->Character->Cset->find('list',array('fields'=>array('id','name'))));

			if($playername != null) {
				$this->set('playername',$playername);
			}
		}
    }

    public function add($player_id = null) {
    	if($player_id != null) {
	    	$this->set('player_id',$player_id);
	    }
    }

    public function actualAddCharacter() {
		$data = $this->request->input('json_decode',true);

    	$this->Character->create();
    	$this->Character->set(array(
    		'player_id' => $data['player_id'],
    		'cset_id' => $data['cset_id'],
    		'cardnumber' => $data['cardnumber'],
    		'past_event_count' => $data['past_event_count'],
    		'name' => $data['name']
    	));
    	$this->Character->save();

    	$this->set('ajax',json_encode($data));
    	$this->layout = 'ajax';
        $this->render('ajax');
    }

    public function renumberCharacters() {
    	$data = $this->request->input('json_decode',true);

    	$this->Player->read(null,$data['Player']['id']);
    	$this->Player->set(array(
    		'cardnumber_prefix' => $data['Player']['cardnumber_prefix']
    	));
    	$this->Player->save();

    	foreach($data['Character'] as $ch) {
	    	$this->Character->read(null,$ch['id']);
	    	$this->Character->set(array(
	    		'cardnumber' => $ch['cardnumber']
	    	));
	    	$this->Character->save();
    	}

    	$this->set('ajax',json_encode($data));
    	$this->layout = 'ajax';
        $this->render('ajax');
    }

    public function getnextid($cset) {

    	$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT COALESCE(MAX( c.cardnumber )+1,s.min_id) as cardnumber FROM characters c LEFT OUTER JOIN csets s ON c.cset_id = s.id WHERE c.cset_id = ? AND c.cardnumber >= s.min_id AND c.cardnumber <= s.max_id',
			array($cset)
		);

		$this->set('nextid', $results[0][0]['cardnumber']);
		$this->layout = 'ajax';
    }

    public function loadPlayers() {
    	$results = $this->Player->find('all',array(
    		'fields' => array('Player.id','Player.name','Player.cardnumber_prefix'),
    		'contain' => array('Character' => array('id','name','cardnumber','new_character')),
    		'order' => array('Player.name')
    	));

    	$this->set('ajax',json_encode($results));
    	$this->layout = 'ajax';
        $this->render('ajax');
    }

    public function loadPlayer($player_id) {
    	$results = $this->Player->find('first',array(
    		'fields' => array('Player.id','Player.name','Player.cardnumber_prefix'),
    		'contain' => array('Character' => array('id', 'name','cardnumber','new_character','cset_id')),
    		'order' => array('Player.name'),
    		'conditions' => array('Player.id' => $player_id)
    	));

    	$this->set('ajax',json_encode($results));
    	$this->layout = 'ajax';
        $this->render('ajax');
    }

    public function getCsets() {
    	$results = $this->Character->Cset->find('all',array('fields'=>array('id','name')));

	    $this->set('ajax', json_encode($results));
	    $this->layout = 'ajax';
        $this->render('ajax');
    }

    public function renumber() {

    }

    public function getRenumber() {
    	$db = $this->Character->getDataSource();

    	$results = $db->fetchAll('SELECT p.id, p.name, p.cardnumber_prefix, c.id, c.cardnumber, c.name from players p left outer join characters c on c.player_id = p.id where c.cardnumber not like CONCAT(p.cardnumber_prefix, "%") order by p.cardnumber_prefix, c.cardnumber');

    	$this->set('ajax',json_encode($results));
    	$this->layout = 'ajax';
        $this->render('ajax');
    }

    public function getRange($lower,$upper) {
    	$db = $this->Character->getDataSource();

    	$results = $db->fetchAll('SELECT p.id, p.name, p.cardnumber_prefix, c.id, c.cardnumber, c.name from players p left outer join characters c on c.player_id = p.id where p.cardnumber_prefix >= ? and p.cardnumber_prefix <= ? order by p.cardnumber_prefix, c.cardnumber',array($lower,$upper));

    	$this->set('ajax',json_encode($results));
    	$this->layout = 'ajax';
        $this->render('ajax');
    }

}
?>