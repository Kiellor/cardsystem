<?php
class Character extends AppModel {
	public $name = 'Character';

	public $belongsTo = array('Player','Cset');
	public $hasAndBelongsToMany = array(
		'Business' => array (
			'className' => 'Business',
			'joinTable' => 'business_partners',
			'with' => 'BusinessPartners'
		)
	);
	public $hasOne = array('War');

	public $virtualFields = array(
		'bank_balance' => 'SELECT sum(value) from character_deposits as cd where cd.character_id = Character.id'
	);

	public $validate = array(
		'name' => array('rule' => 'notEmpty', 'required' => true),
		'cardnumber' => array('rule' => 'notEmpty', 'required' => true),
		'player_id' => array('rule' => 'notEmpty', 'required' => true)
	);

	public function isOwnedBySelf($playerid) {
		if(AuthComponent::user('player_id') === $playerid) {
			return true;
		}

		return false;
	}

	public function adjustAbilities($abilities = null, $collapse_by_skill = null, $abilitygroups = null) {
		$results = array();
		$already_collapsed = array();

		$lore = array();
		$lore_total = 0;

		$monlore = array();
		$monlore_total = 0;

		$reslore = array();
		$reslore_total = 0;

		$literacy = array();
		$literacy_total = 0;

		$lists = array();
		$lists[0] = '';
		$lists_total = 0;

		$other = array();
		$staff = array();

		$career_points_list = array("BP","MP","FP","CP","PP","HP","SP","DP","PsiP");
		$career_points = array();
		$career_points_value = array();
		$career_points_total = array();

		foreach ($career_points_list as $key) {
			$career_points[$key] = $key;
			$career_points_value[$key] = 0;
			$career_points_total[$key] = 0;
		}

		if (!$abilities) {
			return $results;
		} else {
			foreach ($abilitygroups as $grp) {
				$results[$grp['ag']['grp']] = array();
			}

			foreach ($abilities as $char_ability) {

				// pull out the values
				$qty = $char_ability[0]['qty'];
				$uses = $char_ability['a']['uses'];
				$total_uses = $qty * $uses;

				$skip = false;
				if($qty == 0) {
					$skip = true;
				}

				$type = $char_ability['at']['type'];
				if(!array_key_exists($type,$results)) {
					$results[$type] = array();
				}

				$group = $char_ability['ag']['grp'];
				$ability_name = $char_ability['a']['ability'];
				$ability_display = $char_ability['a']['display'];
				$ability_id = $char_ability['a']['id'];
				$opens_list_id = $char_ability['a']['opens_list_id'];
				$ability_ratio = $char_ability['a']['ratio'];
				$cost_increase_interval = $char_ability['a']['cost_increase_interval'];

				$page = $char_ability['ca']['page'];

				if(array_key_exists('option_id',$char_ability[0])) {
					$ability_option_id = $char_ability[0]['option_id'];
				} else {
					$ability_option_id = 0;
				}

				if($opens_list_id == 0) {
					$bold = 0;
				} else {
					$bold = 1;
				}

				if(!$ability_display) {
					$ability_display = $ability_name;
				}

				$ability_option = $char_ability['ao']['opt'];
				$ability_option_display = $char_ability['ao']['opt_disp'];

				$ability_option_id = $char_ability['ca']['option_id'];

				if(!$ability_option_display) {
					$ability_option_display = $ability_option;
				}

				$build_spent = $char_ability['ca']['build_spent'];


				if(strlen($ability_option) > 0) {
					$ability_display = $ability_display.' '.$ability_option_display;
				}

				// Pull out career point alterations for each ability
				foreach ($career_points_list as $key) {
					$value = $char_ability['a'][$key];
					$career_points_value[$key] += $value * $qty;
				}

				// Collapse Skills

				if($collapse_by_skill) {
					if(array_key_exists($ability_id, $collapse_by_skill))
					{
						if(array_key_exists($collapse_by_skill[$ability_id]['name'], $already_collapsed)) {
							$skip = true;
						} else {
							$already_collapsed[$collapse_by_skill[$ability_id]['name']] = '1';

							$ability_display = $collapse_by_skill[$ability_id]['name'];
							$build_spent = $collapse_by_skill[$ability_id]['cost'];
							$qty = '1';
						}
					}
				}

				$total_build = $build_spent * $qty;

				if($skip == false) {
					// Print out each type
					switch($type) {
					case 'Event' :
						if($total_uses == 1) {
							$final_display = $this->formatDisplay($ability_display,$build_spent,$qty);
							$results[$group][] = array(
								'id'			=> $ability_id,
								'optionid'		=> $ability_option_id,
								'qty'			=> $qty,
								'build_spent'	=> $build_spent,
								'ability' 		=> '[0] '.$final_display,
								'bubbles' 		=> '',
								'rows'			=> 1,
								'bold'			=> $bold,
								'ratio'			=> $ability_ratio,
								'page'			=> $page
							);
						} else {
							$bubbles = '';
							$rowcount = 1;
							$i = $total_uses;
							while($i > 15) {
								if(strlen($bubbles) > 0) {
									$bubbles .= "\n";
								}
								$rowcount++;
								//$bubbles .= ' [11111 11111 11111]';
								$i -= 15;
							}
							if($i > 0) {
								if(strlen($bubbles) > 0) {
									$bubbles .= "\n";
								}
								$rowcount++;
								$bubbles .= ' [';
								while($i > 5) {
									//$bubbles .= '11111 ';
									$i -= 5;
								}
								while($i >= 1) {
									//$bubbles .= '1';
									$i--;
								}
								$bubbles .= ']';
							}
							$final_display = $this->formatDisplay($ability_display,$build_spent,$qty);
							if(strlen($final_display) > 30) {
								$rowcount++;
							}
							$results[$group][] = array(
								'id'			=> $ability_id,
								'optionid'		=> $ability_option_id,
								'qty'			=> $qty,
								'uses'			=> $total_uses,
								'build_spent'	=> $build_spent,
								'ability' 		=> $final_display,
								'bubbles' 		=> $bubbles,
								'rows'			=> $rowcount,
								'bold'			=> $bold,
								'ratio'			=> $ability_ratio,
								'page'			=> $page
							);
						}
						break;
					case 'Periodic' :
						$bubbles = '';
						$rowcount = 1;
						$i = $total_uses;
						while($collapse_by_skill && $i >= 10) {
							if(strlen($bubbles) > 0) {
								$bubbles .= "\n";
							}
							$rowcount++;
							//$bubbles .= ' 22222|22222|22222|22222';
							$i -= 10;							
						}
						while($i >= 5) {
							if(strlen($bubbles) > 0) {
								$bubbles .= "\n";
							}
							$rowcount++;
							//$bubbles .= ' 22222|22222|22222|22222';
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
							//$bubbles .= ' '.$subbub.'|'.$subbub.'|'.$subbub.'|'.$subbub;
						}
						$final_display = $this->formatDisplay($ability_display,$build_spent,$qty);
						if(strlen($final_display) > 30) {
							$rowcount++;
						}
						$results[$group][] = array(
							'id'			=> $ability_id,
							'optionid'		=> $ability_option_id,
							'qty'			=> $qty,
							'uses'			=> $total_uses,
							'build_spent'	=> $build_spent,
							'ability' 		=> $final_display,
							'bubbles' 		=> $bubbles,
							'rows'			=> $rowcount,
							'bold'			=> $bold,
							'ratio'			=> $ability_ratio,
							'page'			=> $page
						);
						break;
					case 'Lists' :
						if($build_spent == 0 && $ability_option_id == 0) {
							if($lists[0] == '') {
								$lists[0] = $ability_display;
							} else {
								$lists[] = $ability_display;
							}
						} else if($build_spent == 10) {
							$lists[] = $ability_display;
						} else {
							$lists[] = $ability_display.' ('.$build_spent.')';
						}
						$lists_total += $build_spent;
						break;
					case 'Lore' :
						if(strlen($ability_option) > 0) {
							$ability_display = $ability_option_display;
						}

						if($build_spent == 1) {
							$ability_display = $ability_display;
						} else {
							$ability_display = $ability_display.' ('.$build_spent.')';
						}

						if($group == 'Lore') {
							$lore[] = $ability_display;
							$lore_total += $build_spent;
						} else if($group == 'Monster Lore') {
							$monlore[] = $ability_display;
							$monlore_total += $build_spent;
						} else if($group == 'Restricted Lore') {
							$reslore[] = $ability_display;
							$reslore_total += $build_spent;
						}

						break;
					case 'Literacy' :
						if(strlen($ability_option) > 0) {
							$ability_display = $ability_option_display;
						}

						if($build_spent == 1) {
							$literacy[] = $ability_display;
						} else {
							$literacy[] = $ability_display.' ('.$build_spent.')';
						}
						$literacy_total += $build_spent;
						break;
					case 'Career Points' :
						// Value was already taken care of up above, here we only capture build spent on the particular Career Point
						$career_points_total[$ability_display] += $build_spent * $qty;
						break;
					case 'Race' :
					case 'Religion' :
						$results[$group][] = array(
							'id'			=> $ability_id,
							'optionid'		=> $ability_option_id,
							'qty'			=> $qty,
							'build_spent'	=> $build_spent,
							'ability' 		=> $ability_display,
							'bubbles'		=> '',
							'rows'			=> 1,
							'bold'			=> $bold,
							'ratio'			=> $ability_ratio,
							'page'			=> $page
						);
						break;
					case 'Effects' :
						$ability_display = $this->formatDisplay($ability_display,$build_spent,$qty);
						if($bold == 0) {
							$ability_display = '  '.$ability_display;
						}
						$results[$group][] = array(
							'id'			=> $ability_id,
							'optionid'		=> $ability_option_id,
							'qty'			=> $qty,
							'build_spent'	=> $build_spent,
							'ability' 		=> $ability_display,
							'bubbles'		=> '',
							'rows'			=> 1,
							'bold'			=> $bold,
							'ratio'			=> $ability_ratio,
							'page'			=> $page
						);
						break;
					default:
						if (strpos($ability_display,'+1') !== false) {

							$original_qty = $qty;
							$total = $qty * $build_spent;

							if($cost_increase_interval > 0) {
								if($qty > $cost_increase_interval) {
									$ability_display = '*'.$ability_display;

									$newqty = 0;
									$cost_factor = 1;
									$count_at_cost = 0;
									while($qty > 0) {
										if($qty >= $cost_factor) {
											$count_at_cost++;
											$qty -= $cost_factor;
											$newqty++;
										} else {
											$qty = 0;
										}

										if($count_at_cost == $cost_increase_interval) {
											$count_at_cost = 0;
											$cost_factor++;
										}
									}

									$qty = $newqty;

									$build_spent = $original_qty.'x '.$build_spent;
								}
							}

							$ability_display = str_replace('+1','+'.$qty,$ability_display);

							$ability_display = $this->formatDisplay($ability_display,$build_spent,$qty);
						} else if (strpos($ability_display,'%') !== false) {

							preg_match('/.*%([a-zA-Z]+)%.*/',$ability_display, $matches);
							if(array_key_exists(1,$matches)) {
								$token = $matches[1];

								$value = $char_ability['a'][$token] * $qty;

								$ability_display = str_replace('%'.$token.'%',$value,$ability_display);
							}
							$total = $qty * $build_spent;

							$ability_display = $this->formatDisplay($ability_display,$build_spent,$qty);
						} else if($group == 'Appellation') {
							$ability_display = $ability_display;						
						} else {
							$ability_display = $this->formatDisplay($ability_display,$build_spent,$qty);
						}

						$rowcount = 1;
						if(strlen($ability_display) > 30) {
							$rowcount++;
						}
						$results[$group][] = array(
							'id'			=> $ability_id,
							'optionid'		=> $ability_option_id,
							'qty'			=> $qty,
							'build_spent'	=> $build_spent,
							'ability' 		=> $ability_display,
							'bubbles'		=> '',
							'rows'			=> $rowcount,
							'bold'			=> $bold,
							'ratio'			=> $ability_ratio,
							'page'			=> $page
						);
						break;
					}
				}
			}

			if($lists[0] == '') {
				unset($lists[0]);
			}

			if(count($lists) > 0) {
				$results['Lists'][] = array(
					'id'			=> 0,
					'optionid'		=> '',
					'qty'			=> '',
					'ability' 		=> implode(', ',$lists),
					'all'			=> $lists,
					'build_spent' 	=> $lists_total,
					'bubbles'		=> '',
					'rows'			=> count($lists),
					'bold'			=> 0,
					'ratio'			=> $ability_ratio,
					'page'			=> $page
				);
			}

			foreach(array_keys($career_points) as $cptype) {
				if($career_points_value[$cptype] != 0) {
					$results['Career Points'][] = array(
						'id'			=> 0,
						'optionid'		=> '',
						'qty'			=> '',
						'ability' 		=> $career_points[$cptype],
						'bubbles'		=> '',
						'rows'			=> 1,
						'type'			=> $cptype,
						'value'			=> $career_points_value[$cptype],
						'total'			=> $career_points_total[$cptype],
						'bold'			=> 0,
						'ratio'			=> $ability_ratio,
						'page'			=> $page
					);
				}
			}
			if(count($lore) > 0) {
				$results['Lore'][] = array(
					'id'			=> 0,
					'optionid'		=> '',
					'qty'			=> '',
					'ability' 		=> implode(', ',$lore),
					'all'			=> $lore,
					'build_spent' 	=> $lore_total,
					'bubbles'		=> '',
					'rows'			=> count($lore),
					'bold'			=> 0,
					'ratio'			=> $ability_ratio,
					'page'			=> $page
				);
			}
			if(count($monlore) > 0) {
				$results['Monster Lore'][] = array(
					'id'			=> 0,
					'optionid'		=> '',
					'qty'			=> '',
					'ability' 		=> implode(', ',$monlore),
					'all'			=> $monlore,
					'build_spent' 	=> $monlore_total,
					'bubbles'		=> '',
					'rows'			=> count($monlore),
					'bold'			=> 0,
					'ratio'			=> $ability_ratio,
					'page'			=> $page
				);
			}
			if(count($reslore) > 0) {
				$results['Restricted Lore'][] = array(
					'id'			=> 0,
					'optionid'		=> '',
					'qty'			=> '',
					'ability' 		=> implode(', ',$reslore),
					'all'			=> $reslore,
					'build_spent' 	=> $reslore_total,
					'bubbles'		=> '',
					'rows'			=> count($reslore),
					'bold'			=> 0,
					'ratio'			=> $ability_ratio,
					'page'			=> $page
				);
			}

			if(count($literacy) > 0) {
				$results['Literacy'][] = array(
					'id'			=> 0,
					'optionid'		=> '',
					'qty'			=> '',
					'ability' 		=> implode(', ',$literacy),
					'all'			=> $literacy,
					'build_spent' 	=> $literacy_total,
					'bubbles'		=> '',
					'rows'			=> count($literacy),
					'bold'			=> 0,
					'ratio'			=> $ability_ratio,
					'page'			=> $page
				);
			}
		}

		return $results;
	}

	private function formatDisplay($display, $build, $qty) {
		$total = $qty * $build;

		$hatpos = strpos($display,"^");

		if($hatpos === false) {
			$firstline = $display;
			$otherlines = "";
		} else {
			$firstline = substr($display,0,$hatpos);
			$otherlines = substr($display,$hatpos);
		}

		if($total == 0) {
			$rtn = $firstline.' ('.$build.')';
		} if(strpos($display,"+") !== false) {
			if($qty > 1) {
				$rtn = $firstline.' ('.$build.') = '.$total;
			} else {
				$rtn = $firstline.' ('.$build.')';
			}
		} else if($qty > 1) {
			$rtn = $firstline.' x'.$qty.' ('.$build.') = '.$total;
		} else if($qty < 0) {
			$rtn = $firstline.' x '.$qty.' ('.$build.') = ['.$total.']';
		} else {
			$rtn = $firstline.' ('.$build.')';
		}

		if($hatpos === 0) {
			$rtn = '  '.substr($otherlines,1);
		} else {	
			$rtn = $rtn . $otherlines;
		}

		return $rtn;
	}
}
?>