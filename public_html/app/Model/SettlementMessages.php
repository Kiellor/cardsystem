<?php
class SettlementMessages extends AppModel {
	public $name = 'SettlementMessages';
	public $belongsTo = array('Land','Settlement');
	public $actsAs = array('Containable');
}
?>