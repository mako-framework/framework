<?php

namespace mako\view\renderer;

/**
 * Plain PHP view renderer.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class PHP implements \mako\view\renderer\RendererInterface
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
	 * Global view variables.
	 * 
	 * @var string
	 */

	protected $globalVariables;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $view             View path
	 * @param   array   $variables        View variables
	 * @param   array   $globalVariables  Global view variables
	 */

	public function __construct($view, array $variables, array $globalVariables)
	{
		$this->view            = $view;
		$this->variables       = $variables;
		$this->globalVariables = $globalVariables;
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
		extract(array_merge($this->variables, $this->globalVariables), EXTR_REFS);
		
		ob_start();

		include($this->view);

		return ob_get_clean();
	}
}

/** -------------------- End of file -------------------- **/