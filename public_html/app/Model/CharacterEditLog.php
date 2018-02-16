<?php
class CharacterEditLog extends AppModel {
	public $name = 'CharacterEditLog';
	public $useTable = 'character_edit_log';

	public $belongsTo = array(
		'Character',
		'User'
	);
}
?>