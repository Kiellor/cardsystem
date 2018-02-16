<div id="cardpage1basicdetails">

<h2 id="CharName"><?php 
	echo $character['Character']['name']; 
	echo ' (';
	echo $character['Character']['cardnumber']; 
	echo ') ';
?></h2>
<span><b>Character Set:</b><?php echo $character['Cset']['name'] ?></span>

<div id="CardOptions">
<ul>
<?php 
	echo '<li>';
	echo $this->Html->link('Manage Bonded Items',array('controller' => 'cards', 'action' => 'page2', $character['Character']['cardnumber']));
	echo '</li><li>';
	echo $this->Html->link('Download PDF',array('controller' => 'characters', 'action' => 'downloadpdf', $character['Character']['cardnumber']));
	echo ' (saves PDF on server)</li><li>';
	echo $this->Html->link('View PDF',array('controller' => 'characters', 'action' => 'viewpdf', $character['Character']['cardnumber']));
	echo ' (views saved PDF)</li><li>';
	echo $this->Html->link('View Character',array('controller' => 'characters', 'action' => 'view', $character['Character']['cardnumber']));
	echo '</li><li>';
	echo $this->Html->link('Edit Character Deaths',array('controller' => 'death', 'action' => 'edit', $character['Character']['cardnumber']));
	echo '</li>';
?>
	<li><a id="clearall">Delete All Skills</a></li>
	<li><a id="editdetails">Change Name, Number or Set</a></li>
</ul>
</div>

<div id="savedetailsdiv">
	<input type="text" name="characternameedit" id="characternameedit" value="<?php echo $character['Character']['name']; ?>"/><br/>
	<input type="text" name="characternumberedit" id="characternumberedit" value="<?php echo $character['Character']['cardnumber']; ?>"/><br/>
	<select id="csetid" name="csetid">
	<?php 
		echo '<option value="'.$character['Cset']['id'].'">'.$character['Cset']['name'].'</option>';
		foreach (array_keys($csets) as $csetid) {
			if($csetid != $character['Cset']['id']) {
				echo '<option value="'.$csetid.'">'.$csets[$csetid].'</option>';
			}
		}
	?>
	</select>
	<button name="savedetails" id="savedetails">Save Details</button><span id="savedetails_status"> </span>
</div>

<div id="areyousure">
	<H3>Are you sure?</h3>
		Clicking this link will irrevocably delete all skills from this character resetting it to nothing
		<a href="/cards/deleteAllSkills/<?php echo $character['Character']['id']; ?>/<?php echo $character['Character']['cardnumber']; ?>" id="deleteAllSkills">Delete All Skills</a> (Cannot be undone)
</div>

<script type="text/javascript">
	$("#editdetails").click(function(){
		$("#savedetailsdiv").show();
	});
	$("#savedetailsdiv").hide();

	$("#clearall").click(function(){
		$("#areyousure").show();
	});
	$("#areyousure").hide();

	$("#savedetails").click(function(){
			$.ajax({
				url: '/cards/savedetails/<?php echo $character['Character']['cardnumber']; ?>',
				type: 'POST',
				data: JSON.stringify(
					{
							name: $("#characternameedit").val(), 
							number: $("#characternumberedit").val(),
							csetid: $("#csetid").val()
					}),
				dataType: "json",
				contentType: "application/json",
				cache: false,
				success: function() { 
					window.location = "/cards/page1/"+$("#characternumberedit").val();
				},
				error: function() {
					$("#savedetails_status").empty(); 
					$("#savedetails_status").append("An error occurred ... probably a conflicting card number");
				}
			});
	});
</script>

<p>Played by:<?php  
	echo $this->Html->link($character['Player']['name'],array('controller' => 'players', 'action' => 'view', $character['Player']['id']));
?></p>

<p>Updated: <?php echo h($character['Character']['last_updated_date']); ?></p>

<H2 id="unspent_display"></H2>
<div>
<form method="post" action="/cards/submit" id="updates">
<input type="submit" name="submit" value="Save Changes"/>

<table id="newskills"/>
<tr><th>Skill</th><th>qty</th><th>build</th><th>total</th><th>delete</th></tr>
</table>

<select id="eventid" name="eventid">
	<?php 
		foreach (array_keys($events) as $eventid) {
			echo '<option value="'.$eventid.'">'.$events[$eventid].'</option>';
		}
	?>
</select>
    
<input type="hidden" name="characterid" value="<?php echo h($character['Character']['id']); ?>"/>
<input type="hidden" name="cardnumber" value="<?php echo h($character['Character']['cardnumber']); ?>"/>
<input type="hidden" name="playerid" value="<?php echo h($character['Player']['id']); ?>"/>

<table>

<tr><td>Build Earned</td>				<td><?php echo $build_earned; ?></td></tr>
<tr><td>Events Attended</td>			<td>
	<span id="events-display"><?php echo $numevents; ?></span><button id="edit-events-attended">Edit</button>

	<div id="edit-events-div">
		<input type="text" id="edit-events-input" value="<?php echo $numevents; ?>"/>
		<button id="save-edit-events">save</button>
	</div>
</td></tr>

<script type="text/javascript">
	$("#edit-events-div").hide();
	$("#edit-events-attended").click(function(event){
		event.preventDefault();
		$("#edit-events-div").show();
	});
	
	$("#save-edit-events").click(function(event){
		event.preventDefault();
		$.ajax({
			url: '/cards/saveevents/<?php echo $character['Character']['cardnumber']; ?>',
			type: 'POST',
			data: JSON.stringify({value: $("#edit-events-input").val()}),
			dataType: "json",
			contentType: "application/json",
			cache: false,
			success: function() { 
				$("#events-display").empty(); 
				$("#events-display").append($("#edit-events-input").val());
				$("#edit-events-div").hide();
			}
		});
	});
</script>


<tr><td>Add to Events Attended?</td>	<td><input type="checkbox" name="event-attend" value="<?php echo ($numevents+1); ?>"/></td></tr>
<tr><td>Player is Staff?</td>			<td><input type="checkbox" id="player-is-staff" name="staff" value="yes"/></td></tr>
<tr><td>New Build (base)</td>			<td><input type="text" size="3" name="build-base"/></td></tr>
<tr><td>Build from Service</td>			<td><input type="text" size="3" name="build-service"/></td></tr>
<tr><td>Bought Build</td>				<td><input type="text" size="3" name="build-bought"/></td></tr>
<tr id="staff-bought">
	<td>Staff Bought Build</td>			<td><input type="text" size="3" name="build-bought-staff"/></td>
</tr>
<tr><td>Lifestyle Build</td>			<td><input type="text" size="3" name="build-lifestyle"/></td></tr>

<tr>
	<td>Roleplay Points</td>			<td><?php echo $roleplay_points; ?></td>
</tr>
<tr>
	<td>New Roleplay Points</td>
	<td><input type="text" size="3" name="roleplay-points"/><input type="text" size="3" name="roleplay-points-total" disabled/></td>
</tr>
								
<tr><td>Roleplay Build</td>		<td><input type="text" size="3" name="build-roleplay-display" disabled/>
									<input type="hidden" size="3" name="build-roleplay"/></td></tr>
<tr><td>Other</td>				<td><input type="text" size="3" name="build-other"/></td></tr>
<tr><td>Build Earned Total</td>	<td><input type="text" size="3" name="build-total" disabled/></td></tr>
<tr><td>Build Spent</td>		<td><input type="text" size="3" name="build-spent" disabled/></td></tr>
<tr><td>Unspent Build</td>		<td><input type="text" size="3" name="build-unspent" disabled/></td></tr>

<tr><td>Service Points Balance</td>				<td><?php echo $service_points; ?></td></tr>
<tr><td>Service Points Earned</td>				<td><input type="text" size="8" name="service-added"/></td></tr>
<tr><td>Service Points Spent</td>				<td><input type="text" size="8" name="service-spent"/></td></tr>
<tr><td>Service Points Converted to Build</td>	<td><input type="text" size="8" name="service-convert-display" disabled/>
<input type="hidden" size="8" name="service-convert"/></td></tr>
<tr>
	<td>Service Points Total</td>
	<td><input type="text" size="8" name="service-total" disabled/></td>
</tr>
<tr><td>Bank Balance</td><td><?php echo $bank_value; ?></tr>
<tr><td>Bank Change</td><td><input type="text" size="8" name="bank-change"/></td></tr>
<tr><td>Bank New Balance</td><td><input type="text" size="8" name="bank-total" disabled/></td></tr>
</table>
</form>
</div>

<script type="text/javascript">

	var UN = 1;

	$("input[name=build-base]").change(function(){buildMath()});
	$("input[name=build-service]").change(function(){buildMath()});
	$("input[name=build-bought]").change(function(){buildMath()});
	$("input[name=build-lifestyle]").change(function(){buildMath()});
	$("input[name=roleplay-points]").change(function(){buildMath()});
	$("input[name=build-other]").change(function(){buildMath()});
	$("input[name=service-added]").change(function(){buildMath()});
	$("input[name=service-spent]").change(function(){buildMath()});
	$("input[name=staff]").change(function(){toggleStaff();buildMath()});
	$("input[name=build-bought-staff]").change(function(){buildMath()});
	
	// Bank Math is different
	$("input[name=bank-change]").change(function(){bankMath()});

	function skillMath() {
		for(var i = 1; i <= UN; i++) {
			var qty   = $("input[name=quantity_"+i+"]").val();
			var spend = $("input[name=build_spent_"+i+"]").val();
			var total = qty * spend;
			
			$("input[name=build_total_"+i+"]").val(total);
		}
		buildMath();
	}
	
	function toggleStaff() {
		if($("#player-is-staff").is(':checked')) {
			$("#staff-bought").show();
			$("input[name=build-bought-staff]").val(1);
		} else {
			$("#staff-bought").hide();
			$("input[name=build-bought-staff]").val(0);
		}
	}

	function bankMath() {
		// Compute new Bank Total
	    newbank =  +$("input[name=bank-change]").val() + <?php echo $bank_value; ?>;
		$("input[name=bank-total]").val(newbank);
	}	
	
	function buildMath() {
		$isstaff = $("#player-is-staff").is(':checked');
	
		// Compute build earnings
		$stotal = <?php echo $service_points; ?>;
		$stotal += defaultZero($("input[name=service-added]").val());
		$stotal -= defaultZero($("input[name=service-spent]").val());
		
		$btotal = 0;
		$btotal += defaultZero($("input[name=build-base]").val());
		
		if( $isstaff ) {
			$("input[name=service-convert-display]").val(0);		
			$("input[name=service-convert]").val(0);			
		} else {
			$convert_service = defaultZero($("input[name=build-service]").val()) * 100;
			if($convert_service > $stotal) {
				$convert_service = truncate($stotal / 100) * 100;
			}

			if($convert_service < 0) {
				$convert_service = 0;
			}
		
			$stotal -= $convert_service;
		
			$("input[name=service-convert-display]").val($convert_service);		
			$("input[name=service-convert]").val($convert_service);		
		}
		
		
		$btotal += defaultZero($("input[name=build-service]").val());
		$btotal += defaultZero($("input[name=build-bought]").val());
		$btotal += defaultZero($("input[name=build-bought-staff]").val());
		$btotal += defaultZero($("input[name=build-lifestyle]").val());
	

		if($isstaff) {
			$("input[name=build-roleplay]").val(1);
			$("input[name=roleplay-points-total]").val(0);	
			$rpbuild = 1;
			$btotal++;
		} else {
			// Compute RP points
			$rp = defaultZero($("input[name=roleplay-points]").val());
			$rp += defaultZero(<?php echo $roleplay_points; ?>);

			if($rp >= 10) {
				$rp -= 10;
				$("input[name=build-roleplay]").val(1);
				$btotal++;
				$rpbuild = 1;
			} else {
				$("input[name=build-roleplay]").val(0);
				$rpbuild = 0;
			}

			$("input[name=roleplay-points-total]").val($rp);		
		}
		
		$("input[name=build-roleplay-display]").val($rpbuild);
		
		$btotal += defaultZero($("input[name=build-other]").val());
		
		$("input[name=build-total]").val(defaultZero($btotal));
		$("input[name=service-total]").val(defaultZero($stotal));
		
		var justspent = 0;
		
		$(".total").each(function() {
			justspent += +$(this).val();
		});
		
		var build_earned = <?php echo $build_earned ?>;
		var build_spent = 0+justspent+<?php echo $build_spent; ?>;
		var build_unspent = build_earned + $btotal - build_spent;
		$("input[name=build-spent]").val(build_spent);
		$("input[name=build-unspent]").val(build_unspent);
		$("#unspent_display").empty();
		$("#unspent_display").append("Unspent Build: ");
		$("#unspent_display").append(build_unspent);
	}
	
	toggleStaff();
	buildMath();
	bankMath();
		
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

</div>


<div id="cardspage1altercharacter">

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
<br/>
<button id="add_skill" onClick="addSkill1(1)">Add Skill</button>
<button id="unlearn_skill" onClick="addSkill1(-1)">Unlearn Skill</button>
<br/>

<div id="optionsview">	

<span id="options1"> </span>
<input type="hidden" id="optionlistid" name="optionlistid" value=""/>
</div>

<script type="text/javascript">
	function updateproblem(pid) {
		$.ajax({
				url: '/cards/updateproblem/'+pid,
				type: 'POST',
				data: JSON.stringify({comment: $("#problem-"+pid).val(), resolved: 0}),
				dataType: "json",
				contentType: "application/json",
				cache: false,
				success: function() { $("#problemstatus-"+pid).empty(); $("#problemstatus-"+pid).append("comments saved"); }
		});
	}
	
	function resolveproblem(pid) {
		$.ajax({
				url: '/cards/updateproblem/'+pid,
				type: 'POST',
				data: JSON.stringify({comment: $("#problem-"+pid).val(), resolved: 1}),
				dataType: "json",
				contentType: "application/json",
				cache: false,
				success: function() { 
					$("#problemstatus-"+pid).empty(); $("#problemstatus-"+pid).append("problem resolved"); 
					
					$("#prob-det-"+pid).hide();
				}
		});
	}
</script>

<div>
<?php

	echo '<H4>Problem Reports Summary</H4>';

	if(count($problems) > 0) {
		echo '<table id="CardProblems">';
		foreach($problems as $problem) {
			$pid = $problem['p']['id'];
			echo '<tr id="prob-sum-'.$pid.'"><td>Reported <span class="timeago" title="'.$problem['p']['created'].'"></span></td>';
			if($problem['p']['modified'] == $problem['p']['created']) {
				echo '<td></td>';
			} else {
				echo '<td>Modified <span class="timeago" title="'.$problem['p']['modified'].'"></td>';
			}
			echo '<td><button onClick="updateproblem('.$pid.')" " id="update-'.$pid.'">Save Comment</button>';
			echo '<button onClick="resolveproblem('.$pid.')"  id="resolve-'.$pid.'">Save Comment and Resolve Problem</button><span id="problemstatus-'.$pid.'"> </span></td></tr>'."\n";
			echo '</tr>'."\n";
			echo '<tr id="prob-det-'.$pid.'"><td>'.$problem['p']['problem_report'].'</td>';
			echo '<td>'.$problem['p']['resolution_comments'].'</td>';
			echo '<td><textarea id="problem-'.$pid.'">'.$problem['p']['resolution_comments'].'</textarea></td></tr>'."\n";
		}
		echo '</table>';
	} else {
		echo 'No problems reported';
	}
?>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$(".timeago").timeago();
	});
</script>
</div>

	<div id="cardspage1buildhistory">
	<H4>Character Notes</H4>
		Appears on this Character Sheet<br/>
		<textarea rows="4" cols="60" name="notes" id="oognotes"><?php echo $character['Character']['other_notes']; ?></textarea><br/>

		<button name="savenotes1" id="savenotes1">Save OOG Notes</button><span id="oognotes_status"> </span>

	<H4>Player Notes</H4>
		Appears on all of this Player's Character Sheets<br/>
		<textarea rows="4" cols="60" name="notes" id="playernotes"><?php echo $character['Player']['player_notes']; ?></textarea><br/>

		<button name="saveplayernotes" id="saveplayernotes">Save Player Notes</button><span id="playernotes_status"> </span>

	<H4>Player Medical</H4>
		Appears on all of this Player's Character Sheets<br/>
		<textarea rows="4" cols="60" name="notes" id="mednotes"><?php echo $character['Player']['medical_notes']; ?></textarea><br/>

		<button name="savemednotes" id="savemednotes">Save Medical Notes</button><span id="mednotes_status"> </span>
	
	<H4>Storyteller Notes</H4>
		Does NOT appear on Character Sheet<br/>
		<textarea rows="4" cols="60" name="notes" id="stnotes"><?php echo $character['Character']['st_only_notes']; ?></textarea><br/>

		<button name="savenotes2" id="savenotes2">Save ST Notes</button><span id="stnotes_status"> </span>
		
	<H4>Build History</H4>
	<table><tr>
		<th>Event</th>
		<th>Base</th>
		<th>Service</th>
		<th>Bought</th>
		<th>Lifestyle</th>
		<th>RP Build</th>
		<th>Other</th>
	</tr>
	
	<?php 
		$rowcount = 0;	
		foreach($buildhistory as $eventbuild) {
			
			if($rowcount < 3) {
				echo '<tr>';
				$rowcount++;
			} else {
				echo '<tr class="extendedbuildinfo">';
			}
			
			echo '<td style="text-align:right;">' . $eventbuild['ev']['name'] . '</td>';
			echo '<td style="text-align:right;">' . $eventbuild['0']['base'] . '</td>';
			echo '<td style="text-align:right;">' . $eventbuild['0']['service'] . '</td>';
			echo '<td style="text-align:right;">' . $eventbuild['0']['bought'] . '</td>';
			echo '<td style="text-align:right;">' . $eventbuild['0']['lifestyle'] . '</td>';
			echo '<td style="text-align:right;">' . $eventbuild['0']['roleplay_build'] . '</td>';
			echo '<td style="text-align:right;">' . $eventbuild['0']['other'] . '</td>';
			
			echo '</tr>';
		}
			
		echo '</table>';
	?>
	<button id="showallbuild">Show All Build</button>
	</div>
	
	<script type="text/javascript">
	
		$('tr.extendedbuildinfo').hide();
		<?php if($rowcount < 3) { ?> $('#showallbuild').hide(); <?php } ?>
		
		$('#showallbuild').click(function() {
			$('tr.extendedbuildinfo').show();
			$('#showallbuild').hide();
		});
	</script>



<script type="text/javascript">
	
	$("#oognotes").bind('keyup input', function() { $("#oognotes_status").empty(); $("#oognotes_status").append("changed");} );
	$("#stnotes").bind('keyup input', function() { $("#stnotes_status").empty();  $("#stnotes_status").append("changed");} );
	$("#playernotes").bind('keyup input', function() { $("#playernotes_status").empty(); $("#playernotes_status").append("changed");} );
	$("#mednotes").bind('keyup input', function() { $("#mednotes_status").empty(); $("#mednotes_status").append("changed");} );

	$("#savenotes1").click(function(){
		$.ajax({
			url: '/cards/saveoognotes/<?php echo $character['Character']['cardnumber']; ?>',
			type: 'POST',
			data: JSON.stringify({value: $("#oognotes").val()}),
			dataType: "json",
			contentType: "application/json",
			cache: false,
			success: function() { $("#oognotes_status").empty(); $("#oognotes_status").append("saved"); }
		});
	});

	$("#savenotes2").click(function(){
		$.ajax({
			url: '/cards/savestnotes/<?php echo $character['Character']['cardnumber']; ?>',
			type: 'POST',
			data: JSON.stringify({value: $("#stnotes").val()}),
			dataType: "json",
			contentType: "application/json",
			cache: false,
			success: function() { $("#stnotes_status").empty(); $("#stnotes_status").append("saved"); }
		});
	});

	$("#saveplayernotes").click(function(){
		$.ajax({
			url: '/cards/saveplayernotes/<?php echo $character['Player']['id']; ?>',
			type: 'POST',
			data: JSON.stringify({value: $("#playernotes").val()}),
			dataType: "json",
			contentType: "application/json",
			cache: false,
			success: function() { $("#playernotes_status").empty(); $("#playernotes_status").append("saved"); }
		});
	});

	$("#savemednotes").click(function(){
		$.ajax({
			url: '/players/savemednotes/<?php echo $character['Player']['id']; ?>',
			type: 'POST',
			data: JSON.stringify({value: $("#mednotes").val()}),
			dataType: "json",
			contentType: "application/json",
			cache: false,
			success: function() { $("#mednotes_status").empty(); $("#mednotes_status").append("saved"); }
		});
	});

</script>

<script type="text/javascript">

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
	$("#ab_chooser2").filterByText($("#filter2"),false);
	$("#ab_chooser1").change(function(){loadoptions();});
			
	function removeSkill(row_id, ability, qty, buildCost) {
		$('#newskills').append('<tr id="update_'+UN+'"><td>'+
				'<input type="hidden" name="input_type_'+UN+'" value="delete"/>'+
				'<input type="hidden" name="row_id_'+UN+'" value="'+row_id+'"/>'+
				'Deleting ' + ability + '</td>'+
				'<td>' + qty +'</td>'+
				'<td>'+ buildCost +'</td>'+
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
						'<td><input type="text" size="3" name="quantity_'+UN+'" value="'+delta+'" id="item'+lookupkey+'"/></td>'+
						'<td><input type="text" size="3" name="build_spent_'+UN+'" value="'+buildCost+'"/></td>'+
						'<td><input disabled class="total" type="text" size="3" name="build_total_'+UN+'" value="'+buildCost+'"/></td>'+
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
	
	function addSkill2() {
		var selectEventVal = 0;
		var selectVal = $('#ab_chooser2 :selected').val();
		var selectText = $('#ab_chooser2 :selected').text();
		if($('#option_chooser2').length > 0) {
			var selectOptionVal = $('#option_chooser2 :selected').val();
			var selectOptionText = $('#option_chooser2 :selected').text();
		} else {
			var selectOptionVal = 0;
			var selectOptionText = "";
		}

		if(selectVal.indexOf('@') > 0) {
			var s = selectVal.split('@');
			selectVal = s[0];
			selectOptionVal = s[1];
		}

		var buildCost = 0;
		var lookupkey = selectVal + '-' + selectOptionVal;

		if(selectText.length > 0) {

			if( $('#item'+lookupkey).length > 0 ) {
				var current = $('#item'+lookupkey).val();
				$('#item'+lookupkey).val(parseInt(current)+1);
				skillMath();
			} else {
				$('#newskills').append('<tr id="update_'+UN+'"><td>'+
						'<input type="hidden" name="input_type_'+UN+'" value="skill"/>'+
						'<input type="hidden" name="ability_id_'+UN+'" value="'+selectVal+'"/>'+
						'<input type="hidden" name="option_id_'+UN+'" value="'+selectOptionVal+'"/>'+
						'<input type="hidden" name="event_id_'+UN+'" value="'+selectEventVal+'"/>'+			
						'<input type="hidden" name="teacher_id_'+UN+'" value=""/>'+
						selectText+' '+selectOptionText+'</td>'+
						'<td><input type="text" size="3" name="quantity_'+UN+'" value="1" id="item'+lookupkey+'"/></td>'+
						'<td><input type="text" size="3" name="build_spent_'+UN+'" value="'+buildCost+'"/></td>'+
						'<td><input disabled class="total" type="text" size="3" name="build_total_'+UN+'" value="'+buildCost+'"/></td>'+
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


<div id="cardspage1history">
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

</div>