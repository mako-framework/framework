<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view\renderers;

use mako\view\renderers\EscaperTrait;
use mako\view\renderers\RendererInterface;

/**
 * Plain PHP view renderer.
 *
 * @author  Frederic G. Østby
 */

class PHP implements RendererInterface
{
	use EscaperTrait;

	/**
	 * {@inheritdoc}
	 */

	public function render($__view__, array $__variables__)
	{
		extract($__variables__, EXTR_REFS | EXTR_SKIP);

		ob_start();

		include($__view__);

		return ob_get_clean();
	}
}