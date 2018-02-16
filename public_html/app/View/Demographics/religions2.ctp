<!-- <div id="basic_facts">
	<table>
		<tr><td>Highest Strength</td><td><?php echo $max_strength; ?></td></tr>
		<tr><td>Highest Weapon Proficiency</td><td><?php echo $max_proficiency; ?></td></tr>
		<tr><td>Highest Forgery</td><td><?php echo $max_forgery; ?></td></tr>
	</table>
</div>
-->

<div id="title">Relative Size of each Religious Sect</div>
<div id="graph"></div>

<script type="text/javascript">

	var data_in = [<?php
				
		$total = 0;
		foreach($religion as $datum) { //now spit out the rest of the array.
			$god = $datum['a']['religion'];
			$count = $datum[0]['count'];
			echo '{"label":"'.$god.'","value":'.$count.',"start":'.$total.',"end":'.($total + $count).'},';
			$total = $total + $count;
		}
		
		//Test line: leave commented.
		//echo '{"label":"'.'Secular'.'","value":'.($religious_folks).'},';
	?>];
	
	
	//a few useful variables:
	//the inner radius of the pie chart
	var pie_r_inner = 150;
	//the outer radius of the pie char
	var pie_r_outer = 250;
	//d3s build-in colour palette.
	var colour = d3.scale.category20c();
	//total entries.
	var total_entries = <?php echo $total ?>;
	//our scale for mapping values to angles.
	var my_scale = d3.scale.linear().domain([0, total_entries]).range([0, 2 * Math.PI]);
	//datapane width and height
	var dp_w = 180;
	var dp_h = 150;
	//datapane border thickness
	var dp_b_t = 5;

	//the arc "class"
	var arc = d3.svg.arc()
		.innerRadius(pie_r_inner)
		.outerRadius(pie_r_outer)
		.startAngle(function(d){return my_scale(d.start);})
		.endAngle(function(d){return my_scale(d.end);});
	
	//the tooltip div (hidden, for now.)
	var tooltip = d3.select("#graph")
		.append("div")
		.style("position", "absolute")
		.style("z-index", "10")
		.style("visibility", "hidden")
		.text("Banana")

	//Add the svg element. (this just enters <svg></svg> into the page)
	var my_svg = d3.select("#graph")
		    .append("svg")
		    .attr("id", "svg-frame")
	//Set the width and height of this frame (Geoff, this ones for you!)
		    .attr("width",  "100%")
		    .attr("height", "100%")
		    .data(data_in);		     

	//the indicator dial
	var dial = d3.select("#svg-frame")
		     .append("g")
		     .attr("id", "indicator_dial")
		     .attr("transform", function(d,i) { 
				return "translate(" + (pie_r_outer) + "," + (pie_r_outer) + ")"; 
			})
		     .append("line")
		     .attr("x1", 0)
		     .attr("y1", 0)
		     .attr("x2", 0)
		     .attr("y2", 0)
		     .attr("stroke", "gray")
		     .attr("stroke-width", "2");

	//add svg separators for each arc entry
	var arcs = d3.select("#svg-frame")
		    .selectAll("#pie_arc")
		    .data(data_in)
		    .enter()
		    .append("g")
		    .attr("id", function(d, i) { return "entry_"+i; } )
		    .attr("class", "pie_arc")
		    .attr("transform", function(d,i) { 
				return "translate(" + (pie_r_outer) + "," + (pie_r_outer) + ")"; 
			})
		//add arcs
		    .append("path")
		    .attr("d", arc)
		//fill arcs
		    .attr("fill", function(d, i) { return colour(i); } )
		//add tooltip (this is a bit cheesy at the moment)
		    //.on("mouseover", function(d){return tooltip.style("visibility", "visible").text(d.label);})
		    .on("mousemove", function(){return tooltip.style("top", (event.pageY-10-pie_r_outer)+"px").style("left",(event.pageX+10+pie_r_outer)+"px");})
		    .on("mouseout", function(){return tooltip.style("visibility", "hidden");});

	//the data pane border
	var databorder = d3.select("#svg-frame")
	//first, add the border.
			.append("g")
			.attr("id", "datapane")
			.attr("transform", function(d,i) { 
				return "translate(" + (pie_r_outer) + "," + (pie_r_outer) + ")"; 
			})
			.append("rect")
			.attr("x", (0-(0.5 * (dp_w+dp_b_t))))
			.attr("y", (0-(0.5 * (dp_h+dp_b_t))))
			.attr("width", (dp_w+dp_b_t))
			.attr("height", (dp_h+dp_b_t))
			.attr("rx", 10)
			.attr("ry", 10)
			.attr("fill", "#ffffff");
			
	//the data pane (should sit neatly in the middle of the pie)
	var datapane = d3.select("#svg-frame")
			.append("g")
			.attr("id", "datapane")
			.attr("transform", function(d,i) { 
				return "translate(" + (pie_r_outer) + "," + (pie_r_outer) + ")"; 
			})
			.append("rect")
			.attr("x", (0-(0.5 * dp_w)))
			.attr("y", (0-(0.5 * dp_h)))
			.attr("width", dp_w)
			.attr("height", dp_h)
			.attr("rx", 10)
			.attr("ry", 10)
			.attr("fill", "#FFFFFF");
	
	//The various text elements of the datapane.
	var dp_title = d3.select("#svg-frame")
			.append("g")
			.attr("id", "dp_title")
			.attr("transform", function(d,i) { 
				return "translate(" + (pie_r_outer) + "," + (pie_r_outer) + ")"; 
			})
			.append("text")
			.attr("font-family","Verdana") //I like Verdana, dont judge me.
			.attr("font-weight","bold")
			.attr("y", (0-(dp_h*0.25*1.5)))
			.attr("fill", "#000000")
			.attr("text-anchor","middle") 
			.text(" ");
			
	var dp_line1 = d3.select("#svg-frame")
			.append("g")
			.attr("id", "dp_l1")
			.attr("transform", function(d,i) { 
				return "translate(" + (pie_r_outer) + "," + (pie_r_outer) + ")"; 
			})
			.append("text")
			.attr("font-family","Verdana") //I like Verdana, dont judge me.
			.attr("y", (0-(dp_h*0.25*0.5)))
			.attr("fill", "#000000")
			.attr("text-anchor","middle") 
			.text("PC Religious Groups");

	var dp_line2 = d3.select("#svg-frame")
			.append("g")
			.attr("id", "dp_l2")
			.attr("transform", function(d,i) { 
				return "translate(" + (pie_r_outer) + "," + (pie_r_outer) + ")"; 
			})
			.append("text")
			.attr("font-family","Verdana") //I like Verdana, dont judge me.
			.attr("y", (0+(dp_h*0.25*0.5)))
			.attr("fill", "#000000")
			.attr("text-anchor","middle") 
			.text(" ");
			
	var dp_line3 = d3.select("#svg-frame")
			.append("g")
			.attr("id", "dp_l3")
			.attr("transform", function(d,i) { 
				return "translate(" + (pie_r_outer) + "," + (pie_r_outer) + ")"; 
			})
			.append("text")
			.attr("font-family","Verdana") //I like Verdana, dont judge me.
			.attr("y", (0+(dp_h*0.25*1.5)))
			.attr("fill", "#000000")
			.attr("text-anchor","middle") 
			.text(" ");
				
	//mouseover on each of the segments does the following:					
		arcs.on("mouseover", function(d,i){
		
			//display the segment title
			dp_title.text(d.label);
			//display the total number in this segment
			dp_line1.text("Total = "+d.value);
			//display this segment as a percentage
			dp_line2.text((d.value*100/total_entries).toFixed(2)+"%");
			//display whatever on this line.
			dp_line3.text(" ");
			//show the tooltip
			tooltip.style("visibility", "visible").text(d.label);
			//change the border colour
			databorder.transition()
				  .attr("fill", colour(i) );
			//move the indicator dial
			dial.transition()
				.attr("stroke", colour(i))
				.attr("x2", pie_r_outer*(Math.sin( my_scale((d.end + d.start)*0.5) )))
				.attr("y2", (0-pie_r_outer)*(Math.cos( my_scale((d.end + d.start)*0.5) )));
		});
	
	//
	
	
<!--
	var w = 1000, //width
	h = 500, //height
	r = 250, //radius
	key_offset = 100, //distance from LHS to key
	key_toppad = 30,  //distance from top to key
	color = d3.scale.category20c(); //builtin range of colors

	 
	var vis = d3.select("#graph")
		.append("svg:svg") //create the SVG element inside the <body>
		.data([data]) //associate our data with the document
		.attr("width", w) //set the width and height of our visualization (these will be attributes of the <svg> tag
		.attr("height", h)
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
	.text(function(d, i) { return data[i].label+ " - " + Math.round(data[i].value *100 / <?php  echo $guff[0][0]['count']; ?>) + "% "; }); //get the label from our original data array
	
	arcs.append("svg:rect") //add a label to each slice
	.attr("width", 20)
	.attr("height", 20)
	.attr("transform", function(d,i) { 
	//we have to make sure to set these before calling arc.centroid
	return "translate(" + (r+key_offset-30) + "," + (key_toppad - r +(i*40)-20) + ")"; //this gives us a pair of coordinates like [50, 50]
	})
	.attr("fill", function(d, i) { return color(i); } ); //set the color for each slice to be the same as that of its corresponding slice.
-->
</script>
