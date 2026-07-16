<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\cli;

use ErrorException;
use mako\cli\output\components\Alert;
use mako\cli\output\components\Frame;
use mako\cli\output\Output;
use mako\error\handlers\HandlerInterface;
use mako\error\handlers\hints\traits\HintTrait;
use Override;
use Throwable;

use function array_map;
use function explode;
use function getcwd;
use function implode;
use function str_replace;

/**
 * Development handler.
 */
class DevelopmentHandler implements HandlerInterface
{
	use HintTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output
	) {
	}

	/**
	 * Escape formatting tags.
	 */
	protected function escape(string $string): string
	{
		if (($formatter = $this->output->formatter) === null) {
			return $string;
		}

		return $formatter->escape($string);
	}

	/**
	 * Determines the exception type.
	 */
	protected function determineExceptionType(Throwable $exception): string
	{
		if ($exception instanceof ErrorException) {
			return $exception::class . match ($exception->getCode()) {
				E_COMPILE_ERROR                 => ': Compile Error',
				E_DEPRECATED, E_USER_DEPRECATED => ': Deprecation',
				E_NOTICE, E_USER_NOTICE         => ': Notice',
				E_WARNING, E_USER_WARNING       => ': Warning',
				default                          => '',
			};
		}

		return $exception::class;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function handle(Throwable $exception): mixed
	{
		$alert = (new Alert($this->output))->render(
			"<bold>{$this->escape($this->determineExceptionType($exception))}</bold> [ {$exception->getCode()} ]",
			Alert::DANGER
		);

		$info = " <red><bold>{$this->escape($exception->getMessage())}</bold></red>";

		$cwd = getcwd();

		if (!empty($exception->getFile())) {
			$info .= PHP_EOL
			. PHP_EOL
			. " The error occured in <bold>{$this->escape(str_replace($cwd, '.', $exception->getFile()))}</bold>"
			. " on line <bold>{$exception->getLine()}</bold>"
			. PHP_EOL;
		}

		if (($hint = $this->getHint($exception)) !== null) {
			$info .= PHP_EOL;
			$info .= (new Frame($this->output))->render($hint, 'Hint');
		}

		$trace = $this->escape(
			implode(PHP_EOL, array_map(
				static fn ($str) => str_replace($cwd, '.', " {$str}"),
				explode(PHP_EOL, $exception->getTraceAsString())
			))
		);

		$this->output->write(PHP_EOL);

		$this->output->errorLn($alert . PHP_EOL . $info . PHP_EOL . $trace);

		$this->output->write(PHP_EOL);

		return false;
	}
}
