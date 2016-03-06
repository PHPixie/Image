<?php

namespace PHPixie\Image\Drivers\Type;

/**
 * Imagick Image driver.
 */
class Imagick implements \PHPixie\Image\Drivers\Driver
{
	/**
	 * Image class to initialize
	 * @var string
	 */
	protected $imageClass = '\Imagick';
    
    public function create($width, $height, $color = 0xffffff, $opacity = 0) {
		$image = new $this->imageClass();
		$image->newImage($width, $height, $this->get_color($color, $opacity));
		return $this->buildResource($image, $width, $height);
	}

	public function read($file) {
		$image = new $this->imageClass($file);
		return $this->buildResource($image, $image->getImageWidth(), $image->getImageHeight());
	}

	public function load($bytes) {
		$image = new $this->imageClass();
		$image->readImageBlob($bytes);
        return $this->buildResource($image, $image->getImageWidth(), $image->getImageHeight());
	}
    
    /**
     * @param \Imagick $image
     * @param int $width
     * @param int $height
     * @return Imagick\Resource
     */
    protected function buildResource($image, $width, $height, $format)
    {
        return new Imagick\Resource($image, $width, $height, $format);
    }
}
