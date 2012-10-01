<?php

namespace mako\view\renderer;

/**
 * Renderer interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface RendererInterface
{
	public function __construct($view);
	public function render(array $variables, array $globalVariables);
}

/** -------------------- End of file --------------------**/