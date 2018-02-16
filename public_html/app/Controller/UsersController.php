<?php
App::uses('CakeEmail', 'Network/Email');

class UsersController extends AppController {

	var $uses = array('User','Character','Player');

	public function beforeFilter() {
        parent::beforeFilter();

		$this->Auth->allow('login','logout','confirm','reset','testemail');
    }

    public function isAuthorized($user) {
    	if(!$this->params['pass'][0]) {
			$id = AuthComponent::user('id');
		} else {
			$id = $this->params['pass'][0];
		}

	    // A user can edit and view their own user info
	    if (in_array($this->action, array('index','view','edit'))) {
	        if ($this->User->isOwnedBySelf($id)) {
	            return true;
	        }
	    }

		if(AuthComponent::user('role_cards')) {
            return true;
	    }

	    if(AuthComponent::user('role_newplayermarshal')) {
            return true;
	    }


	    if(AuthComponent::user('role_logistics')) {
            return true;
	    }

	    return parent::isAuthorized($user);
	}

	public function login($token = null) {
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				$player_id = AuthComponent::user('player_id');
				$user_id = AuthComponent::user('id');

				$user = $this->User->findById($user_id);

				if (strlen($user['User']['generated_token']) > 0) {
					$this->redirect('/users/edit/'.$user_id);
				} else {
					$this->redirect($this->Auth->redirect());

					// if($this->Auth->redirect() === '/pages/home') {
					// 	$this->redirect('/players/view');
					// }
				}
			} else {
				$this->Session->setFlash(__('Invalid username or password, try again'));
			}
		}

		if($token) {
			$user = $this->User->find('first',array('conditions' => array('User.generated_token' => $token)));

			if ($user) {
				$this->set('user', $user);
			}
		}
	}

	public function logout() {
		$this->redirect($this->Auth->logout());
	}

    public function index() {
    	$this->redirect(array('controller' => 'users', 'action' => 'view', AuthComponent::user('id')));
    }

    public function view($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        $this->set('user', $this->User->read(null, $id));
    }

    public function add($player_id = null) {
    	if (!$player_id) {
			throw new NotFoundException(__('Invalid player'));
		}

		$player = $this->Player->findById($player_id);

		if (!$player) {
			throw new NotFoundException(__('Invalid player'));
		}

		$this->set('player', $player);

		$user = $this->User->find('first',array('conditions' => array('User.player_id' => $player_id)));

		if ($user) {
			$this->set('user', $user);
		}

        if ($this->request->is('post')) {

        	$token = md5("tempo"+time()+"rary");

        	$password = substr($token,0,8);
        	$subtoken = substr($token,8);

            $this->User->create();

			$this->User->set('role','user');
			$this->User->set('username',$this->request->data['User']['username']);
            $this->User->set('password',$password);
            $this->User->set('player_id',$player_id);
            $this->User->set('generated_token',$subtoken);


            if ($this->User->save()) {

				$emailconfig = Configure::read('UsersController.add');

            	$email = new CakeEmail();
				$email->config($emailconfig['config']);
				$email->to($this->request->data['User']['username']);
				$email->from($emailconfig['from']);
				$email->subject($emailconfig['subject']);
				$email->send(sprintf($emailconfig['body'],$subtoken,$password));

                $this->Session->setFlash(__('The user has been saved'));
                $this->redirect(array('controller' => 'players', 'action' => 'view', $player_id));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }

        }
    }

    public function reset() {
        if ($this->request->is('post')) {
        	$email = $this->request->data['User']['username'];
			if($email) {
				$user = $this->User->find('first', array('conditions' => array('User.username' => $email)));

				if($user) {
					$this->User->id = $user['User']['id'];

					$token = md5("reset"+time()+"pwd");

					if ($this->User->saveField('generated_token',$token)) {

						$emailconfig = Configure::read('UsersController.reset');

						$email = new CakeEmail();
						$email->config($emailconfig['config']);
						$email->to($user['User']['username']);
						$email->from($emailconfig['from']);
						$email->subject($emailconfig['subject']);
						$email->send(sprintf($emailconfig['body'],$token));

						$this->Session->setFlash(__('Your reset email has been sent'));
						$this->redirect(array('controller' => 'users', 'action' => 'login'));
					} else {
						$this->Session->setFlash(__('Your password could not be reset. Please, try again.'));
		         	}
				} else {
					$this->Session->setFlash(__('Email address is not registered. Please, try again.'));
				}
			} else {
				$this->Session->setFlash(__('Not a valid email address. Please, try again.'));
			}
		}
    }

    public function changeemail() {
        if ($this->request->is('post')) {
        	$email = $this->request->data['User']['username'];
        	$newemail = $this->request->data['User']['newusername'];

        	if($newemail) {
				if($email) {
					$user = $this->User->find('first', array('conditions' => array('User.username' => $email)));

					if($user) {
						$this->User->id = $user['User']['id'];

			        	$token = md5("tempo"+time()+"rary");
			        	$password = substr($token,0,8);
			        	$subtoken = substr($token,8);

						if ($this->User->saveField('username',$newemail) ) {
							$this->User->saveField('password',$password);
							$this->User->saveField('generated_token',$subtoken);

							$emailconfig = Configure::read('UsersController.add');

							$email = new CakeEmail();
							$email->config($emailconfig['config']);
							$email->to($newemail);
							$email->from($emailconfig['from']);
							$email->subject($emailconfig['subject']);
							$email->send(sprintf($emailconfig['body'],$subtoken,$password));

							$this->Session->setFlash(__('The users new email has been saved, password reset and email sent'));
							$this->redirect(array('controller' => 'players', 'view' => 'view', $this->User->id));
						} else {
							$this->Session->setFlash(__('Your password could not be reset. Please, try again.'));
						}
					} else {
						$this->Session->setFlash(__('Email address is not registered. Please, try again.'));
					}
				} else {
					$this->Session->setFlash(__('Not a valid email address. Please, try again.'));
				}
			} else {
				$this->Session->setFlash(__('Not a valid email address. Please, try again.'));
			}
		}
    }

    public function confirm($token = null) {
		$user = $this->User->find('first', array('conditions' => array('User.generated_token' => $token)));

		if($user) {
			$token = md5("tempo"+time()+"rary");

			$password = substr($token,0,8);
			$subtoken = substr($token,8);

			$this->User->id = $user['User']['id'];
			$this->User->set('password',$password);
			$this->User->set('generated_token',$subtoken);

			if ($this->User->save()) {

				$emailconfig = Configure::read('UsersController.confirm');

				$email = new CakeEmail();
				$email->config($emailconfig['config']);
				$email->to($user['User']['username']);
				$email->from($emailconfig['from']);
				$email->subject($emailconfig['subject']);
				$email->send(sprintf($emailconfig['body'],$subtoken,$password));

				$this->Session->setFlash(__('Your new temporary password has been sent to your email address'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
		}
		$this->redirect(array('controller' => 'users', 'action' => 'login'));
    }

    public function edit($id = null) {
		if ($this->request->is('post')) {
			$this->User->read(null, $id);
			$player_id = $this->User->data['User']['player_id'];

			if ($this->request->data('password1') == $this->request->data('password2')) {
				$this->User->data['User']['password'] = $this->request->data('password1');
				$this->User->data['User']['generated_token'] = '';

				if($this->User->save()) {
					$this->Session->setFlash(__('Password updated'));
	                $this->redirect('/players/view/'.$player_id);
				} else {
			        $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
				}
			} else {
	        	$this->Session->setFlash(__('The passwords do not match.'));
	        }
		}

		if(!$id) {
			$id = AuthComponent::user('id');
		}

		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
        $this->set('user', $this->User->read(null, $id));
    }

    public function delete($id = null) {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->delete()) {
            $this->Session->setFlash(__('User deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('User was not deleted'));
        $this->redirect(array('action' => 'index'));
    }

    public function testemail() {
		$emailconfig = Configure::read('UsersController.test');

		$email = new CakeEmail();
		$email->config($emailconfig['config']);
		$email->to($emailconfig['to']);
		$email->from($emailconfig['from']);
		$email->subject($emailconfig['subject']);
		$email->send($emailconfig['body']);

    }
}
?>