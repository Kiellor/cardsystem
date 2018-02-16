<?php
class CharacterAttendance extends AppModel {
	public $name = 'CharacterAttendance';
	public $useTable = "character_attendance";
	public $belongsTo = array('Player','Character','Event');
	public $actsAs = array('Containable');
}
?>