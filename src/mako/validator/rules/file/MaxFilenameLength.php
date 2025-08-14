<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\http\request\UploadedFile;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use Override;

use function mb_strlen;
use function sprintf;

/**
 * Max filename length rule.
 */
class MaxFilenameLength extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $maxLength
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['maxLength'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		$filename = $value instanceof UploadedFile ? $value->getReportedFilename() : $value->getFilename();

		return mb_strlen($filename) <= $this->maxLength;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s filename must be at most %2$s characters long.', $field, $this->maxLength);
	}
}
