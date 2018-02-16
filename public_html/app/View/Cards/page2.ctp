<!-- File: /app/View/Cards/page1.ctp -->

<script type="text/javascript">
	var UN = 1;
				
	function addSkill() {
		var delta = 1;
		var item = $('#slot_number :selected').val();
		var socket = $('#socket_number :selected').val();
		var selectEventVal = 0;
		var selectVal = $('#ab_chooser1 :selected').val();
		var selectText = $('#ab_chooser1 :selected').text();
				
		var lookupkey = selectVal + '-' + item + '-' + socket;
		
		var socketDisplay = "<b>Socket " + socket +"</b>";
		if(socket == 0) {
			socketDisplay = "<b>Base Item</b>";
		} 
		
		if(selectText.length > 0) {
		
			if( $('#item'+lookupkey).length > 0 ) {
				var current = $('#item'+lookupkey).val();
				$('#item'+lookupkey).val(parseInt(current)+delta);
			} else {
				$('#item'+item+"div").append('<tr id="update_'+UN+'">'+
						'<td>'+socketDisplay+'</td>'+
						'<td>'+
						'<input type="hidden" name="input_type_'+UN+'" value="skill"/>'+
						'<input type="hidden" name="ability_id_'+UN+'" value="'+selectVal+'"/>'+
						'<input type="hidden" name="item_id_'+UN+'" value="'+item+'"/>'+			
						'<input type="hidden" name="socket_id_'+UN+'" value="'+socket+'"/>'+			
						selectText+'</td>'+
						'<td>x<input type="text" size="3" name="quantity_'+UN+'" value="'+delta+'" id="item'+lookupkey+'"/></td>'+
						'<td><button onClick="deleteSkill('+UN+')">X</button></td>'+
						'</tr>');
				UN++;
			}
		}
	}
	
	function removeSkill(event, row_id, ability, qty, item) {
		event.preventDefault();
		$('#item'+item+"div").append('<tr id="update_'+UN+'"><td>'+
			'<input type="hidden" name="input_type_'+UN+'" value="delete"/>'+
			'<input type="hidden" name="row_id_'+UN+'" value="'+row_id+'"/>'+
			'Deleting ' + ability + '</td>'+
			'<td>x' + qty +'</td>'+
			'<td><button onClick="deleteSkill('+UN+')">X</button></td>'+
			'</tr>');
		UN++;
		buildMath();
	}
	
	function deleteSkill(id) {
		$('#update_'+id).remove();
	}
	
</script>


<table>
<tr>
<td style="vertical-align:top;">

<h2 id="CharName"><?php 
	echo $character['Character']['name']; 
	echo ' (';
	echo $character['Character']['cardnumber']; 
	echo ') ';
?></h2>

<div id="CardOptions">
<ul>
<?php 
	echo '<li>';
	echo $this->Html->link('Enter Card Data',array('controller' => 'cards', 'action' => 'page1', $character['Character']['cardnumber']));
	echo '</li><li>';
	echo $this->Html->link('View Character',array('controller' => 'characters', 'action' => 'view', $character['Character']['cardnumber']));
	echo '</li>';
?>
</ul>
</div>

</td>
<td style="vertical-align:top;">

<H2>Manage Bonded Items</H2>
filter:<input type="text" id="filter1"/>
<br/>
All Skills (Not all skills are legal in bonded items)
<br/>
<select size="10" id="ab_chooser1">
<?php 
	foreach ($available as $text) {
		$row = explode('!',$text);
		
		echo '<option value="'.$row[0].'">'.$row[1].'</option>';
	}
?>
</select>

<br/>
<button id="add_skill" onClick="addSkill()">Add Skill</button>
<select id="slot_number">
	<option value="1">Item 1</option>
	<option value="2">Item 2</option>
	<option value="3">Item 3</option>
</select>
<select id="socket_number">
	<option value="0">Directly on Item</option>
	<option value="1">Socket 1</option>
	<option value="2">Socket 2</option>
	<option value="3">Socket 3</option>
	<option value="4">Socket 4</option>
	<option value="5">Socket 5</option>
	<option value="6">Socket 6</option>
</select>

</td>
<td style="vertical-align:top;">
<span id="options1"> </span>


</td>
<td style="vertical-align:top;">
<H2>Current Bonded Items</H2>

<form method="post" action="/cards/savebonded" id="updates">
<input type="hidden" name="characterid" value="<?php echo h($character['Character']['id']); ?>"/>
<input type="hidden" name="cardnumber" value="<?php echo h($character['Character']['cardnumber']); ?>"/>
<input type="hidden" name="playerid" value="<?php echo h($character['Player']['id']); ?>"/>

<H3>Item 1</H3>

<?php if($item1id > 0) {
	echo '<input type="hidden" name="item1id" value="' .$item1id .'"/>';
} ?>

Name: <input type="text" name="item1name" value="<?php echo $item1name?>"/>
<div id="item1div">
</div>
<div id="item1existing">
<?php 	
	echo '<table>';
	
	$current_socket = -1;

	foreach ($item1_abilities as $row) {
		if($row['ia']['socket_number'] != $current_socket) {
			$current_socket = $row['ia']['socket_number'];
			
			if($current_socket == 0) {
				echo '<tr><td><b>Base Item</b></td>';
			} else {
				echo '<tr><td><b>Socket '.$current_socket.'</b></td>';
			}
		} else {
			echo '<tr><td></td>';
		}
		echo '<td>'.$row['ia']['qty'].'</td>';
		echo '<td>'.$row['a']['ability_name'].'</td>';
		echo '<td>'.$row['a']['display_name'].'</td>'; 
		echo '<td>';

		$arguments  = "'".$row['ia']['id']."'";
		$arguments .= ",'".$row['a']['ability_name']."'";
		$arguments .= ",'".$row['ia']['qty']."'";
		$arguments .= ",'1'";

		echo '<button onClick="removeSkill(event,';
		echo $arguments;
		echo ')">Delete</button>';

		echo '</td>';
		echo '</tr>';
		echo "\n";
	}
	
	echo '</table>';
?>
</div>

<H3>Item 2</H3>

<?php if($item2id > 0) {
	echo '<input type="hidden" name="item2id" value="' .$item2id .'"/>';
} ?>

Name: <input type="text" name="item2name" value="<?php echo $item2name?>"/>
<div id="item2div">
</div>
<div id="item2existing">
<?php 	
	echo '<table>';

	$current_socket = -1;

	foreach ($item2_abilities as $row) {
		if($row['ia']['socket_number'] != $current_socket) {
			$current_socket = $row['ia']['socket_number'];
			
			if($current_socket == 0) {
				echo '<tr><td><b>Base Item</b></td>';
			} else {
				echo '<tr><td><b>Socket '.$current_socket.'</b></td>';
			}
		} else {
			echo '<tr><td></td>';
		}		
		echo '<td>'.$row['ia']['qty'].'</td>';
		echo '<td>'.$row['a']['ability_name'].'</td>';
		echo '<td>'.$row['a']['display_name'].'</td>'; 
		echo '<td>';

		$arguments  = "'".$row['ia']['id']."'";
		$arguments .= ",'".$row['a']['ability_name']."'";
		$arguments .= ",'".$row['ia']['qty']."'";
		$arguments .= ",'2'";

		echo '<button onClick="removeSkill(event,';
		echo $arguments;
		echo ')">Delete</button>';

		echo '</td>';
		echo '</tr>';
		echo "\n";
	}
	
	echo '</table>';
?>
</div>

<H3>Item 3</H3>

<?php if($item3id > 0) {
	echo '<input type="hidden" name="item3id" value="' .$item3id .'"/>';
} ?>

Name: <input type="text" name="item3name" value="<?php echo $item3name?>"/>
<div id="item3div">
</div>
<div id="item3existing">
<?php 	
	echo '<table>';

	$current_socket = -1;

	foreach ($item3_abilities as $row) {
		if($row['ia']['socket_number'] != $current_socket) {
			$current_socket = $row['ia']['socket_number'];
			
			if($current_socket == 0) {
				echo '<tr><td><b>Base Item</b></td>';
			} else {
				echo '<tr><td><b>Socket '.$current_socket.'</b></td>';
			}
		} else {
			echo '<tr><td></td>';
		}		
		echo '<td>'.$row['ia']['qty'].'</td>';
		echo '<td>'.$row['a']['ability_name'].'</td>';
		echo '<td>'.$row['a']['display_name'].'</td>'; 
		echo '<td>';

		$arguments  = "'".$row['ia']['id']."'";
		$arguments .= ",'".$row['a']['ability_name']."'";
		$arguments .= ",'".$row['ia']['qty']."'";
		$arguments .= ",'3'";

		echo '<button onClick="removeSkill(event,';
		echo $arguments;
		echo ')">Delete</button>';

		echo '</td>';
		echo '</tr>';
		echo "\n";
	}
	
	echo '</table>';
?>
</div>

<input type="submit" name="submit" value="Save Bonded Items"/>
</form>

</td>
</tr>
</table>

<script type="text/javascript">
	// filterByText found in eodra.js
	$("#ab_chooser1").filterByText($("#filter1"),false);
</script>
