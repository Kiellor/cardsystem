<?php
class DemographicsController extends AppController {

	var $uses = array('Character','CharacterAbility','Ability','CharacterBuildpoints','PlayerServicepoints','Event','CharacterDeposits');

	public function isAuthorized($user) {
	   	if (isset($user['role']) && ($user['role'] === 'marketing' || $user['role'] === 'staff')) {
	   		return true;
	    }

	    return parent::isAuthorized($user);
	}

	public function index() {}

	public function attendance() {
	}

	public function allattendance() {
	}


	public function loadevents() {
		$db = $this->Character->getDataSource();
		$results = $db->fetchAll(
			'select name, id from events order by id desc'
		);
		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}
	
	public function loadlatestattendance() {
		$db = $this->Character->getDataSource();
		$results = $db->fetchAll(
			'Select b.username, b.playername, b.attendance_note, b.name, b.player_id, b.cardnumber, b.character_id, b.event_id from(Select a.username, a.playername, a.attendance_note, a.name, a.player_id, a.cardnumber, a.character_id, max(a.maxevent) as event_id from (Select u.username, p.name as playername, p.attendance_note, c.name, c.player_id, c.cardnumber, cb.character_id, max(cb.event_id) as maxevent from character_buildpoints cb left outer join characters c on c.id = cb.character_id left outer join players p on p.id = c.player_id left outer join users u on u.player_id = c.player_id where c.cset_id = 1 and c.active = 1 group by c.id ) a group by a.player_id order by event_id desc, name) b order by b.event_id desc, b.playername'
		);
		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function loadattendance() {
		$db = $this->Character->getDataSource();
		$results = $db->fetchAll(
			'Select distinct u.username, p.name, c.name, c.cardnumber, cb.event_id from character_buildpoints cb left outer join characters c on c.id = cb.character_id left outer join players p on p.id = c.player_id left outer join users u on u.player_id = p.id order by cb.event_id desc, p.name'
		);
		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function old_levels() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'Select count(a.character_id) as cnt, if(diff < 60, if(diff < 5, "0-5",concat(10*floor((diff-4)/10)+5, "-", 10*floor((diff-4)/10) + 14)),"60+") as level from ( select character_id, floor(sum(base + service + bought + roleplay_build + lifestyle + other)/10) as diff from character_buildpoints bp left join characters c on c.id = bp.character_id where c.cset_id = 1 and active = 1 group by character_id) a group by 2 order by diff'
		);
		$this->set('build_earned',$results);
	}

	public function levels() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'Select count(a.character_id) as cnt, if(diff < 80, if(diff < 5, "0-5",concat(10*floor((diff-4)/10)+5, "-", 10*floor((diff-4)/10) + 14)),"85+") as level from ( select character_id, floor(sum(build_spent * quantity)/10) as diff from character_abilities ca left join characters c on c.id = ca.character_id where c.cset_id = 1 and c.active = 1 group by character_id) a group by 2 order by diff'
		);
		$this->set('build_earned',$results);
	}

	public function religions() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(

			'SELECT count(ca.character_id) as count, a.ability_name as religion
			from character_abilities ca left outer join abilities a on a.id = ca.ability_id
			left outer join characters c on c.id = ca.character_id 
			where c.cset_id = 1 and c.active = 1 and ca.ability_id in (select id from abilities where abilitytype_id = 26)
			group by ability_id
			order by count desc'

		);
		$results2 = $db->fetchAll(

			'SELECT count(DISTINCT c.id) as count, ("All") as religion
			from characters c where c.cset_id = 1 and c.active = 1'

		);

		$this->set('religion',$results);
		$this->set('guff',$results2);
		$this->set('debug',$results2);
	}



	public function religions2() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(

			'SELECT count(ca.character_id) as count, a.ability_name as religion
			from character_abilities ca left outer join abilities a on a.id = ca.ability_id left outer join characters c on c.id = ca.character_id 
			where c.cset_id = 1 and c.active = 1 and ca.ability_id in (select id from abilities where abilitytype_id = 26)
			group by ability_id
			order by count desc'

		);
		$results2 = $db->fetchAll(

			'SELECT count(DISTINCT c.id) as count, ("All") as religion
			from characters c where c.cset_id = 1 and c.active = 1'

		);

		$this->set('religion',$results);
		$this->set('guff',$results2);
		$this->set('debug',$results2);
	}



	public function lower_lists() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT count(ca.character_id) as count, a.ability_name as list from character_abilities ca left outer join abilities a on a.id = ca.ability_id left outer join characters c on c.id = ca.character_id 
			where c.cset_id = 1 and c.active = 1 and ca.ability_id in (select id from abilities where abilitytype_id = 21) group by ability_id order by count desc'
		);
		$this->set('professions',$results);
		$this->set('debug',$results);
	}


	public function higher_lists() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT count(ca.character_id) as count, a.ability_name as list from character_abilities ca left outer join abilities a on a.id = ca.ability_id left outer join characters c on c.id = ca.character_id 
			where c.cset_id = 1 and c.active = 1 and  ca.ability_id in (select id from abilities where abilitytype_id = 22) group by ability_id order by count desc'
		);
		$this->set('professions',$results);
		$this->set('debug',$results);
	}


	public function races() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT count(ca.character_id) as count, a.ability_name as list from character_abilities ca left outer join abilities a on a.id = ca.ability_id left outer join characters c on c.id = ca.character_id 
			where c.cset_id = 1 and c.active = 1 and  ca.ability_id in (select id from abilities where abilitytype_id = 23) group by ability_id order by count desc'
		);
		$this->set('races',$results);
		$this->set('debug',$results);
	}

	public function check_fence() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll('

			SELECT COUNT(DISTINCT ca.character_id) as sneaky_buggers
			FROM character_abilities ca left outer join characters c on c.id = ca.character_id 
			where c.cset_id = 1 and c.active = 1 and 
			ca.ability_id = 3035

		');

		$results2 = $db->fetchAll('

			SELECT COUNT(DISTINCT c.id) as total
			FROM characters c where c.cset_id = 1 and c.active = 1

		');

		$this->set('neerdowells',$results);
		$this->set('nonrogues',$results2);
		$this->set('debug',$results2);
	}

	public function check_fence_2() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll('

			SELECT COUNT(DISTINCT character_id) as sneaky_buggers
			FROM character_abilities
			WHERE ability_id = 3035

		');

		$results2 = $db->fetchAll('

			SELECT COUNT(DISTINCT character_id) as total
			FROM character_abilities

		');

		$this->set('neerdowells',$results);
		$this->set('nonrogues',$results2);
		$this->set('debug',$results2);
	}

	public function banks() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll('

		SELECT character_id, bank_change FROM character_buildpoints

		');



		$this->set('listdata',$results);
		$this->set('debug',$results);
	}


	public function listcount() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll('

		SELECT not_as_small.list_count as num_lists, count(*) as num_playaz FROM
		(
			SELECT small.char_id as banana, count(*) as list_count FROM(
				SELECT ca.character_id AS char_id, a.ability_name AS thing
				FROM character_abilities ca
				LEFT OUTER JOIN abilities a on a.id = ca.ability_id
				LEFT OUTER JOIN characters c on ca.character_id = c.id
				WHERE c.cset_id = 1 and c.active = 1 and ca.ability_id IN
							(
							SELECT id FROM abilities WHERE abilitytype_id = 21
							)
				ORDER BY char_id
			) small
			GROUP BY banana
		)not_as_small
		GROUP BY num_lists

		');



		$this->set('listdata',$results);
		$this->set('debug',$results);
	}

	public function listcount_2() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll('

		SELECT not_as_small.list_count as num_lists, count(*) as num_playaz FROM
		(
			SELECT small.char_id as banana, count(*) as list_count FROM(
				SELECT character_abilities.character_id AS char_id, abilities.ability_name AS thing
				FROM character_abilities
				LEFT OUTER JOIN abilities on abilities.id = character_abilities.ability_id
				WHERE character_abilities.ability_id IN
							(
							SELECT id FROM abilities WHERE abilitytype_id = 21
							)
				ORDER BY char_id
			) small
			GROUP BY banana
		)not_as_small
		GROUP BY num_lists

		');



		$this->set('listdata',$results);
		$this->set('debug',$results);
	}


	public function average_joe() {
		$db = $this->Character->getDataSource();

		$average_lower_lists = $db->fetchAll('

		SELECT AVG(list_count) FROM
		(
			SELECT small.char_id as banana, count(*) as list_count FROM(
				SELECT character_abilities.character_id AS char_id, abilities.ability_name AS thing
				FROM character_abilities
				LEFT OUTER JOIN abilities on abilities.id = character_abilities.ability_id
				WHERE character_abilities.ability_id IN
							(
							SELECT id FROM abilities WHERE abilitytype_id = 21
							)
				ORDER BY char_id
			) small
			GROUP BY banana
		)not_as_small

		');

		$lower_lists = $db->fetchAll(
			'SELECT count(ca.character_id) as count, a.ability_name as list from character_abilities ca left outer join abilities a on a.id = ca.ability_id where ca.ability_id in (select id from abilities where abilitytype_id = 21) group by ability_id order by count desc'
		);



		$this->set('num_lists',$average_lower_lists);
		$this->set('lists',$lower_lists);
		$this->set('debug',$lower_lists);
	}

	public function debug_printout() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll('
			SELECT * FROM(

									character_abilities

			)

		');
		$this->set('professions',$results);
		$this->set('debug',$results);
	}

	public function num_professions() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
		        'SELECT count(ca.character_id) as count, a.ability_name as list from character_abilities ca left outer join abilities a on a.id = ca.ability_id where ca.ability_id in (select id from abilities where abilitytype_id = 21) group by ability_id order by count desc'
		);
		$this->set('listcount',$results);
		$this->set('debug',$results);
	}



	public function test_1_religions() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(

			'SELECT count(ca.character_id) as count, a.ability_name as religion
			from character_abilities ca left outer join abilities a on a.id = ca.ability_id
			where ca.ability_id in (select id from abilities where abilitytype_id = 26)
			group by ability_id
			order by count desc'

		);


		$this->set('religion',$results);
		$this->set('debug',$results);
	}

	public function test_2_religions() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(

			'SELECT count(ca.character_id) as count, a.ability_name as religion
			from character_abilities ca left outer join abilities a on a.id = ca.ability_id
			where ca.ability_id in (select id from abilities where abilitytype_id = 26)
			group by ability_id
			order by count desc'

		);
		$results2 = $db->fetchAll(

			'SELECT count(DISTINCT ca.character_id) as count, ("All") as religion
			from character_abilities ca'

		);

		$this->set('religion',$results);
		$this->set('guff',$results2);
		$this->set('debug',$results2);
	}

	public function test_2_professions() {
		$db = $this->Character->getDataSource();

		$results = $db->fetchAll(
			'SELECT count(ca.character_id) as count, a.ability_name as list from character_abilities ca left outer join abilities a on a.id = ca.ability_id where ca.ability_id in (select id from abilities where abilitytype_id = 23) group by ability_id order by count desc'
		);
		$this->set('races',$results);
		$this->set('debug',$results);
	}

}


?>
