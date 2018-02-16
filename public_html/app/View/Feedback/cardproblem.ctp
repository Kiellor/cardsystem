<!-- File: /app/View/Feedback/cardproblem.ctp -->

<script>
tinymce.init({
    selector: "textarea#feedback",
    width: 500,
    height: 300,
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

<h1>Report a Problem</h1> 

<div>
<ul>
	<li>Please use this form to send reports of problems with your character card to the appropriate people.</li>
	<li>Please be polite, detailed and specific in your requests.</li>
	<li>A copy of this problem report will be sent to you via email.</li>
</ul>
</div>

<h4>Please remember, we are people too.</h4>

<div>
<p>Character:
<?php 
	echo $this->Html->link($character['Character']['name'],array('controller' => 'characters', 'action' => 'view', $character['Character']['cardnumber']));
	echo ' (';
	echo $character['Character']['cardnumber']; 
	echo ')';
?></p>

<p>Played by:<?php  
	echo $this->Html->link($character['Player']['name'],array('controller' => 'players', 'action' => 'view', $character['Player']['id']));
?></p>
</div>

<div id="problemform">
	<form method="post" action="/feedback/cardproblem/<?php echo $character['Character']['cardnumber']; ?>">
		<textarea id="feedback" name="feedback"></textarea>
		<button name="submit">Submit</button>
	</form>
</div>