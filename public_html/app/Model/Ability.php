<?php
class Ability extends AppModel {
	public $name = 'Ability';
	public $displayField = 'ability_name';

	public $belongsTo = array(
		'AbilityType' => array('foreignKey' => 'abilitytype_id'),
		'AbilityGroup'  => array('foreignKey' => 'abilitygroup_id')
	);
}
?>