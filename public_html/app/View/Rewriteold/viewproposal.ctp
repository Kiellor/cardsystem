
<h2 id="CharName">Character Rewrite Proposal for 
<?php 
	echo $character['Character']['name']; 
	echo ' (';
	echo $character['Character']['cardnumber']; 
	echo ') ';
?></h2>

<div id="CardOptions">
<ul>
<?php
	if(AuthComponent::user('role_admin')) {
		echo '<li>';
		echo $this->Html->link('Accept Proposal',array('controller' => 'rewrite', 'action' => 'accept', $character['Character']['cardnumber']));
		echo '</li>';
	} 
	echo '<li>';
	echo $this->Html->link('Alter Proposal',array('controller' => 'rewrite', 'action' => 'worksheet', $character['Character']['cardnumber']));
	echo '</li><li>';
	echo $this->Html->link('Delete Proposal',array('controller' => 'rewrite', 'action' => 'reset', $character['Character']['cardnumber']));
	echo '</li><li>';
	echo $this->Html->link('View Character',array('controller' => 'characters', 'action' => 'view', $character['Character']['cardnumber']));
	echo '</li>';
?>
</ul>
</div>

<h3>These notes were entered by the player during their re-write process to help explain anything they wanted</h3>
<div><?php echo $character['Character']['rewrite_notes']; ?></div>

<table>
<tr>
<td style="vertical-align:top;">

<H2 id="unlearning">Unlearning</H2>
<?php 
	
	$build = 0;
	
	foreach (array_keys($character_abilities) as $ability_set_name) {
		
		if(count($character_abilities[$ability_set_name]) > 0) {

			$header = false;

			foreach ($character_abilities[$ability_set_name] as $abilities) {
				if($abilities['qty'] < 0) {
					if(!$header) {
						echo '<h3>'.$ability_set_name.'</h3>';
						$header = true;
					}
					echo '<span class="ability" abilityid="'.$abilities['id'].'">';
					echo $abilities['ability']."</span><br/>";
					
					$build += $abilities['qty'] * $abilities['build_spent'];
				}
			}
		}
	}
	echo '<span style="font-weight:bold;">Total: '.$build.'</span>';
	$refunded = $build;

?>

</td>

<td style="vertical-align:top;">
<H2 id="learning">Learning</H2>
<?php 
	
	$build = 0;
	
	foreach (array_keys($character_abilities) as $ability_set_name) {
		
		if(count($character_abilities[$ability_set_name]) > 0) {

			$header = false;

			foreach ($character_abilities[$ability_set_name] as $abilities) {
				if($abilities['qty'] > 0) {
					if(!$header) {
						echo '<h3>'.$ability_set_name.'</h3>';
						$header = true;
					}
					echo '<span class="ability" abilityid="'.$abilities['id'].'">';
					echo $abilities['ability']."</span><br/>";
					
					$build += $abilities['qty'] * $abilities['build_spent'];
				}
			}
		}
	}
	echo '<span style="font-weight:bold;">Total: '.$build.'</span>';
	$spent = $build;
?>


</td>
<td style="vertical-align:top;">
<H2>Build</H2>
<table>
<?php
	echo '<tr><td>Rewrite Refunded</td><td style="text-align:right;">'.$refunded.'</td></tr>';
	echo '<tr><td>Rewrite Spent</td><td style="text-align:right;">'.$spent.'</td></tr>';
	echo '<tr><td>Rewrite Difference</td><td style="text-align:right;">'.($spent + $refunded).'</td></tr>';
	echo '<tr><td colspan="2"><hr/></td></tr>';
	echo '<tr><td>Level</td><td style="text-align:right;">'.$level.'</td></tr>';
	echo '<tr><td>Build Earned</td><td style="text-align:right;">'.$build_earned.'</td></tr>';
	echo '<tr><td>Build Spent</td><td style="text-align:right;">'.$build_spent.'</td></tr>';
	echo '<tr><td>Build Unspent</td><td style="text-align:right;">'.$build_unspent.'</td></tr>';
	echo '<tr><td colspan="2"><hr/></td></tr>';
	echo '<tr><td>Build Unspent after Rewrite</td><td style="text-align:right;">'.($build_unspent - $refunded - $spent).'</td></tr>';
?>
</table>

</td>
</tr>
</table>

