<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\input\http\routing\middleware;

use mako\http\exceptions\BadRequestException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\builders\JSON;
use mako\http\response\senders\Redirect;
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
	public function testShouldRedirectWithSession(): void
	{
		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		//

		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$session = Mockery::mock(Session::class);

		//

		$request->shouldReceive('getMethod')->once()->andReturn('GET');

		$this->assertFalse((function(Request $request, ValidationException $exception)
		{
			return $this->shouldRedirect($request, $exception);
		})->bindTo(new InputValidation($request, $response, $urlBuilder, $session), InputValidation::class)($request, new ValidationException([])));

		//

		$request->shouldReceive('getMethod')->once()->andReturn('HEAD');

		$this->assertFalse((function(Request $request, ValidationException $exception)
		{
			return $this->shouldRedirect($request, $exception);
		})->bindTo(new InputValidation($request, $response, $urlBuilder, $session), InputValidation::class)($request, new ValidationException([])));

		//

		$request->shouldReceive('getMethod')->once()->andReturn('POST');

		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getMeta')->once()->with('should_redirect')->andReturn(false);

		$this->assertFalse((function(Request $request, ValidationException $exception)
		{
			return $this->shouldRedirect($request, $exception);
		})->bindTo(new InputValidation($request, $response, $urlBuilder, $session), InputValidation::class)($request, $exception));

		//

		$middleware = new class ($request, $response, $urlBuilder, $session) extends InputValidation
		{
			protected function respondWithJson(): bool
			{
				return true;
			}
		};

		$request->shouldReceive('getMethod')->once()->andReturn('POST');

		$this->assertFalse((function(Request $request, ValidationException $exception)
		{
			return $this->shouldRedirect($request, $exception);
		})->bindTo($middleware, InputValidation::class)($request, new ValidationException([])));

		//

		$middleware = new class ($request, $response, $urlBuilder, $session) extends InputValidation
		{
			protected function respondWithJson(): bool
			{
				return false;
			}

			protected function respondWithXml(): bool
			{
				return true;
			}
		};

		$request->shouldReceive('getMethod')->once()->andReturn('POST');

		$this->assertFalse((function(Request $request, ValidationException $exception)
		{
			return $this->shouldRedirect($request, $exception);
		})->bindTo($middleware, InputValidation::class)($request, new ValidationException([])));

		//

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
		};

		$request->shouldReceive('getMethod')->once()->andReturn('POST');

		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getMeta')->once()->with('should_redirect')->andReturn(true);

		$this->assertTrue((function(Request $request, ValidationException $exception)
		{
			return $this->shouldRedirect($request, $exception);
		})->bindTo($middleware, InputValidation::class)($request, $exception));

		//

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
		};

		$request->shouldReceive('getMethod')->once()->andReturn('POST');

		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getMeta')->once()->with('should_redirect')->andReturn(null);

		$this->assertTrue((function(Request $request, ValidationException $exception)
		{
			return $this->shouldRedirect($request, $exception);
		})->bindTo($middleware, InputValidation::class)($request, $exception));
	}

	/**
	 *
	 */
	public function testHandleRedirect(): void
	{
		$errors = ['foo' => 'bar'];

		$old = ['bar' => 'foo'];

		//

		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		$response->makePartial();

		//

		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$session = Mockery::mock(Session::class);

		$session->shouldReceive('putFlash')->once()->with('mako.errors', $errors);

		$session->shouldReceive('putFlash')->once()->with('mako.old', $old);

		//

		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getErrors')->once()->andReturn($errors);

		$exception->shouldReceive('getMeta')->once()->with('old_input')->andReturn($old);

		//

		$middleware = new class ($request, $response, $urlBuilder, $session) extends InputValidation
		{
			public function getRedirectUrl(ValidationException $exception): string
			{
				return 'https://example.org';
			}
		};

		//

		$response = (function(Response $response, ValidationException $exception)
		{
			return $this->handleRedirect($response, $exception);
		})->bindTo($middleware, InputValidation::class)($response, $exception);

		$body = $response->getBody();

		$this->assertInstanceOf(Redirect::class, $body);

		$this->assertSame(Redirect::SEE_OTHER, $body->getStatus());

		$this->assertSame('https://example.org', (function()
		{
			return $this->location;
		})->bindTo($body, Redirect::class)());
	}

	/**
	 *
	 */
	public function testHandleOutputWithJson(): void
	{
		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(400);

		$response->makePartial();

		//

		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$session = Mockery::mock(Session::class);

		//

		$middleware = new class ($request, $response, $urlBuilder, $session) extends InputValidation
		{
			protected function respondWithJson(): bool
			{
				return true;
			}

			public function getResponse(ValidationException $exception): Response
			{
				return $this->handleOutput($this->response, $exception);
			}
		};

		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getMeta')->once()->with('message', 'Invalid input.')->andReturn('foobar');

		$exception->shouldReceive('getErrors')->once()->andReturn(['foo' => 'bar']);

		//

		$response = $middleware->getResponse($exception);

		$body = $response->getBody();

		$this->assertInstanceOf(JSON::class, $body);

		$this->assertSame(['message' => 'foobar', 'errors' => ['foo' => 'bar']], (function()
		{
			return $this->data;
		})->bindTo($body, JSON::class)());
	}

	/**
	 *
	 */
	public function testHandleOutputWithXml(): void
	{
		if(function_exists('simplexml_load_string') === false)
		{
			$this->markTestSkipped('XML support is missing.');

			return;
		}

		//

		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(400);

		$response->shouldReceive('getCharset')->once()->andReturn('UTF-8');

		$response->makePartial();

		//

		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$session = Mockery::mock(Session::class);

		//

		$middleware = new class ($request, $response, $urlBuilder, $session) extends InputValidation
		{
			protected function respondWithJson(): bool
			{
				return false;
			}

			protected function respondWithXml(): bool
			{
				return true;
			}

			public function getResponse(ValidationException $exception): Response
			{
				return $this->handleOutput($this->response, $exception);
			}
		};

		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getMeta')->once()->with('message', 'Invalid input.')->andReturn('foobar');

		$exception->shouldReceive('getErrors')->once()->andReturn(['foo' => 'bar']);

		//

		$response = $middleware->getResponse($exception);

		$this->assertSame('application/xml', $response->getType());

		$this->assertSame("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<error><message>foobar</message><errors><foo>bar</foo></errors></error>\n", $response->getBody());
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

		$middleware->execute($request, $response, function($request, $response)
		{
			throw new ValidationException([]);
		});
	}

	/**
	 *
	 */
	public function testExecuteWithErrorsWithRedirect(): void
	{
		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once();

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

		$middleware = new class ($request, $response, $urlBuilder, $session, $viewFactory) extends InputValidation
		{
			protected function shouldRedirect(Request $request, ValidationException $exception): bool
			{
				return true;
			}

			protected function handleRedirect(Response $response, ValidationException $exception): Response
			{
				return $response;
			}
		};

		//

		$this->assertSame($response, $middleware->execute($request, $response, function($request, $response)
		{
			throw new ValidationException([]);
		}));
	}
}
