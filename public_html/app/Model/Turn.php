<?php
class Turn extends AppModel {
	public $name = 'Turn';
	public $hasMany = array('SettlementValues','TurnAction');
	public $actsAs = array('Containable');

	public $belongsTo = array('Event' => array('foreignKey' => 'after_event_id'));
}
?>