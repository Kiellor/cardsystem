<!-- File: /app/View/Characters/view.ctp -->

<table>
<tr><td style="vertical-align:top">
<h1><?php 
	echo $character['Character']['name']; 
	echo ' (';
	echo $character['Character']['cardnumber']; 
	echo ') ';
?></h1>

<div id="dates">
<h4>Last Updated <span id="lastupdate" class="timeago" title="<?php echo $character['Character']['last_updated_date'] ?>"></span></h4>
<h4>PDF Generated <span id="lastprinted" class="timeago" title="<?php echo $character['Character']['last_printed_date'] ?>"></span></h4>
</div>

<div id="CardOptions">
<ul>
	<?php
		if(AuthComponent::user('role_admin') || AuthComponent::user('role_cards') || AuthComponent::user('role_newplayermarshal')) {
		echo '<li>';
		echo $this->Html->link('Quests',array('controller' => 'quest', 'action' => 'view', $character['Character']['cardnumber']));
		echo '</li>';

		echo '<li>';
		echo $this->Html->link('Enter Card Data',array('controller' => 'cards', 'action' => 'page1', $character['Character']['cardnumber']));
		echo '</li><li>';
		echo $this->Html->link('Manage Bonded Items',array('controller' => 'cards', 'action' => 'page2', $character['Character']['cardnumber']));
		echo '</li><li>';
		echo $this->Html->link('Download PDF',array('controller' => 'characters', 'action' => 'downloadpdf', $character['Character']['cardnumber']));
		echo ' (saves PDF on server)</li><li>';

// check if needed
		echo $this->Html->link('Download PDF page 2',array('controller' => 'characters', 'action' => 'downloadpdf_aux', $character['Character']['cardnumber'], '1'));
		echo ' (saves PDF on server)</li><li>';


		echo $this->Html->link('View PDF',array('controller' => 'characters', 'action' => 'viewpdf', $character['Character']['cardnumber']));
		echo ' (views saved PDF)</li><li>';
		echo $this->Html->link('Character Level Breakdown',array('controller' => 'characters', 'action' => 'level', $character['Character']['cardnumber']));
		echo '</li>';
	} 
		
	if(($allowrewrites == 'true')){ //     && (AuthComponent::user('role_cards') || AuthComponent::user('role_admin'))) {
		echo '<li>';
		echo $this->Html->link('Race Rewrite',array('controller' => 'rewrite', $character['Character']['cardnumber']));
		echo '</li>';
	}
	
	echo '<li>';
	echo $this->Html->link('Report a Problem',array('controller' => 'feedback', 'action' => 'cardproblem', $character['Character']['cardnumber']));
	echo '</li>';
	echo '<li>';
	echo $this->Html->link('Between Game Actions',array('controller' => 'personal_action'));
	echo '</li>';
	echo '<li>';
	echo $this->Html->link('Character Chronicle',array('controller' => 'chronicle', 'action' => 'view', $character['Character']['cardnumber']));
	echo '</li><li>';
	if($showratios == 'true') {
		echo $this->Html->link('View without Body Ratios',array('controller' => 'characters', 'action' => 'view', $character['Character']['cardnumber']));
	} else {
		echo $this->Html->link('View with Body Ratios',array('controller' => 'characters', 'action' => 'viewratios', $character['Character']['cardnumber']));
	}
	echo '</li><li>';
	echo $this->Html->link('Explain my Body Total',array('controller' => 'characters', 'action' => 'newbody', $character['Character']['cardnumber']));
	echo '</li><li>';
	echo $this->Html->link('View Character Deaths',array('controller' => 'death', 'action' => 'index', $character['Character']['cardnumber']));
	echo '</li>';
		
	?>
</ul>
</div>
</td><td style="vertical-align:top">

<script type="text/javascript">
	function hideproblem(pid) {
		$.ajax({
				url: '/feedback/hideproblem/<?php echo $character['Character']['cardnumber']; ?>/'+pid,
				type: 'POST',
				data: JSON.stringify({hide: 1}),
				dataType: "json",
				contentType: "application/json",
				cache: false,
				success: function() { 
					$("#problemstatus-"+pid).empty(); $("#problemstatus-"+pid).append("hidden"); 
					
					$("#prob-sum-"+pid).hide();
					$("#prob-det-"+pid).hide();
				}
		});
	}
</script>

<?php

	if(count($problems) > 0) {
		echo '<H4>Problem Reports Summary</H4>';
		echo '<table id="CardProblems">';
		foreach($problems as $problem) {
			if($problem['p']['hidden'] == 0) {
				$pid = $problem['p']['id'];

				echo '<tr id="prob-sum-'.$pid.'"><td>Reported <span class="timeago" title="'.$problem['p']['created'].'"></span></td>';
				if($problem['p']['modified'] == $problem['p']['created']) {
					echo '<td></td>';
				} else {
					echo '<td>Modified <span class="timeago" title="'.$problem['p']['modified'].'"></td>';
				}

				if($problem['p']['resolved'] == 1) {
					echo '<td>Resolved <button onClick="hideproblem('.$pid.')"  id="hide-'.$pid.'">Close Resolved Problem</button><span id="problemstatus-'.$pid.'"> </span></td>';			
				} else {
					echo '<td>Pending</td>';						
				}
				echo '</tr>'."\n";

				echo '<tr id="prob-det-'.$pid.'"><td>'.$problem['p']['problem_report'].'</td><td>'.$problem['p']['resolution_comments'].'</td></tr>'."\n";
			}
		}
		echo '</table>';
	}
?>
</td></tr>
</table>

<script type="text/javascript">
	$(document).ready(function() {
		$(".timeago").timeago();
	});
</script>

<div>
<p>Played by:<?php  
	echo $this->Html->link($character['Player']['name'],array('controller' => 'players', 'action' => 'view', $character['Player']['id']));
?></p>
<?php
	if(AuthComponent::user('role_admin')) {
		echo '<p>Last Edited by: ';
		echo $lastEditedBy['p']['name'];
		echo '</p>';
	}
?>
<p>Events Attended:<?php
	echo $character['Character']['past_event_count'];
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
			if($ability_set_name == 'Lore' || $ability_set_name == 'Monster Lore' || $ability_set_name == 'Literacy' || $ability_set_name == 'Lists') {

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

				// new Body	Rules			
				if($build == null) {
					$build = 0;
				}
				echo '<tr><td style="text-align:left;">BP:</td>';
				echo '<td style="text-align:right;">'.ceil($body).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
				echo '<td style="text-align:right;">('.$build.')</td></tr>';			
				// Ratio			
				echo '<tr><td style="text-align:left;">BP Ratio:</td>';
				echo '<td style="text-align:right;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
				echo '<td style="text-align:right;">'.number_format($ratio,1).'</td></tr>';				
				// new Armor Ratio			
				echo '<tr><td style="text-align:left;">AP Mult:</td>';
				echo '<td style="text-align:right;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
				echo '<td style="text-align:right;">'.$armormod.'x</td></tr>';				

				foreach ($character_abilities[$ability_set_name] as $abilities) {
					if($abilities['ability'] != "BP") {
						echo '<tr><td style="text-align:left;">'.$abilities['ability'].':</td>';
						echo '<td style="text-align:right;">'.$abilities['value'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
						echo '<td style="text-align:right;">('.$abilities['total'].')</td></tr>';
					}
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

					$abilities['ability'] = str_replace("^","<br/>&nbsp;&nbsp;",$abilities['ability']);

					if(isset($abilities['bold']) && $abilities['bold'] == 1) {
						if($rowsvisited >= $rowsinacolumn) {
							echo '</td><td width="25%" style="vertical-align:top">';
							$rowsvisited = 0;
							$currentcol++;
						}
						echo '<h4>'.$abilities['ability']."</h4>";
					} else {
						echo '<span class="ability">';
						if($showratios == 'true' && isset($abilities['ratio']) && $abilities['ratio'] > 0) {
							echo '{'.number_format($abilities['ratio'],0).'} ';
						}
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
	
	echo '<div id="notes"><b>Character Notes:</b> '.str_replace("\n","<br/>",$character['Character']['other_notes']).'</div>';

	echo '<div id="oognotes"><b>Player Notes:</b> '.str_replace("\n","<br/>",$character['Player']['player_notes']).'</div>';

	echo '<div id="charactermemo"><b>Character Memo:</b> '.str_replace("\n","<br/>",$character['Character']['character_memo']).'</div>';

	if($character['Player']['medical_notes']) {
		echo '<div id="medical"><b>Player Medical:</b> '.str_replace("\n","<br/>",$character['Player']['medical_notes']).'</div>';
	}
	
	echo '<H3>Build History:</H3>';
	echo '<table><tr><th>Event</th><th>Build Earned</th></tr>';

	echo '<tr><td style="text-align:right;">Character Chronicles</td><td style="text-align:right;">'.$build_earned_from_history.'</td></tr>';
	echo '<tr><td style="text-align:right;">Character Deaths</td><td style="text-align:right;">'.$build_earned_from_death.'</td></tr>';

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