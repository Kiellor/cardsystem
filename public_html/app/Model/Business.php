<?php
class Business extends AppModel {
	public $useTable = 'businesses';
	public $name = 'Business';
	public $recursive = 2;

	public $hasMany = array(
		'BusinessPartner' => array(
			'className' => 'BusinessPartner',
			'foreignKey' => 'business_id'),
		'SettlementImprovement' => array(
			'className' => 'SettlementImprovement',
			'foreignKey' => 'business_id')
	);
}
?>