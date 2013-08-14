<?php

namespace mako\view\compiler;

/**
 * Template compiler.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Template
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Path to raw template.
	 *
	 * @var string
	 */

	protected $template;

	/**
	 * Path to compiled template.
	 *
	 * @var string
	 */

	protected $compiled;

	/**
	 * Compilation order.
	 *
	 * @var array
	 */

	protected $compileOrder = array
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
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $template  Path to raw template
	 * @param   string  $storage   Path to compiled template
	 */

	public function __construct($template, $compiled)
	{
		$this->template = $template;
		$this->compiled = $compiled;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Compiles comments.
	 *
	 * @access  protected
	 * @param   string     $template  Template
	 * @return  string
	 */

	protected function comments($template)
	{
		// Strip comments from templates

		return preg_replace('/{#(.*?)#}/s', '', $template);
	}

	/**
	 * Compiles template extensions.
	 *
	 * @access  protected
	 * @param   string     $template  Template
	 * @return  string
	 */

	protected function extensions($template)
	{
		// Replace first occurance of extends tag with an empty string
		// and append the template with a view tag

		if(preg_match('/^{%\s*extends:(.*?)\s*%}/i', $template, $matches) > 0)
		{
			$template = preg_replace('/^{%\s*extends:(.*?)\s*%}/i', '', $template, 1);

			$template .= PHP_EOL . '<?php echo new mako\View(\'' . $matches[1] . '\', get_defined_vars()); ?>';
		}

		return $template;
	}

	/**
	 * Compiles view includes.
	 *
	 * @access  protected
	 * @param   string     $template  Template
	 * @return  string
	 */

	protected function views($template)
	{
		// Replace view tags with view redering

		return preg_replace('/{{\s*view:(.*?)\s*}}/i', '<?php echo new mako\View(\'$1\', get_defined_vars()); ?>', $template);
	}

	/**
	 * Compiles blocks.
	 *
	 * @access  protected
	 * @param   string     $template  Template
	 * @return  string
	 */

	protected function blocks($template)
	{
		// Compile blocks

		$template = preg_replace('/{%\s*block:(.*?)\s*%}(.*?){%\s*endblock\s*%}/is', '<?php mako\view\renderer\template\Block::open(\'$1\'); ?>$2<?php mako\view\renderer\template\Block::close(); ?>', $template);

		// Compile block output

		return preg_replace('/{{\s*block:(.*?)\s*}}(.*?){{\s*endblock\s*}}/is', '<?php if(mako\view\renderer\template\Block::exists(\'$1\')): ?><?php echo mako\view\renderer\template\Block::get(\'$1\'); ?><?php else: ?>$2<?php endif; ?>', $template);
	}

	/**
	 * Compiles control structures.
	 *
	 * @access  protected
	 * @param   string     $template  Template
	 * @return  string
	 */

	protected function controlStructures($template)
	{
		// Compile control structures openings

		$template = preg_replace('/{%\s*((foreach|for|while|if|else( )?if|else)(.*?)?)\s*%}/i', '<?php $1: ?>', $template);

		// Compile control structures endings

		return preg_replace('/{%\s*(endforeach|endfor|endwhile|endif|break|continue)\s*%}/i', '<?php $1; ?>', $template);
	}

	/**
	 * Compiles echos.
	 *
	 * @access  protected
	 * @param   string     $template  Template
	 * @return  string
	 */

	protected function echos($template)
	{
		// Closure that matches the "empty else" syntax

		$emptyElse = function($matches)
		{
			if(preg_match('/(.*)\|\|(.*)/', $matches) !== 0)
			{
				return preg_replace('/(.*)\s*\|\|\s*(.*)/', '(!empty($1) ? $1 : $2)', $matches);
			}
			else
			{
				return $matches;
			}
		};

		// Compiles echo tags

		return preg_replace_callback('/{{\s*(.*?)\s*}}/', function($matches) use ($emptyElse)
		{
			if(preg_match('/raw\s*:(.*)/i', $matches[1]) > 0)
			{
				return sprintf('<?php echo %s; ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/preserve\s*:(.*)/i', $matches[1]) > 0)
			{
				return sprintf('<?php echo htmlspecialchars(%s, ENT_QUOTES, MAKO_CHARSET, false); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			else
			{
				return sprintf('<?php echo htmlspecialchars(%s, ENT_QUOTES, MAKO_CHARSET); ?>', $emptyElse($matches[1]));
			}
		}, $template);
	}

	/**
	 * Compiles templates into views.
	 *
	 * @access  public
	 * @param   string  $fileName  Path to template
	 * @return  string
	 */

	public function compile()
	{
		// Get teplate contents
			
		$contents = file_get_contents($this->template);

		// Compile template

		foreach($this->compileOrder as $method)
		{
			$contents = $this->$method($contents);
		}

		// Store compiled template

		file_put_contents($this->compiled, trim($contents));
	}
}

/** -------------------- End of file -------------------- **/