<?php
class User extends AppModel {
	public $belongsTo = 'Player';

    public $validate = array(
        'username' => array(
            'required' => array(
                'rule' => array('email',true),
                'message' => 'Your username must be a valid email address'
            )
        ),
        'password' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'A password is required'
            )
        )
    );

	public function isOwnedBySelf($userid) {
		if(AuthComponent::user('id') === $userid) {
			return true;
		}

		return false;
	}

    public function beforeSave($options = array()) {
	    if (isset($this->data['User']['password'])) {
	        $this->data['User']['password'] = AuthComponent::password($this->data['User']['password']);
	    }
	    return true;
	}
}
?>