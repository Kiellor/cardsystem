<!-- File: /app/View/Cards/printcards.ctp -->

<h1>Report Printing Center</h1>

<h4>Rows: <?php echo count($report); ?></h4>

<a href="/cards/savereport/<?php echo $query; ?>">Save Report</a>

<table>
<tr><th><?php echo $col1['text']; ?></th><th><?php echo $col2['text']; ?></th><th><?php echo $col3['text']; ?></th></tr>
<?php 
	foreach ($report as $line) {

		echo '<tr>';
		echo '<td>'.$line['col1'].'</td>';
		echo '<td>'.$line['col2'].'</td>';
		echo '<td>'.$line['col3'].'</td>';
		echo '</tr>'."\n";
	}
?>
</table>

