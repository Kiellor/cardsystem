<?php
class TurnAction extends AppModel {
	public $name = 'TurnAction';

	public $belongsTo = array(
		'Turn',
		'Character',
		'Action',
		'Settlement',
		'Land',
		'Business',
		'SettlementImprovement' => array('className' => 'SettlementImprovement','foreignKey' => 'improvement_id')
	);

	public $actsAs = array('Containable');
}
?>