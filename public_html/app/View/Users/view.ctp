<!-- File: /app/View/Users/view.ctp -->

<div id="pageheading">
<h1>User Account Information</h1>
</div>

<div id="pagecontent">

	<div>Player Name: <?php 
		$name = h($user['Player']['name']); 
		echo $this->Html->link($name,array('controller' => 'players', 'action' => 'view', $user['Player']['id']));
	?></div>

	<div>Login Name / Email: <?php echo h($user['User']['username']); ?></div>

	<div id="useractions">
	
	
	
</div>
