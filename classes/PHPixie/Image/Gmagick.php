<?php

namespace PHPixie\Image;

/**
 * Gmagick Image driver.
 *
 * @package  Image
 */
class Gmagick extends Imagick{

	/**
	 * Imagick image object
	 * @var \Gmagick
	 */
	public $image;

	/**
	 * Image class to initialize
	 * @var string
	 */
	protected $image_class = '\Gmagick';

	/**
	 * Draw class to initialize
	 * @var string
	 */
	protected $draw_class  = '\GmagickDraw';

	/**
	 * Composition mode
	 * @var int
	 */
	protected $composition_mode =  \Gmagick::COMPOSITE_OVER;

	public function save($file, $format = null, $quality = 90) {
		$image = $this->image;
		if ($format == null)
			$format = $this->get_extension($file);
		switch($format) {
			case 'png':
			case 'gif':
				$image->setImageFormat($format);
				break;
			case 'jpeg':
				$image = $this->jpg_bg($this->image);
				break;
			default:
				throw new \Exception("Type must be either png, jpeg or gif");
		}

		$image->setCompressionQuality($quality);
		$image->writeImage($file);

		if ($format == 'jpeg')
			$image->destroy();

		return $this;
	}

	public function render($format = 'png', $die = true, $quality = 90) {
		$image = $this->image;
		switch($format) {
			case 'png':
			case 'gif':
				header('Content-Type: image/'.$format);
				$image->setImageFormat($format);
				break;
			case 'jpeg':
				header('Content-Type: image/jpeg');
				$image = $this->jpg_bg($this->image);
				break;
			default:
				throw new \Exception("Type must be either png, jpeg or gif");
		}
		$image->setCompressionQuality($quality);
		echo $image;

		if($die){
			die;
		}

		if ($format == 'jpeg')
			$image->destroy();
	}
}