<?php
class CharacterAbility extends AppModel {
	public $name = 'CharacterAbility';

	public $belongsTo = array(
		'Character',
		'Ability',
		'Event'
	);

	public $actsAs = array('Containable');
}
?>