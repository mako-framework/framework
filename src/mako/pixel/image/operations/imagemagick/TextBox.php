<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\operations\TextBox as TextBoxOperation;
use Override;

use function count;
use function explode;

/**
 * {@inheritDoc}
 */
class TextBox extends TextBoxOperation
{
	/**
	 * Metrics sample.
	 *
	 * A = provides an uppercase glyph
	 * g = provides a lowercase glyph with a descender
	 */
	protected const string METRICS_SAMPLE = 'Ag';

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$draw = new ImagickDraw;

		try {
			if ($this->fill !== null || $this->stroke !== null) {
				$draw->setFillColor(new ImagickPixel($this->fill?->toHexaString() ?? 'transparent'));

				if ($this->stroke !== null) {
					$draw->setStrokeColor(new ImagickPixel($this->stroke->toHexaString()));

					$draw->setStrokeWidth($this->strokeWidth);
				}

				$draw->rectangle(
					$this->position->x,
					$this->position->y,
					$this->position->x + $this->dimensions->width,
					$this->position->y + $this->dimensions->height,
				);

				$imageResource->drawImage($draw);

				$draw->clear();
			}

			$draw->setFont($this->font->path);
			$draw->setFontSize($this->font->size);
			$draw->setFillColor(new ImagickPixel($this->font->color->toHexaString()));
			$draw->setTextAlignment(Imagick::ALIGN_CENTER);

			$lines = explode("\n", $this->text);

			$metrics = $imageResource->queryFontMetrics($draw, static::METRICS_SAMPLE);

			$lineHeight = $metrics['textHeight'];
			$totalHeight = count($lines) * $lineHeight;

			$y = $this->position->y
				+ (($this->dimensions->height - $totalHeight) / 2)
				+ $metrics['ascender'];

			foreach ($lines as $line) {
				$imageResource->annotateImage(
					$draw,
					$this->position->x + ($this->dimensions->width / 2),
					$y,
					0,
					$line,
				);

				$y += $lineHeight;
			}
		}
		finally {
			$draw->clear();
			$draw->destroy();
		}
	}
}
