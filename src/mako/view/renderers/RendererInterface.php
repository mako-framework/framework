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
	public function render($__view__, array $__variables__);
}