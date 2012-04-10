<?php

namespace mako\view;

/**
* Template compiler.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Compiler
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Raw echo.
	*
	* @var string
	*/

	const RAW_ECHO = '<?php echo %s; ?>';

	/**
	* Escaped echo.
	*
	* @var string
	*/

	const ESCAPED_ECHO = '<?php echo htmlspecialchars(%s, ENT_COMPAT, MAKO_CHARSET); ?>';

	/**
	* Compilation order.
	*
	* @var array
	*/

	protected static $compileOrder = array
	(
		'comments',
		'controlStructures',
		'echos',
	);

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Protected constructor since this is a static class.
	*
	* @access  protected
	*/

	protected function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns true if the template has been modified since the last compile.
	*
	* @access  protected
	* @param  string      Path to template
	* @param  string      Path to compiled view
	*/

	protected static function isExpired($fileName, $storageName)
	{
		return filemtime($fileName) > filemtime($storageName);
	}

	/**
	* Compiles comments.
	*
	* @access  protected
	* @param   string     Template
	* @return  string     Compiled template
	*/

	protected static function comments($template)
	{
		// Strip comments from templates

		return preg_replace('/{\*.*?\*}/s', '', $template);
	}

	/**
	* Compiles control structures.
	*
	* @access  protected
	* @param   string     Template
	* @return  string     Compiled template
	*/

	protected static function controlStructures($template)
	{
		// Compile loops and conditional statement openings

		$template = preg_replace('/{{((foreach|for|while|if|else( )?if|else)(.*?)?)}}/i', '<?php $1: ?>', $template);

		// Compile loops and conditional statement endings

		return preg_replace('/{{(endforeach|endfor|endwhile|endif)}}/i', '<?php $1; ?>', $template);
	}

	/**
	* Compiles echos.
	*
	* @access  protected
	* @param   string     Template
	* @return  string     Compiled template
	*/

	protected static function echos($template)
	{
		// Compile raw echos

		$template = preg_replace_callback('/{{raw:(.*?)}}/', function($matches)
		{
			return sprintf(Compiler::RAW_ECHO, $matches[1]);
		}, $template);

		// Compile escaped echos

		return preg_replace_callback('/{{(.*?)}}/', function($matches)
		{
			return sprintf(Compiler::ESCAPED_ECHO, $matches[1]);
		}, $template);
	}

	/**
	* Compiles templates into views
	*
	* @access  public
	* @param   string  Path to template
	* @return  string  Path to the compiled view
	*/

	public static function compile($fileName)
	{
		$storageName = MAKO_APPLICATION . '/storage/templates/' . md5($fileName) . '.php';

		if(!file_exists($storageName) || static::isExpired($fileName, $storageName))
		{
			// Get teplate contents
			
			$template = file_get_contents($fileName);

			// Compile template

			foreach(static::$compileOrder as $method)
			{
				$template = static::$method($template);
			}

			// Store compiled template

			file_put_contents($storageName, trim($template));
		}

		return $storageName;
	}
}

/** -------------------- End of file --------------------**/