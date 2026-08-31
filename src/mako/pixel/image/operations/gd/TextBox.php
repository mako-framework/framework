<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\TextBox as TextBoxOperation;
use mako\pixel\image\traits\GdTrait;
use Override;

use function count;
use function explode;
use function imagefilledrectangle;
use function imagerectangle;
use function imagesetthickness;
use function imagettfbbox;
use function imagettftext;
use function trim;

/**
 * {@inheritDoc}
 */
class TextBox extends TextBoxOperation
{
	use GdTrait;

	/**
	 * Draws the box.
	 */
	protected function drawBox(GdImage $imageResource): void
	{
		$x1 = $this->position->x;
		$y1 = $this->position->y;
		$x2 = $x1 + $this->dimensions->width;
		$y2 = $y1 + $this->dimensions->height;

		if ($this->fill !== null) {
			$fill = $this->allocateColor($imageResource, $this->fill);

			imagefilledrectangle($imageResource, $x1, $y1, $x2, $y2, $fill);
		}

		if ($this->stroke !== null) {
			$stroke = $this->allocateColor($imageResource, $this->stroke);

			imagesetthickness($imageResource, $this->strokeWidth);

			imagerectangle($imageResource, $x1, $y1, $x2, $y2, $stroke);

			imagesetthickness($imageResource, 1);
		}
	}

	/**
	 * Wraps the text so that each line fits within the box width.
	 *
	 * @return array<string>
	 */
	protected function wrapText(): array
	{
		$lines = [];

		foreach (explode("\n", $this->text) as $paragraph) {
			$line = '';

			foreach (explode(' ', $paragraph) as $word) {
				$candidate = trim("{$line} {$word}");

				$box = imagettfbbox($this->normalizeFontSize($this->font->size), 0, $this->font->path, $candidate);

				if ($box[2] - $box[0] > $this->dimensions->width && $line !== '') {
					$lines[] = $line;

					$line = $word;

					continue;
				}

				$line = $candidate;
			}

			$lines[] = $line;
		}

		return $lines;
	}

	/**
	 * Draws the text.
	 */
	protected function drawText(GdImage $imageResource): void
	{
		$lines = $this->wrapText();

		// Calculate line height and total text block height

		$box = imagettfbbox($this->normalizeFontSize($this->font->size), 0, $this->font->path, 'Mg');

		$lineHeight = (int) (($box[1] - $box[7]) * 1.2);

		$textHeight = $lineHeight * count($lines);

		// Allocate the font color

		$color = $this->allocateColor($imageResource, $this->font->color);

		// Draw each line centered within the box

		$y = $this->position->y + (int) (($this->dimensions->height - $textHeight) / 2) + $lineHeight;

		foreach ($lines as $line) {
			$box = imagettfbbox($this->normalizeFontSize($this->font->size), 0, $this->font->path, $line);

			$lineWidth = $box[2] - $box[0];

			$x = $this->position->x + (int) (($this->dimensions->width - $lineWidth) / 2);

			imagettftext($imageResource, $this->normalizeFontSize($this->font->size), 0, $x, $y, $color, $this->font->path, $line);

			$y += $lineHeight;
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->fill !== null || $this->stroke !== null) {
			$this->drawBox($imageResource);
		}

		$this->drawText($imageResource);
	}
}
