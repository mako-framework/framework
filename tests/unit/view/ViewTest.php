<?php

/**
 * @copyright Frederic G. Østby
 * @license	http://www.makoframework.com/license
 */

namespace mako\tests\unit\view;

use mako\tests\TestCase;
use mako\view\renderers\RendererInterface;
use mako\view\View;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ViewTest extends TestCase
{
	/**
	 *
	 */
	public function testRender(): void
	{
		$variables =
		[
			'foo' => 'bar',
			'baz' => 4,
		];

		$initial =
		[
			'initial' => 'variables',
		];

		$total = [...$variables, ...$initial];

		$renderer = Mockery::mock(RendererInterface::class);

		$renderer->shouldReceive('render')->once()->with('TestView', $total)->andReturn('The new contents');

		$view = new View('TestView', $initial, $renderer);

		foreach ($variables as $key => $value) {
			$view->assign($key, $value);
		}

		$view->render();
	}

	/**
	 *
	 */
	public function testRenderWithToString(): void
	{
		$variables =
		[
			'foo' => 'bar',
			'baz' => 4,
		];

		$initial =
		[
			'initial' => 'variables',
		];

		$total = [...$variables, ...$initial];

		$renderer = Mockery::mock(RendererInterface::class);

		$renderer->shouldReceive('render')->once()->with('TestView', $total)->andReturn('The new contents');

		$view = new View('TestView', $initial, $renderer);

		foreach ($variables as $key => $value) {
			$view->assign($key, $value);
		}

		(string) $view;
	}
}
