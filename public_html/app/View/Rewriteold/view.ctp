<!-- File: /app/View/Characters/view.ctp -->

<h1><?php 
	echo $character['Character']['name']; 
	echo ' (';
	echo $character['Character']['cardnumber']; 
	echo ') ';
?></h1>

<div id="CardOptions">
<ul>
	<?php
		if(AuthComponent::user('role_admin')) {
		echo '<li>';
		echo $this->Html->link('Enter Card Data',array('controller' => 'cards', 'action' => 'page1', $character['Character']['cardnumber']));
		echo '</li><li>';
		echo $this->Html->link('Manage Bonded Items',array('controller' => 'cards', 'action' => 'page2', $character['Character']['cardnumber']));
		echo '</li><li>';
		echo $this->Html->link('Download PDF',array('controller' => 'characters', 'action' => 'downloadpdf', $character['Character']['cardnumber']));
		echo ' (saves PDF on server)</li><li>';
		echo $this->Html->link('View PDF',array('controller' => 'characters', 'action' => 'viewpdf', $character['Character']['cardnumber']));
		echo ' (views saved PDF)</li><li>';
		echo $this->Html->link('View Character Levels',array('controller' => 'characters', 'action' => 'level', $character['Character']['cardnumber']));
		echo '</li>';
	} 
		
	if(AuthComponent::user('role_admin') && $allowrewrites == 'true') {
		echo '<li>';
		echo $this->Html->link('Rewrite Process',array('controller' => 'rewrite', $character['Character']['cardnumber']));
		echo '</li>';
	}
	
	echo '<li>';
	echo $this->Html->link('Report a Problem',array('controller' => 'feedback', 'action' => 'cardproblem', $character['Character']['cardnumber']));
	echo '</li>';
	
	?>
</ul>
</div>

<div>
<p>Played by:<?php  
	echo $this->Html->link($character['Player']['name'],array('controller' => 'players', 'action' => 'view', $character['Player']['id']));
?></p>

<table><tr><td width="25%" style="vertical-align:top">

	<?php
		echo 'Race ('.$race.')</br>';
		echo 'Religion ('.$religion.')</td></tr><tr>';
		echo '<td colspan="4">';
		echo 'Lists: '.$lists;
		if($lists_build > 0) {
			echo ' = ('.$lists_build.')';
		}
		else '</td></tr>';

		echo '<tr><td width="25%" style="vertical-align:top">';
		echo '<table style="border:solid 1px black;">';
		echo '<tr><td>Level</td><td style="text-align:right;">'.$level.'</td></tr>';
		echo '<tr><td>Build Spent</td><td style="text-align:right;">'.$build_spent.'</td></tr>';
		echo '<tr><td>Build Unspent</td><td style="text-align:right;">'.$build_unspent.'</td></tr>';
		echo '<tr><td>Build Earned</td><td style="text-align:right;">'.$build_earned.'</td></tr>';
		echo '</table>';
	?>

<?php 
	$totalrows = $ability_group_rows['Total'];
	$rowsinacolumn = $totalrows / 4;
	$rowsvisited = 0;
	$currentcol = 0;

	
	foreach (array_keys($character_abilities) as $ability_set_name) {
		if($rowsvisited >= $rowsinacolumn) {
			echo '</td><td width="25%" style="vertical-align:top">';
			$rowsvisited = 0;
			$currentcol++;
		}
		
		if(count($character_abilities[$ability_set_name]) > 0) {
			if($ability_set_name == 'Lore' || $ability_set_name == 'Literacy' || $ability_set_name == 'Lists') {

				$abilities = $character_abilities[$ability_set_name][0];
				if(array_key_exists('build_spent',$abilities)) {
					echo '<h3>'.$ability_set_name.' ('.$abilities['build_spent'].')</h3>';
				} else {
					echo '<h3>'.$ability_set_name.' (0)</h3>';
				}
				echo $abilities['ability'];
				$rowsvisited+=2;

			} else if($ability_set_name == 'Career Points') {
				echo '<table>';
				foreach ($character_abilities[$ability_set_name] as $abilities) {
					echo '<tr><td style="text-align:left;">'.$abilities['ability'].':</td>';
					echo '<td style="text-align:right;">'.$abilities['value'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
					echo '<td style="text-align:right;">('.$abilities['total'].')</td></tr>';
				}
				echo '</table>';
			} else if($ability_set_name == 'Race' || $ability_set_name == 'Religion') {

				if(array_key_exists(0,$character_abilities[$ability_set_name]) ) {
					$abilities = $character_abilities[$ability_set_name][0];
					echo '<h3>'.$ability_set_name.'</h3>'.$abilities['ability'].'<br/>';
					$rowsvisited++;
				}		
			} else {

				echo '<h3>'.$ability_set_name.'</h3>';
				$rowsvisited++;
				
				foreach ($character_abilities[$ability_set_name] as $abilities) {
					if($abilities['bold'] == 1) {
						if($rowsvisited >= $rowsinacolumn) {
							echo '</td><td width="25%" style="vertical-align:top">';
							$rowsvisited = 0;
							$currentcol++;
						}
						echo '<h4>'.$abilities['ability']."</h4>";
					} else {
						echo '<span class="ability" abilityid="'.$abilities['id'].'">';
						echo $abilities['ability']."</span><br/>";
					}
					$rowsvisited++;
				}
			}		
		}
	}
?>
</td></tr></table>
<?php	
	echo '<H3>RP Points: '.$roleplay_points.'</h3>';
	echo '<H3>Service Points: '.$service_points.'</h3>';
	echo '<H3>Gold: '.$gold.'</h3>';

	echo '<H3>Notes:</H3>';
	
	echo '<div id="notes">Character Notes: '.$character['Character']['other_notes'].'</div>';

	echo '<div id="oognotes">Player Notes: '.$character['Player']['player_notes'].'</div>';

	echo '<div id="charactermemo">Character Memo: '.$character['Character']['character_memo'].'</div>';

	if($character['Player']['medical_notes']) {
		echo '<div id="medical">Player Medical: '.$character['Player']['medical_notes'].'</div>';
	}
	
	echo '<H3>Build History:</H3>';
	echo '<table><tr><th>Event</th><th>Build Earned</th></tr>';

	$rowcount = 0;	
	foreach($buildmemo as $eventbuild) {
		
		if($rowcount < 3) {
			echo '<tr>';
			$rowcount++;
		} else {
			echo '<tr class="extendedbuildinfo">';
		}
		echo '<td style="text-align:right;">' . $eventbuild['ev']['name'] . '</td><td style="text-align:right;">' . $eventbuild['0']['total'] . '</td></tr>';
	}
		
	echo '</table>';
?>
	<button id="showallbuild">Show All Build</button>
</div>

<script type="text/javascript">

	$('tr.extendedbuildinfo').hide();
	<?php if($rowcount < 3) { ?> $('#showallbuild').hide(); <?php } ?>
	
	$('#showallbuild').click(function() {
		$('tr.extendedbuildinfo').show();
		$('#showallbuild').hide();
	});
	
	function createTooltip(event){       
		$('<div class="tooltip"></div>').appendTo('body');
		positionTooltip(event);
	};

	function positionTooltip(event){
		var tPosX = event.pageX + 10;
		var tPosY = event.pageY + 25;
		$('div.tooltip').css({'position': 'absolute', 'top': tPosY, 'left': tPosX, 'width': '50%'});
	};
	
	function hideTooltip() {
		$('div.tooltip').remove();
	};
	
	function loadRules(ability_id) {
		$('div.tooltip').load('/ability/loadrules/'+ability_id);
	}
			
</script>