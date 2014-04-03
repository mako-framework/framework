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
	public function __construct($view, array $variables);
	public function assign($key, $value);
	public function render();
}