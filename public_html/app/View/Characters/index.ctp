<!-- File: /app/View/Characters/index.ctp -->

<h1>Characters</h1>

<ul>
	<?php foreach ($csets as $cset): ?>
	<li><a href="/characters/index/<?php echo $cset['Cset']['id']; ?>"><?php echo $cset['Cset']['name']; ?></a>
	<?php endforeach; ?>
    <?php unset($cset); ?>
</ul>

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
			echo '<option value="'.$character['c']['cardnumber'].'">'.$character['c']['cardnumber'].' - '.$character['c']['name'].'</option>';
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