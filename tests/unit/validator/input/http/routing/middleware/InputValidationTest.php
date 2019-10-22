<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\input\http\routing\middleware;

use mako\http\exceptions\BadRequestException;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\URLBuilder;
use mako\session\Session;
use mako\tests\TestCase;
use mako\validator\input\http\routing\middleware\InputValidation;
use mako\validator\ValidationException;
use mako\view\ViewFactory;
use Mockery;

/**
 * @group unit
 */
class InputValidationTest extends TestCase
{
	/**
	 *
	 */
	public function testExecuteWithNoErrors(): void
	{
		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		//

		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$middleware = new InputValidation($request, $response, $urlBuilder);

		//

		$this->assertSame($response, $middleware->execute($request, $response, function($request, $response)
		{
			return $response;
		}));
	}

	/**
	 *
	 */
	public function testExecuteWithNoErrorsAndWithSessionAndViewFactory(): void
	{
		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		//

		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$session = Mockery::mock(Session::class);

		$session->shouldReceive('getFlash')->once()->with('mako.errors')->andReturn(null);

		$session->shouldReceive('getFlash')->once()->with('mako.old')->andReturn(null);

		//

		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('assign')->once()->with('_errors_', null);

		$viewFactory->shouldReceive('assign')->once()/*->with('_old_', (object) null)*/;

		//

		$middleware = new InputValidation($request, $response, $urlBuilder, $session, $viewFactory);

		//

		$this->assertSame($response, $middleware->execute($request, $response, function($request, $response)
		{
			return $response;
		}));
	}

	/**
	 *
	 */
	public function testShouldRedirectWithoutSession(): void
	{
		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		//

		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$this->assertFalse((function(Request $request, ValidationException $exception)
		{
			return $this->shouldRedirect($request, $exception);
		})->bindTo(new InputValidation($request, $response, $urlBuilder), InputValidation::class)($request, new ValidationException([])));
	}

	/**
	 *
	 */
	public function testHandleOutputWithException(): void
	{
		$this->expectException(BadRequestException::class);

		//

		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(400);

		//

		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$session = Mockery::mock(Session::class);

		$middleware = new class ($request, $response, $urlBuilder, $session) extends InputValidation
		{
			protected function respondWithJson(): bool
			{
				return false;
			}

			protected function respondWithXml(): bool
			{
				return false;
			}

			public function getResponse(ValidationException $exception): Response
			{
				return $this->handleOutput($this->response, $exception);
			}
		};

		$middleware->getResponse(new ValidationException([]));
	}

	/**
	 *
	 */
	public function testExecuteWithErrorsWithoutRedirect(): void
	{
		$this->expectException(BadRequestException::class);

		//

		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once();

		$response->shouldReceive('setStatus')->once()->with(400);

		//

		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$middleware = new class ($request, $response, $urlBuilder) extends InputValidation
		{
			protected function respondWithJson(): bool
			{
				return false;
			}

			protected function respondWithXml(): bool
			{
				return false;
			}
		};

		//

		$middleware->execute($request, $response, function($request, $response): void
		{
			throw new ValidationException([]);
		});
	}
}
