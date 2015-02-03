<?php

namespace mako\tests\unit\pixl;

use \Mockery as m;

use mako\pixl\Image;

/**
 * @group unit
 */

class ImageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getProcessor()
	{
		return m::mock('mako\pixl\processors\ProcessorInterface');
	}

	/**
	 *
	 */

	public function testConstructor()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$image = new Image(__FILE__, $processor);
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testConstructorWithNonExistingFile()
	{
		$processor = $this->getProcessor();

		$image = new Image('foobar.png', $processor);
	}

	/**
	 *
	 */

	public function testRotate()
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

	public function testResizeToPercent()
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

	public function testResizeToPixelSize()
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

	public function testResizeToPixelWithRestriction()
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

	public function testCrop()
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

	public function testFlipHorizontal()
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

	public function testFlipVertical()
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

	public function testWatermark()
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

	public function testWatermarkPosition()
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

	public function testWatermarkOpacity()
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

	public function testWatermarkOpacityNormalization()
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
	 * @expectedException \RuntimeException
	 */

	public function testWatermarkWithNonExistingFile()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$image = new Image(__FILE__, $processor);

		$image->watermark('foobar.png');
	}

	/**
	 *
	 */

	public function testBrigtness()
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

	public function testBrigtnessNormalization()
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

	public function testGreyscale()
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

	public function testSepia()
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

	public function testColorize()
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

	public function testPixelate()
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

	public function testNegate()
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

	public function testBorder()
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

	public function testBorderWithCustomColor()
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

	public function testBorderWithCustomColorAndThickness()
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

	public function testGetImageBlob()
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

	public function testGetImageBlobWithType()
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

	public function testGetImageBlobWithTypeAndQuality()
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

	public function testGetImageBlobNormalizeQuality()
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

	public function testSharpen()
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

	public function testSnapshot()
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

	public function testRestore()
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

	public function testGetWidth()
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

	public function testGetHeight()
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

	public function testGetDimensions()
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('open')->with(__FILE__)->once();

		$processor->shouldReceive('getDimensions')->once()->andReturn(['width' => 10, 'height' => 10]);

		$image = new Image(__FILE__, $processor);

		$this->assertSame(['width' => 10, 'height' => 10], $image->getDimensions());
	}
}