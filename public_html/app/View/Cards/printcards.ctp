<!-- File: /app/View/Cards/printcards.ctp -->

<h1>Card Printing Center</h1>

<a href="/card_files">Download Card and Ledger Zip Files</a>
<br/>

<button id="exportall">Generate PDFs</button>
<button id="forceall">Generate All PDFs</button>
<button id="deactivate">Deactivate Inactive Cards</button>
<button id="renumber">Renumber Cards</button>

<br/>

<button id="clear-old-pdfs">Clear Card Directory</button>
<button id="create-pdf-zip">Create Card Zip</button>

<br/>

<button id="clear-old-ledgers">Clear Ledger Directory</button>
<button id="create-ledger-zip">Create Ledger Zip</button>

<span id="generatingstatus"></span>


<table>
<tr><th>Card Num</th><th>Name</th><th>Updated</th><th>Printed</th><th>Status</th></tr>
<tr><td></td><td>By Player Name</td><td></td><td></td><td style="text-align:center;"><button class="single" onClick="savereport(1);" id="status-report-1">Save</button></td></tr>
<tr><td></td><td>By Character Name</td><td></td><td></td><td style="text-align:center;"><button class="single" onClick="savereport(2);" id="status-report-2">Save</button></td></tr>
<tr><td></td><td>By Card Number</td><td></td><td></td><td style="text-align:center;"><button class="single" onClick="savereport(3);" id="status-report-3">Save</button></td></tr>
<?php 
	foreach ($allcards as $card) {
		$cnum = $card['Character']['cardnumber'];

		$itempage = $card['Character']['itempage'];
				
		echo '<tr>';
		
		if(strToTime($card['Character']['last_updated_date']) > strToTime($last_event_date)) {
			$c = "cardnum update";
			$m = "Save";
			$c2 = "cardnum_page update_page";
		} else if(strToTime($card['Player']['update_date']) > strToTime($last_event_date)) {
			$c = "cardnum update";
			$m = "Save";
			$c2 = "cardnum_page update_page";
		} else {
			$c = "cardnum";
			$m = "Done";
			$c2 = "cardnum_page";
		}
		
		echo '<td class="'.$c.'">'.$cnum.'</td>';
		echo '<td>'.$card['Character']['name'].'</td>';
		echo '<td>'.date('M-Y',strToTime($card['Character']['last_updated_date'])).'</td>';
		echo '<td id="p-date-'.$cnum.'">'.date('M-Y',strToTime($card['Character']['last_printed_date'])).'</td>';
		echo '<td style="text-align:center;"><button class="single" onClick="savepdf('.$cnum.');" id="status-'.$cnum.'">'.$m.'</button></td>';
		echo '</tr>'."\n";

		foreach($allpages as $page) {
			if($cnum == $page['c']['cardnumber']) {
				$pagenum = $page['ca']['page'];
				if($itempage == $pagenum) {
					$itempage = 0;
				}

				echo '<tr>';
				echo '<td class="'.$c2.'">'.$cnum.'</td>';
				echo '<td>&nbsp;&nbsp;&nbsp;page #'.$pagenum.'</td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td style="text-align:center;"><button class="single" onClick="savepdf_page('.$cnum.','.$pagenum.');" id="status-'.$cnum.'-'.$pagenum.'">'.$m.'</button></td>';
				echo '</tr>'."\n";
			}
		}

		if($itempage > 0) {
			echo '<tr>';
			echo '<td class="'.$c2.'">'.$cnum.'</td>';
			echo '<td>&nbsp;&nbsp;&nbsp;page #'.$itempage.'</td>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td style="text-align:center;"><button class="single" onClick="savepdf_page('.$cnum.','.$itempage.');" id="status-'.$cnum.'-'.$itempage.'">'.$m.'</button></td>';
			echo '</tr>'."\n";
		}
	}
?>
</table>

<script type="text/javascript">
	
	var active = 0;
	var errors = 0;
	
	function savereport($reportnumber) {
		active++;
		$("#status-report-"+$reportnumber).html("Saving");
		$.ajax({
			url: '/cards/savereport/'+$reportnumber,
			type: 'POST',
			success: function() { 
				active--;
				$("#generatingstatus").empty();
				updateactive();

				$("#status-report-"+$reportnumber).html("Done");
			}
		});	
	}
	
	$("#exportall").click(function() {
		saveallpdfs('update');
	});
	
	$("#forceall").click(function() {
		saveallpdfs('all');
	});

	$("#deactivate").click(function() {
		deactivatecards();
	});

	$("#renumber").click(function() {
		window.location.replace("/characters/renumber");
	})

	$("#clear-old-pdfs").click(function() {
		$("#generatingstatus").append("clearing out pdfs");
		$.ajax({
			url: '/card_files/clearfolderspdfs',
			type: 'GET',
			success: function() { 
				$("#generatingstatus").empty();
			},
			error: function() {
				$("#generatingstatus").empty();
			}
		});
	})

	$("#clear-old-ledgers").click(function() {
		$("#generatingstatus").append("clearing out ledgers");
		$.ajax({
			url: '/card_files/clearfoldersledgers',
			type: 'GET',
			success: function() { 
				$("#generatingstatus").empty();
			},
			error: function() {
				$("#generatingstatus").empty();
			}
		});
	})

	$("#create-pdf-zip").click(function() {
		$("#generatingstatus").append("creating pdf zip");
		$.ajax({
			url: '/card_files/createzippdfs',
			type: 'GET',
			success: function() { 
				$("#generatingstatus").empty();
			},
			error: function() {
				$("#generatingstatus").empty();
			}
		});
	})

	$("#create-ledger-zip").click(function() {
		$("#generatingstatus").append("creating ledgers zip");
		$.ajax({
			url: '/card_files/createzipledgers',
			type: 'GET',
			success: function() { 
				$("#generatingstatus").empty();
			},
			error: function() {
				$("#generatingstatus").empty();
			}
		});
	})

	function deactivatecards() {
		$("#generatingstatus").empty();
		$("#generatingstatus").append("deactivating cards");
		$.ajax({
			url: '/cards/deactivatecards/'+<?php echo $cset; ?>,
			type: 'POST',
			success: function() { 
				$("#generatingstatus").empty();
			}
		});		
	}

	function updateactive() {
		if(active > 0 || errors > 0) {
			$("#generatingstatus").append("generating "+active+" errors "+errors);
		}
	}
	
	function savepdf($cardnumber){
		active++;
		$("#status-"+$cardnumber).html("Saving");
		$.ajax({
			url: '/characters/downloadpdf/'+$cardnumber,
			type: 'POST',
			success: function() { 
				active--;
				$("#generatingstatus").empty();
				updateactive();

				$("#p-date-"+$cardnumber).empty();
				$("#p-date-"+$cardnumber).append("NOW");
				$("#status-"+$cardnumber).html("Done");
			},
			error: function() {
				active--;
				errors++;
				$("#generatingstatus").empty();
				updateactive();

				$("#status-"+$cardnumber).html("***ERROR***");
			}
		});
	}

	function savepdf_page($cardnumber,$pagenum){
		active++;
		$("#status-"+$cardnumber+"-1").html("Saving");
		$.ajax({
			url: '/characters/downloadpdf_aux/'+$cardnumber+'/'+$pagenum,
			type: 'POST',
			success: function() { 
				active--;
				$("#generatingstatus").empty();
				updateactive();

				$("#p-date-"+$cardnumber+"-1").empty();
				$("#p-date-"+$cardnumber+"-1").append("NOW");
				$("#status-"+$cardnumber+"-1").html("Done");
			},
			error: function() {
				active--;
				errors++;
				$("#generatingstatus").empty();
				updateactive();

				$("#status-"+$cardnumber+"-1").html("***ERROR***");
			}
		});
	}
	
	function saveallpdfs($mode){
		
		active = 0;
		errors = 0;

		savereport(1);
		savereport(2);
		savereport(3);
		
		if($mode == "update") {
			$("#generatingstatus").empty();
			$("#generatingstatus").append("generating "+$(".update").size());
			
			$(".update").each(function( index ) {
				savepdf($(this).text());
			});

			$(".update_page").each(function( index ) {
				savepdf_page($(this).text(),"1");
			});

		} else if($mode == "all") {
			$("#generatingstatus").empty();
			$("#generatingstatus").append("generating "+$(".cardnum").size());
			
			$(".cardnum").each(function( index ) {
				savepdf($(this).text());
			});

			$(".cardnum_page").each(function( index ) {
				savepdf_page($(this).text(),"1");
			})
		}
	}
		
</script>

