<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input;

use mako\http\Request;
use mako\http\routing\URLBuilder;

use function array_filter;

/**
 * HTTP input.
 *
 * @author Frederic G. Østby
 */
abstract class HttpInput extends Input
{
	/**
	 * Request.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

	/**
	 * URL builder.
	 *
	 * @var \mako\http\routing\URLBuilder
	 */
	protected $urlBuilder;

	/**
	 * Should the request be redirected if possible?
	 *
	 * @var bool
	 */
	protected $shouldRedirect = true;

	/**
	 * Should the old input be included?
	 *
	 * @var bool
	 */
	protected $includeOldInput = true;

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request            $request    Request
	 * @param \mako\http\routing\URLBuilder $urlBuilder URL builder
	 */
	public function __construct(Request $request, URLBuilder $urlBuilder)
	{
		$this->request = $request;

		$this->urlBuilder = $urlBuilder;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInput(): array
	{
		return $this->request->getData()->all();
	}

	/**
	 * Returns the error message.
	 *
	 * @return string|null
	 */
	protected function getMessage(): ?string
	{
		return null;
	}

	/**
	 * Returns the redirect URL.
	 *
	 * @return string|null
	 */
	protected function getRedirectUrl(): ?string
	{
		return $this->urlBuilder->current();
	}

	/**
	 * Returns the old input.
	 *
	 * @return array|null
	 */
	protected function getOldInput(): ?array
	{
		return $this->request->getData()->all();
	}

	/**
	 * Builds the meta array.
	 *
	 * @return array
	 */
	protected function buildMeta(): array
	{
		return
		[
			'message'         => $this->getMessage(),
			'should_redirect' => $this->shouldRedirect,
			'redirect_url'    => $this->shouldRedirect ? $this->getRedirectUrl() : null,
			'old_input'       => $this->shouldRedirect && $this->includeOldInput ? $this->getOldInput() : null,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMeta(): array
	{
		return array_filter($this->buildMeta(), function($value)
		{
			return $value !== null;
		});
	}
}
