<?php

namespace PHPixie\Image\Drivers\Type\Gmagick;

/**
 * Gmagick image resource.
 */
class Resource extends \PHPixie\Image\Drivers\Type\Imagick\Resource
{
	/**
	 * Image class to initialize
	 * @var string
	 */
	protected $imageClass = '\Gmagick';

	/**
	 * Draw class to initialize
	 * @var string
	 */
	protected $drawClass  = '\GmagickDraw';

    public function __construct($image, $width, $height)
    {
        parent::__construct($image, $width, $height);
        if (class_exists('\Gmagick', false)) {
            $this->compositionMode = \Gmagick::COMPOSITE_OVER;
        }
    }

    protected function setQuality($quality) {
        $this->image->setCompressionQuality($quality);
    }
    
	public function getPixel($x, $y) {
		$image = clone $this->image;
        $image->cropImage(1, 1, $x, $y);
        $pixel = $image->getImageHistogram()[0];
        
		$color = $pixel->getColor(true);
		$color = ($color['r'] << 16) + ($color['g'] << 8) + $color['b'];
        return $this->buildPixel($x, $y, $color, null);
	}
    
	public function textMetrics($text, $size, $fontFile) {
		$draw = new $this->drawClass();
		$draw->setFont($fontFile);
		$draw->setFontSize($size);
		$metrics = $this->image->queryFontMetrics($draw, $text);
		return array(
			'ascender'  => floor($metrics['ascender']),
			'descender' => floor(-$metrics['descender']),
			'width'     => floor($metrics['textWidth']),
			'height'    => floor($metrics['textHeight']),
		);
	}
}
