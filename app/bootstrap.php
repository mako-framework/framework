<?php

//------------------------------------------------------------------------------------------
// Define global convenience functions
//------------------------------------------------------------------------------------------

if(!function_exists('__'))
{
	/**
	* Alias of mako\I18n::translate()
	*
	* Returns a translated string of the current language. 
	* If no translation exists then the submitted string will be returned.
	*
	* @access  public
	* @param   string   Text to translate
	* @param   array   (optional) Value or array of values to replace in the translated text
	* @param   string  (optional) Name of the language you want to translate to
	* @return  string
	*/

	function __($string, array $vars = array(), $language = null)
	{
		return mako\I18n::translate($string, $vars, $language);
	}
}

if(!function_exists('dump_var'))
{
	/**
	* Works like var_dump except that it wraps the variable in <pre> tags.
	*
	* @access  public
	* @param   mixed   Variable you want to dump
	*/

	function dump_var()
	{
		ob_start();

		call_user_func_array('var_dump', func_get_args());

		echo '<pre>' . ob_get_clean() . '</pre>';
	}
}

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