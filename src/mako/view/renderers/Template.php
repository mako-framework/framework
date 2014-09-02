<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view\renderers;

use \mako\file\FileSystem;
use \mako\view\compilers\Template as Compiler;
use \mako\view\renderers\PHP;

/**
 * Mako template view renderer.
 *
 * @author  Frederic G. Østby
 */

class Template extends PHP
{
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

	public function __construct(FileSystem $fileSystem, $cachePath)
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

	protected function getCompiledPath($view)
	{
		return $this->cachePath . '/' . md5($view) . '.php';
	}

	/**
	 * Returns TRUE if the template needs to be compiled and FALSE if not.
	 * 
	 * @access  protected
	 * @param   string     $view      View path
	 * @param   string     $compiled  Compiled view path
	 * @return  boolean
	 */

	protected function needToCompile($view, $compiled)
	{
		return !$this->fileSystem->exists($compiled) || $this->fileSystem->lastModified($compiled) < $this->fileSystem->lastModified($view);
	}

	/**
	 * Compiles view.
	 * 
	 * @access  protected
	 * @param   string     $view  View path
	 */

	protected function compile($view)
	{
		(new Compiler($this->fileSystem, $this->cachePath, $view))->compile();
	}

	/**
	 * Opens a template block.
	 *
	 * @access  public
	 * @param   string  $name  Block name
	 */

	public function open($name)
	{
		ob_start() && $this->openBlocks[] = $name;
	}

	/**
	 * Closes a template block.
	 *
	 * @access  public
	 */

	public function close()
	{
		$this->blocks[array_pop($this->openBlocks)] = ob_get_clean();
	}

	/**
	 * Output a template block.
	 * 
	 * @access  public
	 * @param   string  $name  Block name
	 */

	public function output($name)
	{
		array_pop($this->openBlocks);

		$output = ob_get_clean();

		echo isset($this->blocks[$name]) ? str_replace('__PARENT__', $output, $this->blocks[$name]) : $output;
	}

	/**
	 * {@inheritdoc}
	 */

	public function render($__view__, array $__variables__)
	{
		// Get path to the compiled view

		$compiled = $this->getCompiledPath($__view__);

		// Compile the view if the cache doesn't exist or if it is expired

		if($this->needToCompile($__view__, $compiled))
		{
			$this->compile($__view__);
		}

		// Render the view

		$rendered = parent::render($compiled, array_merge($__variables__, ['__renderer__' => $this]));

		// Reset the view blocks

		$this->openBlocks = $this->blocks = [];

		// Return the rendered view

		return $rendered;
	}
}