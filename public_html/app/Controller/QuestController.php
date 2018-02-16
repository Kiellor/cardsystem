<?php

class QuestController extends AppController {

	var $uses = array('Character','QuestPath','QuestStage','QuestProgress');

	public function isAuthorized($user) {

        if (in_array($this->action, array('view','loadCharacterProgress'))) {
            $cardnumber = $this->params['pass'][0];
            $character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
            if (!$character) {
                throw new NotFoundException(__('Invalid character'));
            }
            if ($this->Character->isOwnedBySelf($character['Character']['player_id'])) {
                return true;
            }
        }

		if ($user['role_quest']) {
			return true;
		}

	    return parent::isAuthorized($user);
	}

	public function index() {
    }

    public function view($cardnumber) {
        $this->set('cardnumber',$cardnumber);
    }

    public function loadCharacterProgress($cardnumber) {
        $quests = $this->QuestProgress->find('all',array(
            'conditions' => array('Character.cardnumber' => $cardnumber),
            'order' => array('QuestPath.id','QuestStage.quest_stage','QuestStage.stage_code'),
            'contain' => array('Character' => array('name','id','cardnumber'),'EventAdded','EventCompleted','QuestPath','QuestStage' => array('Children'))
        ));

        $output = array();
        $item = array();

        foreach($quests as $row) {
            if( !isset($item['QuestPath']) ) {
                $item['QuestPath'] = $row['QuestPath'];
            } else if( $item['QuestPath']['id'] != $row['QuestPath']['id']) {
                $output[] = $item;
                $item = array();
                $item['QuestPath'] = $row['QuestPath'];
            }
            $item['QuestStages'][] = array(
                'QuestProgress' => $row['QuestProgress'], 
                'QuestStage' => $row['QuestStage'],
                'EventAdded' => $row['EventAdded'],
                'EventCompleted' => $row['EventCompleted'],
                'EventCollected' => $row['EventCollected'],
                'EventDropped' => $row['EventDropped']
            );
        }
        if(isset($item['QuestPath'])) {
            $output[] = $item;
        }

        $this->set('ajax',json_encode($output));
        $this->layout = 'ajax';
        $this->render('ajax');
    }

    public function loadQuests() {

    	$results = $this->QuestPath->find('all');

    	$this->set('ajax',json_encode($results));
    	$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function createNewQuest() {
		$data = $this->request->input('json_decode',true);
		$this->QuestPath->create();
		$this->QuestPath->set(array('name' => $data['newQuestName']));
		$this->QuestPath->save();

		$results = $this->QuestPath->findById($this->QuestPath->id);

		$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function createNewStage() {
    	$data = $this->request->input('json_decode',true);

    	$this->QuestStage->create();
    	$this->QuestStage->set(array(
    		'quest_path_id' => $data['quest_path_id'],
    		'quest_stage' => $data['quest_stage'],
    		'stage_code' => $data['stage_code'],
    		'description' => $data['description'],
    		'reward' => $data['reward']
    	));
    	$this->QuestStage->save();

		$results = $this->QuestStage->findById($this->QuestStage->id);

    	$this->set('ajax',json_encode($results));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function deleteStage($stage_id) {
        $this->QuestStage->delete($stage_id);

        $this->set('ajax',$stage_id);
        $this->layout = 'ajax';
        $this->render('ajax');
    }

    private function getCurrentEventId() {
        $db = $this->QuestStage->getDataSource();
        $results = $db->fetchAll('SELECT e.id  from events e where id in (SELECT max(id) from events)');
        
        return $results[0]['e']['id'];
    }

    public function addQuestStages($cardnumber, $path_id, $quest_stage) {
        $stages = $this->QuestStage->find('all',array('conditions' => array('QuestStage.quest_path_id' => $path_id, 'QuestStage.quest_stage' => $quest_stage)));

        $character = $this->Character->find('first',array('conditions' => array('Character.cardnumber' => $cardnumber)));
        $character_id = $character['Character']['id'];

        $event_id = $this->getCurrentEventId();
        
        foreach($stages as $stage) {
            $this->QuestProgress->create();
            $this->QuestProgress->set(array(
                'character_id' => $character_id,
                'quest_path_id' => $stage['QuestStage']['quest_path_id'],
                'quest_stage_id' => $stage['QuestStage']['id'],
                'event_added' => $event_id
            ));
            $this->QuestProgress->save();
        }
    }

    public function addQuestsToCharacter() {
        $data = $this->request->input('json_decode',true);
        // Get the current event_id
        $cardnum = $data['cardnumber'];
        $path_id = $data['add'];

        $this->addQuestStages($cardnum,$path_id,1);
    }

    public function updateQuests() {
        $data = $this->request->input('json_decode',true);
        // Get the current event_id
        $event_id = $this->getCurrentEventId();
        $cardnum = $data['cardnumber'];
        $character_id = $data['updates'][0]['QuestStages'][0]['QuestProgress']['character_id'];

        foreach ($data['updates'] as &$path) {
            $unfinished = count($path['QuestStages']);
            $path_id = $path['id'];
            $quest_stage = 0;
            foreach($path['QuestStages'] as $stage) {
                $quest_stage = max($quest_stage, $stage['QuestStage']['quest_stage']);
                if(isset($stage['completed']) && $stage['completed'] == true) {
                    $unfinished--;
                    $this->QuestProgress->id = $stage['QuestProgress']['id'];
                    $this->QuestProgress->set(array('event_completed' => $event_id));
                    $this->QuestProgress->save();
                } else if(isset($stage['QuestProgress']['event_completed'])) {
                    $unfinished--;
                }
            }
            $path['count'] = $unfinished;
            // If all of the items of the same quest_stage number are done then add all of the items for the next quest stage to this character
            if($unfinished == 0) {
                $this->addQuestStages($cardnum,$path_id,$quest_stage+1);
            }
        }

        $this->set('ajax',json_encode($data));
        $this->layout = 'ajax';
        $this->render('ajax');        
    }

}
?>
