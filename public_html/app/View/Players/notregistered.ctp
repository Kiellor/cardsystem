<!-- File: /app/View/Players/index.ctp -->

<h1>Players without Emails</h1>

<div id="listing">
	<?php 
		foreach ($players as $player) {
			echo '<a href="http://cards.knightrealms.com/users/add/'.$player['p']['id'].'">'.$player['p']['name'].'</a><br/>';
		}
	?>
</div>
