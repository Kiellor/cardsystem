Note: Any ability not listed is not considered when calculating your Body Ratio

<?php
	$group = "";
	$sortname = "";
	
	foreach ($abilities as $ability) {
		if($group != $ability['ag']['grp']) {
			if($group != "") {
				echo '</table>';
			}
			$group = $ability['ag']['grp'];
			echo '<h3>'.$group.'</h3>'."\n";
		}
		
		if($sortname != $ability['ag']['sorting_name']) {
			if($sortname != "") {
				echo '</table>';
			}
			$sortname = $ability['ag']['sorting_name'];
			echo '<h4>'.$sortname.'</h4>'."\n".'<table>';
		}

		$id = $ability['a']['id'];
		$ability_name = $ability['a']['ability'];
		$ability_ratio = $ability['a']['ratio'];

		echo '<tr id="row-view-'.$id.'">';
		echo '<td id="name-'.$id.'">'.$ability_name.'</td>';
		
		if($group == 'Race') {
			echo '<td id="ratio-'.$id.'">'.$ability_ratio.'</td>';
		} else {
			echo '<td id="ratio-'.$id.'">'.round($ability_ratio).'</td>';
		}
		
		echo '</tr>'."\n";
	}
	
	echo '</table>'."\n";
?>
