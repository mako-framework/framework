<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\output\components\Hyperlink;
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
	protected function getHashAndUrl(): array
	{
		$url = 'https://example.org';

		return [hash('xxh128', $url), $url];
	}

	/**
	 *
	 */
	public function testRenderWithHyperlinkSupport(): void
	{
		[$hash, $url] = $this->getHashAndUrl();

		$output = Mockery::mock(Output::class);

		$hyperlink = Mockery::mock(Hyperlink::class, [$output]);

		$hyperlink->makePartial();

		$hyperlink->shouldAllowMockingProtectedMethods();

		$hyperlink->shouldReceive('hasHyperlinkSupport')->andReturn(true);

		$link1 = $hyperlink->render($url);

		$link2 = $hyperlink->render($url, 'Example');

		$this->assertSame("\x1b]8;id={$hash};{$url}\x1b\\{$url}\x1b]8;;\x1b\\", $link1);

		$this->assertSame("\x1b]8;id={$hash};{$url}\x1b\\Example\x1b]8;;\x1b\\", $link2);
	}

	/**
	 *
	 */
	public function testRenderWithoutHyperlinkSupport(): void
	{
		[, $url] = $this->getHashAndUrl();

		$output = Mockery::mock(Output::class);

		$hyperlink = Mockery::mock(Hyperlink::class, [$output]);

		$hyperlink->makePartial();

		$hyperlink->shouldAllowMockingProtectedMethods();

		$hyperlink->shouldReceive('hasHyperlinkSupport')->andReturn(false);

		$link1 = $hyperlink->render($url);

		$link2 = $hyperlink->render($url, 'Example');

		$this->assertSame($url, $link1);

		$this->assertSame("Example ({$url})", $link2);
	}

	/**
	 *
	 */
	public function testDrawWithHyperlinkSupport(): void
	{
		[$hash, $url] = $this->getHashAndUrl();

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with("\x1b]8;id={$hash};{$url}\x1b\\{$url}\x1b]8;;\x1b\\", 1);

		$output->shouldReceive('write')->once()->with("\x1b]8;id={$hash};{$url}\x1b\\Example\x1b]8;;\x1b\\", 1);

		$hyperlink = Mockery::mock(Hyperlink::class, [$output]);

		$hyperlink->makePartial();

		$hyperlink->shouldAllowMockingProtectedMethods();

		$hyperlink->shouldReceive('hasHyperlinkSupport')->andReturn(true);

		$hyperlink->draw($url);

		$hyperlink->draw($url, 'Example');
	}

	/**
	 *
	 */
	public function testDrawWithoutHyperlinkSupport(): void
	{
		[, $url] = $this->getHashAndUrl();

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with($url, 1);

		$output->shouldReceive('write')->once()->with("Example ({$url})", 1);

		$hyperlink = Mockery::mock(Hyperlink::class, [$output]);

		$hyperlink->makePartial();

		$hyperlink->shouldAllowMockingProtectedMethods();

		$hyperlink->shouldReceive('hasHyperlinkSupport')->andReturn(false);

		$hyperlink->draw($url);

		$hyperlink->draw($url, 'Example');
	}
}
