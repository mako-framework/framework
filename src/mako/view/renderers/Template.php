<?php

namespace mako\view\renderers;

use \mako\view\compilers\Template as Compiler;

/**
 * Mako template view renderer.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Template implements \mako\view\renderers\RendererInterface, \mako\view\renderers\CacheableInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * View path.
	 * 
	 * @var string
	 */

	protected $view;

	/**
	 * View variables.
	 * 
	 * @var string
	 */

	protected $variables;

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

	protected $blocks = array();

	/**
	 * Open template blocks.
	 *
	 * @var array
	 */

	protected $openBlocks = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $view       View path
	 * @param   array   $variables  View variables
	 */

	public function __construct($view, array $variables)
	{
		$this->view = $view;

		$this->variables = $variables;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets the cache path.
	 * 
	 * @access  public
	 * @param   string                         $path  Cache path
	 * @return 	\mako\view\renderers\Template
	 */

	public function setCachePath($path)
	{
		$this->cachePath = $path;

		return $this;
	}

	/**
	 * Compiles the template if needed before returning the path to the compiled template.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function compile()
	{
		$compiled = $this->cachePath . '/' . md5($this->view) . '.php';

		if(!file_exists($compiled) || filemtime($compiled) < filemtime($this->view))
		{
			(new Compiler($this->cachePath, $this->view))->compile();
		}

		return $compiled;
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
	 * Returns the rendered view.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function render()
	{
		extract(array_merge($this->variables, ['__renderer__' => $this]), EXTR_REFS);
		
		ob_start();

		include($this->compile());

		return ob_get_clean();
	}
}

/** -------------------- End of file -------------------- **/