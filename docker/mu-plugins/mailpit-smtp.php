<?php

/**
 * Plugin Name: Mailpit SMTP (dev/testing)
 * Description: Routes all outgoing mail through the Mailpit service via SMTP.
 */

add_action( 'phpmailer_init', function ( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->Host        = getenv( 'WORDPRESS_SMTP_HOST' ) ?: 'mailpit';
	$phpmailer->Port        = (int) ( getenv( 'WORDPRESS_SMTP_PORT' ) ?: 1025 );
	$phpmailer->SMTPAuth    = false;
	$phpmailer->SMTPAutoTLS = false;
	$phpmailer->SMTPSecure  = '';
} );

add_filter( 'wp_mail_from', function ( $email ) {
	return getenv( 'WORDPRESS_SMTP_FROM' ) ?: $email;
} );

add_filter( 'wp_mail_from_name', function ( $name ) {
	return getenv( 'WORDPRESS_SMTP_FROM_NAME' ) ?: $name;
} );
