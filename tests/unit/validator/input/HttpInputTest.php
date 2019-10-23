<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\input;

use mako\http\Request;
use mako\http\request\Parameters;
use mako\http\routing\URLBuilder;
use mako\tests\TestCase;
use mako\validator\input\HttpInput;
use Mockery;

/**
 * @group unit
 */
class HttpInputTest extends TestCase
{
	/**
	 *
	 */
	public function testGetInput(): void
	{
		$request = Mockery::mock(Request::class);

		$data = Mockery::mock(Parameters::class);

		$data->shouldReceive('all')->once()->andReturn(['input' => 'value']);

		$request->shouldReceive('getData')->once()->andReturn($data);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = new class ($request, $urlBuilder) extends HttpInput
		{

		};

		$this->assertSame(['input' => 'value'], $input->getInput());
	}

	/**
	 *
	 */
	public function testShouldRedirect(): void
	{
		$request = Mockery::mock(Request::class);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = new class ($request, $urlBuilder) extends HttpInput
		{

		};

		$this->assertTrue($input->shouldRedirect());

		$input = new class ($request, $urlBuilder) extends HttpInput
		{
			protected $shouldRedirect = false;
		};

		$this->assertFalse($input->shouldRedirect());
	}

	/**
	 *
	 */
	public function testGetRedirectUrl(): void
	{
		$request = Mockery::mock(Request::class);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$urlBuilder->shouldReceive('current')->once()->andReturn('https://example.org');

		$input = new class ($request, $urlBuilder) extends HttpInput
		{

		};

		$this->assertSame('https://example.org', $input->getRedirectUrl());
	}

	/**
	 *
	 */
	public function testShouldIncludeOldInput(): void
	{
		$request = Mockery::mock(Request::class);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = new class ($request, $urlBuilder) extends HttpInput
		{

		};

		$this->assertTrue($input->shouldIncludeOldInput());

		$input = new class ($request, $urlBuilder) extends HttpInput
		{
			protected $shouldIncludeOldInput = false;
		};

		$this->assertFalse($input->shouldIncludeOldInput());
	}

	/**
	 *
	 */
	public function testGetOldInput(): void
	{
		$data = Mockery::mock(Parameters::class);

		$data->shouldReceive('all')->once()->andReturn(['field' => 'value']);

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getData')->once()->andReturn($data);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = new class ($request, $urlBuilder) extends HttpInput
		{

		};

		$this->assertSame(['field' => 'value'], $input->getOldInput());
	}
}
