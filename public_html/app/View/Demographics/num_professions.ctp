<!-- <div id="basic_facts">
	<table>
		<tr><td>Highest Strength</td><td><?php echo $max_strength; ?></td></tr>
		<tr><td>Highest Weapon Proficiency</td><td><?php echo $max_proficiency; ?></td></tr>
		<tr><td>Highest Forgery</td><td><?php echo $max_forgery; ?></td></tr>
	</table>
</div>
-->

<div id="title">Proportion of (Lower) Lists known, by type<br></div>
<div id="graph"></div>
<!--
<script type="text/javascript">

	var data = [<?php
	        $total = 0;
		foreach($professions as $datum) {
			$profession = $datum['a']['list'];
			$count = $datum[0]['count'];
			echo '{"label":"'.$profession.'","value":'.$count.'},';
			$total = $total + $count;
		}
	?>];

	var w = 1000, //width
	h = 500, //height
	r = 250, //radius
	key_offset = 100, //distance from LHS to key
	key_toppad = 30,  //distance from top to key
	color = d3.scale.category20c(); //builtin range of colors

	 
	var vis = d3.select("#graph")
		.append("svg:svg") //create the SVG element inside the <body>
		.data([data]) //associate our data with the document
		.attr("width", "100%") //set the width and height of our visualization (these will be attributes of the <svg> tag
		.attr("height", "100%")
		.append("svg:g") //make a group to hold our pie chart
		.attr("transform", "translate(" + r + "," + r + ")") //move the center of the pie chart from 0, 0 to radius, radius
	 
	var arc = d3.svg.arc() //this will create <path> elements for us using arc data
	.outerRadius(r);
	 
	var pie = d3.layout.pie().sort(null) //this will create arc data for us given a list of values
	.value(function(d) { return d.value; }); //we must tell it out to access the value of each element in our data array
	 
	var arcs = vis.selectAll("g.slice") //this selects all <g> elements with class slice (there arent any yet)
	.data(pie) //associate the generated pie data (an array of arcs, each having startAngle, endAngle and value properties)
	.enter() //this will create <g> elements for every "extra" data element that should be associated with a selection. The result is creating a <g> for every object in the data array
	.append("svg:g") //create a group to hold each slice (we will have a <path> and a <text> element associated with each slice)
	.attr("class", "slice"); //allow us to style things in the slices (like text)
	 
	arcs.append("svg:path")
	.attr("fill", function(d, i) { return color(i); } ) //set the color for each slice to be chosen from the color function defined above
	.attr("d", arc); //this creates the actual SVG path using the associated data (pie) with the arc drawing function

	//As these labels are cluttering our pie somewhat, lets add a separate key.

	arcs.append("svg:text") //add a label to each slice
	.attr("transform", function(d,i) { //set the label's origin to the center of the arc
	//we have to make sure to set these before calling arc.centroid
	return "translate(" + (r+key_offset) + "," + (key_toppad - r +(i*40)) + ")"; //this gives us a pair of coordinates like [50, 50]
	})
	.attr("text-anchor", "left") //center the text on it's origin
	.text(function(d, i) { return data[i].label+ " - " + Math.round(data[i].value * 100 /<?php echo $total; ?>) + "% "; }); //get the label from our original data array
	
	arcs.append("svg:rect") //add a label to each slice
	.attr("width", 20)
	.attr("height", 20)
	.attr("transform", function(d,i) { 
	//we have to make sure to set these before calling arc.centroid
	return "translate(" + (r+key_offset-30) + "," + (key_toppad - r +(i*40)-20) + ")"; //this gives us a pair of coordinates like [50, 50]
	})
	.attr("fill", function(d, i) { return color(i); } ); //set the color for each slice to be the same as that of its corresponding slice.

</script>
-->
