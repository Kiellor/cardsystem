<!-- File: /app/View/Users/edit.ctp -->

<div id="pageheading">
<h1>Change Your Password</h1>
</div>

<div id="pagecontent">

	<div>Player Name: <?php 
		$name = $user['Player']['name']; 
		echo $this->Html->link($name,array('controller' => 'players', 'action' => 'view', $user['Player']['id']));
	?></div>

	<div>Login Name / Email: <?php echo $user['User']['username']; ?></div>

	
	<?php echo $this->Form->create('User'); ?>

	<table>
		<tr><td>New Password:</td><td><input type="password" name="password1"/></td></tr>
		<tr><td>Confirm Password:</td><td><input type="password" name="password2"/></td></tr>
	</table>

	<?php echo $this->Form->end(__('Submit')); ?>
	
	
	
</div>
