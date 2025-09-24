<?php

declare( strict_types=1 );

use Isolated\Symfony\Component\Finder\Finder;

$exclusions_dir = __DIR__ . '/scoper-exclusions';

$wordpress_constants = file_exists( $exclusions_dir . '/wordpress_constants.php' )
	? require_once $exclusions_dir . '/wordpress_constants.php'
	: [];

$wordpress_classes = file_exists( $exclusions_dir . '/wordpress_classes.php' )
	? require_once $exclusions_dir . '/wordpress_classes.php'
	: [];

$wordpress_functions = file_exists( $exclusions_dir . '/wordpress_functions.php' )
	? require_once $exclusions_dir . '/wordpress_functions.php'
	: [];

return [
	'prefix'            => 'XCloner',
	'php-version'       => null,
	'exclude-constants' => $wordpress_constants,
	'exclude-classes'   => $wordpress_classes,
	'exclude-functions' => $wordpress_functions,
	'finders'           => [
		Finder::create()
		      ->files()
		      ->exclude( 'admin' )
		      ->in( __DIR__ . '/xcloner-backup-and-restore' ),
		Finder::create()
		      ->files()
		      ->in( __DIR__ . '/xcloner-backup-and-restore/admin' )
		      ->name( 'class-xcloner-admin.php' ),
		Finder::create()
		      ->files()
		      ->ignoreVCS( true )
		      ->notName( '/LICENSE/' )
		      ->notName( '/.*\\.md/' )
		      ->notName( '/.*\\.dist/' )
		      ->notName( '/Makefile/' )
		      ->notName( '/composer\\.json/' )
		      ->notName( '/composer\\.lock/' )
		      ->exclude( [
			      'doc',
			      'test',
			      'test_old',
			      'tests',
			      'Tests',
			      'vendor-bin',
		      ] )
		      ->in( __DIR__ . '/xcloner-backup-and-restore/vendor' ),
		Finder::create()
		      ->append(
			      array_filter(
				      [
					      file_exists( __DIR__ . '/xcloner-backup-and-restore/composer.json' )
						      ? __DIR__ . '/xcloner-backup-and-restore/composer.json'
						      : null
				      ]
			      )
		      ),
	],
];
