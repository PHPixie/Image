<?php

namespace PHPixie\Image\Drivers\Type\Imagick;

/**
 * Imagick image resource.
 */
class Resource extends \PHPixie\Image\Drivers\Driver\Resource
{
	/**
	 * Imagick image object
	 * @var \Imagick
	 */
	protected $image;

	/**
	 * Image class to initialize
	 * @var string
	 */
	protected $imageClass = '\Imagick';

	/**
	 * Draw class to initialize
	 * @var string
	 */
	protected $drawClass  = '\ImagickDraw';

	/**
	 * Composition mode
	 * @var int
	 */
	protected $compositionMode;

    public function __construct($image, $width, $height)
    {
        $this->image = $image;
        $this->updateSize($width, $height);
        if (class_exists('\Imagick', false)) {
            $this->compositionMode = \Imagick::COMPOSITE_OVER;
        }
    }

	/**
	 * Imagick image object
	 * @return \Imagick
	 */
    public function image()
    {
        return $this->image;
    }
    
	/**
	 * Updates size properties
	 *
	 * @param int $width  Image width
	 * @param int $height Image height
	 */
	protected function updateSize($width, $height) {
		$this->width = $width;
		$this->height = $height;
	}

	protected function getColor($color, $opacity) {
		$color = str_pad(dechex($color), 6, '0', \STR_PAD_LEFT);
		$opacity = str_pad(dechex(floor(255 * $opacity)), 2, '0', \STR_PAD_LEFT);
		return '#'.$color.$opacity;
	}

	public function getPixel($x, $y) {
		$pixel = $this->image->getImagePixelColor($x, $y);
		$color = $pixel->getColor();
		$normalizedColor = $pixel->getColor(true);
		$color = ($color['r'] << 16) + ($color['g'] << 8) + $color['b'];
        $opacity = $normalizedColor['a'];
        return $this->buildPixel($x, $y, $color, $opacity);
	}
    
	protected function jpgBg() {
		$bg = new $this->imageClass();
		$bg->newImage($this->width, $this->height, $this->getColor(0xffffff, 1));
		$bg->compositeImage($this->image, $this->compositionMode, 0, 0);
		$bg->setImageFormat('jpeg');
		return $bg;
	}

	public function render($format = 'png', $quality = 90, $destroy = true) {
		$image = $this->image;

		switch($format) {
			case 'png':
			case 'gif':
				$image->setImageFormat($format);
				break;
			case 'jpg':
				$image = $this->jpgBg($this->image);
				break;
			default:
				throw new \Exception("Type must be either png, jpg or gif");
		}
		$this->setQuality($quality);
		return (string)$image;
	}

	public function save($file, $format = null, $quality = 90) {
		$image = $this->image;
		if ($format == null)
			$format = $this->getExtension($file);
		switch($format) {
			case 'png':
			case 'gif':
				$image->setImageFormat($format);
				break;
			case 'jpg':
				$image = $this->jpgBg($this->image);
				break;
			default:
				throw new \Exception("Type must be either png, jpg or gif");
		}

		$this->setQuality($quality);
		$image->writeImage($file);
	}

	public function destroy() {
		if($this->image !== null) {
			$this->image->destroy();
			$this->image = null;
		}
	}

	public function crop($width, $height, $x = 0, $y = 0) {
		if ($width > ($maxwidth = $this->width-$x))
			$width = $maxwidth;

		if ($height > ($maxheight = $this->height-$y))
			$height = $maxheight;

		$this->image->cropImage($width, $height, $x, $y);
		$this->updateSize($width, $height);

		return $this;
	}

	public function scale($scale){
		$width = ceil($this->width*$scale);
		$height = ceil($this->height*$scale);

		$this->image->scaleImage($width, $height, true);
		$this->updateSize($width, $height);
		return $this;
	}

	public function rotate($angle, $bgColor = 0xffffff, $bgOpacity = 0) {
		$this->image->rotateImage($this->getColor($bgColor, $bgOpacity), -$angle);
		$this->updateSize($this->image->getImageWidth(), $this->image->getImageHeight());
		return $this;
	}

	public function flip($flipX = false, $flipY = false) {
		if ($flipX)
			$this->image->flopImage();
		if ($flipY)
			$this->image->flipImage();

		return $this;
	}

	public function overlay($layer, $x = 0, $y = 0) {
		$layerCs = $layer->image->getImageColorspace();
		$layer->image->setImageColorspace($this->image->getImageColorspace() );
		$this->image->compositeImage($layer->image(), $this->compositionMode, $x, $y);
		$layer->image->setImageColorspace($layerCs);

		return $this;
	}

	protected function drawText($text, $size, $fontFile, $x, $y, $color, $opacity, $angle) {

		$draw = new $this->drawClass();
		$draw->setFont($fontFile);
		$draw->setFontSize($size);
		$draw->setFillColor($this->getColor($color, $opacity));
		$this->image->annotateImage($draw, $x, $y, -$angle, $text);
		return $this;
	}

	public function textMetrics($text, $size, $fontFile) {
		$draw = new $this->drawClass();
		$draw->setFont($fontFile);
		$draw->setFontSize($size);
		$metrics = $this->image->queryFontMetrics($draw, $text, true);
		return array(
			'ascender'  => floor($metrics['boundingBox']['y2']),
			'descender' => floor(-$metrics['boundingBox']['y1']),
			'width'     => floor($metrics['textWidth']),
			'height'    => floor($metrics['boundingBox']['y2'] - $metrics['boundingBox']['y1']),
		);
	}
    
	/**
	 * Set Compression Quality
     *
	 * @params integer $quality Compression quality
	 *
     * @return void
	 */
    protected function setQuality($quality) {
        $this->image->setImageCompressionQuality($quality);
    }

}
