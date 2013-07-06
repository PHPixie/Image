<?php

namespace PHPixie\Image;

abstract class Image {


	public $width;
	public $height;
	
	public function crop($width, $height, $x=0, $y=0);
	
	}
	
	public function resize($width = null, $height = null, $fit = true) {
		if ($width && $height) {
			$wscale = $width/$image-> width;
			$hscale = $height/$image-> height;
			$scale = $fit&&$wscale>$hscale?$hscale:$wscale;
		}elseif($width) {
			$scale = $width/$image->width;
		}elseif($height) {
			$scale = $height/$image->height;
		}else {
			throw new \Exception("Either width or height must be set");
		}
		
		$this->scale($scale);
	}
	
}