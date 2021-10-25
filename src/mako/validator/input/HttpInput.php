<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input;

use mako\http\Request;
use mako\http\routing\URLBuilder;

/**
 * HTTP input.
 *
 * @author Frederic G. Østby
 */
abstract class HttpInput extends Input implements HttpInputInterface
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
	 * Should we redirect the client if possible?
	 *
	 * @var bool
	 */
	protected $shouldRedirect = true;

	/**
	 * Should the old input be included?
	 *
	 * @var bool
	 */
	protected $shouldIncludeOldInput = true;

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
	 * {@inheritDoc}
	 */
	public function getInput(): array
	{
		return $this->request->getData()->all();
	}

	/**
	 * {@inheritDoc}
	 */
	public function shouldRedirect(): bool
	{
		return $this->shouldRedirect;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRedirectUrl(): string
	{
		return $this->urlBuilder->current();
	}

	/**
	 * {@inheritDoc}
	 */
	public function shouldIncludeOldInput(): bool
	{
		return $this->shouldIncludeOldInput;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOldInput(): array
	{
		return $this->request->getData()->all();
	}
}
