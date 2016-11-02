<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view;

use mako\view\renderers\RendererInterface;

/**
 * View.
 *
 * @author  Frederic G. Østby
 */
class View
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
	 * @access  public
	 * @param   string                                  $path       View path
	 * @param   array                                   $variables  View variables
	 * @param   \mako\view\renderers\RendererInterface  $renderer   Renderer instance
	 */
	public function __construct(string $path, array $variables, RendererInterface $renderer)
	{
		$this->path = $path;

		$this->variables = $variables;

		$this->renderer = $renderer;
	}

	/**
	 * Returns the renderer instance.
	 *
	 * @access  public
	 * @return  \mako\view\renderers\RendererInterface
	 */
	public function getRenderer(): RendererInterface
	{
		return $this->renderer;
	}

	/**
	 * Assign a local view variable.
	 *
	 * @access  public
	 * @param   string           $name   Variable name
	 * @param   mixed            $value  View variable
	 * @return  \mako\view\View
	 */
	public function assign(string $name, $value): View
	{
		$this->variables[$name] = $value;

		return $this;
	}

	/**
	 * Returns the rendered view.
	 *
	 * @access  public
	 * @return  string
	 */
	public function render(): string
	{
		return $this->renderer->render($this->path, $this->variables);
	}
}