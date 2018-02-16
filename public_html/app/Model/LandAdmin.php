<?php
class LandAdmin extends AppModel {
	public $name = 'LandAdmin';
	public $belongsTo = array('Character','Land');
	public $actsAs = array('Containable');
}
?>