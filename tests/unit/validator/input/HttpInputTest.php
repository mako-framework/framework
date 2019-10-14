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
use mako\validator\Validator;
use Mockery;

/**
 * @group unit
 */
class HttpInputTest extends TestCase
{
	/**
	 * @param  \mako\http\Request              $request    Request
	 * @param  \mako\http\routing\URLBuilder   $urlBuilder URL builder
	 * @return \mako\validator\input\HttpInput
	 */
	protected function getInput(Request $request, URLBuilder $urlBuilder): HttpInput
	{
		return new class ($request, $urlBuilder) extends HttpInput
		{
			protected $rules = ['foo' => 'bar'];

			protected $extensions = ['bar' => 'foo'];

			protected function getMessage(): ?string
			{
				return 'Nope';
			}
		};
	}

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

		$input = $this->getInput($request, $urlBuilder);

		$this->assertSame(['input' => 'value'], $input->getInput());
	}

	/**
	 *
	 */
	public function testGetRules(): void
	{
		$request = Mockery::mock(Request::class);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = $this->getInput($request, $urlBuilder);

		$this->assertSame(['foo' => 'bar'], $input->getRules());
	}

	/**
	 *
	 */
	public function testGetExtensions(): void
	{
		$request = Mockery::mock(Request::class);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = $this->getInput($request, $urlBuilder);

		$this->assertSame(['bar' => 'foo'], $input->getExtensions());
	}

	/**
	 *
	 */
	public function testAddConditionalRules(): void
	{
		$request = Mockery::mock(Request::class);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$input = $this->getInput($request, $urlBuilder);

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('addRule')->never();

		$input->addConditionalRules($validator);
	}

	/**
	 *
	 */
	public function testGetMeta(): void
	{
		$request = Mockery::mock(Request::class);

		$data = Mockery::mock(Parameters::class);

		$data->shouldReceive('all')->once()->andReturn(['input' => 'value']);

		$request->shouldReceive('getData')->once()->andReturn($data);

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$urlBuilder->shouldReceive('current')->once()->andReturn('htts://example.org');

		$input = $this->getInput($request, $urlBuilder);

		$this->assertSame
		([
			'message'         => 'Nope',
			'should_redirect' => true,
			'redirect_url'    => 'htts://example.org',
			'old_input'       => ['input' => 'value'],
		], $input->getMeta());
	}

	/**
	 *
	 */
	public function testGetMetaWithoutRedirect(): void
	{
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getData')->never();

		$urlBuilder = Mockery::mock(URLBuilder::class);

		$urlBuilder->shouldReceive('current')->never();

		$input = new class ($request, $urlBuilder) extends HttpInput
		{
			protected $shouldRedirect = false;
		};

		$this->assertSame
		([
			'should_redirect' => false,
		], $input->getMeta());
	}
}
