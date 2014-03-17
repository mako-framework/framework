<?php

namespace mako\view\renderers;

/**
 * View renderer base.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Renderer implements \mako\view\renderers\RendererInterface
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
	 * Assign a local view variable.
	 *
	 * @access  public
	 * @param   string                                  $name   Variable name
	 * @param   mixed                                   $value  View variable
	 * @return  \mako\view\renderers\RendererInterface
	 */

	public function assign($name, $value)
	{
		$this->variables[$name] = $value;

		return $this;
	}
}

/** -------------------- End of file -------------------- **/