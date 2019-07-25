<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\web;

use ErrorException;
use mako\error\handlers\web\ProductionHandler;
use mako\http\exceptions\MethodNotAllowedException;
use mako\http\Request;
use mako\http\request\Headers as RequestHeaders;
use mako\http\Response;
use mako\http\response\Headers as ResponseHeaders;
use mako\tests\TestCase;
use mako\view\ViewFactory;
use Mockery;

/**
 * @group unit
 */
class ProductionHandlerTest extends TestCase
{
	/**
	 *
	 */
	public function testRegularErrorWithView(): void
	{
		$responseHeaders = Mockery::mock(RequestHeaders::class);

		$responseHeaders->shouldReceive('getAcceptableContentTypes')->twice()->andReturn([]);

		//

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->twice()->andReturn($responseHeaders);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('text/html');

		$response->shouldReceive('setType')->once()->with('text/html');

		$response->shouldReceive('setBody')->once()->with('rendered')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(500)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('registerNamespace')->once();

		$viewFactory->shouldReceive('render')->once()->with('mako-error::error')->andReturn('rendered');

		//

		$handler = new ProductionHandler($request, $response, $viewFactory);

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testHttpExceptionWithView(): void
	{
		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('getAcceptableContentTypes')->twice()->andReturn([]);

		//

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->twice()->andReturn($requestHeaders);

		//

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->once()->with('Allow', 'GET,POST');

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('text/html');

		$response->shouldReceive('setType')->once()->with('text/html');

		$response->shouldReceive('setBody')->once()->with('rendered')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(405)->andReturn($response);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('send')->once();

		//

		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('registerNamespace')->once();

		$viewFactory->shouldReceive('exists')->once()->with('mako-error::405')->andReturn(true);

		$viewFactory->shouldReceive('render')->once()->with('mako-error::405')->andReturn('rendered');

		//

		$handler = new ProductionHandler($request, $response, $viewFactory);

		$this->assertFalse($handler->handle(new MethodNotAllowedException(['GET', 'POST'])));
	}

	/**
	 *
	 */
	public function testRegularErrorWithoutView(): void
	{
		$responseHeaders = Mockery::mock(RequestHeaders::class);

		$responseHeaders->shouldReceive('getAcceptableContentTypes')->twice()->andReturn([]);

		//

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->twice()->andReturn($responseHeaders);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('text/plain');

		$response->shouldReceive('setType')->once()->with('text/plain');

		$response->shouldReceive('setBody')->once()->with('An error has occurred while processing your request.')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(500)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new ProductionHandler($request, $response);

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testRegularErrorWithJsonResponse(): void
	{
		if(function_exists('json_encode') === false)
		{
			$this->markTestSkipped('JSON support is missing.');

			return;
		}

		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/json');

		$response->shouldReceive('clear')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('setType')->once()->with('application/json');

		$response->shouldReceive('setBody')->once()->with('{"error":{"code":500,"message":"An error has occurred while processing your request."}}')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(500)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new ProductionHandler($request, $response);

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testRegularErrorWithXmlResponse(): void
	{
		if(function_exists('simplexml_load_string') === false)
		{
			$this->markTestSkipped('XML support is missing.');

			return;
		}

		$responseHeaders = Mockery::mock(RequestHeaders::class);

		$responseHeaders->shouldReceive('getAcceptableContentTypes')->once()->andReturn([]);

		//

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('application/xml');

		$response->shouldReceive('setType')->once()->with('application/xml');

		$response->shouldReceive('setBody')->once()->with('<?xml version="1.0" encoding="utf-8"?>
<error><code>500</code><message>An error has occurred while processing your request.</message></error>
')->andReturn($response);

		$response->shouldReceive('setStatus')->once()->with(500)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new ProductionHandler($request, $response);

		$this->assertFalse($handler->handle(new ErrorException));
	}
}
