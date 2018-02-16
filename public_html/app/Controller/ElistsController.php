<?php
class ElistsController extends AppController {

	var $uses = array('Elist','ListAbility');

	public $helpers = array('Html', 'Form', 'Session');
    public $components = array('Session');

	public function beforeFilter() {
        parent::beforeFilter();

		$this->Auth->allow('compare','compare_json','getlistabilities','export_json');
    }

    public function isAuthorized($user) {
		if(AuthComponent::user('role_cards')) {
            return true;
	    }

	    if(AuthComponent::user('role_listwrangler')) {
	    	return true;
	    }

	    return parent::isAuthorized($user);
	}

    public function index($typeid = null) {
    	$this->set('listtypes', $this->Elist->ListType->find('all',array('order' => 'ListType.id')));

    	if($typeid !== null) {
			$db = $this->Elist->getDataSource();

			$results = $db->fetchAll(
				'SELECT l.id, lt.name, l.list_name, count(la.id) as skills from elists l LEFT JOIN list_types lt on l.list_type_id = lt.id LEFT JOIN list_abilities la on l.id = la.elist_id where lt.id = ? group by l.id',
				array($typeid)
			);

    		$this->set('elists', $results);
	    }
    }

    public function view($id = null) {
	        if (!$id) {
	            throw new NotFoundException(__('Invalid elist'));
	        }

	        $elist = $this->Elist->findById($id);
	        if (!$elist) {
	            throw new NotFoundException(__('Invalid elist'));
	        }
	        $this->set('elist', $elist);

	        $this->set('abilities', $this->Elist->ListAbility->Ability->find('list',array('order' => 'sort_order')));
    }

    public function newedit($id) {
    	$this->set('list_id', $id);
    }

    public function getlist($list) {
    	$elist = $this->Elist->findById($list);

    	$db = $this->Elist->getDataSource();

		$results = $db->fetchAll(
			'SELECT distinct a.id, a.ability_name, a.display_name, a.cost_increase_interval, a.opens_list_id, a.uses_option_list, a.Ratio, a.abilitytype_id, a.BP, a.MP, a.FP, a.CP, a.PP, a.HP, a.SP, a.DP, a.PsiP, la.id, la.build_cost, la.prerequisites, la.sort_order, la.footnote, la.is_footnote, la.free_set, la.free_set_limit, alo.ability_name, ag.name, ag.sorting_name from abilities a LEFT OUTER JOIN list_abilities la on la.ability_id = a.id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id LEFT OUTER JOIN abilities alo on la.abilityoption_id = alo.id where la.build_cost > -1 and la.elist_id = ? order by la.sort_order', array($list)
		);

		$retval = [];
		$retval['list'] = $elist;
		$retval['skills'] = $results;

		$this->set('ajax', json_encode($retval));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function saveskill() {
    	$data = $this->request->input('json_decode',true);

    	$this->ListAbility->id = $data['la']['id'];
    	$this->ListAbility->set(array(
    		'prerequisites' => $data['la']['prerequisites'],
    		'sort_order' => $data['la']['sort_order'],
    		'footnote' => $data['la']['footnote'],
    		'is_footnote' => $data['la']['is_footnote'],
    		'free_set' => $data['la']['free_set'],
    		'free_set_limit' => $data['la']['free_set_limit']
   		));
   		$this->ListAbility->save();

    	$this->set('ajax', json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function saveallskills() {
    	$data = $this->request->input('json_decode',true);

    	foreach($data as $row) {
	    	$this->ListAbility->id = $row['la']['id'];
	    	$this->ListAbility->set(array(
	    		'prerequisites' => $row['la']['prerequisites'],
	    		'sort_order' => $row['la']['sort_order'],
	    		'footnote' => $row['la']['footnote'],
	    		'is_footnote' => $row['la']['is_footnote'],
	    		'free_set' => $row['la']['free_set'],
	    		'free_set_limit' => $row['la']['free_set_limit']
	   		));
	   		$this->ListAbility->save();
	   	}
	   	
    	$this->set('ajax', json_encode($data));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    public function edit($id = null) {
	        if (!$id) {
	            throw new NotFoundException(__('Invalid elist'));
	        }

	        $this->set('elistid',$id);

	        $elist = $this->Elist->findById($id);
	        if (!$elist) {
	            throw new NotFoundException(__('Invalid elist'));
	        }
			$db = $this->Elist->getDataSource();

			$results = $db->fetchAll(
				'SELECT la.id, a.id, a.display_name, a.ability_name, la.build_cost, at.name as type, ag.name as grp, ao.ability_name, ao.display_name from abilities a LEFT JOIN list_abilities la on a.id = la.ability_id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id LEFT OUTER JOIN abilities ao on la.abilityoption_id = ao.id where la.elist_id = ? order by ag.sorting_name asc, at.sorting_name, concat(a.sort_after_name,a.ability_name)',
				array($id)
			);

	        $this->set('elist', $results);

			if($elist['Elist']['list_type_id'] == 4) {
	        	// Ability Options
		        $this->set('abilities', $this->Elist->ListAbility->Ability->find('list',array(
		        	'fields'=>array('id','ability_name'),
		        	'conditions'=>array('Ability.abilitytype_id' => '25')
		        )));
			} else {
				// Normal Abilities
		        $this->set('abilities', $this->Elist->ListAbility->Ability->find('list',array(
		        	'fields'=>array('id','ability_name'),
		        	'conditions'=>array('Ability.abilitytype_id !=' => '25')
		        )));
		    }

		    // Get list of abilities that have options
		    $db = $this->Elist->getDataSource();
			$results = $db->fetchAll(
				'SELECT  id, uses_option_list from abilities where uses_option_list > 0',
				array()
			);
			$options = array();
			foreach ($results as $row) {
				$options[$row['abilities']['id']] = $row['abilities']['uses_option_list'];
			}

			$this->set('abilities_with_options',$options);
    }

    public function add() {
	        if ($this->request->is('post')) {
	            $this->Elist->create();
	            if ($this->Elist->save($this->request->data)) {
	                $this->Session->setFlash('Your elist has been added.');
	                $this->redirect(array('action' => 'index'));
	            } else {
	                $this->Session->setFlash('Unable to add your elist.');
	            }
	        }

	        $this->set('listtypes', $this->Elist->ListType->find('list',array('fields'=>array('id','name'))));

    }

    public function compare() {
    	$db = $this->Elist->getDataSource();

		$results = $db->fetchAll(
			'SELECT l.id, l.list_name, lt.name from elists l LEFT JOIN list_types lt on lt.id = l.list_type_id where lt.name like "%Profession" order by l.list_name'
		);

		$this->set('elists', $results);

		// Get list of all abilities
		$abilities = $db->fetchAll(
			'SELECT a.id, a.display_name as display, a.ability_name as ability, at.name as type, ag.name as grp from abilities a LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id order by ag.sorting_name, at.sorting_name, concat(a.sort_after_name,a.ability_name)'
		);

   		$this->set('abilities',$abilities);
    }

    public function export_json() {
    	$db = $this->Elist->getDataSource();

		$classes = $db->fetchAll(
			'SELECT l.id, l.list_name, lt.name from elists l LEFT JOIN list_types lt on lt.id = l.list_type_id where lt.name like "%Profession" order by l.list_name'
		);

		foreach($classes as &$class) {
			$class['abilities'] = $this->getlistabilities_impl($class['l']['id']);
		}

		$this->set('ajax', json_encode($classes));
		$this->layout = 'ajax';
		$this->render('ajax');
    }

    private function getlistabilities_impl($id = null) {
    	$db = $this->Elist->getDataSource();

		$results = $db->fetchAll(
			'SELECT a.id, a.display_name, a.ability_name, la.build_cost, at.name as type, ag.name as grp, ao.ability_name as opt, ao.display_name as opt_disp from abilities a LEFT JOIN list_abilities la on a.id = la.ability_id LEFT OUTER JOIN ability_types at on a.abilitytype_id = at.id LEFT OUTER JOIN ability_groups ag on a.abilitygroup_id = ag.id LEFT OUTER JOIN abilities ao on la.abilityoption_id = ao.id where la.elist_id = ? order by ag.sorting_name asc, at.sorting_name, concat(a.sort_after_name,a.ability_name)',
			array($id)
		);

		return $results;
    }

    public function getlistabilities($id = null) {
		if (!$id) {
			throw new NotFoundException(__('Invalid elist'));
		}

		$results = $this->getlistabilities_impl($id);

		$this->set('abilities', json_encode($results));
		$this->layout = 'ajax';
    }
}
?>