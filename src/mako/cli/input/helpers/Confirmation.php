<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use mako\cli\input\helpers\confirmation\Theme;
use mako\cli\input\helpers\traits\InteractiveInputTrait;
use mako\cli\input\Input;
use mako\cli\input\Key;
use mako\cli\output\Output;
use mako\cli\traits\SttyTrait;

use function mb_strtolower;
use function mb_strtoupper;

/**
 * Confirmation helper.
 */
class Confirmation
{
	use InteractiveInputTrait;
	use SttyTrait;

	/**
	 * Current selection.
	 */
	protected bool $currentSelection;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Input $input,
		protected Output $output,
		protected string $trueLabel = 'Yes',
		protected string $falseLabel = 'No',
		protected Theme $theme = new Theme,
	) {
	}

	protected function nonInteractiveConfirmation(): bool
	{
		$trueLabel = $this->currentSelection ? mb_strtoupper($this->trueLabel) : mb_strtolower($this->trueLabel);
		$falseLabel = !$this->currentSelection ? mb_strtoupper($this->falseLabel) : mb_strtolower($this->falseLabel);

		$this->output->write("[{$trueLabel}/{$falseLabel}] {$this->theme->getInputPrefix()} ");

		$confirmed = mb_strtolower($this->input->read());

		if ($confirmed === 'y' || $confirmed === mb_strtolower($this->trueLabel)) {
			return true;
		}
		elseif ($confirmed === 'n' || $confirmed === mb_strtolower($this->falseLabel)) {
			return false;
		}
		elseif ($confirmed === '') {
			return $this->currentSelection;
		}

		return $this->nonInteractiveConfirmation();
	}

	/**
	 * Toggles the selection.
	 */
	protected function toggleSelection(): void
	{
		$this->currentSelection = !$this->currentSelection;
	}

	/**
	 * Renders the input.
	 */
	protected function renderInput(): void
	{
		$output = PHP_EOL . ' ';
		$output .= ($this->currentSelection ? $this->theme->getSelected(true) : $this->theme->getUnselected(true)) . " {$this->trueLabel}";
		$output .= ' ';
		$output .= (!$this->currentSelection ? $this->theme->getSelected(false) : $this->theme->getUnselected(false)) . " {$this->falseLabel}";
		$output .= PHP_EOL;

		$this->render($output);
	}

	/**
	 * Renders a confirmation input and returns the user's answer.
	 */
	protected function interactiveConfirmation(): bool
	{
		$this->output->cursor->hide();

		$confirmed = $this->sttySandbox(function (): bool {
			$this->setSttySettings('-echo -icanon');

			while (true) {
				$this->renderInput();

				$key = Key::tryFrom($this->input->readBytes(3));

				if ($key === Key::SPACE || $key === Key::LEFT || $key === Key::RIGHT) {
					$this->toggleSelection();
				}
				elseif ($key === Key::ENTER) {
					return $this->currentSelection;
				}
			}
		});

		$this->output->cursor->show();

		return $confirmed;
	}

	/**
	 * Asks the user for confirmation.
	 */
	public function ask(string $question, bool $default = false): bool
	{
		$this->currentSelection = $default;

		$this->output->writeLn($question);

		if (!$this->output->environment->hasStty() || $this->output->cursor === null) {
			return $this->nonInteractiveConfirmation();
		}

		return $this->interactiveConfirmation();
	}
}
