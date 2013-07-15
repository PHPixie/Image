<?php

namespace PHPixie\Image;

class Imagick extends Driver{

	public $image;
	
	public function create($width, $height, $color = 0xffffff, $opacity = 0) {
		$this->image = new \Imagick();
		$this->image->newImage($width, $height, $this->get_color($color, $opacity));
		$this->update_size($width, $height);
		return $this;
	}
	
	public function read($file) {
		$this->image = new \Imagick($file);
		$this->update_size($this->image->getImageWidth(), $this->image->getImageHeight());
		return $this;
	}
	
	protected function update_size($width, $height) {
		$this->width = $width;
		$this->height = $height;
	}
	
	protected function create_imagick($width, $height) {
		$image = new \Imagick();
		imagealphablending($image, false);
		return $image;
	}
	
	protected function get_color($color, $opacity) {
		$color = str_pad(dechex($color), 6, '0', \STR_PAD_LEFT);
		$opacity = str_pad(dechex(floor(255*$opacity)), 2, '0', \STR_PAD_LEFT);
		return new \ImagickPixel('#'.$color.$opacity);
	}
	
	public function get_pixel($x, $y) {
		$pixel = $this->image-> getImagePixelColor($x, $y);
		$color = $pixel->getColor();
		$normalized_color = $pixel->getColor(true);
		return array(
			'color' => ($color['r'] << 16) + ($color['g'] << 8) + $color['b'],
			'opacity' => $normalized_color['a']
		);
	}
	
	protected function jpg_bg() {
		$bg = $this->create_gd($this->width, $this->height);
		imagefilledrectangle($bg, 0, 0, $this->width, $this->height, $this->get_color(0xffffff, 1));
		imagealphablending($bg, true);
		imagecopy($bg, $this->image, 0, 0, 0, 0, $this->width, $this->height);
		imagealphablending($bg, false);
		return $bg;
	}
	
	public function render($format = 'png', $die = true) {
		switch($format) {
			case 'png':
				header('Content-Type: image/png');
				$this->image->setFormat('png');
				break;
			case 'jpg':
			case 'jpeg':
				header('Content-Type: image/jpeg');
				$this->image->setFormat('jpeg');
				break;
			case 'gif':
				header('Content-Type: image/gif');
				$this->image->setFormat('gif');
				break;
			default:
				throw new \Exception("Type must be either png, jpg or gif");
		}
		
		echo $this->image;
		if($die){
			die;
		}
	}
	
	public function save($file, $format) {
		switch($format) {
			case 'png':
				$this->image->setFormat('png');
				break;
			case 'jpg':
			case 'jpeg':
				$this->image->setFormat('jpeg');
				break;
			case 'gif':
				$this->image->setFormat('gif');
				break;
			default:
				throw new \Exception("Type must be either png, jpg or gif");
		}
		
		$this->image->writeImage($file);
		
		return $this;
	}
	
	public function destroy() {
		$this->image->destroy();
	}
	
	public function crop($width, $height, $x = 0, $y = 0) {
		if ($width > ($maxwidth = $this->width-$x))
			$width = $maxwidth;
		
		if ($height > ($maxheight = $this->height-$y))
			$height = $maxheight;
			
		$this->image->cropImage($width, $height, $x, $y);
		$this->update_size($width, $height);
		
		return $this;
	}
	
	public function scale($scale){
		$width = floor($this->width*$scale);
		$height = floor($this->height*$scale);
		
		$this->image->scaleImage($width, $height, true);
		$this->update_size($width, $height);
		return $this;
	}
	
	public function rotate($angle, $bg_color = 0xffffff, $bg_opacity = 0) {
		$this->image->rotateImage($this->get_color($bg_color, $bg_opacity), -$angle);
		$this->update_size($this->image->getImageWidth(), $this->image->getImageHeight());
		return $this;
	}
	
	public function flip($flip_x = false, $flip_y = false) {
		if ($flip_x)
			$this->image->flopImage();
		if ($flip_y)
			$this->image->flipImage();
			
		return $this;
	}
	
	public function overlay($layer, $x = 0, $y = 0) {
		$layer_cs = $layer->image->getImageColorspace();
		$layer->image->setImageColorspace($this->image->getImageColorspace() ); 
		$this->image->compositeImage($layer->image, \Imagick::COMPOSITE_DEFAULT, $x, $y);
		$layer->image->setImageColorspace($layer_cs);
		
		return $this;
	}
	
	protected function draw_text($text, $size, $font_file, $x, $y, $color, $opacity, $angle) {
		$box = $this->text_size($text, $size, $font_file);
		$rad = deg2rad($angle);
		$offset = $box['ascender'];
		$offset_x = sin($rad) * $offset;
		$offset_y = cos($rad) * $offset;
		

		$draw = new \ImagickDraw();
		$draw->setFont($font_file);
		$draw->setFontSize($size);
		$draw->setFillColor($this->get_color($color, $opacity));

		$this->image->annotateImage($draw, $x + $offset_x, $y + $offset_y, $angle, $text);
		return $box;
	}
	
	public function text_metrics($text, $size, $font_file) {
		$draw = new \ImagickDraw();
		$draw->setFont($font_file);
		$draw->setFontSize($size);
		$metrics = $this->image->queryFontMetrics($draw, $text, true);
		return array(
			'ascender'  => ceil($metrics['ascender']),
			'descender' => ceil($metrics['descender']),
			'width'     => ceil($metrics['textWidth']),
			'height'    => ceil($metrics['textHeight']),
		);
	}
}