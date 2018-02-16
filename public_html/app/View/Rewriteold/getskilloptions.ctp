<?php 
	echo "Choose an option for ";
	echo $ability['Ability']['ability_name'];
	echo "<br/>";

	foreach($options as $row) {
		
		$ability_display = $row['a']['ability_name'];
		
		$arguments  = "'".$ability['Ability']['id']."','".$row['a']['id']."'";
		$arguments .= ",'".$ability['Ability']['ability_name']." - ".$ability_display."'";
		$arguments .= ",1";
		$arguments .= ",'".$build_cost."'";
		$arguments .= ",'0'";

		echo '<button onClick="learnSkill(';
		echo $arguments;
		echo ')">Choose</button>';
				
		echo $ability_display;
		
		echo '<br/>';
	}
	
?>

