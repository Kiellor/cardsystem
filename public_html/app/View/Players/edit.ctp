<!-- File: /app/View/Players/view.ctp -->

<div id="pageheading">
<h1>Account Information</h1>
</div>

<div id="pagecontent">

	<div>Player Name: <?php 
		$name = h($player['Player']['name']); 
		echo $this->Html->link($name,array('controller' => 'players', 'action' => 'view', $player['Player']['id']));
	?></div>

	<div>Login Name / Email: <?php if(isset($user)) { echo h($user['User']['username']); } else { echo 'Not Registered'; } ?></div>

	<div>Medical Notes:<?php echo h($player['Player']['medical_notes']); ?></div>
	
	<div>Other Notes:<?php echo h($player['Player']['other_notes']); ?></div>
	
	<div id="characterlist">
		<h2>Characters</h2>
		<ul>
		<?php foreach ($player['Character'] as $character): ?>
			<li> <?php echo $this->Html->link($character['name'].' ('.$character['cardnumber'].')',array('controller' => 'characters', 'action' => 'view', $character['cardnumber'])); ?></li>
		<?php endforeach; ?>
		<?php unset($character); ?>
		</ul>
	</div>
	
</div>
	

