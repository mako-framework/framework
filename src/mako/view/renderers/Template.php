<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view\renderers;

use mako\file\FileSystem;
use mako\view\compilers\Template as Compiler;
use mako\view\renderers\PHP;

/**
 * Mako template view renderer.
 *
 * @author  Frederic G. Østby
 */
class Template extends PHP
{
	/**
	 * File system instance.
	 *
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * Cache path.
	 *
	 * @var string
	 */
	protected $cachePath;

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
	 * @access  public
	 * @param   \mako\file\FileSystem  $fileSystem  File system instance
	 * @param   string                 $cachePath   Cache path
	 */
	public function __construct(FileSystem $fileSystem, string $cachePath)
	{
		$this->fileSystem = $fileSystem;

		$this->cachePath = $cachePath;
	}

	/**
	 * Returns the path to the compiled template.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function getCompiledPath(string $view): string
	{
		return $this->cachePath . '/' . md5($view) . '.php';
	}

	/**
	 * Returns TRUE if the template needs to be compiled and FALSE if not.
	 *
	 * @access  protected
	 * @param   string     $view      View path
	 * @param   string     $compiled  Compiled view path
	 * @return  bool
	 */
	protected function needToCompile(string $view, string $compiled): bool
	{
		return !$this->fileSystem->has($compiled) || $this->fileSystem->lastModified($compiled) < $this->fileSystem->lastModified($view);
	}

	/**
	 * Compiles view.
	 *
	 * @access  protected
	 * @param   string     $view  View path
	 */
	protected function compile(string $view)
	{
		(new Compiler($this->fileSystem, $this->cachePath, $view))->compile();
	}

	/**
	 * Opens a template block.
	 *
	 * @access  public
	 * @param   string  $name  Block name
	 */
	public function open(string $name)
	{
		ob_start() && $this->openBlocks[] = $name;
	}

	/**
	 * Closes a template block.
	 *
	 * @access  public
	 * @return  string
	 */
	public function close(): string
	{
		return $this->blocks[array_pop($this->openBlocks)][] = ob_get_clean();
	}

	/**
	 * Output a template block.
	 *
	 * @access  public
	 * @param   string  $name  Block name
	 */
	public function output(string $name)
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
	 * {@inheritdoc}
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