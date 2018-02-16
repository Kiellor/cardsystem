<?php

	$fontname = 'Arial';
	$fixedfont = 'Courier';
	
	$fpdf->AddFont($fixedfont,'','courier.php');
	$fpdf->SetAutoPageBreak(false);
	
	$fontsize = 8;
	$ls = 2.5; 
	
	$fpdf->SetMargins(8,5);
    $fpdf->AddPage();
    $fpdf->SetFont($fixedfont,'',$fontsize);
 	
 	$fpdf->Image("http://cards.knightrealms.com/images/character-sheet-header.png",70);

    // Set up some varibles to retun to
    $startingX=$fpdf->GetX();
    $startingY=$fpdf->GetY();
   	$resetY = $fpdf->GetY();
	$extent = $fpdf->h - 10;
	$col = 0;

	// Figure out column widths
	$max1 = strlen($col1['text']);
	$max2 = strlen($col2['text']);
	$max3 = strlen($col3['text']);

	// foreach ($report as $line) {
	// 	$max1 = max(strlen($line['col1']),$max1);
	// 	$max2 = max(strlen($line['col2']),$max2);
	// 	$max3 = max(strlen($line['col3']),$max3);
	// }

	// $max1 = min(strlen($col1['text'])*2, $max1);
	// $max2 = min(strlen($col2['text'])*2, $max2);
	// $max3 = min(strlen($col3['text'])*2, $max3);

	$colWidth = 100;
	$format_string = $col1['format'].$max1.'s '.$col2['format'].$max2.'s '.$col3['format'].$max3.'s';

	$header = sprintf($format_string,$col1['text'],$col2['text'],$col3['text']);
	$fpdf->SetFont($fixedfont,'BU',$fontsize);
	$fpdf->MultiCell($colWidth,$ls,$header,0,'L');

	$fpdf->SetFont($fixedfont,'',$fontsize);
	$fpdf->SetFillColor(220,220,220);
	
	$fill = false;
	foreach ($report as $line) {
		if(strlen($line['col1']) > $max1) {
			$c1text = substr($line['col1'],0,$max1);
		} else {
			$c1text = $line['col1'];
		}
		if(strlen($line['col2']) > $max1) {
			$c2text = substr($line['col2'],0,$max2);
		} else {
			$c2text = $line['col2'];
		}
		if(strlen($line['col3']) > $max1) {
			$c3text = substr($line['col3'],0,$max3);
		} else {
			$c3text = $line['col3'];
		}

		$content = sprintf($format_string,$c1text,$c2text,$c3text);
		$fpdf->MultiCell($colWidth,$ls,$content,0,'L',$fill);
		
		if($fpdf->GetY() + $ls >= $extent) {
			$col++;
			
			if($col > 1) {
				$fill = false;
				$col = 0;
				$fpdf->AddPage();
				$fpdf->Image("http://cards.knightrealms.com/images/character-sheet-header.png",70);
			}
			
			$fpdf->SetLeftMargin($startingX+($col * $colWidth));
			$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
			$fpdf->SetY($resetY);

			$fpdf->SetFont($fixedfont,'BU',$fontsize);
			$fpdf->MultiCell($colWidth,$ls,$header,0,'L');
			$fpdf->SetFont($fixedfont,'',$fontsize);
		}
		$fill = !$fill;
	}
	
	$fpdf->Output(Configure::read('SavePDFs.directory').$filename.".pdf",'F');
	$fpdf->Output();
?>