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
	public function testRegularError()
	{
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('registerNamespace')->once();

		$viewFactory->shouldReceive('clearAutoAssignVariables')->once()->andReturn($viewFactory);

		$viewFactory->shouldReceive('render')->once()->with('mako-error::error')->andReturn('rendered');

		//

		$responseHeaders = Mockery::mock(RequestHeaders::class);

		$responseHeaders->shouldReceive('acceptableContentTypes')->twice()->andReturn([]);

		//

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->twice()->andReturn($responseHeaders);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('text/html');

		$response->shouldReceive('type')->once()->with('text/html');

		$response->shouldReceive('body')->once()->with('rendered')->andReturn($response);

		$response->shouldReceive('status')->once()->with(500)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new ProductionHandler($viewFactory, $request, $response);

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testRequestException()
	{
		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('registerNamespace')->once();

		$viewFactory->shouldReceive('exists')->once()->with('mako-error::405')->andReturn(true);

		$viewFactory->shouldReceive('clearAutoAssignVariables')->once()->andReturn($viewFactory);

		$viewFactory->shouldReceive('render')->once()->with('mako-error::405')->andReturn('rendered');

		//

		$requestHeaders = Mockery::mock(RequestHeaders::class);

		$requestHeaders->shouldReceive('acceptableContentTypes')->twice()->andReturn([]);

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

		$response->shouldReceive('type')->once()->with('text/html');

		$response->shouldReceive('body')->once()->with('rendered')->andReturn($response);

		$response->shouldReceive('status')->once()->with(405)->andReturn($response);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('send')->once();

		//

		$handler = new ProductionHandler($viewFactory, $request, $response);

		$this->assertFalse($handler->handle(new MethodNotAllowedException(['GET', 'POST'])));
	}

	/**
	 *
	 */
	public function testRegularErrorWithJsonResponse()
	{
		if(function_exists('json_encode') === false)
		{
			$this->markTestSkipped('JSON support is missing.');

			return;
		}

		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('registerNamespace')->once();

		//

		$request = Mockery::mock(Request::class);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getType')->once()->andReturn('application/json');

		$response->shouldReceive('clear')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('type')->once()->with('application/json');

		$response->shouldReceive('body')->once()->with('{"error":{"code":500,"message":"An error has occurred while processing your request."}}')->andReturn($response);

		$response->shouldReceive('status')->once()->with(500)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new ProductionHandler($viewFactory, $request, $response);

		$this->assertFalse($handler->handle(new ErrorException));
	}

	/**
	 *
	 */
	public function testRegularErrorWithXmlResponse()
	{
		if(function_exists('simplexml_load_string') === false)
		{
			$this->markTestSkipped('XML support is missing.');

			return;
		}

		$viewFactory = Mockery::mock(ViewFactory::class);

		$viewFactory->shouldReceive('registerNamespace')->once();

		//

		$responseHeaders = Mockery::mock(RequestHeaders::class);

		$responseHeaders->shouldReceive('acceptableContentTypes')->once()->andReturn([]);

		//

		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		//

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('clear')->once()->andReturn($response);

		$response->shouldReceive('disableCaching')->once()->andReturn($response);

		$response->shouldReceive('disableCompression')->once()->andReturn($response);

		$response->shouldReceive('getType')->twice()->andReturn('application/xml');

		$response->shouldReceive('type')->once()->with('application/xml');

		$response->shouldReceive('body')->once()->with('<?xml version="1.0" encoding="utf-8"?>
<error><code>500</code><message>An error has occurred while processing your request.</message></error>
')->andReturn($response);

		$response->shouldReceive('status')->once()->with(500)->andReturn($response);

		$response->shouldReceive('send')->once();

		//

		$handler = new ProductionHandler($viewFactory, $request, $response);

		$this->assertFalse($handler->handle(new ErrorException));
	}
}
