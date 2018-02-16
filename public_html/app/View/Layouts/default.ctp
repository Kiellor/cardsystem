<html>
<head>
<title>Knight Realms - Online Cards System</title>
<?php
		echo $this->Html->css('layout', null, array('inline' => false));		
		echo $this->Html->css('styling', null, array('inline' => false));		
		// echo $this->Html->css('ng-grid.min', null, array('inline' => false));		
		echo $this->Html->css('angular-chart', null, array('inline' => false));		

		echo $this->Html->script('angular.min');
		echo $this->Html->script('angular-sanitize');
		echo $this->Html->script('jquery-2.1.4.min');
		echo $this->Html->script('eodra');
		echo $this->Html->script('jquery.form');
		echo $this->Html->script('d3.min');
		echo $this->Html->script('/tinymce/js/tinymce/tinymce.min');
		echo $this->Html->script('jquery.timeago');
		// echo $this->Html->script('ng-grid-2.0.7.min');
		echo $this->Html->script('angular-nlp-compromise');
		echo $this->Html->script('Chart.min');
		echo $this->Html->script('angular-chart.min');
		
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
?>

<link href='//fonts.googleapis.com/css?family=Tinos' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Milonga' rel='stylesheet' type='text/css'>

</head>

<body>
<div id="black">

<header>
<img src="/images/banner.jpg">
</header>
<div id="woodboard">

<nav>
	<?php
		echo '<p>Welcome to '. $this->Html->link('The Knight Realms Card System','/') .' - ';
		if(strlen(AuthComponent::user('username')) > 0) {
			echo AuthComponent::user('username').' '.AuthComponent::user('role') .' '.$this->Html->link('Log Out','/users/logout');
		} else {
			echo 'Please '.$this->Html->link('Login','/users/login'). ' to continue</p>';
		}
	?>
</nav>

<div id="left">
<div id="paper">
	<?php echo $this->Session->flash(); ?>
	<?php echo $this->fetch('content'); ?>
</div>
</div>

<div id="right">
<div id="sidebar">
	<?php 
		if(isset($menu)) {
			foreach(array_keys($menu) as $key) {
				$submenu = $menu[$key];
				foreach(array_keys($submenu) as $subkey) {
					$sublist = $submenu[$subkey];
					foreach($sublist as $item) {
						if(array_key_exists('link',$item)) {
							echo $this->Html->link($item['display'],$item['link']);
							echo '<br/>';
						} 
					}
				}
				echo '<br/>';
			}
		}
	?>
</div>
</div>

</div> <!-- Woodboard -->

<footer>
<p>Copyright © 1998-Present, Knight Realms Entertainment, LLC. All Rights Reserved.</br>Knight Realms, KR, "A Live Acting Experience," and "Who Will You Be?" are all Trade Marks of Knight Realms Entertainment, LLC.</p>
<p> </p>
	<?php 
		if(AuthComponent::user('role') === 'admin') {
			echo $this->element('sql_dump');
		}

		if(isset($debug)) {
			echo '<br/><hr/>';
			var_dump($debug);
		}
	?>
</footer>

<?php echo $this->Js->writeBuffer(); ?>
</div> <!--#black-->

<?php include_once("googleanalyticstracking.php") ?>

</body>