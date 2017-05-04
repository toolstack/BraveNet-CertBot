<?php

require_once( './lib/functions.php' );
require_once( './lib/BraveNet.class.php' );
require_once( './lib/LetsEncrypt.class.php' );
require_once( './vendor/autoload.php' );

$config = bncb_load_config();

$BraveNet = new BraveNet();
$LetsEncrypt = new LetsEncrypt();

$BraveNet->login( $config['username'], $config['password'] );

$BraveNet->go_to_certs_page();

$cert_list = $BraveNet->get_cert_list();

$BraveNet->add_cert( $LetsEncrypt->get_public_key( $config['domain'] ), $LetsEncrypt->get_private_key( $config['domain'] ) );

foreach( $cert_list as $id => $domain ) {
	if( $domain == $config['domain'] ) {
		$BraveNet->delete_cert(	$id );
	}
}

$cert_list = $BraveNet->get_cert_list();

$found = false;
foreach( $cert_list as $id => $domain ) {
	if( $domain == $config['domain'] ) {
		$found = true;
	}
}

if( $found === false ) {
	echo 'ERROR: No certificate found for ' . $config['domain'] . ' after update!' . PHP_EOL;
	exit( 1 );
}

exit( 0 );

?>
