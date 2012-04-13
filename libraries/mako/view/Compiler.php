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
	* Compilation order.
	*
	* @var array
	*/

	protected static $compileOrder = array
	(
		'comments',
		'blocks',
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

		return preg_replace('/{\*(.*?)\*}/s', '', $template);
	}

	/**
	* Compiles blocks.
	*
	* @access  protected
	* @param   string     Template
	* @return  string     Compiled template
	*/

	protected static function blocks($template)
	{
		// Compile blocks

		$template = preg_replace('/{%\s{0,}block:(.*?)\s{0,}%}(.*?){%\s{0,}endblock\s{0,}%}/s', '<?php mako\view\Block::open(\'$1\'); ?>$2<?php mako\view\Block::close(); ?>', $template);

		return preg_replace('/{{\s{0,}block:(.*?)\s{0,}}}/', '<?php echo mako\view\Block::get(\'$1\'); ?>', $template);
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
		// Compile control structures openings

		$template = preg_replace('/{%\s{0,}((foreach|for|while|if|else( )?if|else)(.*?)?)\s{0,}%}/i', '<?php $1: ?>', $template);

		// Compile control structures endings

		return preg_replace('/{%\s{0,}(endforeach|endfor|endwhile|endif|break|continue)\s{0,}%}/i', '<?php $1; ?>', $template);
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
		$emptyElse = function($matches)
		{
			if(preg_match('/(.*)\|\|(.*)/', $matches) !== 0)
			{
				return preg_replace('/(.*)\s{0,}\|\|\s{0,}(.*)/', '(!empty($1) ? $1 : $2)', $matches);
			}
			else
			{
				return $matches;
			}
		};

		return preg_replace_callback('/{{\s{0,}(.*?)\s{0,}}}/', function($matches) use ($emptyElse)
		{
			if(preg_match('/raw:(.*)/i', $matches[1]))
			{
				return sprintf('<?php echo %s; ?>', $emptyElse(substr($matches[1], 4)));
			}
			else
			{
				return sprintf('<?php echo htmlspecialchars(%s, ENT_COMPAT, MAKO_CHARSET, false); ?>', $emptyElse($matches[1]));
			}
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