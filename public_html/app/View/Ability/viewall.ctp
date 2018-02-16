<!-- File: /app/View/Characters/view.ctp -->

<?php
	$group = "";
	$sortname = "";
	
	foreach ($abilities as $ability) {
		if($group != $ability['ag']['grp']) {
			if($group != "") {
				echo '</table>';
			}
			$group = $ability['ag']['grp'];
			echo '<h3>'.$group.'</h3>'."\n";
		}
		
		if($sortname != $ability['ag']['sorting_name']) {
			if($sortname != "") {
				echo '</table>';
			}
			$sortname = $ability['ag']['sorting_name'];
			echo '<h4>'.$sortname.'</h4>'."\n".'<table>';
		}

		$id = $ability['a']['id'];
		$ability_name = $ability['a']['ability'];
		$ability_display = $ability['a']['display'];
		$ability_ratio = $ability['a']['ratio'];

		echo '<tr id="row-view-'.$id.'">';
		echo '<td><span class="ability" abilityid="'.$id.'">'.$id.'</td>';
		echo '<td id="name-'.$id.'">'.$ability_name.'</td>';
		echo '<td id="disp-'.$id.'">'.$ability_display.'</td>';
		echo '<td id="ratio-'.$id.'">'.$ability_ratio.'</td>';
		echo '<td><a class="editlink" abilityid="'.$id.'">edit</a></td>';
		echo '</tr>'."\n";
		
		echo '<tr style="display:none" id="row-edit-'.$id.'">';
		echo '<td>'.$id.'</td>';
		echo '<td><input type="text" name="ability_name_'.$id.'" value="'.$ability_name.'"/></td>';
		echo '<td><input type="text" size=80 name="ability_display_'.$id.'" value="'.$ability_display.'"/></td>';
		echo '<td><input type="text" size="3" name="ability_ratio_'.$id.'" value="'.$ability_ratio.'"/></td>';
		echo '<td><button class="save-row" abilityid="'.$id.'">Save</button><button class="reset-row" abilityid="'.$id.'">Reset</button></td>';
		echo '</tr>'."\n";
	}
	
	echo '</table>'."\n";
?>

<script type="text/javascript">

	$('a.editlink').click(function(event) {
		editRow($(this).attr("abilityid"));
	});

	$('button.save-row').click(function(event) {
		saveRow($(this).attr("abilityid"));
	});
	
	$('button.reset-row').click(function(event) {
		resetRow($(this).attr("abilityid"));
	});

	function editRow(ability_id) {
		$('#row-view-'+ability_id).hide();
		$('#row-edit-'+ability_id).show();
		$('a.editlink').hide();
	}

	function resetRow(ability_id) {
		$('#row-view-'+ability_id).show();
		$('#row-edit-'+ability_id).hide();
		$('a.editlink').show();
	}
	
	function saveRow(ability_id) {
		
		data = new Object();
		data.id = ability_id;
		data.name = $("input[name='ability_name_"+ability_id+"']").val();
		data.display = $("input[name='ability_display_"+ability_id+"']").val();
		data.ratio = $("input[name='ability_ratio_"+ability_id+"']").val();
		
		$.ajax({
			url: '/ability/saveinline',
			type: 'POST',
			data: JSON.stringify(data),
			dataType: "json",
			contentType: "application/json",
			cache: false,
			success: function() { 
				$('#name-'+ability_id).text($("input[name='ability_name_"+ability_id+"']").val());
				$('#disp-'+ability_id).text($("input[name='ability_display_"+ability_id+"']").val());
				$('#ratio-'+ability_id).text($("input[name='ability_ratio_"+ability_id+"']").val());
				resetRow(ability_id); 
			}
		});
	
	}
			
</script>
