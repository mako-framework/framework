<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\view\renderers;

use mako\file\FileSystem;
use mako\view\compilers\Template as Compiler;

use function array_merge;
use function array_pop;
use function current;
use function hash;
use function ob_get_clean;
use function ob_start;
use function str_replace;

/**
 * Mako template view renderer.
 */
class Template extends PHP
{
	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Open template blocks.
	 *
	 * @var array
	 */
	protected $openBlocks = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\file\FileSystem $fileSystem File system instance
	 * @param string                $cachePath  Cache path
	 */
	public function __construct(
		protected FileSystem $fileSystem,
		protected string $cachePath
	)
	{}

	/**
	 * Returns the path to the compiled template.
	 *
	 * @return string
	 */
	protected function getCompiledPath(string $view): string
	{
		return "{$this->cachePath}/" . hash('md5', $view) . '.php';
	}

	/**
	 * Returns TRUE if the template needs to be compiled and FALSE if not.
	 *
	 * @param  string $view     View path
	 * @param  string $compiled Compiled view path
	 * @return bool
	 */
	protected function needToCompile(string $view, string $compiled): bool
	{
		return !$this->fileSystem->has($compiled) || $this->fileSystem->lastModified($compiled) < $this->fileSystem->lastModified($view);
	}

	/**
	 * Compiles view.
	 *
	 * @param string $view View path
	 */
	protected function compile(string $view): void
	{
		(new Compiler($this->fileSystem, $this->cachePath, $view))->compile();
	}

	/**
	 * Opens a template block.
	 *
	 * @param string $name Block name
	 */
	public function open(string $name): void
	{
		ob_start() && $this->openBlocks[] = $name;
	}

	/**
	 * Closes a template block.
	 *
	 * @return string
	 */
	public function close(): string
	{
		return $this->blocks[array_pop($this->openBlocks)][] = ob_get_clean();
	}

	/**
	 * Output a template block.
	 *
	 * @param string $name Block name
	 */
	public function output(string $name): void
	{
		$parent = $this->close();

		$output = current($this->blocks[$name]);

		unset($this->blocks[$name]);

		if(!empty($parent))
		{
			$output = str_replace('__PARENT__', $parent, $output);
		}

		echo $output;
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(string $__view__, array $__variables__): string
	{
		$compiled = $this->getCompiledPath($__view__);

		if($this->needToCompile($__view__, $compiled))
		{
			$this->compile($__view__);
		}

		return parent::render($compiled, array_merge($__variables__, ['__renderer__' => $this]));
	}
}
