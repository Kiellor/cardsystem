<?php
class PlayersController extends AppController {

	var $uses = array('User','Character','Player');

	public function isAuthorized($user) {
		if(array_key_exists(0,$this->params['pass'])) {
			$id = $this->params['pass'][0];
		} else {
			$id = AuthComponent::user('player_id');
		} 

	    // A user can edit and view their own user info
	    if (in_array($this->action, array('view','edit','savemednotes','savememo'))) {
	        if ($this->Player->isOwnedBySelf($id)) {
	            return true;
	        }
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

		if ($this->request->is('post')) {
			$this->Player->create();
			if ($this->Player->save($this->request->data)) {

				$nextPrefix = $this->getNextCardPrefixImpl();
				$this->Player->set(array(
					'cardnumber_prefix' => $nextPrefix
				));
				$this->Player->save();

				$this->Session->setFlash('The player has been added.');
				$this->redirect(array('action' => 'view', $this->Player->id));
			} else {
				$this->Session->setFlash('Unable to add the player.');
			}
		}

		$players = $this->getPlayersImpl('1');
		$this->set('players',json_encode($players));
    }

    private function getNextCardPrefixImpl() {
    	$results = $this->Player->find('all',array(
    		'conditions' => array('Player.cardnumber_prefix >' => 0),
    		'fields' => array('Player.id','Player.cardnumber_prefix'),
    		'order' => array('Player.cardnumber_prefix'),
    		'recursive' => 0
    	));

    	$nextCardPrefix = 1;
    	foreach($results as $row) {
    		if($row['Player']['cardnumber_prefix'] > $nextCardPrefix) {
    			break;
    		}
   			$nextCardPrefix++;
    	}

    	return $nextCardPrefix;
    }

    public function getNextCardPrefix() {
    	$next = $this->getNextCardPrefixImpl();
		
		$this->set('ajax',json_encode($next));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    private function getPlayersImpl($cset = 1) {
    	$db = $this->Player->getDataSource();

		$results = $db->fetchAll('SELECT p.id, p.name, c.name, c.cardnumber, c.new_character from players p LEFT OUTER JOIN characters c on c.player_id = p.id where c.cset_id = ? order by p.name, c.cardnumber',array($cset));

		
		$players = array();
		$mergearray = array();
		$counter = 0;

		foreach($results as $row) {

			$playerid = $row['p']['id'];
			$playername = $row['p']['name'];
			$charname = $row['c']['name'];
			$cardnumber = $row['c']['cardnumber'];
			$new_character = $row['c']['new_character'];

			if( array_key_exists($playerid, $mergearray)) {
				$index = $mergearray[$playerid];
				$tempobj = $players[$index];
			} else {
				$index = $counter;
				$counter++;
				$tempobj = array('Player' => array('id' => $playerid, 'name'=> $playername), 'Characters' => array());
			}
			$tempobj['Characters'][] = array('cardnumber' => $cardnumber, 'name' => $charname, 'new_character' => $new_character);

			$players[$index] = $tempobj;
			$mergearray[$playerid] = $index;
		}

		return $players;
    }

	public function getPlayers($cset = 1) {
		$players = $this->getPlayersImpl($cset);
		
		$this->set('ajax',json_encode($players));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function getCSets() {
    	$this->set('ajax', json_encode($this->Character->Cset->find('all',array('order' => 'Cset.id'))));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

	public function notregistered() {
		$db = $this->Player->getDataSource();

		$results = $db->fetchAll('SELECT p.id, p.name from players p where p.id not in (select player_id from users) order by p.name');

		$this->set('players',$results);
    }

   	public function view($id = null) {
		if (!$id) {
			$id = AuthComponent::user('player_id');
		}

		$player = $this->Player->findById($id);

		if (!$player) {
			throw new NotFoundException(__('Invalid player'));
		}

		$this->set('player', $player);

		$user = $this->User->find('first',array('conditions' => array('User.player_id' => $id)));

		if ($user) {
			$this->set('user', $user);
		}
    }

    public function viewnew($id = null) {
    	if (!$id) {
			$id = AuthComponent::user('player_id');
		}

		$player = $this->Player->findById($id);

		if (!$player) {
			throw new NotFoundException(__('Invalid player'));
		}
    	$this->set('playerId',$id);
    }

    public function getPlayerInfo($id = null) {
    	if (!$id) {
			$id = AuthComponent::user('player_id');
		}

		$player = $this->Player->findById($id);

		if (!$player) {
			throw new NotFoundException(__('Invalid player'));
		}

		$ajax = array();
		$ajax['Player'] = $player['Player'];

		$user = $this->User->find('first',array('conditions' => array('User.player_id' => $id)));

		if ($user) {
			$ajax['User'] = $user['User'];
		}

		$characters = $this->Character->find('all',array('conditions' => array('Character.player_id' => $id)));
		$ajax['Characters'] = $characters;

		$this->set('ajax',json_encode($ajax));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

	public function changename($id = null) {
		if (!$id) {
			throw new NotFoundException(__('Invalid player'));
		}

		$player = $this->Player->findById($id);

		if (!$player) {
			throw new NotFoundException(__('Invalid character'));
		}

		$this->Player->id = $player['Player']['id'];

		$data = $this->request->input('json_decode',true);

		$this->set('value',$data);

		$this->Player->saveField('name',$data['value']);
		$this->layout = 'ajax';
    }

     public function savemednotes($id = null) {

			if (!$id) {
				throw new NotFoundException(__('Invalid player'));
			}

			$player = $this->Player->findById($id);

			if (!$player) {
				throw new NotFoundException(__('Invalid character'));
			}

			$data = $this->request->input('json_decode',true);

			$this->set('value',$data);

			$this->Player->id = $id;
			$this->Player->saveField('medical_notes',$data['value']);
			$this->Player->saveField('update_date',date('c'));
			$this->layout = 'ajax';
    }

     public function savememo($id = null, $cardnumber = null) {

			if (!$id) {
				throw new NotFoundException(__('Invalid player'));
			}

			$player = $this->Player->findById($id);
			$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
			$cid = $character['Character']['id'];

			if (!$player) {
				throw new NotFoundException(__('Invalid character'));
			}

			$data = $this->request->input('json_decode',true);

			$this->set('value',$data);

			$this->Character->id = $cid;
			$this->Character->saveField('character_memo',$data['value']);
			$this->Character->saveField('last_updated_date',date('c'));
			$this->layout = 'ajax';
    }
}
?>