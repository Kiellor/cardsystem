<!-- File: /app/View/Cards/bulkadd.ctp -->

<h1>Card Marshall Input Center</h1>

<table>
<tr>
<td>

<h1><?php 
	echo h($character['Character']['name']); 
	echo ' (';
	echo h($character['Character']['id']); 
	echo ') ';
	echo $this->Html->link('Dowload PDF',array('controller' => 'characters', 'action' => 'viewpdf', $character['Character']['id']));
?></h1>

<p>Played by:<?php  
	echo $this->Html->link(h($character['Player']['name']),array('controller' => 'players', 'action' => 'view', $character['Player']['id']));
?></p>

<div>
<form method="post" action="/cards/submit" id="updates">
<input type="submit" name="submit" value="Save Changes"/>
<input type="hidden" name="characterid" value="<?php echo h($character['Character']['id']); ?>"/>

<table><tr><th>qty</th><th>build</th><th>ability</th><th>total</th></tr>
<tr>
<?php 
	$UN = 1;
	
	foreach ($available as $text) {
		$row = explode('!',$text);
	
		if(strpos($row[0],'@') === false) {
			$ab_id = $row[0];
			$ab_op_id = 0;
			$ab_text = $row[1];
		} else {
			$sub = explode('@',$row[0]);

			$ab_id = $sub[0];
			$ab_op_id = $sub[1];
			$ab_text = $row[1].' '.$abilities[$sub[1]];
		}
		
		$cost = explode(' -- ',$ab_text);
		$ab_text = $cost[1];
		
		$buildcost = $cost[0];
		
		?>
	
		<td>
			<input type="hidden" name="input_type_<?php echo $UN; ?>" value="skill"/>
			<input type="text" size="3" name="quantity_<?php echo $UN; ?>" value="0"/>
		</td>
		<td>
			<input type="hidden" name="ability_id_<?php echo $UN; ?>" value="<?php echo $ab_id; ?>"/>
			<input type="hidden" name="option_id_<?php echo $UN; ?>" value="<?php echo $ab_op_id; ?>"/>
			<input type="text" size="3" name="build_spent_<?php echo $UN; ?>" value="<?php echo $buildcost; ?>"/>
		</td>
		<td>
			<?php echo $ab_text; ?>
		</td>
		</tr>
		
		<?php
		$UN++;
	}
?>


</form>
</div>


</td>
<td>

</td></tr></table>

<div id="result">Results</div>
