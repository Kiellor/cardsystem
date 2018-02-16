<?php
class CardsController extends AppController {

	var $uses = array('Item','ItemAbility','Ability');

	public function index() {
    	if ($this->request->is('post')) {
			$item = $this->Item->find('first',array('conditions' => array('Item.id' => $this->request->data['Item']['id'])));
			if (!$Item) {
				throw new NotFoundException(__('Invalid item'));
			} else {
				$this->redirect(array('controller' => 'items', 'action' => 'page1', $this->request->data['Item']['id']));
			}
    	}
    }

    public function page1($id = null) {

		if (!$id) {
			throw new NotFoundException(__('Invalid item'));
		}

		$Item = $this->Item->find('first',array('conditions' => array('Item.id' => $id)));

		if (!$Item) {
			throw new NotFoundException(__('Invalid Item'));
		}

    	$this->set('Item',$Item);

		$db = $this->Item->getDataSource();

		$character_id = $Item['Character']['id'];

		$results = $db->fetchAll(
			'SELECT COALESCE(SUM(ia.quantity * ia.build_value),0) as build from item_abilities ia where ia.item_id = ?'
			,array($id)
		);

		$build_value = $results[0][0]['build'];
		if($build_value == null) {
			$build_value = 0;
		}
		$this->set('build_value',$build_value);

		$results = $db->fetchAll(
			'SELECT e.name, ia.qty, a.display_name as display, a.ability_name as ability, ao.ability_name as opt, at.name as type, ia.id as iaid, ia.build_value as build_value from item_abilities ca LEFT OUTER JOIN abilities a on ia.ability_id = a.id LEFT OUTER JOIN abilities ao on ia.abilityoption_id = ao.id where ia.item_id = ?',
			array($id)
		);
		$this->set('item_abilities',$this->adjustAbilities($results));

		// Get Item Lists
		$results = $db->fetchAll(
			'SELECT  a.opens_list_id, at.name from abilities a LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id where a.opens_list_id > 0 and a.id in (SELECT b.ability_id from (SELECT sum(c.quantity) as qty, c.ability_id from Item_abilities c where Item_id = ? group by ability_id) b where qty > 0)',
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
			// Get list of professions from the race the Item has, but tweak the costs for the first profession
			$results = $db->fetchAll(
				'SELECT DISTINCT a.id, a.ability_name, la.build_cost, at.name from abilities a LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT JOIN list_abilities la on a.id = la.ability_id where at.name = "Lists" and la.build_cost <= 10 and la.elist_id in ('.implode(',',$lists).') order by a.ability_name');
			foreach ($results as $row) {
				$available[] = $row['a']['id'].'!0 -- '.$row['a']['ability_name'];
			}
			$this->set('available', $available);
		} else {
			// Get skills from the lists the Item has
			if(count($lists) > 0) {
				$results = $db->fetchAll(
					'SELECT DISTINCT a.id, la.abilityoption_id, a.ability_name, la.build_cost, at.sorting_name from abilities a LEFT JOIN ability_types at on a.abilitytype_id = at.id JOIN list_abilities la on a.id = la.ability_id where la.build_cost > -1 and la.elist_id in ('.implode(',',$lists).') order by la.build_cost, a.ability_name');
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

    public function submit() {
   		if ($this->request->is('post')) {

   			$abilities = array();
   			$other = array();
   			foreach(array_keys($this->request->data) as $key) {
   				// find the last '_' Item and pull out the row number element, build an array of skills from this

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

   			foreach($abilities as $row) {
   				switch($row['input_type']) {

   				case 'skill' :
					if($row['quantity'] != 0) {
						$this->ItemAbility->create();

						$teacher_id = $this->getValue($row,'teacher_id','0');

						$this->ItemAbility->set(array(
									'Item_id' => $other['Itemid'],
									'ability_id' => $row['ability_id'],
									'build_spent' => $row['build_spent'],
									'event_id' => $other['eventid'],
									'teacher_id' => $teacher_id,
									'abilityoption_id' => $row['option_id'],
									'quantity' => $row['quantity']
								));
						$this->ItemAbility->save();
					}
					break;
				case 'delete' :
					$this->ItemAbility->delete($row['row_id']);
					break;
				}
   			}

   			$db = $this->Item->getDataSource();
   			$results = $db->fetchAll(
				'SELECT sum(roleplay_points) as roleplay from Item_buildpoints bp where bp.Item_id = ?',
				array($other['Itemid'])
			);
			$roleplaystart = $results[0][0]['roleplay'];

   			$buildbase 		= $this->getIntValue($other,'build-base',0);
   			$buildservice 	= $this->getIntValue($other,'build-service',0);
   			$buildbought 	= $this->getIntValue($other,'build-bought',0);
   			$buildlifestyle = $this->getIntValue($other,'build-lifestyle',0);
   			$roleplaypoints = $this->getIntValue($other,'roleplay-points',0);
   			$buildroleplay 	= $this->getIntValue($other,'build-roleplay',0);
   			$buildother 	= $this->getIntValue($other,'build-other',0);

   			if($roleplaypoints + $roleplaystart > 9) {
   				$roleplaypoints = $roleplaypoints  - 10;
   			}

   			if( ($buildbase + $buildservice + $buildbought + $buildlifestyle + $buildroleplay + $buildother + $roleplaypoints) != 0) {
   				$this->ItemBuildpoints->create();
   				$this->ItemBuildpoints->set(array(
   					'Item_id' => $other['Itemid'],
   					'event_id' => $other['eventid'],
   					'base' => $buildbase,
   					'service' => $buildservice,
   					'bought' => $buildbought,
   					'roleplay_build' => $buildroleplay,
   					'roleplay_points' => $roleplaypoints,
   					'lifestyle' => $buildlifestyle,
   					'other' => $buildother
   				));
   				$this->ItemBuildpoints->save();
   			}

			$bankchange 		= $this->getFloatValue($other,'bank-change',0);

			if($bankchange != 0) {
				$this->ItemDeposits->create();
				$this->ItemDeposits->set(array(
					'Item_id' => $other['Itemid'],
					'value' => $bankchange,
					'event_id' => $other['eventid']
				));
				$this->ItemDeposits->save();
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

   			$this->Session->setFlash('Item has been updated');
   			$this->redirect('/cards/page1/'.$other['id']);
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
    	$db = $this->Item->getDataSource();

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
