<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp;

use FFI;
use FFI\CData;
use mako\pixel\metadata\xmp\exceptions\XmpException;
use mako\pixel\metadata\xmp\properties\ArrayProperty;
use mako\pixel\metadata\xmp\properties\QualifierProperty;
use mako\pixel\metadata\xmp\properties\StructProperty;
use mako\pixel\metadata\xmp\properties\Type;
use mako\pixel\metadata\xmp\properties\ValueProperty;

use function array_values;
use function dirname;
use function file_exists;
use function is_readable;
use function preg_replace;
use function register_shutdown_function;
use function sprintf;
use function str_contains;
use function str_ends_with;

/**
 * XMP data reader.
 */
class XmpReader
{
	/**
	 * Exempi readonly flag.
	 */
	protected const int XMP_OPEN_READ = 0x00000001;

	/**
	 * Should we register a shutdown function to clean up?
	 */
	protected static bool $registerShutdownFunction = true;

	/**
	 * FFI instance.
	 */
	protected static ?FFI $ffi = null;

	/**
	 * XMP pointer.
	 */
	protected ?CData $xmp = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		string $imagePath,
		?string $library = null,
	) {
		if (!file_exists($imagePath)) {
			throw new XmpException(sprintf('The image [ %s ] does not exist.', $imagePath));
		}

		if (!is_readable($imagePath)) {
			throw new XmpException(sprintf('The image [ %s ] is not readable.', $imagePath));
		}

		if (static::$ffi === null) {
			static::initExempi($library);
		}

		$this->xmp = $this->loadXmp($imagePath);
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if ($this->xmp !== null && static::$ffi !== null) {
			static::$ffi->xmp_free($this->xmp);
			$this->xmp = null;
		}

		if (!static::$registerShutdownFunction) {
			static::terminateExempi();
		}
	}

	/**
	 * Should we register a shutdown function to automatically
	 * terminate the Exempi library when the program ends?
	 *
	 * If TRUE (default) is passed then we'll keep Exempi initialized until the program ends.
	 * If FALSE is passed then Exempi will be terminated when the XmpReader object is destructed.
	 *
	 * NB! This function must be called before the first XmpReader instance is created.
	 */
	public static function registerShutdownFunction(bool $registerShutdownFunction): void
	{
		static::$registerShutdownFunction = $registerShutdownFunction;
	}

	/**
	 * Initialize the Exempi library.
	 */
	protected static function initExempi(?string $library): void
	{
		// Create bindings

		$code = <<<'CODE'
		typedef void* XmpPtr;
		typedef void* XmpStringPtr;
		typedef void* XmpFilePtr;
		typedef void* XmpIteratorPtr;

		int xmp_init(void);
		void xmp_terminate(void);

		XmpFilePtr xmp_files_open_new(const char* path, int mode);
		void xmp_files_close(XmpFilePtr file);
		void xmp_files_free(XmpFilePtr file);

		XmpPtr xmp_files_get_new_xmp(XmpFilePtr file);
		void xmp_free(XmpPtr xmp);

		XmpIteratorPtr xmp_iterator_new(XmpPtr xmp, const char* schema, const char* propName, int options);
		int xmp_iterator_next(XmpIteratorPtr iter, XmpStringPtr schema, XmpStringPtr name, XmpStringPtr value, int* options);
		void xmp_iterator_free(XmpIteratorPtr iter);

		XmpStringPtr xmp_string_new(void);
		const char* xmp_string_cstr(XmpStringPtr str);
		void xmp_string_free(XmpStringPtr str);

		void xmp_serialize(XmpPtr xmp, XmpStringPtr output, int options, int padding);
		CODE;

		$library ??= match (PHP_OS_FAMILY) {
			'Windows' => 'libexempi.dll',
			'Darwin' => 'libexempi.dylib',
			'Linux' => 'libexempi.so',
			default => throw new XmpException('Unable to automatically determine the name of the libexempi library. Please provide the library path manually.'),
		};

		static::$ffi = FFI::cdef($code, $library);

		// Initialize Exempi

		if (static::$ffi->xmp_init() !== 1) {
			throw new XmpException('Unable to initialize Exempi.');
		}

		// Register shutdown function that terminates Exempi to free up resources

		if (static::$registerShutdownFunction) {
			register_shutdown_function(static function (): void {
				static::terminateExempi();
			});
		}
	}

	/**
	 * Terminates the Exempi library.
	 */
	protected static function terminateExempi(): void
	{
		if (static::$ffi !== null) {
			static::$ffi->xmp_terminate();
			static::$ffi = null;
		}
	}

	/**
	 * Loads XMP data from the image file and returns a XMP pointer.
	 */
	protected function loadXmp(string $file): CData
	{
		$filePointer = static::$ffi->xmp_files_open_new($file, static::XMP_OPEN_READ);

		$xmp = static::$ffi->xmp_files_get_new_xmp($filePointer);

		static::$ffi->xmp_files_close($filePointer);
		static::$ffi->xmp_files_free($filePointer);

		$filePointer = null;

		if ($xmp === null) {
			throw new XmpException(sprintf('Unable to load XMP data from [ %s ].', $file));
		}

		return $xmp;
	}

	/**
	 * Returns an array of arrays containing the "raw" XMP properties matching the provided schema and property name.
	 *
	 * @return array<array{'schema': string, 'options': int, 'name': string, 'value': string}>
	 */
	protected function getRawProperties(?string $schema, ?string $propertyName): array
	{
		$properties = [];

		$iterator = static::$ffi->xmp_iterator_new($this->xmp, $schema, $propertyName, 0);

		if ($iterator === null) {
			return $properties;
		}

		$schemaString = static::$ffi->xmp_string_new();
		$nameString = static::$ffi->xmp_string_new();
		$valueString = static::$ffi->xmp_string_new();

		$options = static::$ffi->new('int');

		while (static::$ffi->xmp_iterator_next(
			$iterator,
			$schemaString,
			$nameString,
			$valueString,
			FFI::addr($options)
		) === 1) {
			$schema = static::$ffi->xmp_string_cstr($schemaString);
			$name = static::$ffi->xmp_string_cstr($nameString);
			$value = static::$ffi->xmp_string_cstr($valueString);

			if ($name === '' && $value === '') {
				continue;
			}

			$properties[$name] = [
				'schema' => $schema,
				'options' => (int) $options->cdata,
				'name' => $name,
				'value' => $value,
			];
		}

		static::$ffi->xmp_string_free($schemaString);
		static::$ffi->xmp_string_free($nameString);
		static::$ffi->xmp_string_free($valueString);

		static::$ffi->xmp_iterator_free($iterator);

		return $properties;
	}

	/**
	 * Returns an array where the "raw" XMP properties have been hydrated to DTO objects.
	 *
	 * @param  array<array{'schema': string, 'options': int, 'name': string, 'value': string}> $properties
	 * @return array<ArrayProperty|QualifierProperty|StructProperty|ValueProperty>
	 */
	protected function hydrate(array $properties): array
	{
		foreach ($properties as &$property) {
			if ($property['options'] & Type::ARRAY->value) {
				$property = new ArrayProperty(
					$property['schema'],
					$property['options'],
					$property['name']
				);
			}
			elseif ($property['options'] & Type::QUALIFIER->value) {
				$property = new QualifierProperty(
					$property['schema'],
					$property['options'],
					$property['name'],
					$property['value']
				);
			}
			elseif ($property['options'] & Type::STRUCT->value) {
				$property = new StructProperty(
					$property['schema'],
					$property['options'],
					$property['name']
				);
			}
			else {
				$property = new ValueProperty(
					$property['schema'],
					$property['options'],
					$property['name'],
					$property['value']
				);
			}
		}

		return $properties;
	}

	/**
	 * Returns the parent struct key of the provided property name.
	 */
	protected function getParentStructKey(string $name): string
	{
		return dirname($name);
	}

	/**
	 * Returns the parent array key of the provided property name.
	 */
	protected function getParentArrayKey(string $name): string
	{
		return preg_replace('/\[\d+\](?!.*\[\d+\])/', '', $name);
	}

	/**
	 * Appends a child property to a property object.
	 */
	protected function appendChildProperty(
		ArrayProperty|StructProperty|ValueProperty $parent,
		ArrayProperty|QualifierProperty|StructProperty|ValueProperty $child,
		string $property
	): void {
		(function () use ($child, $property): void {
			$this->{$property}[] = $child;
		})->bindTo($parent, $parent::class)();
	}

	/**
	 * Loops over the flat property array and nests the objects properly.
	 *
	 * @param array<ArrayProperty|QualifierProperty|StructProperty|ValueProperty> $properties
	 */
	protected function nest(array $properties): array
	{
		$unset = [];

		foreach ($properties as $key => $property) {
			$name = $property->fullyQualifiedName;

			if (str_ends_with($name, ']')) {
				$this->appendChildProperty(
					$properties[$this->getParentArrayKey($name)],
					$property,
					'values'
				);
				$unset[] = $key;
			}
			elseif (str_contains($name, '/')) {
				$this->appendChildProperty(
					$properties[$this->getParentStructKey($name)],
					$property,
					$property instanceof QualifierProperty ? 'qualifiers' : 'values'
				);
				$unset[] = $key;
			}
		}

		// Unset all properties that have been moved into a parent

		foreach ($unset as $key) {
			unset($properties[$key]);
		}

		// Return the nested properties

		return array_values($properties);
	}

	/**
	 * Returns the XMP data as XML.
	 */
	public function getXmpDataAsXml(): string
	{
		$xmlString = static::$ffi->xmp_string_new();

		static::$ffi->xmp_serialize($this->xmp, $xmlString, 0, 0);

		$xml = static::$ffi->xmp_string_cstr($xmlString);

		static::$ffi->xmp_string_free($xmlString);

		return $xml;
	}

	/**
	 * Returns an array contaning all properties matching the provided schema and property name.
	 *
	 * @return array<ArrayProperty|StructProperty|ValueProperty>
	 */
	public function getProperties(?string $schema = null, ?string $propertyName = null): array
	{
		return $this->nest($this->hydrate($this->getRawProperties($schema, $propertyName)));
	}

	/**
	 * Returns the property matching the provided schema and property name.
	 */
	public function getProperty(string $namespace, string $propertyName): null|ArrayProperty|StructProperty|ValueProperty
	{
		return $this->getProperties($namespace, $propertyName)[0] ?? null;
	}
}
