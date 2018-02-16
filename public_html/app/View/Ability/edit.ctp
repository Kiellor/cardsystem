<!-- File: /app/View/Ability/edit.ctp -->


<?php
	echo $this->Form->create('Ability', array('action' => 'edit'));
	echo $this->Form->input( 'id', array('type' => 'hidden') );
	echo $this->Form->input('ability_name');
	echo $this->Form->input('display_name');
	echo $this->Form->input('description');
	echo $this->Form->input('special_rules');

	echo $this->Form->input('abilitytype_id', array('type' => 'select', 'options'=>$ability_types, 'label' => 'Type'));
	echo $this->Form->input('abilitygroup_id', array('type' => 'select', 'options'=>$ability_groups, 'label' => 'Group'));
	echo $this->Form->input('opens_list_id', array('type' => 'select', 'options'=>$all_lists, 'label' => 'Opens List'));
	echo $this->Form->input('uses_option_list', array('type' => 'select', 'options'=>$option_lists, 'label' => 'Options List'));

	echo $this->Form->input('BP', array('label' => 'BP', 'default' => 0));
	echo $this->Form->input('MP', array('label' => 'MP', 'default' => 0));
	echo $this->Form->input('FP', array('label' => 'FP', 'default' => 0));
	echo $this->Form->input('CP', array('label' => 'CP', 'default' => 0));
	echo $this->Form->input('PP', array('label' => 'PP', 'default' => 0));
	echo $this->Form->input('HP', array('label' => 'HP', 'default' => 0));
	echo $this->Form->input('SP', array('label' => 'SP', 'default' => 0));
	echo $this->Form->input('DP', array('label' => 'DP', 'default' => 0));
	echo $this->Form->input('PsiP', array('label' => 'PsiP', 'default' => 0));

	echo $this->Form->end('Save Ability');
?>
