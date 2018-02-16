<?php
class Item extends AppModel {
	public $name = 'Item';

	public $belongsTo = array(
		'Character'
	);
}
?>