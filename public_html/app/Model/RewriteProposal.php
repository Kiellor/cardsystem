<?php
class RewriteProposal extends AppModel {
	public $name = 'RewriteProposal';

	public $belongsTo = array(
		'Character',
		'Ability',
		'AbilityOption' => array(
			'className' => 'Ability',
			'foreignKey' => 'abilityoption_id')
	);

	public $actsAs = array('Containable');
}
?>