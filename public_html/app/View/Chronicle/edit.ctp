<script>
tinymce.init({
    selector: "textarea#entry",
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
		
		?>updated <span class="timeago" title="<?php echo $chron['c']['modified'] ?>"></span></h4> <?php

		echo '<div style="border:solid black 1px; background-color:antiquewhite;" id="chronicle-'.$chron['c']['id'].'">'."\n";
		
		
		if($chron['c']['submitted'] == 0) {
			if(strlen($chron['c']['comments']) > 0) {
				echo 'Comments From Atmosphere<div>'.$chron['c']['comments'].'</div>';
			}
		?>
			<form method="post" action="/chronicle/update/<?php echo $character['Character']['cardnumber']; ?>/<?php echo $chron['c']['id']; ?>">
				<textarea name="entry" id="entry"><?php echo $chron['c']['entry']; ?></textarea>
				<button name="edit">Update</button>
			</form>
		<?php
		} else {
			echo '<div>'.$chron['c']['entry'].'</div>';
			echo '<div>'.$chron['c']['comments'].'</div>';
		} 
		echo "\n".'</div>';
	}
	
?>

<script type="text/javascript">
	$(document).ready(function() {
		$(".timeago").timeago();
	});
</script>