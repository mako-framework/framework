<?php

//---------------------------------------------
// Cryptography configuration
//---------------------------------------------

return array
(
	/**
	 * Default configuration to use.
	 */
	
	'default' => 'mcrypt',
	
	/**
	 * You can define as many cryptography configurations as you want.
	 *
	 * The supported cryptography libraries are: "Mcrypt", and "OpenSSL".
	 *
	 * library: Cryptography library you want to use (case-sensitive).
	 * cipher : The cipher method to use for encryption.
	 * key    : Key used to encrypt/decrypt data. You should NOT use the key included with the framework in a production environment!
	 * mode   : Encryption mode (only required when using the "mcrypt" library).
	 */
	
	'configurations' => array
	(
		'mcrypt' => array
		(
			'library' => 'Mcrypt',
			'cipher'  => MCRYPT_RIJNDAEL_256,
			'key'     => '`F0=nYsPkxolnlyc+z6jcnRdulJEOfqIyMWwlxeYtnFPi[lKMb',
			'mode'    => MCRYPT_MODE_ECB,
		),

		'openssl' => array
		(
			'library'  => 'OpenSSL',
			'key'      => '`F0=nYsPkxolnlyc+z6jcnRdulJEOfqIyMWwlxeYtnFPi[lKMb',
			'cipher'   => 'AES-256-OFB',
		),
	),
);

/** -------------------- End of file --------------------**/