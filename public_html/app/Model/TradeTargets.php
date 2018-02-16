<?php
class TradeTargets extends AppModel {
	public $name = 'TradeTargets';
	public $belongsTo = array('Settlement','Land');
}
?>