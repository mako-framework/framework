<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\input\http\routing\middleware;

use mako\http\exceptions\BadRequestException;
use mako\http\Request;
use mako\http\request\Parameters;
use mako\http\Response;
use mako\http\response\builders\JSON;
use mako\http\response\senders\Redirect;
use mako\http\routing\URLBuilder;
use mako\session\Session;
use mako\tests\TestCase;
use mako\validator\exceptions\ValidationException;
use mako\validator\input\http\routing\middleware\InputValidation;
use mako\validator\input\HttpInput;
use mako\validator\input\HttpInputInterface;
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
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		//

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		//

		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		$middleware = new InputValidation($urlBuilder);

		//-------------

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
		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		//

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		//

		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		$session->shouldReceive('getFlash')->once()->with('mako.errors')->andReturn(null);

		$session->shouldReceive('getFlash')->once()->with('mako.old')->andReturn(null);

		//

		/** @var \mako\view\ViewFactory|\Mockery\MockInterface $viewFactory */
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('assign')->once()->with('_errors_', null);

		$viewFactory->shouldReceive('assign')->once()/*->with('_old_', (object) null)*/;

		//

		$middleware = new InputValidation($urlBuilder, $session, $viewFactory);

		//-------------

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
		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//-------------

		$this->assertFalse((function()
		{
			return $this->shouldRedirect();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());
	}

	/**
	 *
	 */
	public function testShouldRedirectWithSessionWithoutViewFactory(): void
	{
		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		//-------------

		$this->assertFalse((function()
		{
			return $this->shouldRedirect();
		})->bindTo(new InputValidation($urlBuilder, $session), InputValidation::class)());
	}

	/**
	 *
	 */
	public function testShouldRedirectWithSessionAndViewFactory(): void
	{
		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		//

		/** @var \mako\view\ViewFactory|\Mockery\MockInterface $viewFactory */
		$viewFactory = Mockery::mock(ViewFactory::class);

		//

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		//-------------

		$request->shouldReceive('getMethod')->once()->andReturn('GET');

		$this->assertFalse((function() use ($request)
		{
			$this->request = $request;

			return $this->shouldRedirect();
		})->bindTo(new InputValidation($urlBuilder, $session, $viewFactory), InputValidation::class)());

		//-------------

		$request->shouldReceive('getMethod')->once()->andReturn('HEAD');

		$this->assertFalse((function() use ($request)
		{
			$this->request = $request;

			return $this->shouldRedirect();
		})->bindTo(new InputValidation($urlBuilder, $session, $viewFactory), InputValidation::class)());

		//-------------

		$request->shouldReceive('getMethod')->once()->andReturn('POST');

		$middleware = new class ($urlBuilder, $session, $viewFactory) extends InputValidation
		{
			protected function respondWithJson(): bool
			{
				return true;
			}
		};

		$this->assertFalse((function() use ($request)
		{
			$this->request = $request;

			return $this->shouldRedirect();
		})->bindTo($middleware, InputValidation::class)());

		//-------------

		$request->shouldReceive('getMethod')->once()->andReturn('POST');

		$middleware = new class ($urlBuilder, $session, $viewFactory) extends InputValidation
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

		$this->assertFalse((function() use ($request)
		{
			$this->request = $request;

			return $this->shouldRedirect();
		})->bindTo($middleware, InputValidation::class)());

		//-------------

		$request->shouldReceive('getMethod')->once()->andReturn('POST');

		$middleware = new class ($urlBuilder, $session, $viewFactory) extends InputValidation
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

		$this->assertTrue((function() use ($request)
		{
			$this->request = $request;

			return $this->shouldRedirect();
		})->bindTo($middleware, InputValidation::class)());
	}

	/**
	 *
	 */
	public function testShouldIncludeOldInput(): void
	{
		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//-------------

		/** @var \mako\validator\input\HttpInput|\Mockery\MockInterface $input */
		$input = Mockery::mock(HttpInput::class);

		$input->shouldReceive('shouldIncludeOldInput')->once()->andReturn(false);

		$this->assertFalse((function() use ($input)
		{
			$this->input = $input;

			return $this->shouldIncludeOldInput();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());

		//-------------

		/** @var \mako\validator\input\HttpInput|\Mockery\MockInterface $input */
		$input = Mockery::mock(HttpInput::class);

		$input->shouldReceive('shouldIncludeOldInput')->once()->andReturn(true);

		$this->assertTrue((function() use ($input)
		{
			$this->input = $input;

			return $this->shouldIncludeOldInput();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());

		//-------------

		$this->assertTrue((function()
		{
			return $this->shouldIncludeOldInput();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());
	}

	/**
	 *
	 */
	public function testGetOldInput(): void
	{
		$formInput = ['username' => 'foobar', 'password' => 'barfoo'];

		$expected1 = ['username' => 'foobar'];

		$expected2 = [];

		//

		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//-------------

		/** @var \mako\http\request\Parameters|\Mockery\MockInterface $data */
		$data = Mockery::mock(Parameters::class);

		$data->shouldReceive('all')->once()->andReturn($formInput);

		//

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getData')->once()->andReturn($data);

		$this->assertSame($expected1, (function() use ($request)
		{
			$this->request = $request;

			return $this->getOldInput();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());

		//-------------

		/** @var \mako\validator\input\HttpInputInterface|\Mockery\MockInterface $input */
		$input = Mockery::mock(HttpInputInterface::class);

		$input->shouldReceive('getOldInput')->once()->andReturn($formInput);

		$this->assertSame($expected1, (function() use ($input)
		{
			$this->input = $input;

			return $this->getOldInput();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());

		//-------------

		/** @var \mako\validator\input\HttpInputInterface|\Mockery\MockInterface $input */
		$input = Mockery::mock(HttpInputInterface::class);

		$input->shouldReceive('getOldInput')->once()->andReturn($formInput);

		$middleware = new class ($urlBuilder) extends InputValidation
		{
			protected $dontInclude = ['username', 'password'];
		};

		$this->assertSame($expected2, (function() use ($input)
		{
			$this->input = $input;

			return $this->getOldInput();
		})->bindTo($middleware, InputValidation::class)());
	}

	/**
	 *
	 */
	public function testGetRedirectUrl(): void
	{
		$expected = 'https://example.org';

		//

		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//-------------

		$urlBuilder->shouldReceive('current')->once()->andReturn($expected);

		$this->assertSame($expected, (function()
		{
			return $this->getRedirectUrl();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());

		//-------------

		/** @var \mako\validator\input\HttpInputInterface|\Mockery\MockInterface $input */
		$input = Mockery::mock(HttpInputInterface::class);

		$input->shouldReceive('getRedirectUrl')->once()->andReturn($expected);

		$this->assertSame($expected, (function() use ($input)
		{
			$this->input = $input;

			return $this->getRedirectUrl();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());
	}

	/**
	 *
	 */
	public function testGetErrorMessage(): void
	{
		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//-------------

		$this->assertSame('Invalid input.', (function()
		{
			return $this->getErrorMessage();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());

		//-------------

		$middleware = new class ($urlBuilder) extends InputValidation
		{
			protected $defaultErrorMessage = 'foobar';
		};

		$this->assertSame('foobar', (function()
		{
			return $this->getErrorMessage();
		})->bindTo($middleware, InputValidation::class)());

		//-------------

		/** @var \mako\validator\input\HttpInputInterface|\Mockery\MockInterface $input */
		$input = Mockery::mock(HttpInputInterface::class);

		$input->shouldReceive('getErrorMessage')->once()->andReturn(null);

		$this->assertSame('Invalid input.', (function() use ($input)
		{
			$this->input = $input;

			return $this->getErrorMessage();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());

		//-------------

		/** @var \mako\validator\input\HttpInputInterface|\Mockery\MockInterface $input */
		$input = Mockery::mock(HttpInputInterface::class);

		$input->shouldReceive('getErrorMessage')->once()->andReturn('barfoo');

		$this->assertSame('barfoo', (function() use ($input)
		{
			$this->input = $input;

			return $this->getErrorMessage();
		})->bindTo(new InputValidation($urlBuilder), InputValidation::class)());
	}

	/**
	 *
	 */
	public function testHandleRedirectWithoutOldInput(): void
	{
		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		$urlBuilder->shouldReceive('current')->once()->andReturn('https://example.org');

		//

		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		$session->shouldReceive('putFlash')->once()->with('mako.errors', ['foo' => 'bar']);

		//

		/** @var \mako\validator\exceptions\ValidationException|\Mockery\MockInterface $exception */
		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getErrors')->once()->andReturn(['foo' => 'bar']);

		//

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->makePartial();

		//

		$middleware = new class ($urlBuilder, $session) extends InputValidation
		{
			protected function shouldIncludeOldInput(): bool
			{
				return false;
			}
		};

		//-------------

		$response = (function() use ($exception, $response)
		{
			$this->response = $response;

			return $this->handleRedirect($exception);
		})->bindTo($middleware, InputValidation::class)();

		$this->assertInstanceOf(Response::class, $response);

		$redirect = $response->getBody();

		$this->assertInstanceOf(Redirect::class, $redirect);

		$this->assertSame('https://example.org', (function()
		{
			return $this->location;
		})->bindTo($redirect, Redirect::class)());

		$this->assertSame(Redirect::SEE_OTHER, (function()
		{
			return $this->statusCode;
		})->bindTo($redirect, Redirect::class)());
	}

	/**
	 *
	 */
	public function testHandleRedirectWithOldInput(): void
	{
		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		$urlBuilder->shouldReceive('current')->once()->andReturn('https://example.org');

		//

		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		$session->shouldReceive('putFlash')->once()->with('mako.errors', ['foo' => 'bar']);

		$session->shouldReceive('putFlash')->once()->with('mako.old', ['bar' => 'foo']);

		//

		/** @var \mako\validator\exceptions\ValidationException|\Mockery\MockInterface $exception */
		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getErrors')->once()->andReturn(['foo' => 'bar']);

		//

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->makePartial();

		//

		$middleware = new class ($urlBuilder, $session) extends InputValidation
		{
			protected function shouldIncludeOldInput(): bool
			{
				return true;
			}

			protected function getOldInput(): array
			{
				return ['bar' => 'foo'];
			}
		};

		//-------------

		$response = (function() use ($exception, $response)
		{
			$this->response = $response;

			return $this->handleRedirect($exception);
		})->bindTo($middleware, InputValidation::class)();

		$this->assertInstanceOf(Response::class, $response);

		$redirect = $response->getBody();

		$this->assertInstanceOf(Redirect::class, $redirect);

		$this->assertSame('https://example.org', (function()
		{
			return $this->location;
		})->bindTo($redirect, Redirect::class)());

		$this->assertSame(Redirect::SEE_OTHER, (function()
		{
			return $this->statusCode;
		})->bindTo($redirect, Redirect::class)());
	}

	/**
	 *
	 */
	public function testHandleOutputWithJson(): void
	{
		if(function_exists('json_encode') === false)
		{
			$this->markTestSkipped('JSON support is missing.');

			return;
		}

		$middleware = Mockery::mock(InputValidation::class)->shouldAllowMockingProtectedMethods();

		$middleware->shouldReceive('respondWithJson')->once()->andReturn(true);

		$middleware->makePartial();

		//

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(400);

		$response->makePartial();

		//

		/** @var \mako\validator\exceptions\ValidationException|\Mockery\MockInterface $exception */
		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getErrors')->once()->andReturn(['foo' => 'bar']);

		//-------------

		$response = (function() use ($response, $exception)
		{
			$this->response = $response;

			return $this->handleOutput($exception);

		})->bindTo($middleware, InputValidation::class)();

		$body = $response->getBody();

		$this->assertInstanceOf(JSON::class, $body);

		$this->assertSame(['message' => 'Invalid input.', 'errors' => ['foo' => 'bar']], (function()
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

		/** @var \mako\validator\input\http\routing\middleware\InputValidation|\Mockery\MockInterface $middleware */
		$middleware = Mockery::mock(InputValidation::class);

		$middleware = $middleware->shouldAllowMockingProtectedMethods();

		$middleware->shouldReceive('respondWithJson')->once()->andReturn(false);

		$middleware->shouldReceive('respondWithXml')->once()->andReturn(true);

		$middleware->makePartial();

		//

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(400);

		$response->shouldReceive('setType')->once()->with('application/xml');

		$response->shouldReceive('getCharset')->once()->andReturn('UTF-8');

		$response->makePartial();

		//

		/** @var \mako\validator\exceptions\ValidationException|\Mockery\MockInterface $exception */
		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('getErrors')->once()->andReturn(['foo' => 'bar']);

		//-------------

		$response = (function() use ($response, $exception)
		{
			$this->response = $response;

			return $this->handleOutput($exception);

		})->bindTo($middleware, InputValidation::class)();

		$this->assertSame("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<error><message>Invalid input.</message><errors><foo>bar</foo></errors></error>\n", $response->getBody());
	}

	/**
	 *
	 */
	public function testHandleOutputWithException(): void
	{
		$this->expectException(BadRequestException::class);

		//

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		//

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(400);

		//

		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		//-------------

		$middleware = new class ($urlBuilder, $session) extends InputValidation
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
				return $this->handleOutput($exception);
			}
		};

		(function() use ($request, $response): void
		{
			$this->request  = $request;
			$this->response = $response;
		})->bindTo($middleware, InputValidation::class)();

		$middleware->getResponse(new ValidationException([]));
	}

	/**
	 *
	 */
	public function testExecuteWithErrorsWithoutRedirect(): void
	{
		$this->expectException(BadRequestException::class);

		//

		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		//

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once();

		$response->shouldReceive('setStatus')->once()->with(400);

		//-------------

		$middleware = new class ($urlBuilder) extends InputValidation
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

		$middleware->execute($request, $response, function($request, $response): void
		{
			throw new ValidationException([]);
		});
	}

	/**
	 *
	 */
	public function testExecuteWithErrorsWithRedirect(): void
	{
		/** @var \mako\http\routing\URLBuilder|\Mockery\MockInterface $urlBuilder */
		$urlBuilder = Mockery::mock(URLBuilder::class);

		//

		/** @var \mako\session\Session|\Mockery\MockInterface $session */
		$session = Mockery::mock(Session::class);

		$session->shouldReceive('getFlash')->once()->with('mako.errors')->andReturn(null);

		$session->shouldReceive('getFlash')->once()->with('mako.old')->andReturn(null);

		//

		/** @var \mako\view\ViewFactory|\Mockery\MockInterface $viewFactory */
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('assign')->once()->with('_errors_', null);

		$viewFactory->shouldReceive('assign')->once()->with('_old_', null);

		//

		/** @var \mako\http\Request|\Mockery\MockInterface $request */
		$request = Mockery::mock(Request::class);

		//

		/** @var \mako\http\Response|\Mockery\MockInterface $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once();

		//-------------

		$middleware = new class ($urlBuilder, $session, $viewFactory) extends InputValidation
		{
			protected function shouldRedirect(): bool
			{
				return true;
			}

			protected function handleRedirect(ValidationException $exception): Response
			{
				return $this->response;
			}
		};

		(function() use ($request, $response): void
		{
			$this->response = $response;
		})->bindTo($middleware, InputValidation::class)();

		$this->assertSame($response, $middleware->execute($request, $response, function($request, $response): void
		{
			throw new ValidationException([]);
		}));
	}
}
