<div style="width:100%; font-size:150%; text-align:center">
		Welcome to the Knight Realms Card System!
		<br/><br/>
</div>

<?php if(isset($loggedIn) && $loggedIn == 'true') { ?>
	<div style="width:100%; font-size:150%; text-align:center">
		<?php echo $this->Html->link('My Characters',array('controller' => 'players', 'action' => 'view')); ?>
		| 
		<?php echo $this->Html->link('Between Game Actions',array('controller' => 'personal_action')); ?>
		|
		<a href="/bank/myBusinesses">My Business Ledgers</a>
		<br/><br/>
	</div>
	<div>
		For any issues with your card, please use the Report a Problem link found on your character view.
	</div>

<?php } else { ?>

	<div>
		This system is for the use of Knight Realms players and staff only.  To register to use the system, please write your email address clearly on your character sheet at the next event you attend.
	<br/><br/>
		If you are having trouble logging in, please see the <a href="/pages/faq">FAQ</a> page for ways you can address this.  
	<br/><br/>
		For all other issues, please use the Report a Problem link found on your character view within this system.
	</div>

<?php } ?>

<hr>

<a href="/pages/faq">Questions?  Check the FAQ</a> | <a href="/users/reset">Forgot your password?  Click here!</a>

<hr>