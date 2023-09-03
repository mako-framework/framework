<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\view\renderers;

/**
 * Renderer interface.
 */
interface RendererInterface
{
	/**
	 * Returns the rendered view.
	 */
	public function render(string $__view__, array $__variables__): string;
}
