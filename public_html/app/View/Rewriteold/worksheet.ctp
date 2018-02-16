
<script>
tinymce.init({
    selector: "textarea#notes",
    width: 500,
    height: 100,
    menubar:false,
    statusbar: false,
    plugins: [
         "pagebreak spellchecker",
         "wordcount nonbreaking",
         "paste"
   ],
   toolbar: "undo redo | bullist numlist outdent indent"
 }); 
</script>

<h2 id="CharName">Character Rewrite Worksheet for 
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
	echo $this->Html->link('View Proposal',array('controller' => 'rewrite', 'action' => 'viewproposal', $character['Character']['cardnumber']));
	echo '</li>';
?>
</ul>
</div>

<table>
<tr>
<td style="vertical-align:top;">

<h2 id="CharName"><?php 
	echo $character['Character']['name']; 
	echo ' (';
	echo $character['Character']['cardnumber']; 
	echo ') ';
?></h2>

<table>
<?php
		echo '<tr><td>Level</td><td style="text-align:right;">'.$level.'</td></tr>';
		echo '<tr><td>Build Spent</td><td style="text-align:right;">'.$build_spent.'</td></tr>';
		echo '<tr><td>Build Unspent</td><td style="text-align:right;">'.$build_unspent.'</td></tr>';
		echo '<tr><td>Build Earned</td><td style="text-align:right;">'.$build_earned.'</td></tr>';
?>
</table>


<script type="text/javascript">
	
	var UN = 1;
	
	var build = 0;

	function skillMath() {
		for(var i = 1; i <= UN; i++) {
			var qty   = $("input[name=quantity_"+i+"]").val();
			var spend = $("input[name=build_spent_"+i+"]").val();
			var total = qty * spend;

			$("input[name=build_total_"+i+"]").val(total);
		}
		buildMath();
	}
	
	function buildMath() {
		var justspent = 0;
		
		$(".total").each(function() {
			justspent += +$(this).val();
		});
		
		$('#build_display').empty();
		if(justspent >= 0) {
			$('#build_display').append("Build Spent: "+justspent);
		} else {
			justspent = justspent * -1;
			$('#build_display').append("Build Refunded: "+justspent);
		}
	}
	
	function deleteSkill(id,selectVal,selectOptionVal) {
		$("span[abilityid='"+selectVal+"-"+selectOptionVal+"']").show();
		$("span[abilityid='"+selectVal+"-"+selectOptionVal+"']").next("br").show();
		$('#update_'+id).remove();
		buildMath();
	}
	
	function loadoptions() { 

		$("#option_chooser1").remove();
	
		var ability_option_lookup = <?php echo json_encode($abilities_with_options); ?>;
		var selectVal = $('#ab_chooser1 :selected').val();
		var optionlistid = ability_option_lookup[selectVal];
		$("#optionlistid").val(optionlistid);
		if(optionlistid !== undefined) {
			$("#options1").append('loading '+'/cards/getoptionslist/'+optionlistid);
			$("#options1").load('/cards/getoptionslist/'+optionlistid);
		}
	}
		
	// filterByText found in eodra.js
	$("#ab_chooser1").filterByText($("#filter1"),false);
	$("#ab_chooser1").change(function(){loadoptions();});
			
	function addSkill1(delta) {
		var selectEventVal = 0;
		var selectVal = $('#ab_chooser1 :selected').val();
		var selectText = $('#ab_chooser1 :selected').text();
		if($('#option_chooser1').length > 0) {
			var selectOptionVal = $('#option_chooser1 :selected').val();
			var selectOptionText = $('#option_chooser1 :selected').text();
		} else {
			var selectOptionVal = 0;
			var selectOptionText = "";
		}
		
		if(selectVal.indexOf('@') > 0) {
			var s = selectVal.split('@');
			selectVal = s[0];
			selectOptionVal = s[1];
		}
				
		var buildCost = selectText.substring(0,selectText.indexOf(" "));
		selectText = selectText.substring(selectText.indexOf(" -- ")+4);
		
		if(selectText.length > 0) {
			var ability = selectText+' '+selectOptionText;
			
			adjustSkill(selectVal,selectOptionVal,ability,1,buildCost);
		}
	}

	function learnSkill(selectVal,selectOptionVal, ability, delta, buildCost, option_list) {
		
		if(option_list > 0) {
			$("#list_skills").hide();
			$("#skill_options").show();
			$("#skill_options").append("Loading Options, please wait");
			$("#skill_options").load('/rewrite/getskilloptions/'+selectVal+'/'+buildCost);
		} else {
			$("#skill_options").empty();
			$("#list_skills").show();
			adjustSkill(selectVal,selectOptionVal, ability, delta, buildCost);
		}	
	}

	function unlearnAll() {
		$("button.unlearnbutton").click();
	}

	function deleteAll() {
		$("button.deleteSkillButton").click();
	}

	function unlearnSkill(selectVal,selectOptionVal, ability, delta, buildCost) {
		adjustSkill(selectVal,selectOptionVal, ability, delta, buildCost);
		
		var key = selectVal+"-"+selectOptionVal;
		
		$("span[abilityid='"+key+"']").hide();
		$("span[abilityid='"+key+"']").next("br").hide();
	}
	
	function adjustSkill(selectVal,selectOptionVal, ability, delta, buildCost) {
		
		var lookupkey = selectVal + '-' + selectOptionVal;

		if( $('#item'+lookupkey).length > 0 ) {
			var current = $('#item'+lookupkey).val();
			var newValue = parseInt(current)+delta;
			$('#item'+lookupkey).val(newValue);
			$('#qty_'+lookupkey).empty();
			$('#qty_'+lookupkey).append(newValue +' x '+ buildCost);
			skillMath();
		} else {
			
			var column;
			if(delta > 0) {
				column = $('#newskills');
			} else {
				column = $('#oldskills');
			}
			
			column.append('<tr id="update_'+UN+'"><td>'+
					'<input type="hidden" name="input_type_'+UN+'" value="skill"/>'+
					'<input type="hidden" name="ability_id_'+UN+'" value="'+selectVal+'"/>'+
					'<input type="hidden" name="option_id_'+UN+'" value="'+selectOptionVal+'"/>'+
					ability+' <span id="qty_'+lookupkey+'">'+delta +' x '+ buildCost +'</span></td>'+
					'<input type="hidden" size="3" name="quantity_'+UN+'" value="'+delta+'" id="item'+lookupkey+'"/>'+
					'<input type="hidden" size="3" name="build_spent_'+UN+'" value="'+buildCost+'"/>'+
					'<input disabled class="total" type="hidden" size="3" name="build_total_'+UN+'" value="'+(delta * buildCost)+'"/>'+
					' '+
					'<td><button class="deleteSkillButton" onClick="deleteSkill('+UN+','+selectVal+','+selectOptionVal+')">X</button></td>'+
					'</tr>');
			$("input[name=quantity_"+UN+"]").change(function(){skillMath()});
			$("input[name=build_spent_"+UN+"]").change(function(){skillMath()});
			UN++;
		}
		
		buildMath();
	}
	
	function learnList() {
		var selectVal = $('#new_list :selected').val();
		var selectText = $('#new_list :selected').text();
		var abilityId = $('#new_list :selected').attr('ability_id');
		var cost = $('#new_list :selected').attr('cost');
		
		adjustSkill(abilityId,"0",selectText,1,cost);		
	}
			
</script>

<button onClick="unlearnAll()">Unlearn All</button>

<?php 
	
	foreach (array_keys($character_abilities) as $ability_set_name) {
		
		if(count($character_abilities[$ability_set_name]) > 0) {

			echo '<h3>'.$ability_set_name.'</h3>';
			$rowsvisited++;

			foreach ($character_abilities[$ability_set_name] as $abilities) {
				echo '<span class="ability" abilityid="'.$abilities['id'].'-'.$abilities['optionid'].'">';

				$arguments  = "'".$abilities['id']."','".$abilities['optionid']."'";
				$arguments .= ",'".$abilities['ability']."'";
				$arguments .= ",-".$abilities['qty']."";
				$arguments .= ",'".$abilities['build_spent']."'";

				echo '<button class="unlearnbutton" onClick="unlearnSkill(';
				echo $arguments;
				echo ')">Unlearn</button>';

				echo $abilities['ability']."</span><br/>";
			}
		}
	}
?>

</td><td style="vertical-align:top;">



<H2>Add New Lists / Skills</H2>
<div>Note: not all choices displayed are valid, please double check the rulebook</div>

<select size="<?php echo count($lists); ?>" id="new_list">
<?php
	foreach($lists as $row) {
		echo '<option cost="'.$row['la']['build_cost'].'" ability_id="'.$row['a']['id'].'" value="'.$row['a']['opens_list_id'].'">'.$row['a']['ability_name'].' ('.$row['la']['build_cost'].')</option>';			
	}
?>
</select>
<button onClick="learnList()">Add List</button>
<br/>

<div id="skill_options">

</div>
<div id="list_skills">
Select a List above to see skills here
</div>

<script type="text/javascript">

	$("#skill_options").hide();

	function loadlist() { 

		$("#list_skills").show();
		$("#skill_options").hide();
		$("#list_skills").empty();
	
		var selectVal = $('#new_list :selected').val();
		if(selectVal !== undefined) {		
			$("#list_skills").append('loading '+'/getlistabilities/'+selectVal);
			$("#list_skills").load('/rewrite/getlistabilities/'+selectVal);
		}
	}
	
	$("#new_list").change(function(){loadlist();});
	
	<?php if(count($lists) == 1) { echo 'loadlist();'; } ?>

</script>

</td><td  style="vertical-align:top;">

<H2>Proposed Changes</H2>

<H3 id="build_display"></H3>

<button onClick="deleteAll()">Clear Choices</button>

<form method="post" action="/rewrite/propose/<?php echo h($character['Character']['cardnumber']); ?>" id="updates">

<input type="submit" name="submit" value="Save Proposed Changes"/>

<div>Enter any notes you have about your rewrite here.  Only staff and card officers will see this note:</div>
<div><textarea id="notes" name="notes"><?php echo $character['Character']['rewrite_notes']; ?></textarea></div>

<table>
<tr>
	<td><h4>Unlearning</h4></td><td><h4>Learning</h4></td>
</tr>
<tr>
	<td style="vertical-align:top;">
		<table id="oldskills"/> </table>
	</td><td style="vertical-align:top;">
		<table id="newskills"/> </table>
	</td>
</tr>
</table>
    
<input type="hidden" name="characterid" value="<?php echo h($character['Character']['id']); ?>"/>
<input type="hidden" name="cardnumber" value="<?php echo h($character['Character']['cardnumber']); ?>"/>
<input type="hidden" name="playerid" value="<?php echo h($character['Player']['id']); ?>"/>

</td><td  style="vertical-align:top;">



</form>

</td>

</tr>
</table>

<script type="text/javascript">

<?php
	foreach (array_keys($proposed) as $ability_set_name) {
		foreach ($proposed[$ability_set_name] as $abilities) {	
		
			if($abilities['qty'] < 0) {
				echo 'unlearnSkill('.$abilities['id'].','.$abilities['optionid'].',"'.$abilities['ability'].'",'.$abilities['qty'].','.$abilities['build_spent'].');';
				echo "\n";
			} else {
				echo 'adjustSkill('.$abilities['id'].','.$abilities['optionid'].',"'.$abilities['ability'].'",'.$abilities['qty'].','.$abilities['build_spent'].');';
				echo "\n";
			}
		}
	}
?>

</script>