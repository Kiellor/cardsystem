<?php
class RewardsController extends AppController {

	var $uses = array('Character','CharacterBuildpoints','PlayerServicepoints','Event','CharacterDeposits','Player');

	private $filename = "unset";

	public function isAuthorized($user) {
		if(AuthComponent::user('role_cards')) {
            return true;
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

	    $this->layout = 'mobile';
    }

	private function setUpdateDate($cardnumber) {
		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
		$id = $character['Character']['id'];

		$this->Character->id = $id;
		$this->Character->saveField('last_updated_date',date('c'));
	}

	public function submit() {
		if ($this->request->is('post')) {

			// Read the request data

			$cardnumber = 	$this->getIntValue($this->request->data, 'cardnumber',0);
			$build = 		$this->getIntValue($this->request->data, 'build',0);
			$sp = 			$this->getIntValue($this->request->data, 'service-points',0);
			$rp = 			$this->getIntValue($this->request->data, 'rp-points',0);
			$gold = 		$this->getIntValue($this->request->data, 'gold',0);
			$comment = 		$this->request->data['comment'];

			if($cardnumber == 0) {
				$this->Session->setFlash('Choose a character!');
				$this->redirect('/rewards');
			} else {

				// Load the character

				if (!$cardnumber) {
					throw new NotFoundException(__('Invalid character'));
				}

				$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
				$id = $character['Character']['id'];

				if (!$character) {
					throw new NotFoundException(__('Invalid character'));
				}

				// Begin the update process

				$updated = false;
				if($build != 0 || $rp != 0) {
					$updated = true;

					$this->CharacterBuildpoints->create();
					$this->CharacterBuildpoints->set(array(
						'character_id' => $id,
						'event_id' => 0,
						'base' => 0,
						'service' => 0,
						'bought' => 0,
						'roleplay_build' => 0,
						'roleplay_points' => $rp,
						'lifestyle' => 0,
						'other' => $build,
						'comment' => $comment
					));
					$this->CharacterBuildpoints->save();
				}

				if($gold != 0) {
					$updated = true;

					$this->CharacterDeposits->create();
					$this->CharacterDeposits->set(array(
						'character_id' => $id,
						'value' => $gold,
						'event_id' => 0,
						'comment' => $comment
					));
					$this->CharacterDeposits->save();
				}

				if($sp > 0) {
					$updated = true;

					$this->PlayerServicepoints->create();
					$this->PlayerServicepoints->set(array(
						'player_id' => $character['Player']['id'],
						'event_id' => 0,
						'points' => $sp,
						'comment' => $comment
					));
					$this->PlayerServicepoints->save();
				}

				if($updated) {
					$this->Character->id = $id;
					$this->Character->saveField('last_updated_date',date('c'));
				}

				$this->Session->setFlash('Character has been updated');
				$this->redirect('/rewards');
			}
	   	}
	}

	private function getIntValue($array, $key, $default) {
		return intval($this->getValue($array, $key, $default));
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
}
?>