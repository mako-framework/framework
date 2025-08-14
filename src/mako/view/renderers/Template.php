<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\view\renderers;

use mako\file\FileSystem;
use mako\view\compilers\Template as Compiler;
use Override;

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
	 */
	protected array $blocks = [];

	/**
	 * Open template blocks.
	 */
	protected array $openBlocks = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected FileSystem $fileSystem,
		protected string $cachePath
	) {
	}

	/**
	 * Returns the path to the compiled template.
	 */
	protected function getCompiledPath(string $view): string
	{
		return "{$this->cachePath}/" . hash('xxh128', $view) . '.php';
	}

	/**
	 * Returns TRUE if the template needs to be compiled and FALSE if not.
	 */
	protected function needToCompile(string $view, string $compiled): bool
	{
		return !$this->fileSystem->has($compiled) || $this->fileSystem->lastModified($compiled) < $this->fileSystem->lastModified($view);
	}

	/**
	 * Compiles view.
	 */
	protected function compile(string $view): void
	{
		(new Compiler($this->fileSystem, $this->cachePath, $view))->compile();
	}

	/**
	 * Opens a template block.
	 */
	public function open(string $name): void
	{
		ob_start() && $this->openBlocks[] = $name;
	}

	/**
	 * Closes a template block.
	 */
	public function close(): string
	{
		return $this->blocks[array_pop($this->openBlocks)][] = ob_get_clean();
	}

	/**
	 * Output a template block.
	 */
	public function output(string $name): void
	{
		$parent = $this->close();

		$output = current($this->blocks[$name]);

		unset($this->blocks[$name]);

		if (!empty($parent)) {
			$output = str_replace('__PARENT__', $parent, $output);
		}

		echo $output;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function render(string $__view__, array $__variables__): string
	{
		$compiled = $this->getCompiledPath($__view__);

		if ($this->needToCompile($__view__, $compiled)) {
			$this->compile($__view__);
		}

		return parent::render($compiled, [...$__variables__, ...['__renderer__' => $this]]);
	}
}
