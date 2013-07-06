<?php

namespace PHPixie\Image;

class GD {

	protected $image;
	
	public function read($file) {
		$size = getimagesize($file);
		
		if (!$size)
			throw new \Exception("File is not a valid image")
			
		switch($size["mime"]){
			case "image/jpeg":
				$this->image = imagecreatefromjpeg($file);
				break;
			case "image/gif":
				$this->image = imagecreatefromgif($file);
				break;
			case "image/png":
				$this->image = imagecreatefrompng($file);
				break;
			default: 
				throw new \Exception("File is not a valid image")
				break;
		}
		
		$this->width = $size['width'];
		$this->height = $size['height'];
		
	}
	
	public function crop($width, $height, $x=0, $y=0);
		$cropped = imagecrop($this->image, array(
			'x' => $x,
			'y' => $y,
			'width' => $width,
			'height' => $height
		));
		imagedestroy($this->image);
		$this->image = $cropped;
	}
	
	public function scale($scale){
		$width = floor($image->width*$scale);
		$height = floor($image->width*$scale);
		
		$resized = imagecreatetruecolor($width, $height);
		imagecopyresized($resized, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		imagedestroy($this->image);
		$this->width = $width;
		$this->height = $height;
		$this->image = $resized;
	}
	
	public function overlay($layer, $x=0, $y=0) {
		$width = max($this->width, $x + $layer->width);
		$height = max($this->height, $y + $layer->height);
		if ($width > $this->width || $height > $this->height) {
			$canvas = imagecreatetruecolor($width, $height);
			imagecopy($canvas, $this->image, 0, 0, 0, 0, $this->width, $this->height);
			imagedestroy($this->image);
			$this->width = $width;
			$this->height = $height;
			$this->image = $canvas;
		}else {
			$canvas = $this->image;
		}
	}
}