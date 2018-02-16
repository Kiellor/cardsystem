<?php
class Settlement extends AppModel {
	public $name = 'Settlement';
	public $hasMany = array('SettlementImprovement','SettlementValues','SettlementBuilding');
	public $belongsTo = array('Land');
	public $recursive = 2;
	public $actsAs = array('Containable');
}
?>