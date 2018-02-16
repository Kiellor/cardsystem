<!-- File: /app/View/Elists/edit.ctp -->

<?php echo $this->Html->link(
    'Back to All Lists',
    array('controller' => 'elists', 'action' => 'index')
); ?>
<br/>
<?php echo $this->Html->link(
    'Edit Order and Pre-Requisites',
    array('controller' => 'elists', 'action' => 'newedit', $elistid )
); ?>

<h1><?php echo h($elist['Elist']['list_name']); ?></h1>

<p><?php echo h($elist['ListType']['name']); ?></p>

<table>
<tr>
<td style="vertical-align:top;">

<table id="skill_table">
<tr><th>ID</th><th>Skill Name</th><th>Build</th><th>Pre-Requisite</th><!-- <th>Order</th> --><th>Delete</th></tr>
<?php foreach ($elist as $la): ?>
<tr id="row_<?php echo $la['la']['id']; ?>">
	<td><?php 
		echo $la['a']['id']; ?>
	<td><?php 
		echo $la['a']['ability_name']; ?> <?php echo $la['ao']['ability_name']; ?></td>
	<td><?php echo $la['la']['build_cost']; ?></td>
	<td><?php echo $la['la']['prerequisites']; ?></td>
	<td><button id="add_skill" onClick="deleteSkill(<?php echo $la['la']['id']; ?>)">X</button></td>
</tr>
<?php endforeach; ?>
<?php unset($la); ?>
</table>

</td><td style="vertical-align:top;">

filter:<input type="text" id="filter"/>
<br/>
<select size="10" id="ab_chooser1">
<?php 
	foreach (array_keys($abilities) as $key) {
		echo '<option value="'.$key.'">'.$abilities[$key].'</option>';
	}
?>
</select>

<div>Note: Only pick an option if it will be the only<br/>option available to characters with this list</div>

<div id="options1"> </div>
<br/>build cost: <input type="text" id="build_cost" size="3"/>
<br/>prereqs: <input type="text" id="prereq" size="40"/>
<br/><button id="add_skill" onClick="addSkill()">Add Skill</button>

</td></tr></table>

<script type="text/javascript">
	function move(id,delta) {
		$.get('/list_ability/reorder/'+id+'/'+delta, function(data) {
			var message = data;
			var row = $('#row_' + id);
			if(delta > 0) {
				row.insertAfter(row.next());
			} else {
				row.insertBefore(row.prev());
			}
		});	
	}

	function deleteSkill(id) {
		$.get('/list_ability/delete/'+id, function(data) {
			var message = data;
			$('#row_' + id).remove();
		});		
	}

	function addSkill() {
		// assign the value to a variable, so you can test to see if it is working
		var selectVal = $('#ab_chooser1 :selected').val();
		var selectText = $('#ab_chooser1 :selected').text();
		var buildCost = $('#build_cost').val();
		var preReq = $('#prereq').val();
		if($('#option_chooser1 :selected').length > 0) {
			var selectOptionVal = $('#option_chooser1 :selected').val();
			var selectOptionText = $('#option_chooser1 :selected').text();
		} else {
			var selectOptionVal = 0;
			var selectOptionText = "";
		}
		
		if(preReq.length == 0) {
			preReq = "none";
		}
		
		var args = new Array();
		args[0] = '<?php echo $elistid; ?>';
		args[1] = selectVal;
		args[2] = buildCost;
		args[3] = preReq;
		args[4] = selectOptionVal;
		
		$.get('/list_ability/add/'+args.join("/"), function(data) {
			var newId = data;
			if(selectText.length > 0) {
				$('#skill_table').find('tbody')
					.append('<tr id="row_'+newId+'">'+
						'<td>'+selectText+' '+selectOptionText+'</td>'+
						'<td>'+buildCost+'</td>'+
						'<td>'+preReq+'</td>'+				
						//'<td><button onClick="move('+newId+',-1)">Up</button><button onClick="move('+newId+',1)">Down</button></td>'+
						'<td><button onClick="deleteSkill('+newId+')">X</button></td>'+
						'</tr>');
			}
		});
		
		
	}
	
	// filterByText found in eodra.js
	$("#ab_chooser1").filterByText($('#filter'),true);
	$("#ab_chooser1").change(function() { 
		$("#option_chooser1").remove();

		var ability_option_lookup = <?php echo json_encode($abilities_with_options); ?>;
		var selectVal = $('#ab_chooser1 :selected').val();
		if(ability_option_lookup[selectVal] !== undefined) {
			$("#options1").append('loading '+'/cards/getoptionslist/'+ability_option_lookup[selectVal]);
			$("#options1").load('/cards/getoptionslist/'+ability_option_lookup[selectVal]);
		}
	});
	
</script>

<div id="result">Results</div>