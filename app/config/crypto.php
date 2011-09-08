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
	* The supported cryptography libraries are: "mcrypt", and "openssl".
	*
	* library: Cryptography library you want to use.
	* cipher : The cipher method to use for encryption.
	* key    : Key used to encrypt/decrypt data. You should NOT use the key included with the framwork in a production environment!
	* mode   : Encryption mode (only required when using the "mcrypt" library).
	*/
	
	'configurations' => array
	(
		'mcrypt' => array
		(
			'library' => 'mcrypt',
			'cipher'  => MCRYPT_RIJNDAEL_256,
			'key'     => '`F0=nYsPkxolnlyc+z6jcnRdulJEOfqIyMWwlxeYtnFPi[lKMb',
			'mode'    => MCRYPT_MODE_ECB,
		),

		'openssl' => array
		(
			'library'  => 'openssl',
			'key'      => '`F0=nYsPkxolnlyc+z6jcnRdulJEOfqIyMWwlxeYtnFPi[lKMb',
			'cipher'   => 'AES-256-OFB',
		),
	),
);

/** -------------------- End of file --------------------**/