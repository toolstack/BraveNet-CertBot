<?php

class LetsEncrypt {
	function get_public_key( $domain ) {
		return file_get_contents( '/etc/letsencrypt/live/' . $domain . '/fullchain.pem' );
	}
	
	function get_private_key( $domain ) {
		return file_get_contents( '/etc/letsencrypt/live/' . $domain . '/privkey.pem' );
	}
}
