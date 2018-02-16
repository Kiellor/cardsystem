<!-- File: /app/View/Players/add.ctp -->

<h1>Add Player</h1>
<?php
echo $this->Form->create('Player');
echo $this->Form->input('name');
echo $this->Form->end('Save Player');
?>