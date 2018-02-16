<!-- File: /app/View/Elists/compare.ctp -->

<div id="pagetitle">
<h1>Compare Lists</h1>
</div>

<div>Disclaimer: The Rulebook takes precedence over what is displayed here.  This is simply a guide to assist and we will strive to keep it as accurate as possible.  If there is a discrepency, the rulebook wins</div>

<div id="pagecontent">

<table id="thetable">
<tr>
<?php for($i = 1; $i <= 5; $i++) {
	echo '<td><select id="chooser'.$i.'">';
	echo '<option value="">--Pick A List--</option>';
	echo '<option value="43">Commoner</option>';
	foreach ($elists as $listentry) {
		echo '<option value="'.$listentry['l']['id'].'">'.$listentry['l']['list_name'].'</option>';
	}
	
	echo '</select></td>';
}
?>
</tr>
<?php
	foreach ($abilities as $ability) {

		$ability_id = $ability['a']['id'];

		echo '<tr id="row'.$ability_id.'" class="allrows">';
		
		echo '<td id="row'.$ability_id.'-1" class="classcol1"></td>';
		echo '<td id="row'.$ability_id.'-2" class="classcol2"></td>';
		echo '<td id="row'.$ability_id.'-3" class="classcol3"></td>';
		echo '<td id="row'.$ability_id.'-4" class="classcol4"></td>';
		echo '<td id="row'.$ability_id.'-5" class="classcol5"></td>';
		
		echo '</tr>';
	}
	
?>
</table>

<script type="text/javascript">
	
	var displayed_rows = new Array();
		
	$("#chooser1").change(function() { load_abilities(1); } );
	$("#chooser2").change(function() { load_abilities(2); } );
	$("#chooser3").change(function() { load_abilities(3); } );
	$("#chooser4").change(function() { load_abilities(4); } );
	$("#chooser5").change(function() { load_abilities(5); } );
	
	function load_abilities(col) {
		var selectVal = $('#chooser'+col+' :selected').val();

		$(".classcol"+col).empty();
		$(".allrows").each(function () {
			var count = 0;
			$(this).find('td').each(function() {
			     if ($(this).text().length > 0) {
			     	count++;
			     }	
			});
			if(count == 0) {
				$(this).hide();
			}
		});
	
		
		$.getJSON('/elists/getlistabilities/'+selectVal, function(data) { 
			
			$.each(data, function(key,value) {
			
				if( $('#row'+value.a.id).length > 0 ) {
					$('#row'+value.a.id).show();
					
					var ability_display = "";
					if(value.a.display_name == null || value.a.display_name == "") {
						ability_display = value.a.ability_name; 
					} else {
						ability_display = value.a.display_name; 
					}
					
					if(value.at.type == "Career Points") {
						ability_display = value.a.ability_name; 
					}
					
					var current_text = $('#row'+value.a.id+'-'+col).text();
					if(current_text.length > 0) {
						$('#row'+value.a.id+'-'+col).append('<br/>');
					}
					
					if(value.ao.opt == null) {
						$('#row'+value.a.id+'-'+col).append(ability_display+' ('+value.la.build_cost+') ');
					} else {
						$('#row'+value.a.id+'-'+col).append(ability_display+' '+value.ao.opt_disp+' ('+value.la.build_cost+') ');
					}
				}
			});
		});
	}
	
</script>

</div>