<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view\renderers;

/**
 * Plain PHP view renderer.
 *
 * @author  Frederic G. Østby
 */

class PHP implements \mako\view\renderers\RendererInterface
{
	/**
	 * Returns the rendered view.
	 * 
	 * @access  public
	 * @param   string  $__view__       View path
	 * @param   array   $__variables__  View variables
	 * @return  string
	 */

	public function render($__view__, array $__variables__)
	{
		extract($__variables__, EXTR_REFS | EXTR_SKIP);

		ob_start();

		include($__view__);

		return ob_get_clean();
	}
}