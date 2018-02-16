<?php
class Elist extends AppModel {
	public $name = 'Elist';
	public $displayField = 'list_name';
	public $actsAs = array('Containable');

	public $hasMany = array(
		'ListAbility' => array(
			'className' => 'ListAbility',
			'order'		=> 'ListAbility.sort_order ASC'
		)
	);

    public $belongsTo = 'ListType';

    public $validate = array(
        'list_name' => array(
            'rule' => 'notEmpty'
        ),
        'list_type' => array(
			'rule' => 'notEmpty'
        )
    );
}
?>