<?php
class DefaultAction extends AppModel {
	public $name = 'DefaultAction';

	public $belongsTo = array(
		'Character',
		'Action',
		'Settlement',
		'Land',
		'Business',
		'SettlementImprovement' => array('className' => 'SettlementImprovement','foreignKey' => 'improvement_id')
	);
}
?>