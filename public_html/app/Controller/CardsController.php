<?php
class CardsController extends AppController {

	var $uses = array('Character', 'CharacterEditLog', 'CharacterProblem', 'CharacterAbility', 'Ability', 'CharacterBuildpoints', 'PlayerServicepoints', 'Event', 'CharacterDeposits', 'Player', 'Item', 'ItemAbility', 'CharacterAttendance');

	public function isAuthorized($user) {
	    // A user can view their own characters
	    if (in_array($this->action, array('rewrite','submit-rewrite'))) {

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

	    if(AuthComponent::user('role_newplayermarshal')) {
	    	if(in_array($this->action, array('submit','savebonded','getoptionslist'))) {
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

	public function index() {
    	if ($this->request->is('post')) {
    		$cardnumber = $this->request->data['Character']['cardnumber'];
			$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
			if (!$character) {
				throw new NotFoundException(__('Invalid character'));
			} else {
				$this->redirect(array('controller' => 'cards', 'action' => 'page1', $this->request->data['Character']['cardnumber']));
			}
    	}
    }

    public function withproblems() {
    	$db = $this->Character->getDataSource();
		$results = $db->fetchAll('SELECT distinct c.name, c.cardnumber FROM characters c LEFT OUTER JOIN character_problems p ON c.id = p.character_id WHERE p.resolved = 0 ORDER BY c.cardnumber');

		$this->set('cards',$results);
    }

	public function updateproblem($problemid = null) {

		if (!$problemid) {
			throw new NotFoundException(__('Invalid problem report'));
		}

		$problem = $this->CharacterProblem->findById($problemid);

		if (!$problem) {
			throw new NotFoundException(__('Invalid problem report'));
		}

		$data = $this->request->input('json_decode',true);

		$this->set('value',$data);

		$this->CharacterProblem->id = $problemid;
		$this->CharacterProblem->saveField('resolution_comments',$data['comment']);
		$this->CharacterProblem->saveField('resolved',$data['resolved']);
		$this->layout = 'ajax';
	}


	public function test($cardnumber = null) {
		$data = $this->request->input('json_decode',true);
		$this->set('requestdata',$data['value']);
		$this->layout = 'ajax';
	}

	private function getnextid($min, $max) {
		$db = $this->Player->getDataSource();

		$results = $db->fetchAll(
			'SELECT COALESCE(MAX( p.cardnumber_prefix )+1,?) as cardnumber FROM players p WHERE p.cardnumber_prefix >= ? AND p.cardnumber_prefix <= ?',
			array($min, $min, $max)
		);

		return $results[0][0]['cardnumber'];
	}


	public function deactivatecards($cset = 1) {

		// Deactivate players who haven't been around in 18 months
		$db = $this->Player->getDataSource();

		$results = $db->query('select p.id from players p where p.active = 1 and p.id not in (select player_id from characters where last_updated_date > DATE_SUB(curdate(), INTERVAL 12 MONTH))');

		$nextid = $this->getnextid(10000,99999);
		foreach($results as $row) {
			$this->Player->id = $row['p']['id'];
			$this->Player->set('active',0);
			$this->Player->set('cardnumber_prefix',$nextid);
			$this->Player->save();
			$nextid++;
		}

		$db->query('update characters set active = 0 where active = 1 and player_id in (select id from players where active = 0)');

		$db->query('update characters set new_character = 0 where new_character = 1 and past_event_count > 3');

		$this->set('ajax',"done");
		$this->layout = 'ajax';
		$this->render('ajax');
	}

    public function printcards($cset = 1, $interval = 0) {

		$db = $this->Character->getDataSource();

		$this->set('cset',$cset);

		if($interval == 0) {
			$results = $db->fetchAll('SELECT e.date from events e where e.date < NOW() order by e.date desc');
			$this->set('last_event_date',$results[0]['e']['date']);
		} else {
			$results = $db->fetchAll('SELECT e.date from events e where e.date < DATE_SUB(NOW(), INTERVAL '.$interval.' DAY) order by e.date desc');
			$this->set('last_event_date',$results[0]['e']['date']);			
		}

		$allcards = $this->Character->find('all',
			array('order'=>'Character.cardnumber','conditions' => array(
				'Character.cset_id' => $cset,
				'Character.active' => 1)
		));
		$this->set('allcards',$allcards);

		$allpages = $db->fetchAll('SELECT DISTINCT c.cardnumber, ca.page from characters c LEFT OUTER JOIN character_abilities ca on c.id = ca.character_id where ca.page > 0');
		$this->set('allpages',$allpages);
	}

	public function savereport($queryid = 1, $csetid = 1) {
		$this->printreports($queryid, $csetid);

		App::import('Vendor', 'Fpdf', array('file' => 'fpdf/fpdf.php'));
		$this->layout = 'pdf'; //this will use the pdf.ctp layout

		$this->response->type('pdf');

		$this->set('filename','report-'.$queryid);
		$this->set('fpdf', new FPDF('P','mm','Letter'));

		$this->render('savereport');
	}

    public function printreports($queryid = 1, $csetid = 1) {

		$this->set('query',$queryid);

		$db = $this->Character->getDataSource();

		if($queryid == 3) {
			$results = $db->fetchAll(
				'SELECT p.id, p.name, c.name, c.cardnumber
					FROM players p
					LEFT OUTER JOIN characters c ON c.player_id = p.id
					LEFT OUTER JOIN users u ON u.player_id = p.id
					WHERE c.cset_id = ?
					ORDER BY c.cardnumber',
				array($csetid)
			);

			$this->set('col1',array('text' => 'Card #', 'format' => '% '));
			$this->set('col2',array('text' => 'Character Name     ', 'format' => '%-'));
			$this->set('col3',array('text' => 'Player Name        ', 'format' => '%-'));
			foreach($results as $entry) {
				$data[] = array('col1' => $entry['c']['cardnumber'], 'col2' => $entry['c']['name'], 'col3' => $entry['p']['name']);
			}
			$this->set('report',$data);
		} else if($queryid == 2) {
			$results = $db->fetchAll(
				'SELECT p.id, p.name, c.name, c.cardnumber
					FROM players p
					LEFT OUTER JOIN characters c ON c.player_id = p.id
					LEFT OUTER JOIN users u ON u.player_id = p.id
					WHERE c.cset_id = ?
					ORDER BY c.name',
				array($csetid)
			);
			$this->set('col1',array('text' => 'Character Name     ', 'format' => '%-'));
			$this->set('col2',array('text' => 'Card #', 'format' => '% '));
			$this->set('col3',array('text' => 'Player Name        ', 'format' => '%-'));
			foreach($results as $entry) {
				$data[] = array('col2' => $entry['c']['cardnumber'], 'col1' => $entry['c']['name'], 'col3' => $entry['p']['name']);
			}
			$this->set('report',$data);
		} else {
			$results = $db->fetchAll(
				'SELECT p.id, p.name, c.name, c.cardnumber
					FROM players p
					LEFT OUTER JOIN characters c ON c.player_id = p.id
					LEFT OUTER JOIN users u ON u.player_id = p.id
					WHERE c.cset_id = ?
					ORDER BY p.name',
				array($csetid)
			);
			$this->set('col1',array('text' => 'Player Name        ', 'format' => '%-'));
			$this->set('col2',array('text' => 'Character Name     ', 'format' => '%-'));
			$this->set('col3',array('text' => 'Card #', 'format' => '% '));
			foreach($results as $entry) {
				$data[] = array('col3' => $entry['c']['cardnumber'], 'col2' => $entry['c']['name'], 'col1' => $entry['p']['name']);
			}
			$this->set('report',$data);
		}

	}

	public function saveevents($cardnumber = null) {

			if (!$cardnumber) {
				throw new NotFoundException(__('Invalid character'));
			}

			$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
			$id = $character['Character']['id'];

			if (!$character) {
				throw new NotFoundException(__('Invalid character'));
			}

			$data = $this->request->input('json_decode',true);

			$this->set('value',$data);

			$this->Character->id = $id;
			$this->Character->saveField('past_event_count',$data['value']);
			$this->Character->saveField('last_updated_date',date('c'));
			$this->layout = 'ajax';
    }

	public function savedetails($cardnumber = null) {

			if (!$cardnumber) {
				throw new NotFoundException(__('Invalid character'));
			}

			$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
			$id = $character['Character']['id'];

			if (!$character) {
				throw new NotFoundException(__('Invalid character'));
			}

			$data = $this->request->input('json_decode',true);

			$this->set('value',$data);

			$this->Character->id = $id;
			$this->Character->saveField('name',$data['name']);
			$this->Character->saveField('cardnumber',$data['number']);
			$this->Character->saveField('last_updated_date',date('c'));
			$this->Character->saveField('cset_id',$data['csetid']);
			$this->layout = 'ajax';
    }

    public function saveoognotes($cardnumber = null) {

		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$data = $this->request->input('json_decode',true);

		$this->set('value',$data);

		$this->log_edit($id, "Save OOG Notes", $data['value']);

		$this->Character->id = $id;
		$this->Character->saveField('other_notes',$data['value']);
		$this->Character->saveField('last_updated_date',date('c'));
		$this->layout = 'ajax';
    }

    public function savestnotes($cardnumber = null) {

		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$data = $this->request->input('json_decode',true);

		$this->set('value',$data);

		$this->log_edit($id, "Save ST Notes", $data['value']);

		$this->Character->id = $id;
		$this->Character->saveField('st_only_notes',$data['value']);
		$this->layout = 'ajax';
    }

    public function saveplayernotes($playerid = null) {

		if (!$playerid) {
			throw new NotFoundException(__('Invalid player'));
		}

		$player = $this->Player->findById($playerid);

		if (!$player) {
			throw new NotFoundException(__('Invalid player'));
		}

		$data = $this->request->input('json_decode',true);

		$this->set('value',$data);

		$this->Player->id = $playerid;
		$this->Player->saveField('player_notes',$data['value']);
		$this->Player->saveField('update_date',date('c'));
		$this->layout = 'ajax';
    }

    public function page1($cardnumber = null) {

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

		$results = $this->Character->Cset->find('list',array('fields'=>array('id','name'), 'order' => 'Cset.id'));
		$this->set('csets',$results);

		$results = $db->fetchAll('SELECT p.id, p.problem_report, p.resolution_comments, p.created, p.modified, p.resolved from character_problems p where p.character_id = ? and p.resolved = 0',array($id));
		$this->set('problems',$results);

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
			'SELECT COALESCE(sum(base + service + bought + roleplay_build + lifestyle + other),0) as total, COALESCE(sum(roleplay_points),0) as roleplay from character_buildpoints bp where bp.character_id = ?',
			array($id)
		);
		$build_earned = intval($results[0][0]['total']);
		$roleplay_points = intval($results[0][0]['roleplay']);


		$results = $db->fetchAll(
			'SELECT COALESCE(count(cc.id),0) as total from character_chronicles cc where cc.submitted = 1 and cc.approved = 1 and cc.character_id = ?',
			array($id)
		);

		$build_earned_from_death = 0;
		if(isset($character['Character']['resurrect_1'])) { $build_earned_from_death += 10; }
		if(isset($character['Character']['resurrect_2'])) { $build_earned_from_death += 20; }
		if(isset($character['Character']['resurrect_3'])) { $build_earned_from_death += 30; }
		if(isset($character['Character']['resurrect_4'])) { $build_earned_from_death += 40; }
		$build_earned += intval($build_earned_from_death);

		$build_earned_from_history = $results[0][0]['total'];
		$build_earned += intval($build_earned_from_history);

		$this->set('build_earned',$build_earned);
		$this->set('roleplay_points',$roleplay_points);

		$results = $db->fetchAll(
			'SELECT bp.id, ev.id, ev.name, COALESCE(base,0) as base,  COALESCE(service,0) as service, COALESCE(bought,0) as bought, COALESCE(roleplay_build,0) as roleplay_build, COALESCE(lifestyle,0) as lifestyle, COALESCE(other,0) as other from character_buildpoints bp left join events ev on ev.id = bp.event_id where bp.character_id = ? order by event_id desc',
			array($id)
		);
		$this->set('buildhistory',$results);

		$serviceresults = $db->fetchAll(
			'SELECT COALESCE(sum(points),0) as service from player_servicepoints sp where sp.player_id = ?',
			array($player_id)
		);

		$this->set('service_points',$serviceresults[0][0]['service']);

		$bankresults = $db->fetchAll(
			'SELECT COALESCE(sum(value),0) as bank from character_deposits cd where cd.character_id = ?',
			array($id)
		);

		$this->set('bank_value',$bankresults[0][0]['bank']);

		$numevents = $character['Character']['past_event_count'];
		$this->set('numevents',$numevents);

		$results = $db->fetchAll(
			'SELECT e.name, ca.quantity as qty, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, at.name as type, ca.id as caid, ca.build_spent as build_spent from character_abilities ca LEFT OUTER JOIN abilities a on ca.ability_id = a.id LEFT OUTER JOIN abilities ao on ca.abilityoption_id = ao.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN events e on ca.event_id = e.id where ca.character_id = ? order by created desc',
			array($id)
		);
		$this->set('character_abilities',$this->adjustAbilities($results));

		// Get Character Lists
		$results = $db->fetchAll(
			'SELECT  a.opens_list_id, at.name from abilities a LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id where a.opens_list_id > 0 and a.id in (SELECT b.ability_id from (SELECT sum(c.quantity) as qty, c.ability_id from character_abilities c where character_id = ? group by ability_id) b where qty > 0)',
			array($id)
		);
		$race_selected = false;
		$lists_selected = false;
		foreach ($results as $row) {
			if($row['a']['opens_list_id'] != null) {
				if($row['at']['name'] == "Race") {
					$race_selected = true;
					$lists[] = $row['a']['opens_list_id'];
				} else if($row['at']['name'] == "Lists") {
					$lists_selected = true;
					$lists[] = $row['a']['opens_list_id'];
				} else {
					$otherlists[] = $row['a']['opens_list_id'];
				}
			}
		}

		if( !$race_selected ) {
			// No race selected, get Racial Lists
			$results = $db->fetchAll(
				'SELECT id from elists where list_name = "Races"'
			);
			foreach ($results as $row) {
				if($row['elists']['id'] != null) {
					$otherlists[] = $row['elists']['id'];
				}
			}
		} else {
			// Get Universal Lists
			$results = $db->fetchAll(
				'SELECT id from elists where list_type_id in (SELECT id from list_types where name = "Universal")'
			);
			foreach ($results as $row) {
				if($row['elists']['id'] != null) {
					$otherlists[] = $row['elists']['id'];
				}
			}
		}

		$this->set('lists', $lists);
		$this->set('otherlists', $otherlists);

		if( $race_selected && !$lists_selected ) {
			// Get list of professions from the race the character has, but tweak the costs for the first profession
			$results = $db->fetchAll(
				'SELECT DISTINCT a.id, a.ability_name, la.build_cost, at.name from abilities a LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT JOIN list_abilities la on a.id = la.ability_id where at.name = "Lists" and la.build_cost <= 10 and la.elist_id in ('.implode(',',$lists).') order by a.ability_name');
			foreach ($results as $row) {
				$available[] = $row['a']['id'].'!0 -- '.$row['a']['ability_name'];
			}
			$this->set('available', $available);
		} else {
			// Get skills from the lists the character has
			if(count($lists) > 0) {
				$results = $db->fetchAll(
					'SELECT DISTINCT a.id, la.abilityoption_id, a.ability_name, la.build_cost, at.sorting_name from abilities a LEFT JOIN ability_types at on a.abilitytype_id = at.id JOIN list_abilities la on a.id = la.ability_id where la.build_cost > -1 and la.is_footnote = 0 and la.elist_id in ('.implode(',',$lists).') order by la.build_cost, a.ability_name');
				foreach ($results as $row) {
					if($row['la']['abilityoption_id'] > 0) {
						$available[] = $row['a']['id'].'@'.$row['la']['abilityoption_id'].'!'.$row['la']['build_cost'].' -- '.$row['a']['ability_name'];
					} else {
						$available[] = $row['a']['id'].'!'.$row['la']['build_cost'].' -- '.$row['a']['ability_name'];
					}
				}
			}
			// Now add in the universal lists
			if(count($otherlists) > 0) {
				$results = $db->fetchAll(
					'SELECT DISTINCT a.id, la.abilityoption_id, a.ability_name, la.build_cost, at.sorting_name from abilities a LEFT JOIN ability_types at on a.abilitytype_id = at.id JOIN list_abilities la on a.id = la.ability_id where la.build_cost > -1 and la.elist_id in ('.implode(',',$otherlists).') order by la.build_cost, a.ability_name');
				foreach ($results as $row) {
					if($row['la']['abilityoption_id'] > 0) {
						$available[] = $row['a']['id'].'@'.$row['la']['abilityoption_id'].'!'.$row['la']['build_cost'].' -- '.$row['a']['ability_name'];
					} else {
						$available[] = $row['a']['id'].'!'.$row['la']['build_cost'].' -- '.$row['a']['ability_name'];
					}
				}
			}
			$this->set('available', $available);
		}

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
		$this->set('events', $this->Event->find('list',array('fields'=>array('id','name'), 'order' => 'Event.date DESC')));
    }

    public function deleteAllSkills($character_id = null, $cardnumber = null) {
    	if (!$character_id) {
			throw new NotFoundException(__('Invalid character'));
		}

		$this->CharacterAbility->deleteAll(array('CharacterAbility.character_id' => $character_id));

		return $this->redirect(
			array('controller' => 'cards', 'action' => 'page1', $cardnumber)
		);
    }

    private function log_edit($character_id, $function, $comments = "") {
		$this->CharacterEditLog->create();
		$this->CharacterEditLog->set(array(
			'character_id' => $character_id,
			'user_id' => AuthComponent::user('id'),
			'function' => $function,
			'comments' => $comments,
			'logged_at' => date('c')
		));
		$this->CharacterEditLog->save();
    }

    public function submit() {
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
   			$this->Character->saveField('last_updated_date',date('c'));
   			$this->Character->saveField('active',1);

   			// Check for active character_attendance record
   			$attendance = $this->CharacterAttendance->find('first',array('conditions' => array(
				'Event.id' => $other['eventid'], 
				'Character.id' => $other['characterid']
			)));

			if($attendance != null) {
				
				$this->CharacterAttendance->id = $attendance['CharacterAttendance']['id'];
				$this->CharacterAttendance->set(array(
					'card_updated' => 1
				));
				$this->CharacterAttendance->save();
			}

			$attended = $this->getIntValue($other,'event-attend',0);
			if($attended > 0) {
				$this->Character->saveField('past_event_count',$attended);
			}

   			foreach($abilities as $row) {
   				switch($row['input_type']) {

   				case 'skill' :
					if($row['quantity'] != 0) {
						$this->CharacterAbility->create();

						$teacher_id = $this->getValue($row,'teacher_id','0');

						$this->CharacterAbility->set(array(
									'character_id' => $other['characterid'],
									'ability_id' => $row['ability_id'],
									'build_spent' => $row['build_spent'],
									'event_id' => $other['eventid'],
									'teacher_id' => $teacher_id,
									'abilityoption_id' => $row['option_id'],
									'quantity' => $row['quantity']
								));
						$this->CharacterAbility->save();

						$this->log_edit($other['characterid'], "Add Skill", "Ability_ID = ".$row['ability_id']);
					}
					break;
				case 'delete' :
					$this->CharacterAbility->delete($row['row_id']);
					$this->log_edit($other['characterid'], "Delete Skill", "Row_ID was ".$row['row_id']);
					break;
				}
   			}

   			$db = $this->Character->getDataSource();
   			$results = $db->fetchAll(
				'SELECT sum(roleplay_points) as roleplay from character_buildpoints bp where bp.character_id = ?',
				array($other['characterid'])
			);
			$roleplaystart = $results[0][0]['roleplay'];

   			$buildbase 		= $this->getIntValue($other,'build-base',0);
   			$buildservice 	= $this->getIntValue($other,'build-service',0);
   			$buildbought 	= $this->getIntValue($other,'build-bought',0);
   			$buildboughtstaff = $this->getIntValue($other,'build-bought-staff',0);
   			$buildlifestyle = $this->getIntValue($other,'build-lifestyle',0);
   			$roleplaypoints = $this->getIntValue($other,'roleplay-points',0);
   			$buildroleplay 	= $this->getIntValue($other,'build-roleplay',0);
   			$buildother 	= $this->getIntValue($other,'build-other',0);

   			if($roleplaypoints + $roleplaystart > 9) {
   				$roleplaypoints = $roleplaypoints  - 10;
   			}

   			if( $buildbase != 0 || $buildservice != 0 || $buildbought !=0 || $buildlifestyle !=0 || $buildroleplay !=0 || $buildother != 0 || $roleplaypoints != 0) {
   				$this->CharacterBuildpoints->create();
   				$this->CharacterBuildpoints->set(array(
   					'character_id' => $other['characterid'],
   					'event_id' => $other['eventid'],
   					'base' => $buildbase,
   					'service' => $buildservice,
   					'bought' => $buildbought + $buildboughtstaff,
   					'roleplay_build' => $buildroleplay,
   					'roleplay_points' => $roleplaypoints,
   					'lifestyle' => $buildlifestyle,
   					'other' => $buildother
   				));
   				$this->CharacterBuildpoints->save();

   				$total_build_added = $buildbase + $buildservice + $buildbought + $buildlifestyle + $buildroleplay + $buildother;
   				$this->log_edit($other['characterid'], "Add Build / RP Points", "Build: ".$total_build_added." / RP Points: ".$roleplaypoints." ");
   			}

			$bankchange 		= $this->getFloatValue($other,'bank-change',0);

			if($bankchange != 0) {
				$this->CharacterDeposits->create();
				$this->CharacterDeposits->set(array(
					'character_id' => $other['characterid'],
					'value' => $bankchange,
					'event_id' => $other['eventid']
				));
				$this->CharacterDeposits->save();

				$this->log_edit($other['characterid'], "Change Bank", "Gold: ".$bankchange);
			}

			$serviceconvert 	= $this->getIntValue($other,'service-convert',0);
			$servicespent		= $this->getIntValue($other,'service-spent',0);
			$serviceadded		= $this->getIntValue($other,'service-added',0);

			if($serviceadded > 0) {
				$this->PlayerServicepoints->create();
				$this->PlayerServicepoints->set(array(
					'player_id' => $other['playerid'],
					'event_id' => $other['eventid'],
					'points' => $serviceadded
				));
				$this->PlayerServicepoints->save();
				$this->log_edit($other['characterid'], "Add SP", "Service: ".$serviceadded);
			}

			if($servicespent > 0) {
				$this->PlayerServicepoints->create();
				$this->PlayerServicepoints->set(array(
					'player_id' => $other['playerid'],
					'event_id' => $other['eventid'],
					'points' => ($servicespent * -1),
					'comment' => "spent"
				));
				$this->PlayerServicepoints->save();
				$this->log_edit($other['characterid'], "Deduct SP", "Service: ".$servicespent);
			}

			if($serviceconvert > 0) {
				$this->PlayerServicepoints->create();
				$this->PlayerServicepoints->set(array(
					'player_id' => $other['playerid'],
					'event_id' => $other['eventid'],
					'points' => ($serviceconvert * -1),
					'comment' => "converted to build"
				));
				$this->PlayerServicepoints->save();
			}

   			$this->Session->setFlash('Character has been updated');
   			$this->redirect('/cards/page1/'.$other['cardnumber']);
   		}
    }

	public function page2($cardnumber = null) {

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

		$player_id = $character['Player']['id'];

		$results = $db->fetchAll('SELECT DISTINCT a.id, a.ability_name from abilities a where a.deprecated = 0 order by a.abilitygroup_id, a.ability_name');

		foreach ($results as $row) {
			$available[] = $row['a']['id'].'!'.$row['a']['ability_name'];
		}

		$this->set('available', $available);


		// Get the abilities that already exist in items

		$results = $db->fetchAll(
					'SELECT i.id, i.name, i.slot_number from items i where i.character_id = ?',
					array($id)
				);
		foreach ($results as $row) {
			$this->set('item'.$row['i']['slot_number'].'id',$row['i']['id']);
			$this->set('item'.$row['i']['slot_number'].'name',$row['i']['name']);
		}

		$results = $db->fetchAll(
					'SELECT ia.qty, a.display_name, a.ability_name, ia.id, ia.slot_number, ia.socket_number from item_abilities ia LEFT OUTER JOIN abilities a on ia.ability_id = a.id where ia.character_id = ? and ia.slot_number = 1 order by ia.socket_number',
					array($id)
				);
		$this->set('item1_abilities',$results);

		$results = $db->fetchAll(
					'SELECT ia.qty, a.display_name, a.ability_name, ia.id, ia.slot_number, ia.socket_number from item_abilities ia LEFT OUTER JOIN abilities a on ia.ability_id = a.id where ia.character_id = ? and ia.slot_number = 2 order by ia.socket_number',
					array($id)
				);
		$this->set('item2_abilities',$results);

		$results = $db->fetchAll(
					'SELECT ia.qty, a.display_name, a.ability_name, ia.id, ia.slot_number, ia.socket_number from item_abilities ia LEFT OUTER JOIN abilities a on ia.ability_id = a.id where ia.character_id = ? and ia.slot_number = 3 order by ia.socket_number',
					array($id)
				);
		$this->set('item3_abilities',$results);
	}

	public function savebonded() {
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
   			$this->Character->saveField('last_updated_date',date('c'));

   			foreach($abilities as $row) {
   				switch($row['input_type']) {

   				case 'skill' :
					if($row['quantity'] != 0) {
						$this->ItemAbility->create();

						$this->ItemAbility->set(array(
									'character_id' => $other['characterid'],
									'ability_id' => $row['ability_id'],
									'qty' => $row['quantity'],
									'slot_number' => $row['item_id'],
									'socket_number' => $row['socket_id']
								));
						$this->ItemAbility->save();
					}
					break;
				case 'delete' :
					$this->ItemAbility->delete($row['row_id']);
					break;
				}
   			}

   			for($i = 1; $i <= 3; $i++) {
   				if( array_key_exists('item'.$i.'name',$other) ) {

   					$item_name = $other['item'.$i.'name'];

   					if( array_key_exists('item'.$i.'id',$other) ) {
   						$item_id = $other['item'.$i.'id'];

						$this->Item->id = $item_id;
						$this->Item->saveField('name',$item_name);
   					} else {
   						if(strlen($item_name) > 0) {
							$this->Item->create();
							$this->Item->set(array(
								'character_id' => $other['characterid'],
								'slot_number' => $i,
								'name' => $item_name
							));
							$this->Item->save();
						}
   					}
   				}
   			}

   			$this->Session->setFlash('Bonded Items have been updated');
   			$this->redirect('/cards/page2/'.$other['cardnumber']);
   		}
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


    private function adjustAbilities($abilities = null) {

	    	$results = array();

	    	$lore = array();
	    	$lore_total = 0;

	    	$literacy = array();
	    	$literacy_total = 0;

	    	$lists = array();
	    	$lists[0] = '';
    		$lists_total = 0;

			$career_points = array();
			$career_points_value = array();
			$career_points_total = array();

	    	if (!$abilities) {
				return $results;
			} else {
				foreach ($abilities as $char_ability) {

					// pull out the values
					$qty = $char_ability['ca']['qty'];

					$type = $char_ability['at']['type'];
					$ability_name = $char_ability['a']['ability'];
					$ability_display = $char_ability['a']['display'];

					$event_name = $char_ability['e']['name'];

					if(!$ability_display) {
						$ability_display = $ability_name;
					}

					if($type == 'Career Points') {
						$ability_display = $ability_name;
					}

					$ability_option = $char_ability['ao']['opt'];
					$build_spent = $char_ability['ca']['build_spent'];
					$row_id = $char_ability['ca']['caid'];
					$total_build = $build_spent * $qty;

					if(strlen($ability_option) > 0) {
						$ability_display = $ability_display.' '.$ability_option;
					}

					$results[$event_name][] = array(
						'id'			=> $row_id,
						'qty'			=> $qty,
						'ability' 		=> $ability_display.' {'.$build_spent.'}',
						'build_spent' 	=> sprintf('%3d',$total_build)
					);
				}
	    	}

	    	return $results;
	}
}
?>
