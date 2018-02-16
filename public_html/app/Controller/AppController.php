<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

	public $helpers = array('Js' => array('Jquery'));

	public $components = array(
        'Session',
        'Auth' => array(
            'loginRedirect' => array('controller' => 'pages', 'action' => 'home'),
            'logoutRedirect' => array('controller' => 'pages', 'action' => 'home'),
            'authorize' => array('Controller')
        )
    );

    public function beforeFilter() {
		$this->set('menu',$this->generateMenu());

		if(AuthComponent::user('id')) {
			$this->set('loggedIn','true');
		}
    }

    public function isAuthorized($user) {


	    // Admin can access every action
	    if ($user['role_admin']) {
	        return true;
	    }

	    // Default deny
	    return false;
	}

	public function generateMenu() {
		$user_id = AuthComponent::user('id');
		$player_id = AuthComponent::user('player_id');

		$menu = array();

		$role_admin 				= AuthComponent::user('role_admin') == true;
		$role_cards 				= AuthComponent::user('role_cards') || $role_admin;
		$role_staff		 			= AuthComponent::user('role_staff') || $role_admin;
		$role_marketing 			= AuthComponent::user('role_marketing') || $role_admin;
		$role_storyteller 			= AuthComponent::user('role_storyteller') || $role_admin;
		$role_newplayermarshal 		= AuthComponent::user('role_newplayermarshal') || $role_admin;
		$role_listwrangler			= AuthComponent::user('role_listwrangler') || $role_admin;
		$role_atmosphere			= AuthComponent::user('role_atmosphere') || $role_admin;
		$role_banking				= AuthComponent::user('role_banking') || $role_admin;
		$role_landadmin				= AuthComponent::user('role_landadmin') || $role_admin;
		$role_logistics				= AuthComponent::user('role_logistics') || $role_admin;


		if($role_staff || $role_cards || $role_newplayermarshal || $role_logistics) {
			$menu['attend']['attend'][] = array('display' => 'Attendance', 'link' => '/attendance');
		}

		$menu['cbuild']['character_builder'][] = array('display' => 'Character Builder', 'link' => '/character_builder');
		if($role_cards || $role_newplayermarshal) {
			$menu['cbuild']['character_builder'][] = array('display' => '--Waiting Room', 'link' => '/character_builder/waiting');
			$menu['cbuild']['character_builder'][] = array('display' => '--Tracking', 'link' => '/character_builder/tracking');
		}

		if($role_staff || $role_cards) {
			$menu['attend']['attend'][] = array('display' => '--pending update', 'link' => '/attendance/outstanding');
		}
		
		if($role_cards || $role_newplayermarshal) {
			$menu['cards']['characters'][] = array('display' => 'Players and Characters', 'link' => '/players/');
			$menu['cards']['characters'][] = array('display' => '--Add Character', 'link' => '/characters/add');			
		}

		if($role_cards) {
			$menu['cards']['characters'][] = array('display' => '--Card Problems', 'link' => '/cards/withproblems');
			$menu['cards']['characters'][] = array('display' => '--with List or Skill', 'link' => '/ability/abilityfinder');
			//$menu['cards']['characters'][] = array('display' => '--Race Rewrites', 'link' => '/rewrite/listProposals');
			//$menu['cards']['characters'][] = array('display' => '--with Old Style Deaths', 'link' => '/death/hasOldDeaths');
			$menu['cards']['characters'][] = array('display' => 'Generate PDFs', 'link' => '/cards/printcards');
		}	

		if($role_banking) {
			$menu['bank']['characters'][] = array('display' => 'Bank Deposits', 'link' => '/bank');
		}

		if($role_cards || $role_listwrangler) {
			$menu['cards']['lists'][] = array('display' => 'Manage Lists', 'link' => '/elists/');
			$menu['cards']['lists'][] = array('display' => '--Add List', 'link' => '/elists/add');

			$menu['cards']['ability'][] = array('display' => 'Manage Abilities', 'link' => '/ability/');
			$menu['cards']['ability'][] = array('display' => '--View All', 'link' => '/ability/viewall');

			$menu['cards']['events'][] = array('display' => 'Manage Events', 'link' => '/events/');
		}

		if($role_atmosphere) {
			$menu['chronicle']['needapproval'][] = array('display' => 'Pending Chronicles', 'link' => '/chronicle/needapproval');
			$menu['chronicle']['needapproval'][] = array('display' => 'Approved Chronicles', 'link' => '/chronicle/approved');
		}

		// if($role_newplayermarshal) {
		// 	$menu['newplayer']['newplayer'][] = array('display' => 'Character Builder', 'link' => '/new_player_marshal/');
		// }

		if($role_staff || $role_marketing) {
			$menu['marketing']['demographics'][] = array('display' => 'Demographics', 'link' => '/demographics/');
		}

		if($user_id) {
			$menu['user']['players'][] = array('display' => 'My Characters', 'link' => '/players/view/');
			$menu['user']['players'][] = array('display' => 'My Businesses', 'link' => '/bank/myBusinesses');
			$menu['user']['players'][] = array('display' => 'Between Game Actions','link' => '/personal_action');
			if($role_landadmin) {
				$menu['user']['players'][] = array('display' => '--Between Game Actions Admin','link' => '/personal_action/manage');
			}
			if($role_admin) {
				$menu['user']['players'][] = array('display' => '--Land System Admin','link' => '/land_system');
				$menu['user']['players'][] = array('display' => '--Turn the Crank','link' => '/land_system/trades');
			}
			$menu['user']['players'][] = array('display' => 'Change Password', 'link' => '/users/edit/');
		}


		// Open to all
		$menu['open']['lists'][] = array('display' => 'Compare Lists', 'link' => '/elists/compare');
		$menu['open']['ability'][] = array('display' => 'Abilities and Ratios', 'link' => '/ability/viewratios');


		return $menu;
	}
}
