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

	/**
	 * Set Compression Quality function
	 * @var string
	 */
	protected $set_compression_quality = 'setCompressionQuality';
}