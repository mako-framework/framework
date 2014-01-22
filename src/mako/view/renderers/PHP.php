<?php

namespace mako\view\renderers;

/**
 * Plain PHP view renderer.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class PHP implements \mako\view\renderers\RendererInterface
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
	 * Returns the rendered view.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function render()
	{
		extract($this->variables, EXTR_REFS);
		
		ob_start();

		include($this->view);

		return ob_get_clean();
	}
}

/** -------------------- End of file -------------------- **/