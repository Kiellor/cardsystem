<?php
class SettlementImprovement extends AppModel {
	public $name = 'SettlementImprovement';
	public $belongsTo = array('Improvement','Business','Settlement','Land');
}
?>