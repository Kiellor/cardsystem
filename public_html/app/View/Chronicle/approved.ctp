<h1>Approved Chronicle Entries</h1>

<?php 
		
		
		sort($cards);
	foreach ($cards as $card) {
		$name = $card['c']['name'];
		$cnum = $card['c']['cardnumber'];
		
		echo '<a href="/chronicle/view/'.$cnum.'"> - '.$name.' - '.$cnum.'</a> <a href="/chronicle/distill/'.$cnum.'">(distill)</a><br/>' 	;
	}
	
?>
