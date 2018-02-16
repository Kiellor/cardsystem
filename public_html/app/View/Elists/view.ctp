<!-- File: /app/View/Elists/view.ctp -->

<?php echo $this->Html->link(
    'List',
    array('controller' => 'lists', 'action' => 'index')
); ?>

<h1><?php echo h($elist['Elist']['list_name']); ?></h1>

<p><?php echo h($elist['Elist']['description']); ?></p>

<p><?php echo h($elist['ListType']['name']); ?></p>

<table>
<tr><th>Skills</th><th>Build</th><th>Pre-Requisite</th></tr>
<?php foreach ($elist['ListAbility'] as $la): ?>
<tr>
	<td><?php 
		echo $abilities[$la['ability_id']]; ?></td>
	<td><?php echo $la['build_cost']; ?></td>
	<td><?php echo $la['prerequisites']; ?></td>
</tr>
<?php endforeach; ?>
<?php unset($la); ?>
</table>

