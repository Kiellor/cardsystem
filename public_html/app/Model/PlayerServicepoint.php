<?php
class PlayerServicepoint extends AppModel {
	public $name = 'PlayerServicepoint';

	public $belongsTo = array(
		'Player', 'Event'
	);
}
?>