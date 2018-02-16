<?php

	$fontname = 'Arial';
	$fixedfont = 'Courier';
	
	$fpdf->AddFont($fixedfont,'','courier.php');
	$fpdf->SetAutoPageBreak(false);
	
	$fontsize = 10;
	$ls = 3;
	$lst = 4.5;
	
	$fpdf->SetMargins(8,5);
    $fpdf->AddPage();
    $fpdf->SetFont($fontname,'',$fontsize);
 	
 	$fpdf->Image("http://cards.knightrealms.com/images/ledger-header.png",57);

   	// Print business name in the center
    $fpdf->SetFont($fontname,'',16);
	$fpdf->Cell(0,6,$details['Business']['name'],0,0,'C');

	$fpdf->ln();

    $fpdf->SetFont($fontname,'',$fontsize);
	$fpdf->Cell(0,4,"A Merchants Guild of Travance Chartered Business",0,0,'C');

	$fpdf->ln();

    $fpdf->SetFont($fontname,'',$fontsize-1);
	$fpdf->Cell(0,4,date('M j, Y'),0,0,'C');

	$fpdf->ln();

    $fpdf->SetFont($fontname,'',$fontsize);

    // Set up some varibles to retun to
    $startingX=$fpdf->GetX();
    $startingY=$fpdf->GetY();
	$resetY = $fpdf->GetY();

	// Draw a line
	$fpdf->Line($startingX,$fpdf->getY(),($fpdf->w - $startingX),$fpdf->getY());
	$fpdf->ln(3);
	
    $fpdf->SetFont($fontname,'B',$fontsize+1);	

	$fpdf->Cell(45,$lst,"Company Members");
	$fpdf->Cell(10,$lst,"Card #",0,0,'R');
	$fpdf->Cell(30,$lst,"Gold Limit",0,0,'R');
	$fpdf->Cell(30,$lst,"Goods Limit",0,0,'R');
	$fpdf->Cell(42,$lst,"New Gold Limit",0,0,'R');
	$fpdf->Cell(42,$lst,"New Goods Limit",0,0,'R');
	$fpdf->ln();

    $fpdf->SetFont($fontname,'',$fontsize);	

	// Print out the members who can deposit and withdraw
	foreach($details['BusinessPartner'] as $partner) {
		$fpdf->Cell(45,$lst,$partner['Character']['name']);
		$fpdf->Cell(10,$lst,$partner['Character']['cardnumber'],0,0,'R');
		
		$limit = $partner['gold_limit'];
		if($partner['gold_limit'] == 0) {
			$limit = "no limit";
		} else if($partner['gold_limit'] == -1) {
			$limit = "deposit only";
		}
		$fpdf->Cell(30,$lst,$limit,0,0,'R');

		$limit = $partner['commodities_limit'];
		if($partner['commodities_limit'] == 0) {
			$limit = "no limit";
		} else if($partner['commodities_limit'] == -1) {
			$limit = "deposit only";
		}
		$fpdf->Cell(30,$lst,$limit,0,0,'R');

		$fpdf->Cell(42,$lst,"______________",0,0,'R');
		$fpdf->Cell(42,$lst,"_______________",0,0,'R');

		$fpdf->ln();
	}
	$fpdf->ln();
	$fpdf->ln();

	// Draw a line
	$fpdf->ln(3);
	$fpdf->Line($startingX,$fpdf->getY(),($fpdf->w - $startingX),$fpdf->getY());
	$fpdf->ln(3);

    $resetY=$fpdf->GetY();

    // Set up Deposit and Withdrawl blocks

    $blocks = array( "Gold", "Luxury", "Durable", "Consumable", "Wearable" );
    foreach($blocks as $block) {
	    $fpdf->SetFont($fontname,'B',$fontsize);	

		$fpdf->Cell(35,$lst,$block." Deposit");
		$fpdf->Cell(20,$lst,"Card #",0,0,'R');
		$fpdf->Cell(40,$lst,"Signature",0,0,'C');
		$fpdf->Cell(10,$lst,"");
		$fpdf->Cell(35,$lst,$block." Withdrawl");
		$fpdf->Cell(20,$lst,"Card #",0,0,'R');
		$fpdf->Cell(40,$lst,"Signature",0,0,'C');
		$fpdf->ln();

	    $fpdf->SetFont($fontname,'',$fontsize);	

		$fpdf->Cell(35,$lst,intval($deposits[0]['BusinessDeposit'][$block.'_total']),0,0,'R');
		$fpdf->Cell(20,$lst,"",0,0,'R');
		$fpdf->Cell(40,$lst,"",0,0,'C');
		$fpdf->Cell(10,$lst,"");
		$fpdf->Cell(35,$lst,"_________________");
		$fpdf->Cell(20,$lst,"______",0,0,'R');
		$fpdf->Cell(40,$lst,"______________",0,0,'C');
		$fpdf->ln();

		for($i=0; $i < 4; $i++) {
			$fpdf->Cell(35,$lst,"_________________");
			$fpdf->Cell(20,$lst,"______",0,0,'R');
			$fpdf->Cell(40,$lst,"______________",0,0,'C');
			$fpdf->Cell(10,$lst,"");
			$fpdf->Cell(35,$lst,"_________________");
			$fpdf->Cell(20,$lst,"______",0,0,'R');
			$fpdf->Cell(40,$lst,"______________",0,0,'C');
			$fpdf->ln();
		}

		// Draw a line
		$fpdf->ln(3);
		$fpdf->Line($startingX,$fpdf->getY(),($fpdf->w - $startingX),$fpdf->getY());
		$fpdf->ln(3);
	}

	foreach($details['SettlementImprovement'] as $imp) {
		$bus = $imp['name'] . ' is a ' . $imp['Improvement']['name'] . ' in ' . $imp['Settlement']['name'] . ', ' . $imp['Land']['name'] . ' [Rank ' . $imp['rank'] .' '. $imp['commodity'] .']'; 

		$fpdf->Cell(100,$lst,$bus);
		$fpdf->ln();
	}
	
	$fpdf->Output(Configure::read('SaveLedgerPDFs.directory').$filename.".pdf",'F');
	$fpdf->Output();
?>