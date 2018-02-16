<!-- <div id="basic_facts">
	<table>
		<tr><td>Highest Strength</td><td><?php echo $max_strength; ?></td></tr>
		<tr><td>Highest Weapon Proficiency</td><td><?php echo $max_proficiency; ?></td></tr>
		<tr><td>Highest Forgery</td><td><?php echo $max_forgery; ?></td></tr>
	</table>
</div>
-->

<div id="title">Character Races<br></div>
<div id="graph"></div>

<script type="text/javascript">

	var data_in = [<?php
	        $total = 0;
	        $index = 0;
		foreach($races as $datum) {
			$racename = $datum['a']['list'];
			$count = $datum[0]['count'];
			echo '{"label":"'.$racename.'","value":'.$count.',"start":'.$total.',"end":'.($total + $count).',"index":'.$index.'},';
			$total = $total + $count;
		}
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
			.text("PC Races");

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


	
</script>
