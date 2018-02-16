<?php
class BusinessDeposit extends AppModel {
	public $name = 'BusinessDeposit';

	public $belongsTo = array(
		'Business',
		'Character',
		'Event'
	);

	public $virtualFields = array(
		'Gold_total' => "",
		'Luxury_total' => "",
		'Durable_total' => "",
		'Wearable_total' => "",
		'Consumable_total' => ""
	);
}
?>