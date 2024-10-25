<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixl;

use mako\pixl\exceptions\PixlException;
use mako\pixl\Image;
use mako\pixl\processors\ProcessorInterface;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ImageTest extends TestCase
{
	/**
	 * @return \mako\pixl\processors\ProcessorInterface|\Mockery\MockInterface
	 */
	public function getProcessor()
	{
		return Mockery::mock(ProcessorInterface::class);
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$image = new Image(__FILE__, $processor);
	}

	/**
	 *
	 */
	public function testConstructorWithNonExistingFile(): void
	{
		$this->expectException(PixlException::class);

		$processor = $this->getProcessor();

		$image = new Image('foobar.png', $processor);
	}

	/**
	 *
	 */
	public function testRotate(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('rotate')->with(180)->once();

		$image = new Image(__FILE__, $processor);

		$image->rotate(180);
	}

	/**
	 *
	 */
	public function testResizeToPercent(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('resize')->with(50, null, Image::RESIZE_IGNORE)->once();

		$image = new Image(__FILE__, $processor);

		$image->resize(50);
	}

	/**
	 *
	 */
	public function testResizeToPixelSize(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('resize')->with(300, 300, Image::RESIZE_IGNORE)->once();

		$image = new Image(__FILE__, $processor);

		$image->resize(300, 300);
	}

	/**
	 *
	 */
	public function testResizeToPixelWithRestriction(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('resize')->with(300, 300, Image::RESIZE_AUTO)->once();

		$image = new Image(__FILE__, $processor);

		$image->resize(300, 300, Image::RESIZE_AUTO);
	}

	/**
	 *
	 */
	public function testCrop(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('crop')->with(300, 300, 50, 50)->once();

		$image = new Image(__FILE__, $processor);

		$image->crop(300, 300, 50, 50);
	}

	/**
	 *
	 */
	public function testFlipHorizontal(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('flip')->with(Image::FLIP_HORIZONTAL)->once();

		$image = new Image(__FILE__, $processor);

		$image->flip();

		//

		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('flip')->with(Image::FLIP_HORIZONTAL)->once();

		$image = new Image(__FILE__, $processor);

		$image->flip(Image::FLIP_HORIZONTAL);
	}

	/**
	 *
	 */
	public function testFlipVertical(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('flip')->with(Image::FLIP_VERTICAL)->once();

		$image = new Image(__FILE__, $processor);

		$image->flip(Image::FLIP_VERTICAL);
	}

	/**
	 *
	 */
	public function testWatermark(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('watermark')->with(__FILE__, Image::WATERMARK_TOP_LEFT, 100)->once();

		$image = new Image(__FILE__, $processor);

		$image->watermark(__FILE__);
	}

	/**
	 *
	 */
	public function testWatermarkPosition(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('watermark')->with(__FILE__, Image::WATERMARK_BOTTOM_RIGHT, 100)->once();

		$image = new Image(__FILE__, $processor);

		$image->watermark(__FILE__, Image::WATERMARK_BOTTOM_RIGHT);
	}

	/**
	 *
	 */
	public function testWatermarkOpacity(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('watermark')->with(__FILE__, Image::WATERMARK_BOTTOM_RIGHT, 70)->once();

		$image = new Image(__FILE__, $processor);

		$image->watermark(__FILE__, Image::WATERMARK_BOTTOM_RIGHT, 70);
	}

	/**
	 *
	 */
	public function testWatermarkOpacityNormalization(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('watermark')->with(__FILE__, Image::WATERMARK_BOTTOM_RIGHT, 100)->once();

		$image = new Image(__FILE__, $processor);

		$image->watermark(__FILE__, Image::WATERMARK_BOTTOM_RIGHT, 300);

		//

		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('watermark')->with(__FILE__, Image::WATERMARK_BOTTOM_RIGHT, 0)->once();

		$image = new Image(__FILE__, $processor);

		$image->watermark(__FILE__, Image::WATERMARK_BOTTOM_RIGHT, -300);
	}

	/**
	 *
	 */
	public function testWatermarkWithNonExistingFile(): void
	{
		$this->expectException(PixlException::class);

		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$image = new Image(__FILE__, $processor);

		$image->watermark('foobar.png');
	}

	/**
	 *
	 */
	public function testBrigtness(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('brightness')->with(50)->once();

		$image = new Image(__FILE__, $processor);

		$image->brightness(50);

		//

		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('brightness')->with(-50)->once();

		$image = new Image(__FILE__, $processor);

		$image->brightness(-50);
	}

	/**
	 *
	 */
	public function testBrigtnessNormalization(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('brightness')->with(100)->once();

		$image = new Image(__FILE__, $processor);

		$image->brightness(200);

		//

		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('brightness')->with(-100)->once();

		$image = new Image(__FILE__, $processor);

		$image->brightness(-200);
	}

	/**
	 *
	 */
	public function testGreyscale(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('greyscale')->once();

		$image = new Image(__FILE__, $processor);

		$image->greyscale();
	}

	/**
	 *
	 */
	public function testSepia(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('sepia')->once();

		$image = new Image(__FILE__, $processor);

		$image->sepia();
	}

	/**
	 *
	 */
	public function testBitonal(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('bitonal')->once();

		$image = new Image(__FILE__, $processor);

		$image->bitonal();
	}

	/**
	 *
	 */
	public function testColorize(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('colorize')->with('#ff0000')->once();

		$image = new Image(__FILE__, $processor);

		$image->colorize('#ff0000');
	}

	/**
	 *
	 */
	public function testPixelate(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('pixelate')->with(10)->once();

		$image = new Image(__FILE__, $processor);

		$image->pixelate();

		//

		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('pixelate')->with(20)->once();

		$image = new Image(__FILE__, $processor);

		$image->pixelate(20);
	}

	/**
	 *
	 */
	public function testNegate(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('negate')->once();

		$image = new Image(__FILE__, $processor);

		$image->negate();
	}

	/**
	 *
	 */
	public function testBorder(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('border')->with('#000', 5)->once();

		$image = new Image(__FILE__, $processor);

		$image->border();
	}

	/**
	 *
	 */
	public function testBorderWithCustomColor(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('border')->with('#ff0000', 5)->once();

		$image = new Image(__FILE__, $processor);

		$image->border('#ff0000');
	}

	/**
	 *
	 */
	public function testBorderWithCustomColorAndThickness(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('border')->with('#ff0000', 10)->once();

		$image = new Image(__FILE__, $processor);

		$image->border('#ff0000', 10);
	}

	/**
	 *
	 */
	public function testGetImageBlob(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('getImageBlob')->with(null, 95)->once();

		$image = new Image(__FILE__, $processor);

		$image->getImageBlob();
	}

	/**
	 *
	 */
	public function testGetImageBlobWithType(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('getImageBlob')->with('png', 95)->once();

		$image = new Image(__FILE__, $processor);

		$image->getImageBlob('png');
	}

	/**
	 *
	 */
	public function testGetImageBlobWithTypeAndQuality(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('getImageBlob')->with('png', 50)->once();

		$image = new Image(__FILE__, $processor);

		$image->getImageBlob('png', 50);
	}

	/**
	 *
	 */
	public function testGetImageBlobNormalizeQuality(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('getImageBlob')->with(null, 100)->once();

		$image = new Image(__FILE__, $processor);

		$image->getImageBlob(null, 200);

		//

		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('getImageBlob')->with(null, 1)->once();

		$image = new Image(__FILE__, $processor);

		$image->getImageBlob(null, -10);
	}

	/**
	 *
	 */
	public function testSharpen(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('sharpen')->once();

		$image = new Image(__FILE__, $processor);

		$image->sharpen();
	}

	/**
	 *
	 */
	public function testSnapshot(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('snapshot')->once();

		$image = new Image(__FILE__, $processor);

		$image->snapshot();
	}

	/**
	 *
	 */
	public function testRestore(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('restore')->once();

		$image = new Image(__FILE__, $processor);

		$image->restore();
	}

	/**
	 *
	 */
	public function testGetWidth(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('getWidth')->once()->andReturn(10);

		$image = new Image(__FILE__, $processor);

		$this->assertSame(10, $image->getWidth());
	}

	/**
	 *
	 */
	public function testGetHeight(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('getHeight')->once()->andReturn(10);

		$image = new Image(__FILE__, $processor);

		$this->assertSame(10, $image->getHeight());
	}

	/**
	 *
	 */
	public function testGetDimensions(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('getDimensions')->once()->andReturn(['width' => 10, 'height' => 10]);

		$image = new Image(__FILE__, $processor);

		$this->assertSame(['width' => 10, 'height' => 10], $image->getDimensions());
	}
}
