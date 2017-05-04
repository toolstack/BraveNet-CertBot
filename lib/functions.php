<?php

function bncb_load_config() {
	$config = array();

	if( ! file_exists( 'BraveNet-CertBot.ini' ) ) {
		echo 'ERROR: No INI file found!';
		exit( 1 );
	}
	
	$config = parse_ini_file( 'BraveNet-CertBot.ini' );
	
	$config['password'] = trim( $config['password'] );
	
	// Decrypt the password to use if it's been encrypted.
	if( substr( $config['password'], 0, 5 ) === ':enc:' ) {
		$config['password'] = bncb_decrypt_password( substr( $config['password'], 5 ) );
	} else if( strlen( $config['password'] ) > 0 ) {
		// Encrypt the password if it currently isn't and write out a new ini file.
		$new_ini  = '[Login]' . PHP_EOL;
		$new_ini .= 'username=' . $config['username'] . PHP_EOL;
		$new_ini .= 'password=":enc:' . bncb_encrypt_password( $config['password'] ) . '"' . PHP_EOL;
		
		file_put_contents( 'BraveNet-CertBot.ini', $new_ini );
	}

	GLOBAL $argc, $argv;

	// If we have less than one parameter ( [0] is always the script name itself ), bail.
	if( $argc < 2 ) {
		echo 'ERROR: You must provide a domain name!' . PHP_EOL;
		exit( 1 );
	}

	// First param is the domain to update.
	$config['domain'] = $argv[1];

	return $config;
	}

function bncb_get_encrypt_key() {
	// First determine how large of key we need.
	$key_size = mcrypt_get_key_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );

	exec( 'ifconfig', $output, $ret );

	$output = implode( $output, PHP_EOL );
	$pmac = strpos( $output, 'HWaddr' );
	$mac = substr( $output, ( $pmac + 7 ), 17 );

	$dirlist = scandir( '/etc/letsencrypt/renewal' );
	$filenames = '';

	foreach( $dirlist as $dir ) {
		if( strlen( $dir ) > 5 && '.conf' == substr( $dir, -5 ) ) {
			$filenames .= $dir;
		}
	}

	$hash = hash( 'sha256', $mac . $filenames );
	
	$key = substr( $hash, 0, $key_size );
	
	return $key;
}

function bncb_encrypt_password( $password ) {
	// If mcrypt isn't supported or it's a blank password, don't encrypt it.
	if( function_exists( 'mcrypt_encrypt' ) && $password != '' ) {
		// Get the encryption key we're going to use.
		$key = bncb_get_encrypt_key();

		// Create a random IV (with the specific length we need) to use with CBC encoding.
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );

		// Paste the IV and newly encrypted string together.
		$cpassword = $iv . mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $key, $password . ':enc:', MCRYPT_MODE_CBC, $iv );

		// Return a nice base64 encoded string to make it all look nice.
		return base64_encode( $cpassword );
	} else {
		echo 'ERROR: MCRYPT not installed!' . PHP_EOL;
		exit( 1 );
 	}
}

function bncb_decrypt_password( $password ) {
	// If mcrypt isn't supported or it's a blank password, don't decrypt it.
	if( function_exists( 'mcrypt_encrypt' ) && $password != '') {
		// Get the encryption key we're going to use.
		$key = bncb_get_encrypt_key();

		// Since we made it look nice with base64 while encrypting it, make it look messy again.
		$password = base64_decode( $password );
		
		// Retrieves the IV from the combined string.
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );
		$iv = substr( $password, 0, $iv_size );
		
		// Retrieves the cipher text (everything except the $iv_size in the front).
		$password = substr( $password, $iv_size );

		// Decrypt the password.
		$dpassword = mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $key, $password, MCRYPT_MODE_CBC, $iv );
		
		// may have to remove 00h valued characters from the end of plain text
		$dpassword = str_replace( chr(0), '', $dpassword );

		if( substr( $dpassword, -5 ) != ':enc:' ) {
			echo 'ERROR: Password could not be decrypted, has your CertBot config changed?' . PHP_EOL;
			exit( 1 );
		}
	
		$dpassword = substr( $dpassword, 0, -5 );
	
		return $dpassword;
	} else {
		echo 'ERROR: MCRYPT not installed!' . PHP_EOL;
		exit( 1 );
	}
}

?>
