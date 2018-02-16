<script>
tinymce.init({
    selector: "textarea",
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

<h1>Character Chronicle</h1> 

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

<?php 
	
	foreach($chronicles as $chron) {

		if($chron['c']['approved'] == 1) {
			echo 'approved -- ';
		}
		?>
		
		updated <span class="timeago" title="<?php echo $chron['c']['modified'] ?>"></span></h4> <?php

		echo '<div style="border:solid black 1px; background-color:antiquewhite;" id="chronicle-'.$chron['c']['id'].'">'."\n";
		
		
		if($chron['c']['submitted'] == 0) {
		?>
			<ul>
			<li><a href="/chronicle/edit/<?php echo $character['Character']['cardnumber']; ?>/<?php echo $chron['c']['id']; ?>">Edit</a></li>
			<li><a href="/chronicle/submit/<?php echo $character['Character']['cardnumber']; ?>/<?php echo $chron['c']['id']; ?>">Submit for Approval</a> (once you submit you cannot edit again)</li>
			</ul>
		<?php
		} else if($chron['c']['approved'] == 0) {
			if(AuthComponent::user('role_atmosphere')) {
			?>
				<ul>
				<li><a href="/chronicle/comment/<?php echo $character['Character']['cardnumber']; ?>/<?php echo $chron['c']['id']; ?>">Provide Suggestions</a></li>
				<li><a href="/chronicle/approve/<?php echo $character['Character']['cardnumber']; ?>/<?php echo $chron['c']['id']; ?>">Approve</a></li>
				<li><a href="/chronicle/submit/<?php echo $character['Character']['cardnumber']; ?>/<?php echo $chron['c']['id']; ?>/0">Send back to Player</a> (removes it from submitted status)</li>
				</ul>
			<?php			
			} else {
				echo 'Submitted<br/><br/>';
			}
		}
			
		
		echo '<div>'.$chron['c']['entry'].'</div>';
		if(strlen($chron['c']['comments']) > 0) {
			echo '<hr/>Comments From Atmosphere<div>'.$chron['c']['comments'].'</div>';
		}
		echo "\n".'</div>';
	}
	
?>

<div>
<ul>
	<li>Please use this form to enter your character history initially.</li>
	<li>As time goes on additional entries may also be made.</li>
	<li>The Atmosphere Officer and Storytellers along with certain Marshals will have access to this information and may use it to enhance your Knight Realms experience.</li>
	<li>Backstory and History not valid until approved</li>
</ul>
</div>

<div id="chronicleform">
	<form method="post" action="/chronicle/view/<?php echo $character['Character']['cardnumber']; ?>">
		<textarea id="entry" name="entry"></textarea>
		<button name="submit">Submit New Entry</button>
	</form>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$(".timeago").timeago();
	});
</script>