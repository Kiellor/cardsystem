<?php
class Player extends AppModel {
	public $name = 'Player';

	public $hasMany = array(
		'Character' => array(
			'className' 	=> 'Character',
			'foreignKey'	=> 'player_id'
		)
	);

	public $actsAs = array('Containable');

	public function isOwnedBySelf($playerid) {
		if(AuthComponent::user('player_id') === $playerid) {
			return true;
		}

		return false;
	}
}
?>