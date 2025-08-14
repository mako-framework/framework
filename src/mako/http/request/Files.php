<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use Override;

use function array_keys;
use function count;
use function is_array;

/**
 * Files.
 */
class Files extends Parameters
{
	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $parameters = [])
	{
		parent::__construct($this->convertToUploadedFileObjects($parameters));
	}

	/**
	 * Creates a UploadedFile object.
	 */
	protected function createUploadedFile(array $file): UploadedFile
	{
		return new UploadedFile($file['tmp_name'], $file['name'], $file['size'], $file['type'], $file['error']);
	}

	/**
	 * Normalizes a multi file upload array to a more manageable format.
	 */
	protected function normalizeMultiUpload(array $files): array
	{
		$normalized = [];

		$keys = array_keys($files);

		$count = count($files['name']);

		for ($i = 0; $i < $count; $i++) {
			foreach ($keys as $key) {
				$normalized[$i][$key] = $files[$key][$i];
			}
		}

		return $normalized;
	}

	/**
	 * Converts the $_FILES array to an array of UploadedFile objects.
	 */
	protected function convertToUploadedFileObjects(array $files): array
	{
		$uploadedFiles = [];

		foreach ($files as $name => $file) {
			if (is_array($file['name'])) {
				foreach ($this->normalizeMultiUpload($file) as $file) {
					$uploadedFiles[$name][] = $this->createUploadedFile($file);
				}
			}
			else {
				$uploadedFiles[$name] = $this->createUploadedFile($file);
			}
		}

		return $uploadedFiles;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function add(string $name, $value): void
	{
		if (is_array($value)) {
			$value = $this->createUploadedFile($value);
		}

		parent::add($name, $value);
	}
}
