<!-- File: /app/View/Ability/index.ctp -->

<h1>Abilities</h1>

<div id="filter">
filter:<input type="text" size="75" id="filter1"/><span id="count"></span>
</div>
<div id="instructions">
Please type the name of the ability you want to add:
<ul>
<li>if it doesn't exist the form will show up to add it</li>
<li>if it does, well then your job is done.</li>
</ul>
</div>

<div id="listing">
	<select size="10" id="ab_chooser1">
	<?php 
		foreach (array_keys($abilities) as $key) {
			echo '<option value="'.$key.'">'.$abilities[$key].'</option>';
		}
	?>
	</select>

	<div id="options1"> </div>
</div>

<div id="newskill"> 
Skill does not exist, use this form to add it
<?php
	echo $this->Form->create('Ability');
	echo $this->Form->input('ability_name');
	echo $this->Form->input('display_name');

	echo $this->Form->input('abilitytype_id', array('type' => 'select', 'options'=>$ability_types, 'label' => 'Type'));
	echo $this->Form->input('abilitygroup_id', array('type' => 'select', 'options'=>$ability_groups, 'label' => 'Group'));
	echo $this->Form->input('opens_list_id', array('type' => 'select', 'options'=>$all_lists, 'label' => 'Opens List'));
	echo $this->Form->input('uses_option_list', array('type' => 'select', 'options'=>$option_lists, 'label' => 'Options List'));

	echo $this->Form->end('Save Ability');
?>
</div>

<script type="text/javascript">
		
	$("#newskill").hide();
	
	function callback() {
		var name = $("#filter1").val();
		var subname = name.match(/.*-[ ]*([a-zA-Z0-9+ ]+)/);
		
		$("#AbilityAbilityName").val(name);
		if(subname !== null && subname.length > 0) {
			$("#AbilityDisplayName").val(subname[1]);		
		} else {
			$("#AbilityDisplayName").val(name);
		}
		
		var length = $('#ab_chooser1').children('option').length;
				
		$("#count").text(length);
		
		if(length == 0) {
			$("#newskill").show();
			$("#listing").hide();		
		} else {
			$("#newskill").hide();
			$("#listing").show();
		}
	}
		
	// filterByText found in eodra.js
	$("#ab_chooser1").filterByText($("#filter1"),true, callback);
	$("#ab_chooser1").change(function() { 

		$("#option_chooser1").remove();
	
		var ability_option_lookup = <?php echo json_encode($abilities_with_options); ?>;
		var selectVal = $('#ab_chooser1 :selected').val();
		if(ability_option_lookup[selectVal] !== undefined) {
			$("#options1").append('loading '+'/cards/getoptionslist/'+ability_option_lookup[selectVal]);
			$("#options1").load('/cards/getoptionslist/'+ability_option_lookup[selectVal]);
		}
	});
</script>