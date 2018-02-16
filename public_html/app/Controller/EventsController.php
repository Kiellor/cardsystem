<?php
class EventsController extends AppController {
    public $scaffold;

    public function isAuthorized($user) {
		if ($user['role_listwrangler']) {
			return true;
		}

		return parent::isAuthorized($user);
	}
}
?>