<!-- File: /app/View/Elists/index.ctp -->

<h1>Lists</h1>

<ul>
	<?php foreach ($listtypes as $list): ?>
	<li><a href="/elists/index/<?php echo $list['ListType']['id']; ?>"><?php echo $list['ListType']['name']; ?></a>
	<?php endforeach; ?>
    <?php unset($list); ?>
</ul>

<?php

if(isset($elists)) {
?>
<table>
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Type</th>
        <th>Count</th>
    </tr>

    <?php foreach ($elists as $list): ?>
    <tr>
        <td><?php echo $list['l']['id']; ?></td>
        <td>
            <?php echo $this->Html->link($list['l']['list_name'],array('controller' => 'elists', 'action' => 'edit', $list['l']['id'])); ?>
        </td>
        <td><?php echo $list['lt']['name']; ?></td>
        <td><?php echo $list['0']['skills']; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php unset($list); ?>
</table>

<?php } ?>