<?php
App::uses('CakeEmail', 'Network/Email');

class FeedbackController extends AppController {

	var $uses = array('Character','Player','User','CharacterProblem');

	public function isAuthorized($user) {
	    // A user can view their own characters
	    if (in_array($this->action, array('cardproblem','hideproblem'))) {

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

	public function cardproblem($cardnumber = null) {

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

		if ($this->request->is('post')) {

			$this->CharacterProblem->create();
			$this->CharacterProblem->set(array(
				'character_id' => $id,
				'problem_report' => $this->request->data['feedback']
			));
			$this->CharacterProblem->save();


			$emailconfig = Configure::read('FeedbackController.cardproblem');

			$email = new CakeEmail();
			$email->config($emailconfig['config']);
			$email->to($emailconfig['to']);
			$email->cc(AuthComponent::user('username'));
			$email->from($emailconfig['from']);
			$email->replyTo(AuthComponent::user('username'));
			$email->subject(sprintf($emailconfig['subject'],$cardnumber));
			$email->emailFormat('html');

			$body = sprintf($emailconfig['body'],$cardnumber,$cardnumber, $character['Character']['name'], $character['Player']['name'], $this->request->data['feedback']);

			$email->send($body);

			$this->Session->setFlash(__('Your issue has been reported'));
			$this->redirect(array('controller' => 'characters', 'action' => 'view', $cardnumber));
		}
    }

	public function hideproblem($cardnumber = null, $problemid = null) {

		if (!$problemid) {
			throw new NotFoundException(__('Invalid problem report'));
		}

		$problem = $this->CharacterProblem->findById($problemid);

		if (!$problem) {
			throw new NotFoundException(__('Invalid problem report'));
		}

		$this->CharacterProblem->id = $problemid;
		$this->CharacterProblem->saveField('hidden',1);
		$this->layout = 'ajax';
	}

}
?>