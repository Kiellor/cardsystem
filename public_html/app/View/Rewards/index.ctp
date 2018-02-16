<!-- File: /app/View/Rewards/index.ctp -->

<h1>Quick Rewards</h1>

<form id="rewardform" method="POST" action="/rewards/submit">
<table>
<tr><td>Filter</td><td><input type="text" id="filter1"/><span id="count"></span></td></tr>
<tr><td>Select</td><td><select name="cardnumber" size="1" id="chooser">
	<?php 
		foreach ($characters as $character) {
			echo '<option value="'.$character['c']['cardnumber'].'">'.$character['c']['cardnumber'].' - '.$character['c']['name'].'</option>';
		}
	?>
	</select>
</td></tr>


<tr><td>Build</td><td><input type="text" size="5" id="build" name="build"/></td></tr>
<tr><td>SP</td><td><input type="text" size="5" id="sp" name="service-points"/></td></tr>
<tr><td>RP</td><td><input type="text" size="5" id="rp" name="rp-points"/></td></tr>
<tr><td>Gold</td><td><input type="text" size="5" id="gold" name="gold"/></td></tr>
<tr><td>Comment</td><td>
	<select id="commonoptions" name="commonoptions">
		<option value="0"></option>
		<option value="1">Camp Work Day</option>
		<option value="2">Convention</option>
		<option value="42">Other</option>
	</select>
</td></tr>
<tr id="otherrow"><td>Other</td><td><input type="text" size="40" id="comment" name="comment"/></td></tr>
</table>

</form>

<button id="clear">Clear</button> <button id="enterdata">Submit Reward</button>

<script type="text/javascript">
			
	// filterByText found in eodra.js
	$("#chooser").filterByText($("#filter1"),true);
	$("#clear").click(function() { 

		$("#filter1").val("");
		$("#build").val("");
		$("#sp").val("");
		$("#rp").val("");
		$("#gold").val("");
		$("#comment").val("");
		
	});

	$("#otherrow").hide();
	
	$("#commonoptions").change(function() {
		if($("#commonoptions").val() == 42) {
			$("#otherrow").show();
			$("#comment").val("");
		} else {
			$("#otherrow").hide();
			$("#comment").val($("#commonoptions option:selected").text());
		}
	});

	$("#enterdata").click(function() { 
		$("#rewardform").submit();
	});
	
</script>
