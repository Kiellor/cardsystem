<!-- File: /app/View/Characters/add.ctp -->

<h1>Add Character</h1>
<?php
echo $this->Form->create('Character');
echo $this->Form->input('name');
echo $this->Form->input('cset_id', array('type' => 'select', 'options'=>$csets, 'label' => 'Set', 'default' => 1));
echo $this->Form->input('cardnumber', array(
    'div' => array('id' => 'cardnumberinput')
));


?>

<script type="text/javascript">
	$('#cardnumberinput').append('<button id="newcardnumber">New</button>');
	$('#newcardnumber').click(function(event) {
		event.preventDefault();
		
		$cset = $("#CharacterCsetId option:selected").val();
		$url = '/characters/getnextid/'+$cset;
		
		$.get($url, function(result) { 
			$('#CharacterCardnumber').val(result);					
		});
	});
</script>

<div>
	Find Player:<input type="text" id="filter1" value="<?php echo $playername ?>"/><span id="count"></span>
	<br/>
	<select id="CharacterPlayerId" name="data[Character][player_id]" size="10">
	<?php 
		foreach (array_keys($players) as $key) {
			echo '<option value="'.$key.'">'.$players[$key].'</option>';
		}
	?>
	</select>
</div>

<script type="text/javascript">
			
	// filterByText found in eodra.js
	$("#CharacterPlayerId").filterByText($("#filter1"),true);
	$("#filter1").change();
	
</script>

<?php

echo $this->Form->input('past_event_count', array('label' => 'Events Attended','default' => 0));
echo $this->Form->end('Save Character');

?>