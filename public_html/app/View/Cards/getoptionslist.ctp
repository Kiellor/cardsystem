options filter:<input type="text" id="filteroptions"/>
<br/>
Options available for this skill
<br/>
<select id="option_chooser1" size="8">
<?php 

	foreach(array_keys($options) as $key) {
		echo '<option value="'.$key.'">'.$options[$key].'</option>'."\n";
	}

?>
</select>

<div id="newoption"> 
Skill option does not exist, use this form to add it
<?php
	echo $this->Form->create('Ability',array('url' => array('controller' => 'ability', 'action' => 'index'),'default'=>false));
	echo $this->Form->input('ability_name');
	echo $this->Form->input('display_name');
?>
	<input name="data[Other][addtooptionlist]" type="hidden" id="optionlistidinput"  value="0"/>
	<input name="data[Ability][abilitytype_id]" type="hidden" value="25"/>
	<input name="data[Ability][abilitygroup_id]" type="hidden" value="25"/>
	
	<button id="saveoption">Save Option</button>
</div>

<script type="text/javascript">
	$("#newoption").hide();
	$("#optionlistidinput").val($("#optionlistid").val());
	
	function callback() {		
		var name = $("#filteroptions").val();
		var subname = name.match(/.*-[ ]*([a-zA-Z0-9+ ]+)/);
		
		$("#AbilityAbilityName").val(name);
		if(subname !== null && subname.length > 0) {
			$("#AbilityDisplayName").val(subname[1]);		
		} else {
			$("#AbilityDisplayName").val(name);
		}
		
		var length = $('#option_chooser1').children('option').length;
		
		if(length == 0) {
			$("#newoption").show();
			$("#option_chooser1").hide();		
		} else {
			$("#newoption").hide();
			$("#option_chooser1").show();
		}
	}
	
	$("#option_chooser1").filterByText($("#filteroptions"),true,callback);
	
	$("#saveoption").click(function() {
		//$("#AbilityGetoptionslistForm").ajaxSubmit();
		$("#AbilityGetoptionslistForm").ajaxSubmit(loadoptions());
	});

</script>
