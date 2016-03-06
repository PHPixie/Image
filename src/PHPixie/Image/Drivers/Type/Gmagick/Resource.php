<?php

namespace PHPixie\Image\Drivers\Type\Imagick;

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

	/**
	 * Composition mode
	 * @var int
	 */
	protected $compositionMode =  \Gmagick::COMPOSITE_OVER;
    
    protected function setQuality($quality) {
        $this->image->setCompressionQuality($quality);
    }
}