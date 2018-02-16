<?php
class DeathController extends AppController {

	var $uses = array('Character','Events','CharacterAbility');

	public function isAuthorized($user) {

		// All users can use this function
	    if (in_array($this->action, array('loadevents'))) {
	    	return true;
	    }

	    // A user can view their own characters
	    if (in_array($this->action, array('index','load'))) {

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

	    return parent::isAuthorized($user);
	}

	public function index($cardnumber) {
		$this->set('cardnumber',$cardnumber);
    }

    public function edit($cardnumber) {
    	$this->set('cardnumber',$cardnumber);
    }

    public function hasOldDeaths() {

    }

    public function hasOldDeathsDetails() {
    	$deaths = $this->CharacterAbility->find('all',array(
    		'conditions' => array('Ability.id' => array(26294,26287), 'Character.cset_id' => 1, 'Character.active' => 1),
    		'contain' => array('Character.name','Character.cardnumber','Ability.id'),
    		'fields' => array('SUM(CharacterAbility.quantity)'),
    		'group' => array('Character.id')
    	));

    	$this->set('ajax',json_encode($deaths));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function loadevents() {
    	$events = $this->Events->find('all',array('order' => array('Events.id DESC')));

    	$this->set('ajax',json_encode($events));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function load($cardnumber) {    	
		$character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));

		$this->set('ajax',json_encode($character));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function loadOldDeaths($cardnumber) {
    	$deaths = $this->CharacterAbility->find('all',array(
    		'conditions' => array('Character.cardnumber' => $cardnumber, 'Ability.id' => array(26287,26294)),
    		'contain' => array('Character.cardnumber','Ability.id','Ability.ability_name','Event.name'),
    		'fields' => array('CharacterAbility.quantity')
    	));

    	$this->set('ajax',json_encode($deaths));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function deleteOldDeaths($cardnumber) {
        $this->CharacterAbility->deleteAll(array('Character.cardnumber' => $cardnumber, 'Ability.id' => array(26287,26294)));

        $deaths = $this->CharacterAbility->find('all',array(
            'conditions' => array('Character.cardnumber' => $cardnumber, 'Ability.id' => array(26287,26294)),
            'contain' => array('Character.cardnumber','Ability.id','Ability.ability_name','Event.name'),
            'fields' => array('CharacterAbility.quantity')
        ));

        $this->set('ajax',json_encode($deaths));
        $this->layout = 'ajax';
        $this->render('ajax');
    }

    private function newValue($data, $key) {
    	if(isset($data['new_'.$key])) {
    		return $data['new_'.$key];
    	}

    	if(isset($data[$key])) {
    		return $data[$key];
    	}

    	return null;
    }

    public function saveValues() {
    	$data = $this->request->input('json_decode',true);

		$character = $this->Character->find('first',array('conditions' => array('Character.id' => $data['id'])));

    	$this->Character->id = $character['Character']['id'];
    	$this->Character->saveField('reanimate_1',$this->newValue($data,'reanimate_1'));
    	$this->Character->saveField('cheatdeath_1',$this->newValue($data,'cheatdeath_1'));
    	$this->Character->saveField('resurrect_1',$this->newValue($data,'resurrect_1'));
    	$this->Character->saveField('reanimate_2',$this->newValue($data,'reanimate_2'));
    	$this->Character->saveField('cheatdeath_2',$this->newValue($data,'cheatdeath_2'));
    	$this->Character->saveField('resurrect_2',$this->newValue($data,'resurrect_2'));
    	$this->Character->saveField('reanimate_3',$this->newValue($data,'reanimate_3'));
    	$this->Character->saveField('cheatdeath_3',$this->newValue($data,'cheatdeath_3'));
    	$this->Character->saveField('resurrect_3',$this->newValue($data,'resurrect_3'));
    	$this->Character->saveField('reanimate_4',$this->newValue($data,'reanimate_4'));
    	$this->Character->saveField('cheatdeath_4',$this->newValue($data,'cheatdeath_4'));
    	$this->Character->saveField('resurrect_4',$this->newValue($data,'resurrect_4'));
    	$this->Character->saveField('reanimate_5',$this->newValue($data,'reanimate_5'));
    	$this->Character->saveField('cheatdeath_5',$this->newValue($data,'cheatdeath_5'));
    	$this->Character->saveField('finaldeath',$this->newValue($data,'finaldeath'));
    	$this->Character->saveField('empathy_rank4',$this->newValue($data,'empathy_rank4'));

		$character = $this->Character->find('first',array('conditions' => array('Character.id' => $data['id'])));

    	$this->set('ajax',json_encode($character));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

}
?>