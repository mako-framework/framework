<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\view;

use mako\view\renderers\RendererInterface;
use Stringable;

/**
 * View.
 */
class View implements Stringable
{
	/**
	 * View path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * View variables.
	 *
	 * @var array
	 */
	protected $variables;

	/**
	 * View renderer instance.
	 *
	 * @var \mako\view\renderers\RendererInterface
	 */
	protected $renderer;

	/**
	 * Constructor.
	 *
	 * @param string                                 $path      View path
	 * @param array                                  $variables View variables
	 * @param \mako\view\renderers\RendererInterface $renderer  Renderer instance
	 */
	public function __construct(string $path, array $variables, RendererInterface $renderer)
	{
		$this->path = $path;

		$this->variables = $variables;

		$this->renderer = $renderer;
	}

	/**
	 * Returns the assigned view variables.
	 *
	 * @return array
	 */
	public function getVariables(): array
	{
		return $this->variables;
	}

	/**
	 * Returns the renderer instance.
	 *
	 * @return \mako\view\renderers\RendererInterface
	 */
	public function getRenderer(): RendererInterface
	{
		return $this->renderer;
	}

	/**
	 * Assign a local view variable.
	 *
	 * @param  string          $name  Variable name
	 * @param  mixed           $value View variable
	 * @return \mako\view\View
	 */
	public function assign(string $name, mixed $value): View
	{
		$this->variables[$name] = $value;

		return $this;
	}

	/**
	 * Returns the rendered view.
	 *
	 * @return string
	 */
	public function render(): string
	{
		return $this->renderer->render($this->path, $this->variables);
	}

	/**
	 * Returns the rendered view.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->render();
	}
}
