<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\view\compilers;

use mako\file\FileSystem;

use function ltrim;
use function md5;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;
use function strpos;
use function substr;
use function trim;

/**
 * Template compiler.
 */
class Template
{
	/**
	 * Verbatim placeholder.
	 *
	 * @var string
	 */
	protected const VERBATIM_PLACEHOLDER = '__VERBATIM_PLACEHOLDER__';

	/**
	 * Verbatims.
	 */
	protected array $verbatims = [];

	/**
	 * Compilation order.
	 */
	protected array $compileOrder =
	[
		'collectVerbatims',
		'comments',
		'extensions',
		'nospaces',
		'views',
		'captures',
		'blocks',
		'controlStructures',
		'echos',
		'insertVerbatims',
	];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected FileSystem $fileSystem,
		protected string $cachePath,
		protected string $template
	)
	{}

	/**
	 * Collects verbatim blocks and replaces them with a palceholder.
	 */
	protected function collectVerbatims(string $template): string
	{
		return preg_replace_callback('/{%\s*verbatim\s*%}(.*?){%\s*endverbatim\s*%}/is', function ($matches)
		{
			$this->verbatims[] = $matches[1];

			return static::VERBATIM_PLACEHOLDER;
		}, $template);
	}

	/**
	 * Replaces verbatim placeholders with their original values.
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
	 */
	protected function comments(string $template): string
	{
		// Strip comments from templates

		return preg_replace('/{#(.*?)#}/s', '', $template);
	}

	/**
	 * Compiles template extensions.
	 */
	protected function extensions(string $template): string
	{
		// Replace first occurance of extends tag with an empty string
		// and append the template with a view tag

		if(preg_match('/^{%\s*extends:(.*?)\s*%}/i', $template, $matches) === 1)
		{
			$replacement = '<?php $__view__ = $__viewfactory__->create(' . $matches[1] . '); $__renderer__ = $__view__->getRenderer(); ?>';

			$template = preg_replace('/^{%\s*extends:(.*?)\s*%}/i', $replacement, $template, 1);

			$template .= '<?php echo $__view__->render(); ?>';
		}

		return $template;
	}

	/**
	 * Compiles nospace blocks.
	 */
	protected function nospaces(string $template): string
	{
		// Compile regular nospace blocks

		$template = preg_replace_callback('/{%\s*nospace\s*%}(.*?){%\s*endnospace\s*%}/is', static function ($matches)
		{
			return trim(preg_replace('/>\s+</', '><', $matches[1]));
		}, $template);

		// Compile buffered nospace blocks

		$template = preg_replace('/{%\s*nospace:buffered\s*%}(.*?){%\s*endnospace\s*%}/is', '<?php ob_start(); ?>$1<?php echo trim(preg_replace(\'/>\s+</\', \'><\', ob_get_clean())); ?>', $template);

		return $template;
	}

	/**
	 * Compiles view includes.
	 */
	protected function views(string $template): string
	{
		// Compile view includes with parameters

		$template = preg_replace('/{{\s*view:(.*?)\s*,(?![^\(]*\))\s*(.*?)\s*}}/i', '<?php echo $__viewfactory__->create($1, $2 + get_defined_vars())->render(); ?>', $template);

		// Compile view includes without parameters

		return preg_replace('/{{\s*view:(.*?)\s*}}/i', '<?php echo $__viewfactory__->create($1, get_defined_vars())->render(); ?>', $template);
	}

	/**
	 * Compiles capture blocks.
	 */
	protected function captures(string $template): string
	{
		return preg_replace_callback('/{%\s*capture:(\$?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*?)\s*%}(.*?){%\s*endcapture\s*%}/is', static function ($matches)
		{
			return '<?php ob_start(); ?>' . $matches[2] . '<?php $' . ltrim($matches[1], '$') . ' = ob_get_clean(); ?>';
		}, $template);
	}

	/**
	 * Compiles blocks.
	 */
	protected function blocks(string $template): string
	{
		// Compile blocks

		$template = preg_replace("/{%\s*block:'?(.*?)'?\s*%}(.*?){%\s*endblock\s*%}/is", '<?php $__renderer__->open(\'$1\'); ?>$2<?php $__renderer__->close(); ?>', $template);

		// Compile block output

		return preg_replace("/{{\s*block:'?(.*?)'?\s*}}(.*?){{\s*endblock\s*}}/is", '<?php $__renderer__->open(\'$1\'); ?>$2<?php $__renderer__->output(\'$1\'); ?>', $template);
	}

	/**
	 * Compiles control structures.
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
	 */
	protected function echos(string $template): string
	{
		// Closure that matches the "empty else" syntax

		$emptyElse = static function ($matches)
		{
			if(preg_match('/(.*)((,\s*default:\s*))(.+)/', $matches) === 1)
			{
				return preg_replace_callback('/(.*)(,\s*default:\s*)(.+)/', static function ($matches)
				{
					return '(empty(' . trim($matches[1]) . ') ? (isset(' . trim($matches[1]) . ') && (' . trim($matches[1]) . ' === 0 || ' . trim($matches[1]) . ' === 0.0 || ' . trim($matches[1]) . ' === \'0\') ? ' . trim($matches[1]) . ' : ' . trim($matches[3]) . ') : ' . trim($matches[1]) . ')';
				}, $matches);
			}

			return $matches;
		};

		// Compiles echo tags

		return preg_replace_callback('/{{\s*(.*?)\s*}}/', static function ($matches) use ($emptyElse)
		{
			if(preg_match('/raw\s*:(.*)/i', $matches[1]) === 1)
			{
				return sprintf('<?php echo %s; ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/preserve\s*:(.*)/i', $matches[1]) === 1)
			{
				return sprintf('<?php echo $this->escapeHTML(%s, $__charset__, false); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/attribute\s*:(.*)/i', $matches[1]) === 1)
			{
				return sprintf('<?php echo $this->escapeAttribute(%s, $__charset__); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/js\s*:(.*)/i', $matches[1]) === 1)
			{
				return sprintf('<?php echo $this->escapeJavascript(%s, $__charset__); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/css\s*:(.*)/i', $matches[1]) === 1)
			{
				return sprintf('<?php echo $this->escapeCSS(%s, $__charset__); ?>', $emptyElse(substr($matches[1], strpos($matches[1], ':') + 1)));
			}
			elseif(preg_match('/url\s*:(.*)/i', $matches[1]) === 1)
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
	 */
	public function compile(): void
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
