<?php
class SettlementValues extends AppModel {
	public $name = 'SettlementValues';
	public $belongsTo = array('Settlement','Land');
	public $actsAs = array('Containable');
}
?>