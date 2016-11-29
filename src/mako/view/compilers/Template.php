<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view\compilers;

use mako\file\FileSystem;

/**
 * Template compiler.
 *
 * @author  Frederic G. Østby
 */
class Template
{
	/**
	 * Verbatim placeholder.
	 *
	 * @var string
	 */
	const VERBATIM_PLACEHOLDER = '__VERBATIM_PLACEHOLDER__';

	/**
	 * File system instance.
	 *
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * Path to compiled template.
	 *
	 * @var string
	 */
	protected $cachePath;

	/**
	 * Path to raw template.
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * Verbatims.
	 *
	 * @var array
	 */
	protected $verbatims = [];

	/**
	 * Compilation order.
	 *
	 * @var array
	 */
	protected $compileOrder =
	[
		'collectVerbatims',
		'comments',
		'extensions',
		'views',
		'blocks',
		'controlStructures',
		'echos',
		'insertVerbatims',
	];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\file\FileSystem  $fileSystem  File system instance
	 * @param   string                 $cachePath   Cache path
	 * @param   string                 $template    Path to template
	 */
	public function __construct(FileSystem $fileSystem, string $cachePath, string $template)
	{
		$this->fileSystem = $fileSystem;

		$this->cachePath = $cachePath;

		$this->template = $template;
	}

	/**
	 * Collects verbatim blocks and replaces them with a palceholder.
	 *
	 * @access  protected
	 * @param   string    $template  Template
	 * @return  string
	 */
	protected function collectVerbatims(string $template): string
	{
		return preg_replace_callback('/{%\s*verbatim\s*%}(.*?){%\s*endverbatim\s*%}/is', function($matches)
		{
			$this->verbatims[] = $matches[1];

			return static::VERBATIM_PLACEHOLDER;
		}, $template);
	}

	/**
	 * Replaces verbatim placeholders with their original values.
	 *
	 * @access  protected
	 * @param   string    $template  Template
	 * @return  string
	 */
	public function insertVerbatims(string $template): string
	{
		foreach($this->verbatims as $verbatim)
		{
			$template = preg_replace('/' . static::VERBATIM_PLACEHOLDER . '/', $verbatim, $template, 1);
		}

		return $template;
	}

	/**
	 * Compiles comments.
	 *
	 * @access  protected
	 * @param   string     $template  Template
	 * @return  string
	 */
	protected function comments(string $template): string
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
	protected function extensions(string $template): string
	{
		// Replace first occurance of extends tag with an empty string
		// and append the template with a view tag

		if(preg_match('/^{%\s*extends:(.*?)\s*%}/i', $template, $matches) > 0)
		{
			$replacement = '<?php $__view__ = $__viewfactory__->create(' . $matches[1] . '); $__renderer__ = $__view__->getRenderer(); ?>';

			$template = preg_replace('/^{%\s*extends:(.*?)\s*%}/i', $replacement, $template, 1);

			$template .= '<?php echo $__view__->render(); ?>';
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
	protected function views(string $template): string
	{
		// Compile view includes with parameters

		$template = preg_replace('/{{view:((((?!,(?![^()]*\))[^,]+).)+?),\s*\[(.*?)\]}})/i', '<?php echo $__viewfactory__->create($2, [$4] + get_defined_vars())->render(); ?>', $template);

		// Compile view includes without parameters

		return preg_replace('/{{\s*view:(.*?)\s*}}/i', '<?php echo $__viewfactory__->create($1, get_defined_vars())->render(); ?>', $template);
	}

	/**
	 * Compiles blocks.
	 *
	 * @access  protected
	 * @param   string     $template  Template
	 * @return  string
	 */
	protected function blocks(string $template): string
	{
		// Compile blocks

		$template = preg_replace('/{%\s*block:(.*?)\s*%}(.*?){%\s*endblock\s*%}/is', '<?php $__renderer__->open(\'$1\'); ?>$2<?php $__renderer__->close(); ?>', $template);

		// Compile block output

		return preg_replace('/{{\s*block:(.*?)\s*}}(.*?){{\s*endblock\s*}}/is', '<?php $__renderer__->open(\'$1\'); ?>$2<?php $__renderer__->output(\'$1\'); ?>', $template);
	}

	/**
	 * Compiles control structures.
	 *
	 * @access  protected
	 * @param   string     $template  Template
	 * @return  string
	 */
	protected function controlStructures(string $template): string
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
	protected function echos(string $template): string
	{
		// Closure that matches the "empty else" syntax

		$emptyElse = function($matches)
		{
			if(preg_match('/(.*)\|\|(.*)/', $matches) !== 0)
			{
				return preg_replace_callback('/(.*)\s*\|\|\s*(.*)/', function($matches)
				{
					return trim($matches[1]) . ' ?? ' . trim($matches[2]);
				}, $matches);
			}

			return $matches;
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
				return sprintf('<?php echo $this->escapeHTML(%s, $__charset__, false); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/attribute\s*:(.*)/i', $matches[1]) > 0)
			{
				return sprintf('<?php echo $this->escapeAttribute(%s, $__charset__); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/js\s*:(.*)/i', $matches[1]) > 0)
			{
				return sprintf('<?php echo $this->escapeJavascript(%s, $__charset__); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/css\s*:(.*)/i', $matches[1]) > 0)
			{
				return sprintf('<?php echo $this->escapeCSS(%s, $__charset__); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/url\s*:(.*)/i', $matches[1]) > 0)
			{
				return sprintf('<?php echo $this->escapeURL(%s, $__charset__); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			else
			{
				return sprintf('<?php echo $this->escapeHTML(%s, $__charset__); ?>', $emptyElse($matches[1]));
			}
		}, $template);
	}

	/**
	 * Compiles templates into views.
	 *
	 * @access  public
	 */
	public function compile()
	{
		// Get teplate contents

		$contents = $this->fileSystem->get($this->template);

		// Compile template

		foreach($this->compileOrder as $method)
		{
			$contents = $this->$method($contents);
		}

		// Store compiled template

		$this->fileSystem->put($this->cachePath . '/' . md5($this->template) . '.php', trim($contents));
	}
}