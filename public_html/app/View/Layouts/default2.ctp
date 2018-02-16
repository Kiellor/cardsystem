<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo $this->Html->charset(); ?>
<title>
Knight Realms Cards System
</title>
<?php
		echo $this->Html->meta('icon');
		
		echo $this->Html->css('eodra', null, array('inline' => false));		
		
		echo $this->Html->script('jquery-1.9.1');
		echo $this->Html->script('eodra');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
?>
</head>
<body style="margin:0;padding:0;background-color:black">
<div id="Paper">
 <div id="Header" width="100%" style="margin:0;background:url('/backgrounds/50-paper-tm.png');min-height:50px">
  <div id="UL" style="margin:0;float:left"><img src="/backgrounds/50-paper-tl.png"></div>
  <div id="UR" style="margin:0;float:right"><img src="/backgrounds/50-paper-tr.png"></div>
  <p align="center" style="margin:0;padding:2em 0 0 0">	</p>
 </div>
 <div id="Left-Border" width="100%" style="margin:0;background:url('/backgrounds/50-paper-ml.png');clear:both">
  <div id="Right=Border" width="100%" style="margin:0 0 0 50px;background:url('/backgrounds/50-paper-mr.png');background-position:right top;display:block">
   <div id="Middle" width="100%" style="background:url('/backgrounds/paper-mm.png'); margin:0 50px 0 0;display:block;padding:1em;min-height:75%">
    <center><h1 style="font-family: Papyrus, fantasy;">Knight Realms Cards System</h1></center>
    
    <table><tr>
		<td style="width:205px; padding:10px; border: 1px solid black;">
			<?php 
				echo 'Welcome to '. $this->Html->link('Eodra','/') .'<br/>';
				if(strlen(AuthComponent::user('username')) > 0) {
					echo AuthComponent::user('username').' '.AuthComponent::user('role') .' '.$this->Html->link('Log Out','/users/logout');
				} else {
					echo 'Please '.$this->Html->link('Login','/users/login'). ' or ' . $this->Html->link('Register','/users/add'). ' to continue';
				}

				if(isset($menu)) {
					echo '<hr/>';
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
		</td>
		<td>
			<?php echo $this->Session->flash(); ?>

			<?php echo $this->fetch('content'); ?>
		</td>
		</tr>
	</table>

	 <?php 
		if(AuthComponent::user('role') === 'admin') {
			echo $this->element('sql_dump');
		}

		if(isset($debug)) {
			echo '<br/><hr/>';
			var_dump($debug);
		}
	  ?>
	
   </div>
  </div>
 </div>
 <div id="Footer" width="100%" style="margin:0;background:url('/backgrounds/50-paper-bm.png');clear:both;min-height:50px">
  <div id="BL" style="margin:0;float:left"><img src="/backgrounds/50-paper-bl.png"></div>
  <div id="BR" style="margin:0;float:right"><img src="/backgrounds/50-paper-br.png"></div>
  <p align="center" style="margin:0;padding:1em 0 0 0"> </p>
 </div>
</div>

<?php echo $this->Js->writeBuffer(); ?>

</body>