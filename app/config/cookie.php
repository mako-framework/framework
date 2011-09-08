<?php

//---------------------------------------------
// Cookie configuration
//---------------------------------------------

return array
(
	/**
	* Secret used to sign cookie. 
	* You should NOT use the secret included with the framwork in a production environment!
	*/
	
	'secret'   => 'oib[H7:Jqn2QcMv77>qMpc<gTLFndNLd',
	
	/**
	* The domain that the cookie is available to.
	* To make the cookie available on all subdomains of example.org (including example.org itself) then you'd set it to '.example.org'.
	*/
	
	'domain'   => '',
	
	/**
	* The path on the server in which the cookie will be available on.
	* If set to '/', the cookie will be available within the entire domain. 
	* If set to '/foo/', the cookie will only be available within the /foo/ directory and all sub-directories.
	*/
	
	'path'     => '/',
);

/** -------------------- End of file --------------------**/