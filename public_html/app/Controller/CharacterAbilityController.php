<?php
class CharacterAbilityController extends AppController {

	public function index() {
		$this->set('character_ability', $this->CharacterAbility->find('all'));
    }

	public function view($id = null) {
		if (!$id) {
			throw new NotFoundException(__('Invalid character_ability'));
		}

		$character_ability = $this->CharacterAbility->findById($id);
		if (!$character_ability) {
			throw new NotFoundException(__('Invalid character_ability'));
		}
		$this->set('character_ability', $character_ability);
    }

    public function add() {
		if ($this->request->is('post')) {
			$this->CharacterAbility->create();
			if ($this->CharacterAbility->saveAssociated($this->request->data)) {
				$this->Session->setFlash('The character_ability has been added.');
				$this->redirect(array('action' => 'add'));
			} else {
				$this->Session->setFlash('Unable to add the character_ability.');
			}
		}

		$this->set('characters', $this->CharacterAbility->Character->find('list',array('fields'=>array('id','name'))));
		$this->set('abilities', $this->CharacterAbility->Ability->find('list',array('fields'=>array('id','ability_name'))));
    }

    public function ajaxadd($character_id, $ability_id, $build_spent, $event_id, $teacher_id, $option_id, $quantity) {



		$this->CharacterAbility->create();
		$this->CharacterAbility->set(array(
		    		'character_id' => $character_id,
		    		'ability_id' => $ability_id,
		    		'build_spent' => $build_spent,
		    		'event_id' => $event_id,
		    		'teacher_id' => $teacher_id,
		    		'option_id' => $option_id,
		    		'quantity' => $quantity
		    	));
    	$this->CharacterAbility->save();

    	$this->layout = 'ajax';
		$this->set('results', $this->CharacterAbility->id);
    }

}
?>