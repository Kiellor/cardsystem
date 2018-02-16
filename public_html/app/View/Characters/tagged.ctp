<!-- File: /app/View/Characters/index.ctp -->

<h1>Characters</h1>

<?php

if(isset($characters)) {
?>

<H4>Count: <?php echo $count; ?></H4>

filter:<input type="text" id="filter1"/><span id="count"></span>

<br/>

<div id="listing">
	<select size="10" id="chooser">
	<?php 
		foreach ($characters as $character) {
			echo '<option value="'.$character['Character']['cardnumber'].'">'.$character['Character']['cardnumber'].' - '.$character['Character']['name'].'</option>';
		}
	?>
	</select>

</div>
<button id="viewchar">View Character</button>
<button id="editchar">Enter Card Data</button>

<script type="text/javascript">
			
	// filterByText found in eodra.js
	$("#chooser").filterByText($("#filter1"),true);
	$("#viewchar").click(function() { 
	
		var selectVal = $('#chooser :selected').val();
		
		if(selectVal != 'undefined') {
			window.location = '/characters/view/'+selectVal;
		}
	});

	$("#editchar").click(function() { 
	
		var selectVal = $('#chooser :selected').val();
		
		if(selectVal != 'undefined') {
			window.location = '/cards/page1/'+selectVal;
		}
	});
	
</script>

<?php } ?>