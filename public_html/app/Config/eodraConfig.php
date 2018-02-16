<?php

$config = array(
	'debug' => 0,
	'SavePDFs' => array(
		'directory' => '/home/cards/public_html/app/tmp/pdfs/'
	),
	'SaveLedgerPDFs' => array(
		'directory' => '/home/cards/public_html/app/tmp/ledgers/'
	),
	'UsersController' => array(
		'add' => array(
			'config' => 'email',
			'from' => 'noreply@cards.knightrealms.com',
			'subject' => 'Confirm your cards.knightrealms.com account',
			'body' => "Please use this link to confirm your email address by visiting: http://cards.knightrealms.com/users/login/%s\n\nYour temporary password (which you will be asked to change after login) is: %s"
		),
		'reset' => array(
			'config' => 'email',
			'from' => 'noreply@cards.knightrealms.com',
			'subject' => 'Reset your cards.knightrealms.com account',
			'body' => "Please use this link to reset your password by visiting: http://cards.knightrealms.com/users/confirm/%s"
		),
		'confirm' => array(
			'config' => 'email',
			'from' => 'noreply@cards.knightrealms.com',
			'subject' => 'Confirm your cards.knightrealms.com account',
			'body' => "Please use this link to confirm your email address by visiting: http://cards.knightrealms.com/users/login/%s\n\nYour temporary password (which you will be asked to change after login) is: %s"
		),
		'test' => array(
			'config' => 'email',
			'to' => 'development@knightrealms.com',
			'from' => 'noreply@cards.knightrealms.com',
			'subject' => 'Test email...',
			'body' => "Is a test\n\nTesting test test.  Is this thing on?"
		)
	),
	'FeedbackController' => array(
		'cardproblem' => array(
			'config' => 'email',
			'to' => 'cardissues@knightrealms.com',
			'from' => 'noreply@cards.knightrealms.com',
			'subject' => 'Card Issue Reported',
			'body' => "Issue reported on card <a href='http://cards.knightrealms.com/characters/view/%s'>#%s, %s</a> by %s\n\n%s"
		)
	),
	'ChronicleController' => array(
		'approve' => array(
			'config' => 'email',
			'from' => 'noreply@knightrealms.com',
			'subject' => 'Character Chronicle Approved',
			'body' => "Character Chronicle Entry approved for card <a href='http://cards.knightrealms.com/characters/view/%s'>#%s, %s</a> %s"
		)
	),
	'PersonalAction' => array(
		'results' => array(
			'config' => 'email',
			'from' => 'noreply@knightrealms.com',
			'subject' => 'Between Game Action Results',
			'body' => "You are receiving this message because you submitted an action with the Knight Realms Between Game Action system.  If you would like to stop receiving these notifications you can opt-out by simply not submitting more actions or by sending an email to development@knightrealms.com with the subject of 'Opt Out Land Action Emails' and we will remove you from these automatic mailings.\n\n%s performed the %s action in %s of %s.\nTarget: %s.\nComment: %s.\n\nResults: %s."
		)
	),
	'CharacterBuilder' => array(
		'submit' => array(
			'config' => 'email',
			'subject' => 'Please Import my Character',
			'body' => "Attention Card Team\n\nA Character Builder character is ready for import.  Please visit http://cards.knightrealms.com/character_builder/index/%s to being the import process."
		)
	)
);

?>