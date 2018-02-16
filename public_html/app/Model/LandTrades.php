<?php
class LandTrades extends AppModel {
	public $name = 'LandTrades';
	public $belongsTo = array('Land');
	public $actsAs = array('Containable');
}
?>