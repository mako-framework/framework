<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\helpers\Hyperlink;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class HyperlinkTest extends TestCase
{
	/**
	 *
	 */
	public function testRenderWithHyperlinkSupport(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		/** @var \mako\cli\output\helpers\Hyperlink|\Mockery\MockInterface $hyperlink */
		$hyperlink = Mockery::mock(Hyperlink::class, [$output]);

		$hyperlink->makePartial();

		$hyperlink->shouldAllowMockingProtectedMethods();

		$hyperlink->shouldReceive('hasHyperlinkSupport')->andReturn(true);

		$link1 = $hyperlink->render('https://example.org');

		$link2 = $hyperlink->render('https://example.org', 'Example');

		$this->assertSame("\033]8;id=a7e05a2cf451704d0710759e269cbc8a;https://example.org\033\\https://example.org\033]8;;\033\\", $link1);

		$this->assertSame("\033]8;id=a7e05a2cf451704d0710759e269cbc8a;https://example.org\033\\Example\033]8;;\033\\", $link2);
	}

	/**
	 *
	 */
	public function testRenderWithoutHyperlinkSupport(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		/** @var \mako\cli\output\helpers\Hyperlink|\Mockery\MockInterface $hyperlink */
		$hyperlink = Mockery::mock(Hyperlink::class, [$output]);

		$hyperlink->makePartial();

		$hyperlink->shouldAllowMockingProtectedMethods();

		$hyperlink->shouldReceive('hasHyperlinkSupport')->andReturn(false);

		$link1 = $hyperlink->render('https://example.org');

		$link2 = $hyperlink->render('https://example.org', 'Example');

		$this->assertSame('https://example.org', $link1);

		$this->assertSame('Example (https://example.org)', $link2);
	}

	/**
	 *
	 */
	public function testDrawWithHyperlinkSupport(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with("\033]8;id=a7e05a2cf451704d0710759e269cbc8a;https://example.org\033\\https://example.org\033]8;;\033\\", 1);

		$output->shouldReceive('write')->once()->with("\033]8;id=a7e05a2cf451704d0710759e269cbc8a;https://example.org\033\\Example\033]8;;\033\\", 1);

		/** @var \mako\cli\output\helpers\Hyperlink|\Mockery\MockInterface $hyperlink */
		$hyperlink = Mockery::mock(Hyperlink::class, [$output]);

		$hyperlink->makePartial();

		$hyperlink->shouldAllowMockingProtectedMethods();

		$hyperlink->shouldReceive('hasHyperlinkSupport')->andReturn(true);

		$hyperlink->draw('https://example.org');

		$hyperlink->draw('https://example.org', 'Example');
	}

	/**
	 *
	 */
	public function testDrawWithoutHyperlinkSupport(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('https://example.org', 1);

		$output->shouldReceive('write')->once()->with('Example (https://example.org)', 1);

		/** @var \mako\cli\output\helpers\Hyperlink|\Mockery\MockInterface $hyperlink */
		$hyperlink = Mockery::mock(Hyperlink::class, [$output]);

		$hyperlink->makePartial();

		$hyperlink->shouldAllowMockingProtectedMethods();

		$hyperlink->shouldReceive('hasHyperlinkSupport')->andReturn(false);

		$hyperlink->draw('https://example.org');

		$hyperlink->draw('https://example.org', 'Example');
	}
}
