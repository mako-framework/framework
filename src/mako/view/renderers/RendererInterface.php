<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view\renderers;

/**
 * Renderer interface.
 *
 * @author  Frederic G. Østby
 */

interface RendererInterface
{
	/**
	 * Returns the rendered view.
	 *
	 * @access  public
	 * @param   string  $__view__       View path
	 * @param   array   $__variables__  View variables
	 * @return  string
	 */

	public function render($__view__, array $__variables__);
}