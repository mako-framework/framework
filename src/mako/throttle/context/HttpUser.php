<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\throttle\context;

use mako\gatekeeper\Gatekeeper;
use mako\http\Request;
use Override;

/**
 * HTTP user context.
 */
class HttpUser implements ContextInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Request $request,
		protected ?Gatekeeper $gatekeeper = null
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getIdentifier(): string
	{
		if ($this->gatekeeper !== null && $this->gatekeeper->isLoggedIn()) {
			return (string) $this->gatekeeper->getUser()->getId();
		}

		return $this->request->getIp();
	}
}
