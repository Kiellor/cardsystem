<h1>Cards with Reported Problems</h1>

<?php 
	foreach ($cards as $card) {
		$cnum = $card['c']['cardnumber'];
		$name = $card['c']['name'];

		echo '<a href="/characters/view/'.$cnum.'">'.$name.'</a><br/>';		
	}
?>
