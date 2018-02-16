<!-- File: /app/View/Cards/page1.ctp -->

<!-- <h1>Card Marshall Input Center</h1> -->

<table>
<tr>
<td style="vertical-align:top;">

<h2 id="CharName"><?php 
	echo $character['Character']['name']; 
	echo ' (';
	echo $character['Character']['cardnumber']; 
	echo ') ';
?></h2>

<div id="CardOptions">
<ul>
</ul>
</div>

<script type="text/javascript">

	var UN = 1;
		
	function defaultZero($value) {
		$result = parseInt($value);
		
		if(isNaN($result)) {
			return 0;
		}
		
		return $result;
	}
	
	function truncate(n) {
	  return Math[n > 0 ? "floor" : "ceil"](n);
	}

</script>

<?php 	
	echo '<table>';

	foreach (array_keys($character_abilities) as $ability_set_name) {
		echo '<tr><td colspan=4><h3>'.$ability_set_name.'</h3></td></tr>';
		echo '<tr><th>qty</th><th>ability {build}</th><th>total build</th><th>actions</th></tr>';

		foreach ($character_abilities[$ability_set_name] as $cabilities) {
			echo '<tr>';
			echo '<td>'.$cabilities['qty'].'</td>';
			echo '<td>'.$cabilities['ability'].'</td>';
			echo '<td>'.$cabilities['build_spent'].'</td>'; 
			echo '<td>';
			
			$arguments  = "'".$cabilities['id']."'";
			$arguments .= ",'".$cabilities['ability']."'";
			$arguments .= ",'".$cabilities['qty']."'";
			$arguments .= ",'".$cabilities['build_spent']."'";
			
				echo '<button onClick="removeSkill(';
				echo $arguments;
				echo ')">Delete</button>';
				
				/*
				echo '<button onClick="unlearnSkill(';
				echo $arguments;
				echo ')">Unlearn</button>';
				*/
				
			echo '</td>';
			echo '</tr>';
			echo "\n";
		}
	}
	
	echo '</table>';
?>




</td><td style="vertical-align:top;">



<H2>Add or Unlearn Skills</H2>
filter:<input type="text" id="filter1"/>
<br/>
Skills available to this character (and skill options when appropriate):
<br/>
<select size="10" id="ab_chooser1">
<?php 
	foreach ($available as $text) {
		$row = explode('!',$text);
		
		if(strpos($row[0],'@') === false) {
			echo '<option value="'.$row[0].'">'.$row[1].'</option>';
		} else {
			$sub = explode('@',$row[0]);
			
			echo '<option value="'.$row[0].'">'.$row[1].' '.$abilities[$sub[1]].'</option>';
		}
	}
?>
</select>

<button id="add_skill" onClick="addSkill1(1)">Add Skill</button>
<button id="unlearn_skill" onClick="addSkill1(-1)">Unlearn Skill</button>
	
</td>
<td style="vertical-align:top;">
<span id="options1"> </span>
<input type="hidden" id="optionlistid" name="optionlistid" value=""/>
</td>

<script type="text/javascript">
	
	function buildMath() {
	
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
			
	function removeSkill(row_id, ability, qty, buildCost) {
		$('#newskills').append('<tr id="update_'+UN+'"><td>'+
				'<input type="hidden" name="input_type_'+UN+'" value="delete"/>'+
				'<input type="hidden" name="row_id_'+UN+'" value="'+row_id+'"/>'+
				'Deleting ' + ability + '</td>'+
				'<td>x' + qty +'</td>'+
				'<td>('+ buildCost +')</td>'+
				'<td></td>'+
				'<td><button onClick="deleteSkill('+UN+')">X</button></td>'+
				'</tr>');
			UN++;
			buildMath();
	}
	
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
		
		var lookupkey = selectVal + '-' + selectOptionVal;
		
		var buildCost = selectText.substring(0,selectText.indexOf(" "));
		selectText = selectText.substring(selectText.indexOf(" -- ")+4);
		
		if(selectText.length > 0) {
		
			if( $('#item'+lookupkey).length > 0 ) {
				var current = $('#item'+lookupkey).val();
				$('#item'+lookupkey).val(parseInt(current)+delta);
				skillMath();
			} else {
				$('#newskills').append('<tr id="update_'+UN+'"><td>'+
						'<input type="hidden" name="input_type_'+UN+'" value="skill"/>'+
						'<input type="hidden" name="ability_id_'+UN+'" value="'+selectVal+'"/>'+
						'<input type="hidden" name="option_id_'+UN+'" value="'+selectOptionVal+'"/>'+
						'<input type="hidden" name="event_id_'+UN+'" value="'+selectEventVal+'"/>'+			
						'<input type="hidden" name="teacher_id_'+UN+'" value=""/>'+
						selectText+' '+selectOptionText+'</td>'+
						'<td>x<input type="text" size="3" name="quantity_'+UN+'" value="'+delta+'" id="item'+lookupkey+'"/></td>'+
						'<td>(<input type="text" size="3" name="build_spent_'+UN+'" value="'+buildCost+'"/>)</td>'+
						'<td>=<input disabled class="total" type="text" size="3" name="build_total_'+UN+'" value="'+buildCost+'"/></td>'+
						' '+
						'<td><button onClick="deleteSkill('+UN+')">X</button></td>'+
						'</tr>');
				$("input[name=quantity_"+UN+"]").change(function(){skillMath()});
				$("input[name=build_spent_"+UN+"]").change(function(){skillMath()});
				UN++;
			}
		}
		buildMath();
	}
		
	function deleteSkill(id) {
		$('#update_'+id).remove();
		buildMath();
	}
	
</script>

</td><td  style="vertical-align:top;">

<H2>Proposed Changes</H2>

<form method="post" action="/cards/submit-rewrite" id="updates">
<input type="submit" name="submit" value="Save Changes"/>

<table id="newskills"/> </table>
    
<input type="hidden" name="characterid" value="<?php echo h($character['Character']['id']); ?>"/>
<input type="hidden" name="cardnumber" value="<?php echo h($character['Character']['cardnumber']); ?>"/>
<input type="hidden" name="playerid" value="<?php echo h($character['Player']['id']); ?>"/>


</form>

</td>

</tr>
</table>