<!-- File: /app/View/Cards/page1.ctp -->

<h2 id="CharName">Characters with Pending Rewrites (<?php echo count($characters); ?>)</h2>

<ul>

<?php 
	
	foreach ($characters as $character) {
		$display = " " . $character['c']['name'] . " (" . $character['c']['cardnumber'] . ")";
		
		echo '<li>';
		echo $this->Html->link($display,array('controller' => 'rewrite', 'action' => 'viewproposal', $character['c']['cardnumber']));
		
		if(strpos($character['c']['rewrite_notes'], 'HOLD') !== FALSE) {
			echo ' HOLD';
		}

		echo '</li>';
		
	}
?>

</ul>
