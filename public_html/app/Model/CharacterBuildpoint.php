<?php
class CharacterBuildpoint extends AppModel {
	public $name = 'CharacterBuildpoint';

	public $belongsTo = array(
		'Character', 'Event'
	);
}
?>