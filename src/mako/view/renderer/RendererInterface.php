<?php

namespace mako\view\renderer;

/**
 * Renderer interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface RendererInterface
{
	public function __construct($view, array $variables, array $globalVariables);
	public function render();
}

/** -------------------- End of file -------------------- **/