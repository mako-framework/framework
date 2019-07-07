<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\cli;

use ErrorException;
use mako\cli\output\Output;
use mako\error\handlers\HandlerInterface;
use Throwable;

use function array_keys;
use function get_class;
use function in_array;

/**
 * Development handler.
 *
 * @author Frederic G. Østby
 */
class DevelopmentHandler implements HandlerInterface
{
	/**
	 * Output.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\Output $output Output
	 */
	public function __construct(Output $output)
	{
		$this->output = $output;
	}

	/**
	 * Escape formatting tags.
	 *
	 * @param  string $string String to escape
	 * @return string
	 */
	protected function escape(string $string): string
	{
		if(($formatter = $this->output->getFormatter()) === null)
		{
			return $string;
		}

		return $formatter->escape($string);
	}

	/**
	 * Determines the exception type.
	 *
	 * @param  \Throwable $exception Throwable
	 * @return string
	 */
	protected function determineExceptionType(Throwable $exception): string
	{
		if($exception instanceof ErrorException)
		{
			$code = $exception->getCode();

			$codes =
			[
				E_ERROR             => 'Fatal Error',
				E_PARSE             => 'Parse Error',
				E_COMPILE_ERROR     => 'Compile Error',
				E_COMPILE_WARNING   => 'Compile Warning',
				E_STRICT            => 'Strict Mode Error',
				E_NOTICE            => 'Notice',
				E_WARNING           => 'Warning',
				E_RECOVERABLE_ERROR => 'Recoverable Error',
				E_DEPRECATED        => 'Deprecated',
				E_USER_NOTICE       => 'Notice',
				E_USER_WARNING      => 'Warning',
				E_USER_ERROR        => 'Error',
				E_USER_DEPRECATED   => 'Deprecated',
			];

			return in_array($code, array_keys($codes)) ? $codes[$code] : 'ErrorException';
		}

		return get_class($exception);
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Throwable $exception)
	{
		$type = $this->escape($this->determineExceptionType($exception));

		$message = $this->escape($exception->getMessage());

		if(!empty($exception->getFile()))
		{
			$message .= PHP_EOL . PHP_EOL;
			$message .= "Error location: {$this->escape($exception->getFile())}";
			$message .= " on line {$this->escape($exception->getLine())}";
		}

		$trace = $this->escape($exception->getTraceAsString());

		$this->output->errorLn("<bg_red><white>{$type}: {$message}" . PHP_EOL . PHP_EOL . $trace . PHP_EOL . '</white></bg_red>');

		return false;
	}
}
