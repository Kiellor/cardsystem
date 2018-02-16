<html>
<head>
<title>Knight Realms - Online Cards System</title>
<?php
		echo $this->Html->css('mobile-layout', null, array('inline' => false));		
		echo $this->Html->css('mobile-styling', null, array('inline' => false));		

		echo $this->Html->script('angular.min');
		echo $this->Html->script('jquery-1.9.1');
		echo $this->Html->script('eodra');
		echo $this->Html->script('jquery.form');
		echo $this->Html->script('d3.v3.min');
		echo $this->Html->script('/tinymce/js/tinymce/tinymce.min');
		
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
?>

</head>

<body>
<header>
</header>

<nav>
	<?php
		echo $this->Html->link('Card System','/');
	?>
</nav>

<div id="mobilebody">
	<div id="mobilemessages">
		<?php echo $this->Session->flash(); ?>
	</div>
	<div id="mobilecontent">
		<?php echo $this->fetch('content'); ?>
	</div>
</div>

<footer>
	<?php 
		if(AuthComponent::user('role') === 'admin') {
			echo $this->element('sql_dump');

			if(isset($debug)) {
				echo '<br/><hr/>';
				var_dump($debug);
			}
		}
	?>
</footer>

<?php echo $this->Js->writeBuffer(); ?>

</body>