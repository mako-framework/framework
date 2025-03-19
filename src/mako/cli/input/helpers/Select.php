<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use mako\cli\input\helpers\select\Theme;
use mako\cli\input\Input;
use mako\cli\input\Key;
use mako\cli\output\Output;
use mako\cli\output\traits\OutputTrait;
use mako\cli\traits\SttyTrait;

use function array_keys;
use function count;
use function explode;
use function implode;

/**
 * Select helper.
 */
class Select
{
	use OutputTrait;
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
		protected Theme $theme = new Theme,
		protected bool $returnKey = true,
		protected bool $allowMultiple = false,
		protected bool $allowEmptySelection = false,
	) {
	}

	/**
	 * Returns the key or value of the chosen option.
	 */
	protected function nonInteractiveSelect(array $options, callable $optionFormatter): mixed
	{
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
			$output .= ($i++) . ') ' . $optionFormatter($option) . PHP_EOL;
		}

		$this->output->write("{$output}{$this->theme->getActivePointer()} ");

		// Read the user input

		$input = $this->input->read();

		// Process the user input

		if (empty($input)) {
			if ($this->allowEmptySelection) {
				return null;
			}

			$displayChoiceRequiredMessage = true;

			goto render_list;
		}

		$possibleKeys = explode(',', $input);

		$selection = [];

		foreach ($possibleKeys as $possibleKey) {
			$key = $keys[(int) $possibleKey - 1] ?? false;

			if ($key === false || $this->allowMultiple === false && count($possibleKeys) > 1) {
				$displayInvalidChoiceMessage = true;

				goto render_list;
			}

			$selection[] = $this->returnKey ? $key : $options[$key];
		}

		// Return the selection

		return $this->allowMultiple ? $selection : $selection[0];
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
	 * Renders the input.
	 */
	protected function renderInput(callable $optionFormatter): void
	{
		$output = [];

		$i = 0;

		foreach ($this->optionsState as $option) {
			$pointer = ($i++ === $this->currentOption ? $this->theme->getActivePointer() : $this->theme->getInactivePointer());

			$selected = $option['selected'] ? $this->theme->getSelected() : $this->theme->getUnselected();

			$output[] = "{$pointer} {$selected} {$optionFormatter($option['value'])}";
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
	protected function toggleSelection(): void
	{
		if (!$this->allowMultiple) {
			$this->unselectAllExceptCurrent();
		}

		$selected = $this->optionsState[$this->currentOption]['selected'] = !$this->optionsState[$this->currentOption]['selected'];

		if ($selected && $this->showChoiceRequiredMessage) {
			$this->showChoiceRequiredMessage = false;
		}
	}

	/**
	 * Returns the chosen selection.
	 */
	protected function getSelection(): mixed
	{
		$selection = [];

		foreach ($this->optionsState as $option) {
			if ($option['selected']) {
				$selection[] = $this->returnKey ? $option['key'] : $option['value'];
			}
		}

		if (empty($selection)) {
			return null;
		}

		return $this->allowMultiple ? $selection : $selection[0];
	}

	/**
	 * Returns the chosen selection.
	 */
	protected function interactiveSelect(array $options, callable $optionFormatter): mixed
	{
		$this->buildInitialState($options);

		$this->output->cursor->hide();

		$selection = $this->sttySandbox(function () use ($optionFormatter): mixed {
			$this->setSttySettings('-echo -icanon');

			while (true) {
				$this->renderInput($optionFormatter);

				$key = Key::tryFrom($this->input->readBytes(3));

				if ($key === Key::UP) {
					$this->moveCursorUp();
				}
				elseif ($key === Key::DOWN) {
					$this->moveCursorDown();
				}
				elseif ($key === Key::SPACE || $key === Key::LEFT || $key === Key::RIGHT) {
					$this->toggleSelection();
				}
				elseif ($key === Key::ENTER) {
					$selection = $this->getSelection();

					if ($this->allowEmptySelection || $selection !== null) {
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
	public function ask(string $label, array $options, ?callable $optionFormatter = null): mixed
	{
		$this->output->writeLn($label);

		$optionFormatter ??= fn (mixed $option): string => $option;

		if (!$this->output->environment->hasStty() || !$this->output->environment->hasAnsiSupport() || $this->output->cursor === null) {
			return $this->nonInteractiveSelect($options, $optionFormatter);
		}

		return $this->interactiveSelect($options, $optionFormatter);
	}
}
