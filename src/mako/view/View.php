<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\view;

use mako\view\renderers\RendererInterface;
use Override;
use Stringable;

/**
 * View.
 */
class View implements Stringable
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $path,
		protected array $variables,
		protected RendererInterface $renderer
	) {
	}

	/**
	 * Returns the assigned view variables.
	 */
	public function getVariables(): array
	{
		return $this->variables;
	}

	/**
	 * Returns the renderer instance.
	 */
	public function getRenderer(): RendererInterface
	{
		return $this->renderer;
	}

	/**
	 * Assign a local view variable.
	 */
	public function assign(string $name, mixed $value): View
	{
		$this->variables[$name] = $value;

		return $this;
	}

	/**
	 * Returns the rendered view.
	 */
	public function render(): string
	{
		return $this->renderer->render($this->path, $this->variables);
	}

	/**
	 * Returns the rendered view.
	 */
	#[Override]
	public function __toString(): string
	{
		return $this->render();
	}
}
