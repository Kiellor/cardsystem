<?php
class ListAbilityController extends AppController {

    public function isAuthorized($user) {
        if(AuthComponent::user('role_cards')) {
            return true;
        }

        if(AuthComponent::user('role_listwrangler')) {
            return true;
        }

        return parent::isAuthorized($user);
    }

    public function add($listid, $ability, $cost, $prereq, $abilityoption) {

		$db = $this->ListAbility->getDataSource();

		$results = $db->fetchAll(
			'SELECT MAX(sort_order) as pos from list_abilities where elist_id = ?',array($listid)
		);

		$newpos = $results[0][0]['pos'] + 1;

		if($newpos < 0) {
			$newpos = 0;
		}

    	$this->ListAbility->create();
    	$this->ListAbility->set(array(
    		'elist_id' => $listid,
    		'ability_id' => $ability,
    		'build_cost' => $cost,
    		'prerequisites' => $prereq,
    		'sort_order' => $newpos,
    		'abilityoption_id' => $abilityoption
    	));
    	$this->ListAbility->save();

    	$this->layout = 'ajax';
		$this->set('list_ability', $this->ListAbility->id);
    }

    public function delete($id) {
    	$this->ListAbility->delete($id,false);

    	$this->layout = 'ajax';
    	$this->set('list_ability','done');
    }

    public function reorder($id, $delta) {
    	if($delta == -1 || $delta == 1) {
    		$row = $this->ListAbility->findById($id);
    		$newpos = $row['ListAbility']['sort_order'] + $delta;

			$this->ListAbility->id = $id;
			$this->ListAbility->saveField('sort_order',$newpos);
		}
    	$this->layout = 'ajax';
		$this->set('list_ability', $newpos);
    }

}
?>