<?php

	$fontname = 'Arial';
	$fixedfont = 'Courier';
	
	$fpdf->AddFont($fixedfont,'','courier.php');
	$fpdf->SetAutoPageBreak(false);
	
	$fontsize = 8;
	
	$fpdf->SetMargins(8,5);
    $fpdf->AddPage();
    $fpdf->SetFont($fontname,'',$fontsize);
 	
 	$fpdf->Image("http://cards.knightrealms.com/images/character-sheet-header.png",70);

    // Set up some varibles to retun to
    $startingX=$fpdf->GetX();
    $startingY=$fpdf->GetY();
   	$resetY = $fpdf->GetY();


	$colWidth=49;
	$col = 0;
	$fpdf->SetRightMargin($colWidth);

	$ls = 3; 
	$lst = 3.5;

    $fpdf->SetFont($fontname,'B',$fontsize+1);	
	$col = 0;
	$vitals  = $character['Character']['name'].'   ('.$character['Character']['cardnumber'].')';
	$fpdf->setY($fpdf->GetY()-10);
	$fpdf->MultiCell($colWidth*2,$lst,$vitals,0);
			
	$resetY = $fpdf->GetY();
	$vitals  = 'Player: '.$character['Player']['name'];
	$fpdf->MultiCell($colWidth,$lst,$vitals,0);
	
	$fpdf->ln(1);

	$resetY = $fpdf->GetY();

	$vitals  = 'Auxiliary Page'."\n";
	$fpdf->SetFont($fontname,'',$fontsize);
	$startheight = $fpdf->GetY();
	$fpdf->MultiCell($colWidth*2,$ls,$vitals,0);
	$endheight = $fpdf->GetY();
	$lineheight = $endheight - $startheight;
	
	$fpdf->ln(1);
	
	$lowestY = $fpdf->GetY();

	// Right Side of Header
	$col=3;
	$fpdf->SetLeftMargin($startingX+($col * $colWidth));
	$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
	$fpdf->SetY($resetY-$lst);

	$fpdf->SetFont($fontname,'',$fontsize);	
	
	$vitals  = 'Updated: '.date('M j, Y', strToTime($character['Character']['last_updated_date']));
	$fpdf->MultiCell($colWidth,$lst,$vitals,0,'R');
	
	$col = 0;
	$fpdf->SetX($startingX);
	$fpdf->SetLeftMargin($startingX+($col * $colWidth));
	$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));

	$fpdf->ln(1);
	
	$fpdf->SetY($lowestY);

	$fpdf->Line($startingX,$fpdf->getY(),($fpdf->w - $startingX),$fpdf->getY());

	$col = 0;
	$fpdf->SetX($startingX);
	$fpdf->SetLeftMargin($startingX+($col * $colWidth));
	$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
	
	$resetY = $fpdf->GetY();
	$footerTop = $fpdf->h - 10;
	$skillsheight = $footerTop - $resetY;
	$extent = $footerTop;
	
	$fpdf->SetLeftMargin($startingX);

	// Calculate density of the sheet
	$totalrows = $ability_group_rows['Total'] + (count($character_abilities) * 2) + 6;
	$rowsinacolumn = $skillsheight / $lineheight;
	$availablerows = 4 * $rowsinacolumn;
	$remainingrows = $totalrows;
	
	$resetY += 1;
	
	foreach (array_keys($character_abilities) as $ability_set_name) {
	
		if(count($character_abilities[$ability_set_name]) > 0) {
	
			$rows_needed = 1;
			if($collapsing == 'false') {
				foreach ($character_abilities[$ability_set_name] as $abilities) {
					$rows_needed += $abilities['rows'];
				}
			}
			
			$rowsleftincolumn = ($extent - $fpdf->GetY()) / $lineheight;
			$rowsleft = $rowsleftincolumn + (3 - $col) * $rowsinacolumn;
			$rowsinsection = $ability_group_rows[$ability_set_name];
			$remainingrows -= $rowsinsection;
			$skills = '';

			if($fpdf->GetY() + ($rows_needed * $lineheight) + $lineheight >= $extent) {
				$col++;
				$fpdf->SetLeftMargin($startingX+($col * $colWidth));
				$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
				$fpdf->SetY($resetY);
			}	

			if($ability_set_name == 'TotalRows') {
				// do nothing
			} else if($ability_set_name == 'Career Points') {
				// do nothing
			} else if($ability_set_name == 'Lore' || $ability_set_name == 'Literacy') {

				$abilities = $character_abilities[$ability_set_name][0];

				if($fpdf->GetY() > $resetY) {
					$fpdf->ln();
				} 

				$fpdf->SetFont($fontname,'BU',$fontsize);
				if(array_key_exists('build_spent',$abilities)) {
					$fpdf->MultiCell($colWidth,$ls,$ability_set_name.' ('. $abilities['build_spent'] .')',0,'L');
				} else {
					$fpdf->MultiCell($colWidth,$ls,$ability_set_name.' (0)',0,'L');
				}
				$fpdf->SetFont($fontname,'',$fontsize);

				$skills = $abilities['ability'];

				$remainder = $fpdf->MultiCell($colWidth,$ls,$skills,0,'L',0,1);
				while(strlen($remainder) > 0) {
					if($fpdf->GetY() + $lineheight >= $extent) {
						$col++;
						$fpdf->SetLeftMargin($startingX+($col * $colWidth));
						$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
						$fpdf->SetY($resetY);

						$fpdf->SetFont($fontname,'BU',$fontsize);
						$fpdf->MultiCell($colWidth,$ls,$ability_set_name.' (cont.)',0,'L');
						$fpdf->SetFont($fontname,'',$fontsize);
					}
					$remainder = $fpdf->MultiCell($colWidth,$ls,$remainder,0,'L',0,1);
				}
			} else if($ability_set_name == 'Race' || $ability_set_name == 'Religion') {
				$skillname = "";
				if(array_key_exists(0,$character_abilities[$ability_set_name]) ) {
					$abilities = $character_abilities[$ability_set_name][0];
					$skillname = $abilities['ability'];
				}
					
				$skills = $ability_set_name.': '.$skillname;
				$fpdf->SetFont($fontname,'B',$fontsize);
				$fpdf->MultiCell($colWidth,$ls,$skills,0,'L');
				$fpdf->SetFont($fontname,'',$fontsize);
			} else {
				if($fpdf->GetY() > $resetY) {
					$fpdf->ln();
				} 

				$fpdf->SetFont($fontname,'BU',$fontsize);
				$fpdf->MultiCell($colWidth,$ls,$ability_set_name,0,'L');
				$fpdf->SetFont($fontname,'',$fontsize);

				foreach ($character_abilities[$ability_set_name] as $abilities) {
					$rows_needed = $abilities['rows'];

					if($fpdf->GetY() + ($rows_needed * $lineheight) >= $extent) {
						$col++;
						$fpdf->SetLeftMargin($startingX+($col * $colWidth));
						$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
						$fpdf->SetY($resetY);

						$fpdf->SetFont($fontname,'BU',$fontsize);
						$fpdf->MultiCell($colWidth,$ls,$ability_set_name.' (cont.)',0,'L');
						$fpdf->SetFont($fontname,'',$fontsize);
					}	

					if($abilities['bold'] == 1) {
						$fpdf->SetFont($fontname,'B',$fontsize);
						$fpdf->MultiCell($colWidth,$ls,$abilities['ability'],0,'L');
					} else {
						$fpdf->SetFont($fontname,'',$fontsize);
						$fpdf->MultiCell($colWidth,$ls,$abilities['ability'],0,'L');
					}
					
					if(strlen($abilities['bubbles']) > 0) {
						$fpdf->SetFont($fixedfont,'',$fontsize+1);
						$fpdf->MultiCell($colWidth,$ls,$abilities['bubbles'],0,'L');
						$fpdf->SetFont($fontname,'',$fontsize);
					}
				}
			}
		}
	}
	


	$fpdf->Output(Configure::read('SavePDFs.directory').$filename.".pdf",'F');
	$fpdf->Output();
?>