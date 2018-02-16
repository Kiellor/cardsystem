<?php 

	foreach($abilities as $row) {
		
		$ability_display = $row['a']['ability_name'];
		
		$arguments  = "'".$row['a']['id']."','".$row[0]['aoid']."'";
		$arguments .= ",'".$ability_display."'";
		$arguments .= ",1";
		$arguments .= ",'".$row['la']['build_cost']."'";
		$arguments .= ",'".$row['a']['uses_option_list']."'";

		echo '<button onClick="learnSkill(';
		echo $arguments;
		echo ')">Learn</button>';
				
		echo $ability_display;
		
		echo '<br/>';
	}
	
?>

