<?php

//------------------------------------------------------------------------------------------
// Define global convenience functions
//------------------------------------------------------------------------------------------

if(!function_exists('e'))
{
	/**
	* Returns a string where special characters have been converted to HTML entities.
	*
	* @access  public
	* @param   string   The string being converted.
	* @return  string
	*/

	function e($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, MAKO_CHARSET);
	}
}

//------------------------------------------------------------------------------------------
// Setup autoloading of third party libraries
//------------------------------------------------------------------------------------------

// Zend Framework

//mako\ClassLoader::directory(MAKO_LIBRARIES_PATH . '/Zend');

// Swift Mailer

//require MAKO_LIBRARIES_PATH . '/swiftmailer/swift_required.php'

/** -------------------- End of file --------------------**/