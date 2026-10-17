<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\metadata;

use FFI\Exception as FFIException;
use mako\pixel\metadata\xmp\exceptions\XmpException;
use mako\pixel\metadata\xmp\properties\ArrayProperty;
use mako\pixel\metadata\xmp\properties\ValueProperty;
use mako\pixel\metadata\xmp\XmpReader;
use mako\tests\TestCase;
use Override;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('ffi')]
class XmpReaderTest extends TestCase
{
	protected const string FIXTURE = __DIR__ . '/fixtures/metadata-test-file.jpg';

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public static function setUpBeforeClass(): void
	{
		try {
			new XmpReader(static::FIXTURE);
		}
		catch (XmpException $e) {
			// Only skip when the library itself can't be loaded; anything else is a real failure
			if ($e->getPrevious() instanceof FFIException) {
				static::markTestSkipped('Unable to load libexempi.');
			}

			throw $e;
		}
	}

	/**
	 *
	 */
	public function testGetProperties(): void
	{
		$reader = new XmpReader(static::FIXTURE);

		$properties = $reader->getProperties();

		$this->assertCount(4, $properties);

		// Test namespace

		$properties = $reader->getProperties(XmpReader::NS_DC);

		$this->assertCount(3, $properties);

		$properties = $reader->getProperties(XmpReader::NS_TIFF);

		$this->assertCount(1, $properties);

		// Test namespace + property name

		$properties = $reader->getProperties(XmpReader::NS_DC, 'dc:creator');

		$this->assertInstanceOf(ArrayProperty::class, $properties[0]);

		$this->assertInstanceOf(ValueProperty::class, $properties[0]->values[0]);

		$this->assertSame(XmpReader::NS_DC, $properties[0]->values[0]->namespace);

		$this->assertSame('dc:creator[1]', $properties[0]->values[0]->fullyQualifiedName);

		$this->assertTrue(str_contains($properties[0]->values[0]->value, 'Carl Seibert'));
	}

	/**
	 *
	 */
	public function testGetProperty(): void
	{
		$reader = new XmpReader(static::FIXTURE);

		$property = $reader->getProperty(XmpReader::NS_DC, 'dc:creator[1]');

		$this->assertInstanceOf(ValueProperty::class, $property);

		$this->assertSame(XmpReader::NS_DC, $property->namespace);

		$this->assertSame('dc:creator[1]', $property->fullyQualifiedName);

		$this->assertTrue(str_contains($property->value, 'Carl Seibert'));
	}

	/**
	 *
	 */
	public function testToXml(): void
	{
		$reader = new XmpReader(static::FIXTURE);

		$xml = $reader->toXml();

		$this->assertTrue(str_starts_with($xml, '<?xpacket begin='));
	}

	/**
	 *
	 */
	public function testToString(): void
	{
		$reader = new XmpReader(static::FIXTURE);

		$this->assertSame($reader->toXml(), (string) $reader);
	}
}
