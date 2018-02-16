<!-- app/View/Users/add.ctp -->
<div class="users form">

<div>Adding user account for player: <?php 
	$name = $player['Player']['name']; 
	echo $this->Html->link($name,array('controller' => 'players', 'action' => 'view', $player['Player']['id']));
?></div>
	
<?php echo $this->Form->create('User'); ?>
<?php echo $this->Form->input('username'); ?> (Enter their email address)
<?php echo $this->Form->end(__('Submit')); ?>
</div>