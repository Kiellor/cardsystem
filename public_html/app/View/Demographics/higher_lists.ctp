<!-- <div id="basic_facts">
	<table>
		<tr><td>Highest Strength</td><td><?php echo $max_strength; ?></td></tr>
		<tr><td>Highest Weapon Proficiency</td><td><?php echo $max_proficiency; ?></td></tr>
		<tr><td>Highest Forgery</td><td><?php echo $max_forgery; ?></td></tr>
	</table>
</div>
-->

<div id="title">Proportion of (Higher) Lists known, by type<br></div>
<div id="graph"></div>

<script type="text/javascript">

	var data = [<?php
	        $total = 0;
	        $num_entries = 0;
		foreach($professions as $datum) {
			$profession = $datum['a']['list'];
			$count = $datum[0]['count'];
			echo '{"label":"'.$profession.'","value":'.$count.'},';
			$total = $total + $count;
			$num_entries = $num_entries + 1;
		}
	?>];

	var w = 1000, //width
	h = 500, //height
	r = 250, //radius
	key_offset = 100, //distance from LHS to key
	key_toppad = 20,  //distance from top to key
	key_entry_height = 40, //height of each entry
	color = d3.scale.category20c(), //builtin range of colors
	
	databoxheight = 100,
	
	leftheight = (2 * r) + databoxheight + 60,
	rightheight = (<?php echo $num_entries ?> * key_entry_height) + key_toppad + 18;
	
	var totalheight = 0;
	if(leftheight > rightheight){
	  totalheight = leftheight;
	}
	else{
	  totalheight = rightheight;
	}
	 
	var vis = d3.select("#graph")
		.append("svg:svg") //create the SVG element inside the <body>
		.data([data]) //associate our data with the document
		//.attr("width", w) //set the width and height of our visualization (these will be attributes of the <svg> tag)
		.attr("width", "100%")
		//.attr("height", h)
		.attr("height", totalheight)
		.append("svg:g") //make a group to hold our pie chart
		.attr("transform", "translate(" + r + "," + r + ")") //move the center of the pie chart from 0, 0 to radius, radius

        var framewidth = 4;
	
	var dataframe = d3.select("svg")
	        .append("svg:rect") //make a group to hold it. (Frame.)
	        .attr("width", (2*r - 20))
	        .attr("height", databoxheight)
	        .attr("transform", "translate(" + (20) + "," + ((2*r) + 20) + ")")
	        .attr("fill", "skyblue");
	        
	var dataread = d3.select("svg")
	        .append("svg:rect") //make a group to hold it.
	        .attr("width", (2*r - 20 - (2*framewidth)))
	        .attr("height", (databoxheight - (2*framewidth)))
	        .attr("transform", "translate(" + (20+ framewidth) + "," + ((2*r) + 20 + framewidth) + ")")
	        .attr("fill", "white");



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
	.attr("d", arc) //this creates the actual SVG path using the associated data (pie) with the arc drawing function
	.on("mouseover", function(){
	  dataread.transition()
	          .attr("fill", function(i) { return color(i); } )
	          
	          .delay(0)
                  .duration(2000)
	});
	
	//As these labels are cluttering our pie somewhat, lets add a separate key.

	arcs.append("svg:text") //add a label to each slice
	.attr("transform", function(d,i) { //set the label's origin to the center of the arc
	//we have to make sure to set these before calling arc.centroid
	return "translate(" + (r+key_offset) + "," + (key_toppad - r +(i*key_entry_height) + 18) + ")"; //this gives us a pair of coordinates like [50, 50]
	})
	.attr("text-anchor", "left") //center the text on it's origin
	.text(function(d, i) { return data[i].label+ " - " + Math.round(data[i].value * 100 /<?php echo $total; ?>) + "% "; }); //get the label from our original data array
	
	arcs.append("svg:rect") //add a label to each slice
	.attr("width", 20)
	.attr("height", 20)
	.attr("transform", function(d,i) { 
	//we have to make sure to set these before calling arc.centroid
	return "translate(" + (r+key_offset-30) + "," + (key_toppad - r +(i*key_entry_height)) + ")"; //this gives us a pair of coordinates like [50, 50]
	})
	.attr("fill", function(d, i) { return color(i); } ); //set the color for each slice to be the same as that of its corresponding slice
	
	        
</script>
