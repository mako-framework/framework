<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\view\renderers;

use mako\view\renderers\traits\EscaperTrait;

use function extract;
use function ob_get_clean;
use function ob_start;

/**
 * Plain PHP view renderer.
 *
 * @author Frederic G. Østby
 */
class PHP implements RendererInterface
{
	use EscaperTrait;

	/**
	 * {@inheritdoc}
	 */
	public function render(string $__view__, array $__variables__): string
	{
		extract($__variables__, EXTR_SKIP);

		ob_start();

		include($__view__);

		return ob_get_clean();
	}
}
