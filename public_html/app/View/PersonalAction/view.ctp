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

<div id="LandSystem">
<h2>Land System Actions</h2>

<?php if(count($turn_actions) > 0) {
	echo '<h3>Current Action</h3>';
	
	foreach($turn_actions as $ta) {
		echo '<h4>'.$ta['a']['name'].' in '.$ta['s']['name'].'</h4>';
		echo 'Target: '.$ta['ta']['target'] .'</br>';
		echo 'Comments: '.$ta['ta']['comments'] .'</br>';
	}
} ?>


<?php if($turn_status == 1) { 

	echo '<h3>Submissions are Closed for this Turn</h3>';
} else {

?>

<h3>Submit Action</h3>

<form method="POST" action="/personal_action/addaction/<?php echo $character['Character']['cardnumber'] ?>">
<input type="hidden" name="turnid" value="<?php echo $current_turn_id; ?>"/>

<select id="action" name="action">
<option value="-1">-- Choose an Action --</option>
<?php
	foreach($available_actions as $act) {
		echo '<option value="'.$act['a']['id'].'">'.$act['a']['name'].'</option>';	
	}
?>
</select>

<select id="location" name="location">
<option value="-1">-- Choose a Settlement --</option>
<?php
	$land = "none";
	foreach($locations as $loc) {
		if($loc['l']['name'] != $land) {
			$land = $loc['l']['name'];
			echo '<option disabled>'.$loc['l']['name'].'</option>';	
		}
		echo '<option value="'.$loc['l']['id'].'!'.$loc['s']['id'].'">-- '.$loc['s']['name'].'</option>';	
	}
?>
</select>

<br/>
Specific Target: <input type="text" name="target" size="50"/>
<br/>
Comments: <input type="text" name="comments" size="50"/>
<br/>
If you feel that you have a skill/ability/list that would give you a bonus for these actions, please feel free to write it in the comments section. Please pick the single most appropriate skill/ability/list you would like us to consider.
<ul>
<li>If you would like to change your action just submit a new one.</li>
<li>If you would like to delete your action, submit this form without choosing a settlement</li>
</ul>


<input type="submit" name="submit" value="Save Changes"/>

</form>

<?php } ?>

</div>
