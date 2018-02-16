<?php
class QuestProgress extends AppModel {
	public $useTable = 'quest_progress';
	public $name = 'QuestProgress';
	public $actsAs = array('Containable');

	public $belongsTo = array(
		'QuestPath',
		'QuestStage',
		'Character',
		'EventAdded' => array('className' => 'Event', 'foreignKey' => 'event_added'),
		'EventCompleted' => array('className' => 'Event', 'foreignKey' => 'event_completed'),
		'EventCollected' => array('className' => 'Event', 'foreignKey' => 'event_collected'),
		'EventDropped' => array('className' => 'Event', 'foreignKey' => 'event_dropped')
	);
}
?>