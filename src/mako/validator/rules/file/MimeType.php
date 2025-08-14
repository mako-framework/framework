<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use Override;

use function implode;
use function in_array;
use function sprintf;

/**
 * Mime type rule.
 */
class MimeType extends Rule implements RuleInterface
{
	/**
	 * Mime types.
	 */
	protected array $mimeTypes;

	/**
	 * Constructor.
	 */
	public function __construct(array|string $mimeType)
	{
		$this->mimeTypes = (array) $mimeType;
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['mimeTypes'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return in_array($value->getMimeType(), $this->mimeTypes);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be a file of type: %2$s.', $field, implode(', ', $this->mimeTypes));
	}
}
