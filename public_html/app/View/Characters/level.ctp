<!-- File: /app/View/Characters/level.ctp -->

<h1><?php 
	echo $character['Character']['name']; 
	echo ' (';
	echo $character['Character']['cardnumber']; 
	echo ') ';
?></h1>

<div id="CardOptions">
<ul>
	<?php
		echo '<li>';
		echo $this->Html->link('View Character',array('controller' => 'characters', 'action' => 'view', $character['Character']['cardnumber']));
		echo '</li>';	
	?>
</ul>
</div>

<table><tr><th>Profession</th><th>Level</th><th>Build</th><th>Ratio</th></tr>

<?php
	echo '<tr><td>Current Level</td><td style="text-align:right">' . $charlevel . '</td><td style="text-align:right">' . $build_earned . '</td></tr>';

	foreach($levels as $level) {
		if($level['id'] == $level['parent']) {
			
			echo '<tr><td>';
			echo $level['list'];
			echo '</td><td style="text-align:right">';
			echo $level['level']; 
			echo '</td><td style="text-align:right">';
			echo $level['build'];
			echo '</td><td style="text-align:right">';
			echo $level['ratio'];
			
			if($details == 'true') {
				echo '</td><td>';
				echo '<ul>';
				foreach($level['skills'] as $skill) {
					echo '<li>' . $skill . '</li>';
				}
				echo '</ul>';
			}				
			echo '</td></tr>';
		}		
	}

?>
</table>
