<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\hints;

use Exception;
use mako\error\handlers\hints\ContainerException;
use mako\syringe\exceptions\ContainerException as SyringeContainerException;
use mako\syringe\exceptions\UnableToResolveParameterException;
use mako\tests\TestCase;
use PHPUnit\Metadata\Group;

#[Group('unit')]
class ContainerExceptionTest extends TestCase
{

	/**
	 *
	 */
	public function testWithInvalidExceptionType(): void
	{
		$exception = new Exception('Foobar');

		$hint = new ContainerException;

		$this->assertFalse($hint->canProvideHint($exception));
	}

	/**
	 *
	 */
	public function testWithValidExceptionTypeButInvalidChild(): void
	{
		$exception = new SyringeContainerException('Foobar');

		$hint = new ContainerException;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithValidExceptionType1WithServiceHint(): void
	{
		$exception = new UnableToResolveParameterException('Unable to resolve the [ $adapter ] parameter of [ mako\gatekeeper\Gatekeeper::__construct ].');

		$hint = new ContainerException;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame('Have you forgotten to enable the GatekeeperService service?', $hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithValidExceptionType1WithoutServiceHint(): void
	{
		$exception = new UnableToResolveParameterException('Unable to resolve the [ $foobar ] parameter of [ Foobar::__construct ].');

		$hint = new ContainerException;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame('Have you forgotten to enable a service or to register a dependency in the container?', $hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithValidExceptionType2WithServiceHint(): void
	{
		$exception = new UnableToResolveParameterException('Unable to create a [ mako\gatekeeper\Gatekeeper ] instance.');

		$hint = new ContainerException;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame('Have you forgotten to enable the GatekeeperService service?', $hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithValidExceptionType2WithoutServiceHint(): void
	{
		$exception = new UnableToResolveParameterException('Unable to create a [ Foobar ] instance.');

		$hint = new ContainerException;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame('Have you forgotten to enable a service or to register a dependency in the container?', $hint->getHint($exception));
	}
}
