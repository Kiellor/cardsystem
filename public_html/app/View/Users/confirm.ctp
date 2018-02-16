<!-- app/View/Users/add.ctp -->
<div class="users form">
<?php echo $this->Form->create('User'); ?>
	<?php 
		echo $this->Form->input('username',array('default'=>$user['User']['username']));
		echo $this->Form->input('password');
		echo $this->Form->input('generated_token',array('default'=>$token));
	?>
<?php echo $this->Form->end(__('Submit')); ?>
</div>