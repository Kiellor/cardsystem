<?php
class ListAbility extends AppModel {
	public $name = 'ListAbility';
    public $belongsTo = array (
    	'Elist', 'Ability'
    );
}
?>