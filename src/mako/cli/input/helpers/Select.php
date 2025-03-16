<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use mako\cli\input\helpers\select\Theme;
use mako\cli\input\helpers\traits\InteractiveInputTrait;
use mako\cli\input\Input;
use mako\cli\input\Key;
use mako\cli\output\Output;
use mako\cli\traits\SttyTrait;

use function array_keys;
use function count;
use function explode;
use function implode;
use function trim;

/**
 * Select helper.
 */
class Select
{
	use InteractiveInputTrait;
	use SttyTrait;

	/**
	 * Current option.
	 */
	protected int $currentOption = 0;

	/**
	 * Number of options.
	 */
	protected int $optionsCount;

	/**
	 * Options state.
	 */
	protected array $optionsState;

	/**
	 * Show choice required message?
	 */
	protected bool $showChoiceRequiredMessage = false;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Input $input,
		protected Output $output,
		protected string $invalidChoiceMessage = 'Invalid choice. Please try again.',
		protected string $choiceRequiredMessage = 'You need to make a selection.',
		protected Theme $theme = new Theme
	) {
	}

	/**
	 * Returns the key or value of the chosen option.
	 */
	protected function nonInteractiveSelect(
		array $options,
		bool $returnKey,
		bool $allowMultiple,
		bool $allowEmptySelection
	): mixed {
		$keys = array_keys($options);

		$displayInvalidChoiceMessage = false;
		$displayChoiceRequiredMessage = false;

		// Render the list of options and prompt the user for input

		render_list:

		$output = '';

		$i = 1;

		if ($displayInvalidChoiceMessage) {
			$output .=  PHP_EOL . $this->invalidChoiceMessage . PHP_EOL . PHP_EOL;
			$displayInvalidChoiceMessage = false;
		}

		if ($displayChoiceRequiredMessage) {
			$output .= PHP_EOL . $this->choiceRequiredMessage . PHP_EOL . PHP_EOL;
			$displayChoiceRequiredMessage = false;
		}

		foreach ($options as $option) {
			$output .= ($i++) . ') ' . $option . PHP_EOL;
		}

		$this->output->write("{$output}{$this->theme->getActivePointer()} ");

		// Read the user input

		$input = $this->input->read();

		// Process the user input

		if (empty($input)) {
			if ($allowEmptySelection) {
				return null;
			}

			$displayChoiceRequiredMessage = true;

			goto render_list;
		}

		$possibleKeys = explode(',', $input);

		$selection = [];

		foreach ($possibleKeys as $possibleKey) {
			$key = $keys[(int) $possibleKey - 1] ?? false;

			if ($key === false || $allowMultiple === false && count($possibleKeys) > 1) {
				$displayInvalidChoiceMessage = true;

				goto render_list;
			}

			$selection[] = $returnKey ? $key : $options[$key];
		}

		// Return the selection

		return $allowMultiple ? $selection : $selection[0];
	}

	/**
	 * Builds the initial state of the options.
	 */
	protected function buildInitialState(array $options): void
	{
		$this->optionsCount = count($options);

		$this->optionsState = [];

		$i = 0;

		foreach ($options as $key => $option) {
			$this->optionsState[$i++] = [
				'key' => $key,
				'value' => $option,
				'selected' => false,
			];
		}
	}

	/**
	 * Renders the list of options.
	 */
	protected function renderOptions(): void
	{
		$output = [];

		$i = 0;

		foreach ($this->optionsState as $option) {
			$pointer = ($i++ === $this->currentOption ? $this->theme->getActivePointer() : $this->theme->getInactivePointer());

			$selected = $option['selected'] ? $this->theme->getSelected() : $this->theme->getUnselected();

			$output[] = "{$pointer} {$selected} {$option['value']}";
		}

		if ($this->showChoiceRequiredMessage) {
			$output[] = PHP_EOL . $this->choiceRequiredMessage;
		}

		$this->render(PHP_EOL . implode(PHP_EOL, $output) . PHP_EOL);
	}

	/**
	 * Moves the cursor up.
	 */
	protected function moveCursorUp(): void
	{
		if ($this->currentOption > 0) {
			$this->currentOption--;
		}
		else {
			$this->currentOption = $this->optionsCount - 1;
		}
	}

	/**
	 * Moves the cursor down.
	 */
	protected function moveCursorDown(): void
	{
		if ($this->currentOption < $this->optionsCount - 1) {
			$this->currentOption++;
		}
		else {
			$this->currentOption = 0;
		}
	}

	/**
	 * Unselects all options except the current one.
	 */
	protected function unselectAllExceptCurrent(): void
	{
		foreach ($this->optionsState as $i => $_) {
			if ($i !== $this->currentOption) {
				$this->optionsState[$i]['selected'] = false;
			}
		}
	}

	/**
	 * Toggles the selection of the current option.
	 */
	protected function toggleSelection(bool $allowMultiple): void
	{
		if (!$allowMultiple) {
			$this->unselectAllExceptCurrent();
		}

		$this->optionsState[$this->currentOption]['selected'] = !$this->optionsState[$this->currentOption]['selected'];
	}

	/**
	 * Returns the chosen selection.
	 */
	protected function getSelection(bool $returnKey, bool $allowMultiple): mixed
	{
		$selection = [];

		foreach ($this->optionsState as $option) {
			if ($option['selected']) {
				$selection[] = $returnKey ? $option['key'] : $option['value'];
			}
		}

		if (empty($selection)) {
			return null;
		}

		return $allowMultiple ? $selection : $selection[0];
	}

	/**
	 * Returns the chosen selection.
	 */
	protected function interactiveSelect(
		array $options,
		bool $returnKey,
		bool $allowMultiple,
		bool $allowEmptySelection
	): mixed {
		$this->buildInitialState($options);

		$this->output->cursor->hide();

		$selection = $this->sttySandbox(function () use ($returnKey, $allowMultiple, $allowEmptySelection): mixed {
			$this->setSttySettings('-echo -icanon');

			while (true) {
				$this->renderOptions();

				$input = Key::tryFrom($this->input->readCharacters(3));

				if ($input === Key::UP) {
					$this->moveCursorUp();
				}
				elseif ($input === Key::DOWN) {
					$this->moveCursorDown();
				}
				elseif ($input === Key::SPACE || $input === Key::LEFT || $input === Key::RIGHT) {
					$this->toggleSelection($allowMultiple);
				}
				elseif ($input === Key::ENTER) {
					$selection = $this->getSelection($returnKey, $allowMultiple);

					if ($allowEmptySelection || $selection !== null) {
						if ($this->showChoiceRequiredMessage) {
							$this->output->cursor->up(2);
							$this->output->cursor->clearScreenFromCursor();
						}

						return $selection;
					}

					$this->showChoiceRequiredMessage = true;
				}
			}
		});

		$this->resetNewlinesInLastRender();

		$this->output->cursor->show();

		return $selection;
	}

	/**
	 * Prints out a list of options and returns the selection.
	 */
	public function ask(
		string $question,
		array $options,
		bool $returnKey = true,
		bool $allowMultiple = false,
		bool $allowEmptySelection = false
	): mixed {
		$this->output->writeLn(trim($question));

		if (!$this->output->environment->hasStty() || $this->output->cursor === null) {
			return $this->nonInteractiveSelect($options, $returnKey, $allowMultiple, $allowEmptySelection);
		}

		return $this->interactiveSelect($options, $returnKey, $allowMultiple, $allowEmptySelection);
	}
}
