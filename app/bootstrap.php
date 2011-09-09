<?php

//------------------------------------------------------------------------------------------
// Define global convenience functions
//------------------------------------------------------------------------------------------

/**
* Alias of mako\I18n::getText()
*
* Returns a translated string of the current language. 
* If no translation exists then the submitted string will be returned.
*
* @access  public
* @param   string  Text to translate
* @param   array  (optional) Value or array of values to replace in the translated text
* @return  string
*/

function __($string, array $vars = null)
{
	return mako\I18n::getText($string, $vars);
}

//------------------------------------------------------------------------------------------
// Setup autoloading of third party libraries
//------------------------------------------------------------------------------------------

// Doctrine

/*require MAKO_LIBRARIES_PATH . '/doctrine/Doctrine.php';

spl_autoload_register(array('Doctrine', 'autoload'));*/

// Swift Mailer

/*require MAKO_LIBRARIES_PATH . '/swiftmailer/swift_required.php';*/

// Zend Framework

/*set_include_path(get_include_path() . PATH_SEPARATOR . MAKO_LIBRARIES_PATH);

require MAKO_LIBRARIES_PATH . '/Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance();*/

/** -------------------- End of file --------------------**/