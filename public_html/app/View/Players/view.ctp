<!-- File: /app/View/Players/view.ctp -->

<div id="pageheading">
<h1>Account Information</h1>
<h2>Your Player Number is <?php echo $player['Player']['cardnumber_prefix'] ?></h2>
</div>

<div id="pagecontent">

	<div id="player-name">Player Name: <?php 
		$name = $player['Player']['name']; 
		
		echo '<span id=playernamedisplay>';
		echo $this->Html->link($name,array('controller' => 'players', 'action' => 'view', $player['Player']['id']));
		echo '</span>';

		echo ' (';
		echo $player['Player']['cardnumber_prefix'];
		echo ')';
	
		if(AuthComponent::user('role_admin') || AuthComponent::user('role_cards') || AuthComponent::user('role_newplayermarshal') || AuthComponent::user('role_logistics')) {
			echo ' <button onclick="editplayername()">edit</button>'; 
		}
	?></div>

	<div id="player-name-edit">
		Update Player Name:<input size="40" id="newplayername" type="text" name="data[Player][name]" value="<?php echo $name ?>"/>
		<button id="saveplayername">Submit</button>
	</div>
	
	<script type="text/javascript">
		$("#saveplayername").click(function(){
				$.ajax({
					url: '/players/changename/<?php echo $player['Player']['id']; ?>',
					type: 'POST',
					data: JSON.stringify({value: $("#newplayername").val()}),
					dataType: "json",
					contentType: "application/json",
					cache: false,
					success: function() { 
						$("#playernamedisplay > a").empty();
						$("#playernamedisplay > a").append($("#newplayername").val()); 
						$("#player-name-edit").hide();
					}
				});
		});
	</script>


<?php
	if(AuthComponent::user('role_admin') || AuthComponent::user('role_cards') || AuthComponent::user('role_newplayermarshal') || AuthComponent::user('role_logistics')) {
		echo '<div>Login Name / Email: ';
		
		if(isset($user)) { 
			echo $user['User']['username']; 
			echo '</div>';
			echo '<div><button onclick="changeemail()">change email address</button>'; 
			echo '<button onclick="resetplayer('."'".$user['User']['username']."'".')">reset password</button></div>'; 
			
			?>
				<div style="display:none;">
					<form id="UserResetForm" accept-charset="utf-8" method="post" action="/users/reset">
					<input type="hidden" name="data[User][username]" value="<?php echo $user['User']['username']; ?>"/>
					</form>
				</div>
				
				<div id="changeemail">
					<form id="PasswordChange" accept-charset="utf-8" method="post" action="/users/changeemail">
						<input type="hidden" name="data[User][username]" value="<?php echo $user['User']['username']; ?>"/>
						New Email Address:<input type="text" name="data[User][newusername]"/>
						<button>Submit</button>
					</form>
				</div>
			<?php
		} else { 
			echo '<a href="/users/add/'.$player['Player']['id'].'">Enter Email</a>'; 
			echo '</div>';
		}

		echo $this->Html->link("Start New Character",array('controller' => 'characters', 'action' => 'add', $player['Player']['cardnumber_prefix']));
	} else {
		echo '<div>Login Name (Email): ';
			
			if(isset($user)) { 
				echo $user['User']['username']; 
			} else { 
				echo 'No Email Registered'; 
			}
		echo '</div>';
	}
?>

<?php /*
<div id="birthday-display">
	<a class="edit-birth">Year of Birth: <?php echo $player['Player']['birth_year'] ?></a><br/>
	<a class="edit-birth">Month of Birth: <?php echo $player['Player']['birth_month'] ?></a><br/>
</div>
*/ ?>

<div id="birthday-edit">
	<?php
	/*
		echo $this->Form->create();
	
		echo 'Year of Birth: ';
		
		echo 'Month of Birth: ';
		
		$months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		
		echo $this->Form->input('birth_month', 
			array(
				'options' => $months, 
				'empty' => '(choose one)',
				'selected' => echo $player['Player']['birth_month']
		));
	*/ ?>
</div>

	<script type="text/javascript">
	
		$("#changeemail").hide();
		$("#player-name-edit").hide();
	
		function resetplayer($username) {
			var r=confirm("Reset password for " + $username +"?");
			if (r==true) {
				$("#UserResetForm").submit();
			} 
		}
		
		function changeemail() {
			$("#changeemail").show();
		}

		function editplayername() {
			$("#player-name-edit").show();
		}

	</script>


	<div id="characterlist">
		<?php foreach ($player['Character'] as $character): ?>
			
		<div>		
			<H3><?php echo $this->Html->link($character['name'] . ' (' . $character['cardnumber'] .')',array('controller' => 'characters', 'action' => 'view', $character['cardnumber'])); ?></H3>
			<div id="CardOptions">
				<ul>
				<li><?php echo $this->Html->link('View Character Sheet',array('controller' => 'characters', 'action' => 'view', $character['cardnumber'])); ?></li>			
				</ul>
			</div>
			Character Memo -- Appears on Character Sheet<br/>
			<textarea rows="3" cols="80" 
				name="memo<?php echo $character['cardnumber']; ?>" 
				id="memo<?php echo $character['cardnumber']; ?>"><?php echo h($character['character_memo']); ?></textarea><br/>
				
			<button name="savememo<?php echo $character['cardnumber']; ?>" id="savememo<?php echo $character['cardnumber']; ?>">Save Memo</button><span id="memo<?php echo $character['cardnumber']; ?>_status"> </span>
				<script type="text/javascript">
			
					$("#memo<?php echo $character['cardnumber']; ?>").bind('keyup input', function() { $("#memo<?php echo $character['cardnumber']; ?>_status").empty(); $("#memo<?php echo $character['cardnumber']; ?>_status").append("changed");} );
			
					$("#savememo<?php echo $character['cardnumber']; ?>").click(function(){
						$.ajax({
							url: '/players/savememo/<?php echo $player['Player']['id']; ?>/<?php echo $character['cardnumber']; ?>',
							type: 'POST',
							data: JSON.stringify({value: $("#memo<?php echo $character['cardnumber']; ?>").val()}),
							dataType: "json",
							contentType: "application/json",
							cache: false,
							success: function() { $("#memo<?php echo $character['cardnumber']; ?>_status").empty(); $("#memo<?php echo $character['cardnumber']; ?>_status").append("saved"); }
						});
					});
			
				</script>
		</div>

		<?php endforeach; ?>
		<?php unset($character); ?>
	</div>
	
	<div>		
		<H4>Medical Notes</H4>
		Appears on Character Sheet<br/>
		<textarea rows="3" cols="80" name="medical" id="mednotes"><?php echo h($player['Player']['medical_notes']); ?></textarea><br/>
		</div>
		
		<button name="savemednotes" id="savemednotes">Save Medical Notes</button><span id="mednotes_status"> </span>
		<script type="text/javascript">
	
			$("#mednotes").bind('keyup input', function() { $("#mednotes_status").empty(); $("#mednotes_status").append("changed");} );
	
			$("#savemednotes").click(function(){
				$.ajax({
					url: '/players/savemednotes/<?php echo $player['Player']['id']; ?>',
					type: 'POST',
					data: JSON.stringify({value: $("#mednotes").val()}),
					dataType: "json",
					contentType: "application/json",
					cache: false,
					success: function() { $("#mednotes_status").empty(); $("#mednotes_status").append("saved"); }
				});
			});
	
	</script>
	
</div>
	
