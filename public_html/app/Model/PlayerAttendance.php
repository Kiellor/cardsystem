<?php
class PlayerAttendance extends AppModel {
	public $name = 'PlayerAttendance';
	public $useTable = "player_attendance";
	public $belongsTo = array('Player','Event');
	public $actsAs = array('Containable');
}
?>