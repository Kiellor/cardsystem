<?php
App::uses('CakeEmail', 'Network/Email');

class ChronicleController extends AppController {

	var $uses = array('Character','Player','User','CharacterChronicle');

	public function isAuthorized($user) {

		if ($user['role_atmosphere']) {
			return true;
		}

	    // A user can view their own characters chronicle
	    if (in_array($this->action, array('view','submit'))) {

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

	public function distill($cardnumber) {
		$this->set('cardnumber',$cardnumber);
	}

	public function loadChronicles($cardnumber) {
		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));

		$results = $this->CharacterChronicle->find('all',array('conditions' => array('CharacterChronicle.character_id' => $character['Character']['id'])));
	
		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
	}

	public function needapproval() {
    	$db = $this->Character->getDataSource();
		$results = $db->fetchAll('SELECT distinct c.name, c.cardnumber FROM characters c LEFT OUTER JOIN character_chronicles p ON c.id = p.character_id WHERE p.submitted = 1 and p.approved = 0 ORDER BY c.cardnumber');

		$this->set('cards',$results);
    }

	public function approved() {
    	$db = $this->Character->getDataSource();
		$results = $db->fetchAll('SELECT distinct c.name, c.cardnumber FROM characters c LEFT OUTER JOIN character_chronicles p ON c.id = p.character_id WHERE p.submitted = 1 and p.approved = 1 ORDER BY c.cardnumber');

		$this->set('cards',$results);
    }

	public function view($cardnumber = null) {

		if (!$cardnumber) {
			throw new NotFoundException(__('Invalid character'));
		}

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		if (!$character) {
			throw new NotFoundException(__('Invalid character'));
		}

		$player_id = $character['Player']['id'];

		$this->set('character', $character);

		$db = $this->Character->getDataSource();
		$results = $db->fetchAll('SELECT c.id, c.entry, c.comments, c.created, c.modified, c.submitted, c.approved from character_chronicles c where c.character_id = ? order by c.submitted asc',array($id));
		$this->set('chronicles',$results);

		if ($this->request->is('post')) {

			$this->CharacterChronicle->create();
			$this->CharacterChronicle->set(array(
				'character_id' => $id,
				'entry' => $this->request->data['entry']
			));
			$this->CharacterChronicle->save();

			$this->redirect(array('controller' => 'chronicle', 'action' => 'view', $cardnumber));
		}
    }

    public function edit($cardnumber = null, $chroncileid = null) {

			if (!$cardnumber) {
				throw new NotFoundException(__('Invalid character'));
			}

			$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
			$id = $character['Character']['id'];

			if (!$character) {
				throw new NotFoundException(__('Invalid character'));
			}

			$player_id = $character['Player']['id'];

			$this->set('character', $character);

			$db = $this->Character->getDataSource();
			$results = $db->fetchAll('SELECT c.id, c.entry, c.comments, c.created, c.modified, c.submitted, c.approved from character_chronicles c where c.character_id = ? and c.id = ? order by c.submitted asc',array($id,$chroncileid));
			$this->set('chronicles',$results);
    }

    public function comment($cardnumber = null, $chroncileid = null) {

			if (!$cardnumber) {
				throw new NotFoundException(__('Invalid character'));
			}

			$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
			$id = $character['Character']['id'];

			if (!$character) {
				throw new NotFoundException(__('Invalid character'));
			}

			$player_id = $character['Player']['id'];

			$this->set('character', $character);

			$db = $this->Character->getDataSource();
			$results = $db->fetchAll('SELECT c.id, c.entry, c.comments, c.created, c.modified, c.submitted, c.approved from character_chronicles c where c.character_id = ? and c.id = ? order by c.submitted asc',array($id,$chroncileid));
			$this->set('chronicles',$results);
    }

    public function update($cardnumber = null, $chroncileid = null) {

		if (!$chroncileid) {
			throw new NotFoundException(__('Invalid chronicle'));
		}

		$chronicle = $this->CharacterChronicle->findById($chroncileid);

		if (!$chronicle) {
			throw new NotFoundException(__('Invalid chronicle'));
		}

		$this->CharacterChronicle->id = $chroncileid;
		$this->CharacterChronicle->saveField('entry',$this->request->data['entry']);

		$this->redirect(array('controller' => 'chronicle', 'action' => 'view', $cardnumber));
	}

    public function updatecomments($cardnumber = null, $chroncileid = null) {

		if (!$chroncileid) {
			throw new NotFoundException(__('Invalid chronicle'));
		}

		$chronicle = $this->CharacterChronicle->findById($chroncileid);

		if (!$chronicle) {
			throw new NotFoundException(__('Invalid chronicle'));
		}

		$this->CharacterChronicle->id = $chroncileid;
		$this->CharacterChronicle->saveField('comments',$this->request->data['comments']);

		$this->redirect(array('controller' => 'chronicle', 'action' => 'view', $cardnumber));
	}

	public function submit($cardnumber = null, $chroncileid = null, $submitvalue = 1) {

		if (!$chroncileid) {
			throw new NotFoundException(__('Invalid chronicle'));
		}

		$chronicle = $this->CharacterChronicle->findById($chroncileid);

		if (!$chronicle) {
			throw new NotFoundException(__('Invalid chronicle'));
		}

		$this->CharacterChronicle->id = $chroncileid;
		$this->CharacterChronicle->saveField('submitted',$submitvalue);

		$this->redirect(array('controller' => 'chronicle', 'action' => 'view', $cardnumber));
	}

	public function approve($cardnumber = null, $chroncileid = null) {

		if (!$chroncileid) {
			throw new NotFoundException(__('Invalid chronicle'));
		}

		$chronicle = $this->CharacterChronicle->findById($chroncileid);

		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$player_id = $character['Character']['player_id'];

		$user = $this->User->find('first',array('conditions' => array('User.player_id' => $player_id)));
		$username = $user['User']['username'];

		if (!$chronicle) {
			throw new NotFoundException(__('Invalid chronicle'));
		}

		$this->CharacterChronicle->id = $chroncileid;
		$this->CharacterChronicle->saveField('approved',1);

		$emailconfig = Configure::read('ChronicleController.approve');

		$email = new CakeEmail();
		$email->config($emailconfig['config']);
		$email->to($username);
		$email->from($emailconfig['from']);
		$email->subject($emailconfig['subject']);
		$email->emailFormat('html');

		$body = sprintf($emailconfig['body'],$cardnumber,$cardnumber, $character['Character']['name'],$chronicle['CharacterChronicle']['entry']);

		$email->send($body);

		$this->redirect(array('controller' => 'chronicle', 'action' => 'view', $cardnumber));
	}

}
?>