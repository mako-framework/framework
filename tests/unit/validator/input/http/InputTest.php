<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\input\http;

use mako\http\Request;
use mako\http\request\Parameters;
use mako\http\routing\URLBuilder;
use mako\tests\TestCase;
use mako\validator\input\http\Input;
use Mockery;

/**
 * @group unit
 */
class InputTest extends TestCase
{
	/**
	 *
	 */
	public function testGetInput(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\request\Parameters|\Mockery\MockInterface $data */
		$data = Mockery::mock(Parameters::class);

		$data->shouldReceive('all')->once()->andReturn(['input' => 'value']);

		$request->shouldReceive('getData')->once()->andReturn($data);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = new class ($request, $urlBuilder) extends Input {

		};

		$this->assertSame(['input' => 'value'], $input->getInput());
	}

	/**
	 *
	 */
	public function testShouldRedirect(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = new class ($request, $urlBuilder) extends Input {

		};

		$this->assertTrue($input->shouldRedirect());

		$input = new class ($request, $urlBuilder) extends Input {
			protected bool $shouldRedirect = false;
		};

		$this->assertFalse($input->shouldRedirect());
	}

	/**
	 *
	 */
	public function testGetRedirectUrl(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		$urlBuilder->shouldReceive('current')->once()->andReturn('https://example.org');

		$input = new class ($request, $urlBuilder) extends Input {

		};

		$this->assertSame('https://example.org', $input->getRedirectUrl());
	}

	/**
	 *
	 */
	public function testShouldIncludeOldInput(): void
	{
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = new class ($request, $urlBuilder) extends Input {

		};

		$this->assertTrue($input->shouldIncludeOldInput());

		$input = new class ($request, $urlBuilder) extends Input {
			protected bool $shouldIncludeOldInput = false;
		};

		$this->assertFalse($input->shouldIncludeOldInput());
	}

	/**
	 *
	 */
	public function testGetOldInput(): void
	{
		/** @var \mako\http\request\Parameters|\Mockery\MockInterface $data */
		$data = Mockery::mock(Parameters::class);

		$data->shouldReceive('all')->once()->andReturn(['field' => 'value']);

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getData')->once()->andReturn($data);

		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = new class ($request, $urlBuilder) extends Input {

		};

		$this->assertSame(['field' => 'value'], $input->getOldInput());
	}
}
