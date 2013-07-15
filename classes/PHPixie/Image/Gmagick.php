<?php

namespace PHPixie\Image;

class Gmagick extends Imagick{

	protected $image_class    = '\Gmagick';
	protected $draw_class     = '\GmagickDraw';
	protected $pixel_class    = '\GmagickPixel';
	protected $composite_mode =  \Gmagick::COMPOSITE_OVER;
}