<?php
class ItemAbility extends AppModel {
	public $name = 'ItemAbility';

	public $belongsTo = array(
		'Character',
		'Ability'
	);
}
?>