<?php

/**
 * @copyright Frederic G. Østby
 * @license	http://www.makoframework.com/license
 */

namespace mako\tests\unit\view;

use mako\tests\TestCase;
use mako\view\renderers\RendererInterface;
use mako\view\View;

/**
 * @group unit
 */
class ViewTest extends TestCase
{
	public function testView(): void
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

		$total = array_merge($variables, $initial);

		$renderer = $this->createMock(RendererInterface::class);

		$renderer->expects($this->once())
		->method('render')
		->with('TestView', $total)
		->willReturnCallback(function()
		{
			return 'The view contents';
		});

		$view = new View('TestView', $initial, $renderer);

		foreach($variables as $key => $value)
		{
			$view->assign($key, $value);
		}

		$view->render();
	}
}
