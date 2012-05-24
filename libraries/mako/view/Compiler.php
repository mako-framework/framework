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
		'extensions',
		'views',
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

		return preg_replace('/{#(.*?)#}/s', '', $template);
	}

	/**
	* Compiles template extensions.
	*
	* @access  protected
	* @param   string     Template
	* @return  string     Compiled template
	*/

	protected static function extensions($template)
	{
		// Replace first occurance of extends tag with an empty string
		// and append the template with a view tag

		if(preg_match('/^{%\s{0,}extends:(.*?)\s{0,}%}/i', $template, $matches) > 0)
		{
			$template = preg_replace('/^{%\s{0,}extends:(.*?)\s{0,}%}/i', '', $template, 1);

			$template .= PHP_EOL . '<?php echo new mako\View(\'' . $matches[1] . '\'); ?>';
		}

		return $template;
	}

	/**
	* Compiles view includes.
	*
	* @access  protected
	* @param   string     Template
	* @return  string     Compiled template
	*/

	protected static function views($template)
	{
		// Replace view tags with view redering

		return preg_replace('/{{\s{0,}view:(.*?)\s{0,}}}/i', '<?php echo new mako\View(\'$1\', get_defined_vars()); ?>', $template);
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

		$template = preg_replace('/{%\s{0,}block:(.*?)\s{0,}%}(.*?){%\s{0,}endblock\s{0,}%}/is', '<?php mako\view\Block::open(\'$1\'); ?>$2<?php mako\view\Block::close(); ?>', $template);

		// Compile block output

		$template = preg_replace('/{{\s{0,}block:(.*?)\s{0,}}}(.*?){{\s{0,}endblock\s{0,}}}/is', '<?php if(mako\view\Block::exists(\'$1\')): ?>{{block:$1}}<?php else: ?>$2<?php endif; ?>', $template);

		return preg_replace('/{{\s{0,}block:(.*?)\s{0,}}}/i', '<?php echo mako\view\Block::get(\'$1\'); ?>', $template);
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
		// Closure that matches the "empty else" syntax

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

		// Compiles echo tags

		return preg_replace_callback('/{{\s{0,}(.*?)\s{0,}}}/', function($matches) use ($emptyElse)
		{
			if(preg_match('/raw:(.*)/i', $matches[1]) > 0)
			{
				return sprintf('<?php echo %s; ?>', $emptyElse(substr($matches[1], 4)));
			}
			else
			{
				return sprintf('<?php echo htmlspecialchars(%s, ENT_QUOTES, MAKO_CHARSET); ?>', $emptyElse($matches[1]));
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