<?php
class CharacterDeposit extends AppModel {
	public $name = 'CharacterDeposit';

	public $belongsTo = array(
		'Character',
		'Event'
	);
}
?>