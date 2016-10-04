<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use Closure;

use mako\http\response\builders\JSON;
use mako\http\response\builders\JSONP;
use mako\http\response\senders\File;
use mako\http\response\senders\Redirect;
use mako\http\response\senders\Stream;
use mako\syringe\ContainerAwareTrait;

/**
 * Controller helper trait.
 *
 * @author  Frederic G. Østby
 */
trait ControllerHelperTrait
{
	use ContainerAwareTrait;

	/**
	 * Returns a file response container.
	 *
	 * @access  public
	 * @param   string                            $file  File path
	 * @return  \mako\http\response\senders\File
	 */
	protected function fileResponse(string $file): File
	{
		return new File($this->fileSystem, $file);
	}

	/**
	 * Returns a redirect response container.
	 *
	 * @access  public
	 * @param   string                                $location     Location
	 * @param   array                                 $routeParams  Route parameters
	 * @param   array                                 $queryParams  Associative array used to build URL-encoded query string
	 * @param   string                                $separator    Argument separator
	 * @param   mixed                                 $language     Request language
	 * @return  \mako\http\response\senders\Redirect
	 */
	protected function redirectResponse(string $location, array $routeParams = [], array $queryParams = [], string $separator = '&amp;', $language = true): Redirect
	{
		if($this->routes->hasNamedRoute($location))
		{
			$location = $this->urlBuilder->toRoute($location, $routeParams, $queryParams, $separator, $language);
		}

		return new Redirect($location);
	}

	/**
	 * Returns a stream response container.
	 *
	 * @access  public
	 * @param   \Closure                            $stream  Stream
	 * @return  \mako\http\response\senders\Stream
	 */
	protected function streamResponse(Closure $stream): Stream
	{
		return new Stream($stream);
	}

	/**
	 * Returns a JSON response builder.
	 *
	 * @param   mixed                             $data     Data
	 * @param   int                               $options  JSON encode coptions
	 * @return  \mako\http\response\builder\JSON
	 */
	protected function jsonResponse($data, int $options = 0): JSON
	{
		return new JSON($data, $options);
	}

	/**
	 * Returns a JSONP response builder.
	 *
	 * @param   mixed                              $data      Data
	 * @param   int                                $options   JSON encode coptions
	 * @return  \mako\http\response\builder\JSONP
	 */
	protected function jsonpResponse($data, int $options = 0): JSONP
	{
		return new JSONP($data, $options);
	}
}