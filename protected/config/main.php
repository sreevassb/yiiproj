<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Web Application',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'ext.YiiMongoDbSuite.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		
	 'gii'=>array(
        'class'=>'system.gii.GiiModule',
        'password'=>'mongogii',
        // add additional generator path
        'generatorPaths'=>array(
            'ext.YiiMongoDbSuite.gii'
        ),
    ),
		
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
			
		'mongodb' => array(
        'class'             => 'EMongoDB',
        'connectionString'  => 'mongodb://localhost',
        'dbName'            => 'test',
        'fsyncFlag'         => false,
        'safeFlag'          => false,
        'useCursor'         => false,
    ),
		// uncomment the following to enable URLs in path-format
		
		'urlManager'=>array(
                    				'urlFormat'=>'path',
                    				'showScriptName'=>false,
                    				'rules'=>include('urlRules.php'),
                    		),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
);
