<!-- File: /app/View/Lists/add.ctp -->

<h1>Add List</h1>
<?php
echo $this->Form->create('Elist');
echo $this->Form->input('list_name');
echo $this->Form->input('list_type_id', array('type' => 'select', 'options'=>$listtypes, 'label' => 'Type'));
echo $this->Form->input('description', array('rows' => '3'));
echo $this->Form->input('collapse_name');
echo $this->Form->input('collapse_order');
echo $this->Form->end('Save List');
?>