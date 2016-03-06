<?php

namespace PHPixie\Image\Drivers\Type;

/**
 * Gmagick Image driver.
 */
class Gmagick extends Imagick
{
	/**
	 * Image class to initialize
	 * @var string
	 */
	protected $imageClass = '\Gmagick';
    
    /**
     * @param \Gmagick $image
     * @param int $width
     * @param int $height
     * @return Gmagick\Resource
     */
    protected function buildResource($image, $width, $height, $format)
    {
        return new Gmagick\Resource($image, $width, $height, $format);
    }
}