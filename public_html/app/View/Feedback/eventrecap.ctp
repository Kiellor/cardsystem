<!-- File: /app/View/Feedback/eventrecap.ctp -->

<script>
tinymce.init({
    selector: "textarea",
    width: 500,
    height: 100,
    menubar:false,
    statusbar: false,
    plugins: [
         "pagebreak spellchecker",
         "wordcount nonbreaking",
         "paste"
   ],
   toolbar: "undo redo | bullist numlist outdent indent"
 }); 
</script>

<h1>Event Recap</h1> 

<div>
<ul>
	<li>Please use this form to tell us about you're experience at the most recent event.</li>
	<li>A copy of this feedback will be sent to you via email.</li>
	<li>All of this feedback will be sent to all Knight Realms Officers and the Director.</li>
	<li>Select portions of this feedback may be shared with other Staff members as appropriate at the Officers' discretion</li>
</ul>
</div>

<div>
<p>Player: <?php  
	echo $player['Player']['name'];
?></p>
</div>

<div id="problemform">
	<form method="post" action="/feedback/eventrecap/<?php echo $character['Character']['cardnumber']; ?>">
	
		<h4>Event:</h4>
		<select id="event" name="event">
			<?php 
				foreach (array_keys($events) as $eventid) {
					echo '<option value="'.$events[$eventid].'-'.$eventid.'">'.$events[$eventid].'</option>';
				}
			?>
		</select>
		
		<h4>Cabin you stayed in:</h4>
		<select id="cabin" name="cabin">
			<option></option>
			<option>Baroness Manor / Winterdark</option>
			<option>Armory</option>
			<option>Dragons Claw Inn</option>
			<option></option>
			<option></option>
			<option></option>
			<option></option>
			<option></option>
			<option></option>
			<option value="None">-- Did not stay overnight --</option>
			<option value="Other">-- Other --</option>
		</select>
		
		<div id="cabin-other-div">
			please specify:<br/>
			<input type="text" id="cabin-other" name="cabin-other"/>
		</div>
		
		<script type="text/javascript">
			$('#cabin-other-div').hide();

			$('#cabin').change(function() {
				var selected = $(this).val();
				if(selected == 'Other'){
				  $('#cabin-other-div').show();
				}
				else{
				  $('#cabin-other-div').hide();
				}
			});
		</script>
		
		<h4>Activities this past event (PC and NPC):</h4>
		<textarea id="activities" name="activities"></textarea>
		
		<h4>Are there any skills or lists that you are having trouble finding In-Game?</h4>
		<div>Note: Just because you ask for it, does not mean it will be sent out! The Staff of the game uses this field to be aware of gaps in the lower lists and skills present in the game, and to know if a PC should be watched for a possible higher list. No list or skill, ESPECIALLY higher lists, are guaranteed! Higher lists must be earned through Role-Play! Lower lists will not be denied being sent out, but Role-Play is still a factor (i.e. - dont piss off your teacher).</div>
		<textarea id="skillsneeded" name="skillsneeded"></textarea>

		<h4>My suggestion for Character of the Month</h4>
		<div>Please state why you chose them!</div>
		<textarea id="pcmonth" name="pcmonth"></textarea>

		<h4>My suggestion for NPC of the Month:</h4>
		<div>Please state why you chose them!</div>
		<textarea id="npcmonth" name="npcmonth"></textarea>

		<h4>My suggestion for Player of the Month:</h4>
		<div>Please state why you chose them!</div>
		<textarea id="playermonth" name="playermonth"></textarea>

		<h4>Favorite Quote from the Event:</h4>
		<div>Note: Quotes should be In-Game, and must not be a quote said by your PCs. A good quote is one that doesn't need a setup to explain, and doesn't involve game mechanics. If the staff likes it, your quote may be added to the rotation on the website.</div>
		<textarea id="quote" name="quote"></textarea>

		<h4>Do you have any feedback regarding the Feast?:</h4>
		<textarea id="feast" name="feast"></textarea>

		<h4>What I liked best about the Event:</h4>
		<textarea id="best" name="best"></textarea>

		<h4>What I liked least about the Event:</h4>
		<textarea id="least" name="least"></textarea>

		<h4>What can we do to improve future Events?:</h4>
		<textarea id="improve" name="improve"></textarea>

		<button name="submit">Submit</button>
	</form>
</div>