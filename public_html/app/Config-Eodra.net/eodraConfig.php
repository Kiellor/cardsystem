<?php

$config = array(
	'debug' => 0,
	'SavePDFs' => array(
		'directory' => '/home/eodra/public_html/app/tmp/pdfs/'
	),
	'UsersController' => array(
		'add' => array(
			'config' => 'email',
			'from' => 'cardsystem@eodra.net',
			'subject' => 'Confirm your eodra.net account',
			'body' => "Please use this link to confirm your email address by visiting: http://eodra.net/users/login/%s\n\nYour temporary password (which you will be asked to change after login) is: %s"
		),
		'reset' => array(
			'config' => 'email',
			'from' => 'cardsystem@eodra.net',
			'subject' => 'Reset your eodra.net account',
			'body' => "Please use this link to reset your password by visiting: http://eodra.net/users/confirm/%s"
		),
		'confirm' => array(
			'config' => 'email',
			'from' => 'cardsystem@eodra.net',
			'subject' => 'Confirm your eodra.net account',
			'body' => "Please use this link to confirm your email address by visiting: http://eodra.net/users/login/%s\n\nYour temporary password (which you will be asked to change after login) is: %s"
		),
		'test' => array(
			'config' => 'email',
			'to' => 'development@knightrealms.com',
			'from' => 'noreply@eodra.net',
			'subject' => 'Test email...',
			'body' => "Is a test\n\nTesting test test.  Is this thing on?"
		)
	),
	'FeedbackController' => array(
		'cardproblem' => array(
			'config' => 'email',
			'to' => 'development@knightrealms.com',
			'bcc' => '',
			'from' => 'noreply@eodra.net',
			'subject' => 'Card Issue Reported',
			'body' => "Issue reported on card <a href='http://eodra.net/characters/view/%d'>#%d, %s</a> by %s\n\n%s"
		)
	),
	'ChronicleController' => array(
		'approve' => array(
			'config' => 'email',
			'bcc' => 'atmosphere@knightrealms.com',
			'from' => 'atmosphere@eodra.net',
			'subject' => 'Character Chronicle Approved',
			'body' => "Character Chronicle Entry approved for card <a href='http://cards.knightrealms.com/characters/view/%d'>#%d, %s</a>"
		)
	),
	'PersonalAction' => array(
		'results' => array(
			'config' => 'email',
			'from' => 'noreply@eodra.net',
			'subject' => 'Between Game Action Results',
			'body' => "%s performed the %s action in %s of %s.\nTarget: %s.\nComment: %s.\n\nResults: %s."
		)
	)
);

?>