<?php

namespace mako\view\renderers;

/**
 * Plain PHP view renderer.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class PHP extends \mako\view\renderers\Renderer implements \mako\view\renderers\RendererInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

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

