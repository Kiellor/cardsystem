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

	$vitals  = 'Race: '.$race.'      '.'Religion: '.$religion;
	$fpdf->SetFont($fontname,'',$fontsize);
	$startheight = $fpdf->GetY();
	$fpdf->MultiCell($colWidth*2,$ls,$vitals,0);
	$endheight = $fpdf->GetY();
	$lineheight = $endheight - $startheight;
	
	$fpdf->ln(1);

	$lowestY = $fpdf->GetY();

	// Professions
	$col = 0;
	$fpdf->SetX($startingX);
	$fpdf->SetLeftMargin($startingX+($col * $colWidth));
	$fpdf->SetRightMargin($startingX+($colWidth * 4));

	$vitals  = 'Lists: '.$lists;
	if($lists_build > 0) {
		$vitals .= ' = ('.$lists_build.')';
	}
	$fpdf->SetFont($fontname,'',$fontsize);
	$wrap = $fpdf->MultiCell($colWidth * 3, $ls, $vitals, 0,'L',0,1);
	$fpdf->SetX($startingX);
		
	if(strlen($wrap) > 0) {
		$fpdf->MultiCell($colWidth * 3, $ls, $wrap, 0,'L',0);
	}

	$lowestY = $fpdf->GetY();

	// Right Side of Header
	$col=3;
	$fpdf->SetLeftMargin($startingX+($col * $colWidth));
	$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
	$fpdf->SetY($resetY-$lst);

	$fpdf->SetFont($fontname,'',$fontsize);	
	
	$vitals  = 'Updated: '.date('M j, Y', strToTime($character['Character']['last_updated_date']))."\n".'Events Attended: '.$numevents."\n".$email;
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
	$footerTop = $fpdf->h - 66;
	$skillsheight = $footerTop - $resetY;
	$extent = $footerTop;
	
	// Show Build Block
	
	$buildblock = "Level:\nBuild Spent:\nUnspent:\nEarned:";
	$fpdf->MultiCell(30,$lst,$buildblock,1);
	
	$buildblock  = sprintf("%4s\n%4s\n%4s\n%4s",$level,$build_spent,$build_unspent,$build_earned);
	$fpdf->SetY($resetY);
	$fpdf->SetLeftMargin($startingX+20);
	$fpdf->MultiCell(10,$lst,$buildblock,0,'R');

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
				if($col > 3) {
					// exceeded column width, throw error
					throw new InternalErrorException('Card longer than one page');
				}
				$fpdf->SetLeftMargin($startingX+($col * $colWidth));
				$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
				$fpdf->SetY($resetY);
			}	

			if($ability_set_name == 'TotalRows') {
				// do nothing
			} else if($ability_set_name == 'Career Points') {

				$localResetY = $fpdf->getY();
				
				$column1 = '';
				$column2 = '';
				$column3 = '';
				
				if($build == null) {
					$build = 0;
				}
				$column1 .= 'BP'."\n";
				$column2 .= ceil($body)."\n";
				$column3 .= '('.$build.')'."\n";

				$column1 .= 'BP Ratio'."\n";
				$column2 .= "\n";
				$column3 .=  number_format($ratio,1)."\n";
 
				$column1 .= 'AP Multiple'."\n";
				$column2 .= "\n";
				$column3 .= $armormod.'x'."\n";

				$fpdf->SetLeftMargin($startingX+($col * $colWidth));
				$fpdf->setX($startingX);
				
				$fpdf->MultiCell(30,$lst,$column1,1);

				$fpdf->SetY($localResetY);
				$fpdf->SetLeftMargin($startingX+10);
				$fpdf->MultiCell(10,$lst,$column2,0,'R');

				$fpdf->SetY($localResetY);
				$fpdf->SetLeftMargin($startingX+15);
				$fpdf->MultiCell(15,$lst,$column3,0,'R');
				
				$fpdf->SetLeftMargin($startingX);

				$localResetY = $fpdf->getY();
				
				$column1 = '';
				$column2 = '';
				$column3 = '';
				$careerpointcount = 0;
				
				foreach ($character_abilities[$ability_set_name] as $abilities) {
					if($abilities['ability'] != "BP") {
						$careerpointcount++;
						$column1 .= $abilities['ability']."\n";
						$column2 .= $abilities['value']."\n";
						if($abilities['total'] > 0) {
							$column3 .= '('.$abilities['total'].")\n";
						} else {
							$column3 .= "\n";
						}
					}
				}
				
				if($careerpointcount > 0) {				
					$fpdf->SetLeftMargin($startingX+($col * $colWidth));
					$fpdf->setX($startingX);

					$fpdf->MultiCell(30,$lst,$column1,1);

					$fpdf->SetY($localResetY);
					$fpdf->SetLeftMargin($startingX+10);
					$fpdf->MultiCell(10,$lst,$column2,0,'R');

					$fpdf->SetY($localResetY);
					$fpdf->SetLeftMargin($startingX+20);
					$fpdf->MultiCell(10,$lst,$column3,0,'R');

					$fpdf->SetLeftMargin($startingX);
				}
				
			} else if($ability_set_name == 'Lore' || $ability_set_name == 'Literacy') {

				$abilities = $character_abilities[$ability_set_name][0];

				if($fpdf->GetY() > $resetY) {
					if($collapsing == 'false') {
						$fpdf->ln();
					} else {
						$fpdf->ln(0.5);
					}
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
					if($collapsing == 'false') {
						$fpdf->ln();
					} else {
						$fpdf->ln(0.5);
					}
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

					$abilities['ability'] = str_replace("^","\n  ",$abilities['ability']);
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


	if($fpdf->GetY() > $resetY) {
		if($collapsing == 'false') {
			$fpdf->ln();
		} else {
			$fpdf->ln(0.5);
		}
	}
	 
	// Deaths
	$fpdf->SetFont($fontname,'BU',$fontsize);
	$fpdf->MultiCell($colWidth,$ls,"Life Events",0,'L');
	$fpdf->SetFont($fontname,'',$fontsize);

	if($character['Character']['empathy_rank4'] > -1) {
		$fpdf->MultiCell($colWidth,$ls,"Received Empathy Rank IV",0,'L');
	}

	if($character['Character']['finaldeath'] > -1) {
		$fpdf->MultiCell($colWidth,$ls,"Final Death",0,'L');
	} else {
		if($character['Character']['resurrect_1'] > -1) {
			$fpdf->MultiCell($colWidth,$ls,"Life 1 Resurrected (+10)",0,'L');
		} else {
			if($character['Character']['reanimate_1'] > -1) {
				$fpdf->MultiCell($colWidth,$ls,"Life 1 Reanimated",0,'L');
			}
			if($character['Character']['cheatdeath_1'] > -1) {
				$fpdf->MultiCell($colWidth,$ls,"Life 1 Cheated Death",0,'L');
			}
		}

		if($character['Character']['resurrect_2'] > -1) {
			$fpdf->MultiCell($colWidth,$ls,"Life 2 Resurrected (+20)",0,'L');
		} else {
			if($character['Character']['reanimate_2'] > -1) {
				$fpdf->MultiCell($colWidth,$ls,"Life 2 Reanimated",0,'L');
			}
			if($character['Character']['cheatdeath_2'] > -1) {
				$fpdf->MultiCell($colWidth,$ls,"Life 2 Cheated Death",0,'L');
			}
		}

		if($character['Character']['resurrect_3'] > -1) {
			$fpdf->MultiCell($colWidth,$ls,"Life 3 Resurrected (+30)",0,'L');
		} else {
			if($character['Character']['reanimate_3'] > -1) {
				$fpdf->MultiCell($colWidth,$ls,"Life 3 Reanimated",0,'L');
			}
			if($character['Character']['cheatdeath_3'] > -1) {
				$fpdf->MultiCell($colWidth,$ls,"Life 3 Cheated Death",0,'L');
			}
		}

		if($character['Character']['resurrect_4'] > -1) {
			$fpdf->MultiCell($colWidth,$ls,"Life 4 Resurrected (+40)",0,'L');
		} else {
			if($character['Character']['reanimate_4'] > -1) {
				$fpdf->MultiCell($colWidth,$ls,"Life 4 Reanimated",0,'L');
			}
			if($character['Character']['cheatdeath_4'] > -1) {
				$fpdf->MultiCell($colWidth,$ls,"Life 4 Cheated Death",0,'L');
			}
		}

		if($character['Character']['reanimate_5'] > -1) {
			$fpdf->MultiCell($colWidth,$ls,"Final Life Reanimated",0,'L');
		}
		if($character['Character']['cheatdeath_4'] > -1) {
			$fpdf->MultiCell($colWidth,$ls,"Final Life Cheated Death",0,'L');
		}
	}

	// Quests
	// if(count($quests) > 0) {
	// 	if($col < 3) {
	// 		$col = 3;
	// 		$fpdf->SetLeftMargin($startingX+($col * $colWidth));
	// 		$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
	// 		$fpdf->SetY($resetY);
	// 	} else {
	// 		if($collapsing == 'false') {
	// 			$fpdf->ln();
	// 		} else {
	// 			$fpdf->ln(0.5);
	// 		}
	// 	}

	// 	$fpdf->SetFont($fontname,'BU',$fontsize);
	// 	$fpdf->MultiCell($colWidth,$ls,"Quests",0,'L');
	// 	$fpdf->SetFont($fontname,'',$fontsize);

	// 	foreach ($quests as $q) {
	// 		$questDisplayLimit = 5;

	// 		if($fpdf->GetY() + ($rows_needed * $lineheight) >= $extent) {
	// 			$col++;
	// 			$fpdf->SetLeftMargin($startingX+($col * $colWidth));
	// 			$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));
	// 			$fpdf->SetY($resetY);

	// 			$fpdf->SetFont($fontname,'BU',$fontsize);
	// 			$fpdf->MultiCell($colWidth,$ls,"Quests (cont.)",0,'L');
	// 			$fpdf->SetFont($fontname,'',$fontsize);
	// 		}	

	// 		$fpdf->SetFont($fontname,'B',$fontsize);
	// 		$fpdf->MultiCell($colWidth,$ls,$q['QuestPath']['name'],0,'L');	

	// 		foreach($q['QuestStages'] as $stage) {
	// 			if( !isset($stage['EventCompleted']['name']) && $questDisplayLimit > 0) {
	// 				$questDisplayLimit--;
	// 				$fpdf->SetFont($fontname,'',$fontsize);
	// 				$fpdf->MultiCell($colWidth,$ls,"[   ] ".$stage['QuestStage']['description'],0,'L');	
	// 			}
	// 			if( !isset($stage['EventCollected']['name']) && isset($stage['QuestStage']['reward'])) {
	// 				$fpdf->SetFont($fontname,'',$fontsize);
	// 				$fpdf->MultiCell($colWidth,$ls,">>> ".$stage['QuestStage']['reward'],0,'L');	
	// 				$fpdf->MultiCell($colWidth,$ls,"Signed:_____________",0,'L');	
	// 			}
	// 		}

	// 		if($collapsing == 'false') {
	// 			$fpdf->ln();
	// 		} else {
	// 			$fpdf->ln(0.5);
	// 		}	
	// 	}
	// }
	
	
	// Fixed items at bottom of page

	$extent += 2;

	$fpdf->SetY($extent);
	$fpdf->SetX($startingX);

	// PAID

	$fpdf->SetFont($fontname,'',$fontsize);
	$paidtop = $fpdf->GetY();

	$fpdf->SetLeftMargin($startingX+2);
	$fpdf->SetRightMargin($startingX+$colWidth+($col * $colWidth));

	$fpdf->Rect($startingX,$paidtop,$colWidth*4,58);

	if($character['Player']['is_not_minor'] == 1) {
	 	$fpdf->Image("http://cards.knightrealms.com/images/seal3.png",$startingX+5,$paidtop+30);
	} else {
		$fpdf->Image("http://cards.knightrealms.com/images/seal-minor.png",$startingX+5,$paidtop+30);
	}
	$fpdf->SetLeftMargin($startingX);
 	$fpdf->SetY($paidtop);
	$fpdf->SetFont($fontname,'B',$fontsize);
	$fpdf->MultiCell(37,$ls,"Staff Use Only",0,'C');

	$fpdf->SetFont($fontname,'',$fontsize-3);
 	$fpdf->SetY($paidtop+55);
	$fpdf->MultiCell(37,$ls,"Paid if Stamped",0,'C');
	$fpdf->SetFont($fontname,'',$fontsize);

/* 	
 	$fpdf->SetY($extent+30);
	$fpdf->MultiCell(30,$ls,"Director's Stamp or Signature",0,'C');

	$fpdf->ln(1);

	// Boxes
	$fpdf->Rect($startingX+3,$fpdf->GetY()+0*$lst,3,3);
	$fpdf->Rect($startingX+3,$fpdf->GetY()+1*$lst,3,3);

	// Lines
	$fpdf->Line($startingX+3,$fpdf->GetY()+3*$lst+1.5,$startingX+7,$fpdf->GetY()+3*$lst+1.5);
	$fpdf->Line($startingX+3,$fpdf->GetY()+4*$lst+1.5,$startingX+7,$fpdf->GetY()+4*$lst+1.5);

	$fpdf->Line($startingX+17,$fpdf->GetY()+3*$lst+1.5,$startingX+33,$fpdf->GetY()+3*$lst+1.5);
	$fpdf->Line($startingX+17,$fpdf->GetY()+4*$lst+1.5,$startingX+33,$fpdf->GetY()+4*$lst+1.5);
	$fpdf->Line($startingX+3,$fpdf->GetY()+5*$lst+1.5,$startingX+33,$fpdf->GetY()+5*$lst+1.5);

	$fpdf->SetX($startingX+6);
	$fpdf->MultiCell($colWidth,$lst,"Paid to PC\nNPC Full Time",0);
	
	$fpdf->ln(2);
	$fpdf->SetX($startingX+6);
	$fpdf->MultiCell($colWidth,$lst,"Bought\nOther",0);
*/

	// RP Points
	$leftoffset = 37;
	$fpdf->SetY($extent);
	$fpdf->SetX($startingX+$leftoffset);
	$fpdf->SetLeftMargin($startingX+$leftoffset);
	$fpdf->SetFont($fontname,'B',$fontsize);

	$text  = "Roleplaying Points: ".$roleplay_points."\n";
	$text .= " \n";
	$text .= " \n";
	$text .= " \n";
	
	$fpdf->MultiCell(40,$ls,$text,1,'L');

	// Service Points
	$text  = "Service Points: ".$service_points."\n";
	$text .= " \n";
	$text .= " \n";
	$text .= " \n";
	$text .= " \n";

	$fpdf->MultiCell(40,$ls,$text,1,'L');

	$fpdf->Rect($startingX+$leftoffset,$fpdf->GetY(),40,31);

	$text  = "Bank: ".$gold." gold\n";
	$fpdf->MultiCell(40,$ls,$text,0,'L');

	$text  = "  Deposit    Withdraw \n";
	$text .= " \n";
	$text .= "__________  __________\n";
	$text .= " \n";
	$text .= "__________  __________\n";
	$text .= " \n";
	$text .= "__________  __________\n";
	$text .= " \n";
	$text .= "__________  __________";

	$fpdf->SetFont($fixedfont,'',$fontsize);
	$fpdf->MultiCell(40,$ls,$text,0,'L');
	
	$fpdf->SetFont($fontname,'',$fontsize);

	// OOG Notes

	$fpdf->ln(1);
	
	$leftoffset += 40;
	$rightlimit = $colWidth * 4 - $leftoffset;

	$fpdf->SetY($extent);

	if($allowrewrites == 'true') {
		$fpdf->SetY($extent);
		$fpdf->SetX($startingX+$leftoffset);

		$fpdf->SetFont($fontname,'B',$fontsize);
		$fpdf->Cell(24,$ls,"Rewrite Required: ");
		$fpdf->SetFont($fontname,'',$fontsize);
		$wrap = $fpdf->MultiCell($rightlimit-24, $ls, "Any skill with !!! in front of it is unusable.  A rewrite is required and may be accomplished online or with a Staff member.", 0,'L',0,1);
		$fpdf->SetX($startingX+$leftoffset);
		if(strlen($wrap) > 0) {
			$fpdf->MultiCell($rightlimit, $ls, $wrap, 0,'L',0);
		}			
	}
	
	$fpdf->SetX($startingX+$leftoffset);

	$fpdf->SetFont($fontname,'B',$fontsize);
	$fpdf->Cell(24,$ls,"Character Notes: ");
	$fpdf->SetFont($fontname,'',$fontsize);
	$wrap = $fpdf->MultiCell($rightlimit-24, $ls, $character['Character']['other_notes'], 0,'L',0,1);
	$fpdf->SetX($startingX+$leftoffset);
	if(strlen($wrap) > 0) {
		$fpdf->MultiCell($rightlimit, $ls, $wrap, 0,'L',0);
	}
	
	if($fpdf->GetY() < $extent+10) {
		$fpdf->SetY($extent+10);
	}
	$fpdf->SetX($startingX+$leftoffset);
	
	$fpdf->SetFont($fontname,'B',$fontsize);
	$fpdf->Cell(24,$ls,"Player Notes: ");
	$fpdf->SetFont($fontname,'',$fontsize);
	$wrap = $fpdf->MultiCell($rightlimit-24, $ls,$character['Player']['player_notes'], 0,'L',0,1);
	$fpdf->SetX($startingX+$leftoffset);
	if(strlen($wrap) > 0) {
		$fpdf->MultiCell($rightlimit, $ls, $wrap, 0,'L',0);
	}

	if($character['Character']['character_memo']) {
		if($fpdf->GetY() < $extent+20) {
			$fpdf->SetY($extent+20);
		}
		$fpdf->SetX($startingX+$leftoffset);

		$fpdf->SetFont($fontname,'B',$fontsize);
		$fpdf->Cell(24,$ls,"Character Memo: ");
		$fpdf->SetFont($fontname,'',$fontsize);
		$wrap = $fpdf->MultiCell($rightlimit-24, $ls, $character['Character']['character_memo'], 0,'L',0,1);
		$fpdf->SetX($startingX+$leftoffset);
		
		if(strlen($wrap) > 0) {
			$fpdf->MultiCell($rightlimit, $ls, $wrap, 0,'L',0);
		}
	} 

	if($character['Player']['medical_notes']) {

		if($fpdf->GetY() < $extent+30) {
			$fpdf->SetY($extent+30);
		}
		$fpdf->SetX($startingX+$leftoffset);

		$fpdf->SetFont($fontname,'B',$fontsize);
		$fpdf->Cell(24,$ls,"Player Medical: ");
		$fpdf->SetFont($fontname,'',$fontsize);
		$wrap = $fpdf->MultiCell($rightlimit-24, $ls, $character['Player']['medical_notes'], 0,'L',0,1);
		$fpdf->SetX($startingX+$leftoffset);
		
		if(strlen($wrap) > 0) {
			$fpdf->MultiCell($rightlimit, $ls, $wrap, 0,'L',0);
		}
	} 
	
	if($prompt_for_email == 'true') {

		if($fpdf->GetY() < $extent+40) {
			$fpdf->SetY($extent+40);
		}
		$fpdf->SetX($startingX+$leftoffset);

		$fpdf->SetFont($fontname,'B',$fontsize);
		$fpdf->Cell(20,$ls,"Player Email: ");
		$fpdf->SetFont($fontname,'',$fontsize);
		$wrap = $fpdf->MultiCell($rightlimit-20, $ls, "write your email address here to register for the Knight Realms Online Card System.  One email address will give you access all of your character cards between events at http://cards.knightrealms.com.", 0,'L',0,1);
		$fpdf->SetX($startingX+$leftoffset);
		
		if(strlen($wrap) > 0) {
			$fpdf->MultiCell($rightlimit, $ls, $wrap, 0,'L',0);
		}
	
	}

	$fpdf->Output(Configure::read('SavePDFs.directory').$filename.".pdf",'F');
	$fpdf->Output();
?>