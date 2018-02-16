<!-- <div id="basic_facts">
	<table>
		<tr><td>Highest Strength</td><td><?php echo $max_strength; ?></td></tr>
		<tr><td>Highest Weapon Proficiency</td><td><?php echo $max_proficiency; ?></td></tr>
		<tr><td>Highest Forgery</td><td><?php echo $max_forgery; ?></td></tr>
	</table>
</div>
-->

<div id="title">De-bugger<br></div>
[<?php print_r ($professions) ?>]
<div id="graph"></div>

<script type="text/javascript">
<!--
	var data_in = [<?php
	        $total = 0;
		foreach($professions as $datum) {
			$profession = $datum['a']['list'];
			$count = $datum[0]['count'];
			echo '{"label":"'.$profession.'","value":'.$count.'},';
			$total = $total + $count;
		}
	?>];



	//Add the svg element. (this just enters <svg></svg> into the page)
	var my_svg = d3.select("#graph")
		    .append("svg")
		    .attr("id", "svg-frame")
	//Set the width and height of this frame (Geoff, this ones for you!)
		    .attr("width",  "100%")
		    .attr("height", "100%")
		    .data(data_in);	
		    
	//add svg separators for each arc entry
	var arcs = d3.select("#svg-frame")
		    .selectAll("g")
		    .data(data_in)
		    .enter()
		    .append("g")
		    .attr("transform", function(d,i) { 
				return "translate(" + 20 + "," + 20*(i+1) + ")"; 
				})
		    .append("text")
		    .text(function(d) {return d.label+" "+d.value;} );
-->
</script>
