<?php
class Land extends AppModel {
	public $name = 'Land';
	public $hasMany = array('Settlement','LandAdmin','LandAction');
	public $actsAs = array('Containable');
}
?>