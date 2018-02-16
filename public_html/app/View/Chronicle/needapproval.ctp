<h1>Chronicle Entries that need Approval</h1>

<?php 
sort($cards);
	foreach ($cards as $card) {
		$cnum = $card['c']['cardnumber'];
		$name = $card['c']['name'];

		echo '<a href="/chronicle/view/'.$cnum.'"> - '.$name.' - '.$cnum.'</a><br/>';		
	}
?>
