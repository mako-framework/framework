<?php

namespace mako\view\renderer;

/**
 * Plain PHP view renderer.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $view  View path
	 */

	public function __construct($view)
	{
		$this->view = $view;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the rendered view.
	 * 
	 * @access  public
	 * @param   array   $variables        View variables
	 * @param   array   $globalVariables  Global view variables
	 * @return  string
	 */

	public function render(array $variables, array $globalVariables)
	{
		extract(array_merge($variables, $globalVariables), EXTR_REFS); // Extract variables as references
		
		ob_start();

		include($this->view);

		return ob_get_clean();
	}
}

/** -------------------- End of file --------------------**/