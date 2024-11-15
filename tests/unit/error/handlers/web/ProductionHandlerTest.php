<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\web;

use ErrorException;
use mako\error\handlers\web\ProductionHandler;
use mako\http\exceptions\ForbiddenException;
use mako\http\exceptions\MethodNotAllowedException;
use mako\http\Request;
use mako\http\request\Headers as RequestHeaders;
use mako\http\Response;
use mako\http\response\Headers as ResponseHeaders;
use mako\http\response\Status;
use mako\tests\TestCase;
use mako\view\ViewFactory;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

#[Group('unit')]
class ProductionHandlerTest extends TestCase
{
	/**
	 *
	 */
	public function testRegularErrorWithView(): void
	{
		/** @var Mockery\MockInterface|RequestHeaders $requestHeaders */
		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('getAcceptableContentTypes')->twice()->andReturn([]);

		//

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		//

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clearExcept')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('text/html');

		$response->shouldReceive('setType')->once()->with('text/html')->andReturn($response);

		$response->shouldReceive('setBody')->once()->with('rendered')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(Status::INTERNAL_SERVER_ERROR)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		/** @var Mockery\MockInterface|ViewFactory $viewFactory */
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('registerNamespace')->once();

		$viewFactory->shouldReceive('assign')->once()->with('exception_id', 'exception_id');

		$viewFactory->shouldReceive('render')->once()->with('mako-error::error', ['_metadata_' => []])->andReturn('rendered');

		//

		$handler = new class($request, $response, $viewFactory) extends ProductionHandler {
			protected function generateExceptionId(): string
			{
				return 'exception_id';
			}
		};

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testRegularErrorWithRenderException(): void
	{
		/** @var Mockery\MockInterface|ViewFactory $viewFactory */
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('registerNamespace')->once();

		$viewFactory->shouldReceive('assign')->once()->with('exception_id', 'exception_id');

		$viewFactory->shouldReceive('render')->once()->with('mako-error::error', ['_metadata_' => []])->andThrow(RuntimeException::class);

		$viewFactory->shouldReceive('clearAutoAssignVariables')->once()->andReturn($viewFactory);

		$viewFactory->shouldReceive('render')->once()->with('mako-error::error', ['_metadata_' => []])->andReturn('rendered');

		//

		/** @var Mockery\MockInterface|RequestHeaders $requestHeaders */
		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('getAcceptableContentTypes')->twice()->andReturn([]);

		//

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		//

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clearExcept')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('text/html');

		$response->shouldReceive('setType')->once()->with('text/html')->andReturn($response);

		$response->shouldReceive('setBody')->once()->with('rendered')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(Status::INTERNAL_SERVER_ERROR)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new class($request, $response, $viewFactory) extends ProductionHandler {
			protected function generateExceptionId(): string
			{
				return 'exception_id';
			}
		};

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testHttpExceptionWithView(): void
	{
		/** @var Mockery\MockInterface|RequestHeaders $requestHeaders */
		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('getAcceptableContentTypes')->twice()->andReturn([]);

		//

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		//

		/** @var Mockery\MockInterface|ResponseHeaders $responseHeaders */
		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->once()->with('Allow', 'GET,POST');

		//

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clearExcept')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('text/html');

		$response->shouldReceive('setType')->once()->with('text/html')->andReturn($response);

		$response->shouldReceive('setBody')->once()->with('rendered')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(Status::METHOD_NOT_ALLOWED)->andReturn($response);

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('send')->once();

		//

		/** @var Mockery\MockInterface|ViewFactory $viewFactory */
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('registerNamespace')->once();

		$viewFactory->shouldReceive('assign')->once()->with('exception_id', 'exception_id');

		$viewFactory->shouldReceive('exists')->once()->with('mako-error::405')->andReturn(true);

		$viewFactory->shouldReceive('render')->once()->with('mako-error::405', ['_metadata_' => []])->andReturn('rendered');

		//

		$handler = new class($request, $response, $viewFactory) extends ProductionHandler {
			protected function generateExceptionId(): string
			{
				return 'exception_id';
			}
		};

		$this->assertFalse($handler->handle(new MethodNotAllowedException(['GET', 'POST'])));
	}

	/**
	 *
	 */
	public function testRegularErrorWithoutView(): void
	{
		/** @var Mockery\MockInterface|RequestHeaders $requestHeaders */
		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('getAcceptableContentTypes')->twice()->andReturn([]);

		//

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		//

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clearExcept')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('text/plain');

		$response->shouldReceive('setType')->once()->with('text/plain')->andReturn($response);

		$response->shouldReceive('setBody')->once()->with('An error has occurred while processing your request.')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(Status::INTERNAL_SERVER_ERROR)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new ProductionHandler($request, $response);

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testRegularErrorWithoutViewWithResetExceptions(): void
	{
		/** @var Mockery\MockInterface|RequestHeaders $requestHeaders */
		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('getAcceptableContentTypes')->twice()->andReturn([]);

		//

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		//

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clearExcept')->once()->with(['headers' => ['Access-Control-.*']])->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('text/plain');

		$response->shouldReceive('setType')->once()->with('text/plain')->andReturn($response);

		$response->shouldReceive('setBody')->once()->with('An error has occurred while processing your request.')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(Status::INTERNAL_SERVER_ERROR)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new ProductionHandler($request, $response, null, ['headers' => ['Access-Control-.*']]);

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testRegularErrorWithJsonResponse(): void
	{
		if (function_exists('json_encode') === false) {
			$this->markTestSkipped('JSON support is missing.');

			return;
		}

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		//

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/json');

		$response->shouldReceive('clearExcept')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('setType')->once()->with('application/json')->andReturn($response);

		$response->shouldReceive('setBody')->once()->with('{"error":{"code":500,"message":"An error has occurred while processing your request.","exception_id":"exception_id"}}')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(Status::INTERNAL_SERVER_ERROR)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new class($request, $response) extends ProductionHandler {
			protected function generateExceptionId(): string
			{
				return 'exception_id';
			}
		};

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testHttpExceptionWithJsonResponse(): void
	{
		if (function_exists('json_encode') === false) {
			$this->markTestSkipped('JSON support is missing.');

			return;
		}

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		//

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/json');

		$response->shouldReceive('clearExcept')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('setType')->once()->with('application/json')->andReturn($response);

		$response->shouldReceive('setBody')->once()->with('{"error":{"code":403,"message":"You don\'t have permission to access the requested resource.","exception_id":"exception_id","metadata":{"foo":"bar"}}}')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(Status::FORBIDDEN)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new class($request, $response) extends ProductionHandler {
			protected function generateExceptionId(): string
			{
				return 'exception_id';
			}
		};

		$this->assertFalse($handler->handle((new ForbiddenException)->setMetadata(['foo' => 'bar'])));
	}

	/**
	 *
	 */
	public function testRegularErrorWithXmlResponse(): void
	{
		if (function_exists('simplexml_load_string') === false) {
			$this->markTestSkipped('XML support is missing.');

			return;
		}

		/** @var Mockery\MockInterface|RequestHeaders $requestHeaders */
		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('getAcceptableContentTypes')->once()->andReturn([]);

		//

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		//

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clearExcept')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('application/xml');

		$response->shouldReceive('setType')->once()->with('application/xml')->andReturn($response);

		$response->shouldReceive('setBody')->once()->with('<?xml version="1.0" encoding="utf-8"?>
<error><code>500</code><message>An error has occurred while processing your request.</message><exception_id>exception_id</exception_id></error>
')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(Status::INTERNAL_SERVER_ERROR)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new class($request, $response) extends ProductionHandler {
			protected function generateExceptionId(): string
			{
				return 'exception_id';
			}
		};

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testHttpExceptionWithXmlResponse(): void
	{
		if (function_exists('simplexml_load_string') === false) {
			$this->markTestSkipped('XML support is missing.');

			return;
		}

		/** @var Mockery\MockInterface|RequestHeaders $requestHeaders */
		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('getAcceptableContentTypes')->once()->andReturn([]);

		//

		/** @var Mockery\MockInterface|Request $request */
		$request = Mockery::mock(Request::class);

		(function () use ($requestHeaders): void {
			$this->headers = $requestHeaders;
		})->bindTo($request, Request::class)();

		//

		/** @var Mockery\MockInterface|Response $response */
		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clearExcept')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('application/xml');

		$response->shouldReceive('setType')->once()->with('application/xml')->andReturn($response);

		$response->shouldReceive('setBody')->once()->with('<?xml version="1.0" encoding="utf-8"?>
<error><code>403</code><message>You don\'t have permission to access the requested resource.</message><exception_id>exception_id</exception_id><metadata><foo>bar</foo></metadata></error>
')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(Status::FORBIDDEN)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new class($request, $response) extends ProductionHandler {
			protected function generateExceptionId(): string
			{
				return 'exception_id';
			}
		};

		$this->assertFalse($handler->handle((new ForbiddenException)->setMetadata(['foo' => 'bar'])));
	}
}
