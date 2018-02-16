<?php
class ListType extends AppModel {
	public $name = 'ListType';
	public $hasMany = array(
        'Elist'
    );

    public $displayField = 'name';
}
?>