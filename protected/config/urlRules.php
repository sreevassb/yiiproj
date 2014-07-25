<?php
return array (
		'/login' => '/site/login',
		'/contact' => 'site/contact',
		
		'api/user/login.<extension:\w+>' => 'ApiUsers/FbLogin',

		
		/**
		 * Don't add/edit after this comment
		 */
		
		// Add rules before this line. Don't add/edit after this line
		'<controller:\w+>/<id:\d+>' => '<controller>/view',
		'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
		'<controller:\w+>/<action:\w+>' => '<controller>/<action>' 
)
;
