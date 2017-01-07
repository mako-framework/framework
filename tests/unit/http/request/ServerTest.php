<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use PHPUnit_Framework_TestCase;

use mako\http\request\Server;

/**
 * @group unit
 */
class ServerTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testGetHeaders()
	{
		$headers =
		[
			'HTTP_FOO'       => 1,
			'HTTP_FOO_BAR'   => 2,
			'CONTENT_LENGTH' => 3,
			'CONTENT_MD5'    => 4,
			'CONTENT_TYPE'   => 5,
			'NOPE'           => 6,
		];

		$headers = new Server($headers);

		$extractedHeaders =
		[
			'FOO'            => 1,
			'FOO_BAR'        => 2,
			'CONTENT_LENGTH' => 3,
			'CONTENT_MD5'    => 4,
			'CONTENT_TYPE'   => 5,
		];

		$this->assertSame($extractedHeaders, $headers->getHeaders());
	}
}
