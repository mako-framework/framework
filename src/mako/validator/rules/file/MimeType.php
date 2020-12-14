<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;

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
	 *
	 * @var array
	 */
	protected $mimeTypes;

	/**
	 * Constructor.
	 *
	 * @param array|string $mimeType Mime type or array of mime types
	 */
	public function __construct($mimeType)
	{
		$this->mimeTypes = (array) $mimeType;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['mimeTypes'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return in_array($value->getMimeType(), $this->mimeTypes);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be a file of type: %2$s.', $field, implode(', ', $this->mimeTypes));
	}
}
