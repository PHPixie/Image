<?php

namespace PHPixie\Image;

class GD extends Image{

	protected $image;
	
	protected function set_image($image, $width, $height) {
		if($this->image)
			imagedestroy($this->image);
		
		$this->image = $image;
		$this->width = $width;
		$this->height = $height;
	}
	
	protected function create($width, $height, $force_transparency = false) {
		$image = imagecreatetruecolor($width, $height);
		$this->enable_alpha($image);
		if ($force_transparency)
			imagefilledrectangle($image, 0, 0, $width, $height, $this->get_color(0xffffff, 0));
		return $image;
	}
	
	protected function enable_alpha($image) {
		imagealphablending($image, false);
		imagesavealpha($image, true);
	}
	
	protected function get_color($color, $opacity) {
		$r = ($color >> 16) & 0xFF;
		$g = ($color >> 8) & 0xFF;
		$b = $color & 0xFF;
		return imagecolorallocatealpha($this->image, $r, $g, $b, 127*(1-$opacity));
	}
	
	public function get_pixel($x, $y) {
		$pixel = imagecolorat($this->image, $x, $y);
		echo($pixel);
		$rgba = imagecolorsforindex($this->image, $pixel);
		print_r(array(
			'color' => dechex(($rgba['red'] << 16) + ($rgba['green'] << 8) + $rgba['blue']),
			'opacity' => 1 - $rgba['alpha'] / 127
		));
		return array(
			'color' => ($rgba['red'] << 16) + ($rgba['green'] << 8) + $rgba['blue'],
			'opacity' => 1 - $rgba['alpha'] / 127
		);
	}
	
	public function read($file) {
		$size = getimagesize($file);
		
		if (!$size)
			throw new \Exception("File is not a valid image");
			
		switch($size["mime"]){
			case "image/jpeg":
				$image = imagecreatefromjpeg($file);
				break;
			case "image/gif":
				$image = imagecreatefromgif($file);
				break;
			case "image/png":
				$image = imagecreatefrompng($file);
				break;
			default: 
				throw new \Exception("File is not a valid image");
				break;
		}
		
		$this->enable_alpha($image);
		$this->set_image($image, $size[0], $size[1]);
		
	}
	
	public function blank($width, $height, $color = 0xffffff, $opacity = 0) {
		$image = $this->create($width, $height);
		$this->set_image($image, $width, $height);
		imagefilledrectangle($this->image, 0, 0, $width, $height, $this->get_color($color, $opacity));
	}
	
	public function render($format = 'png') {
		switch($format) {
			case 'png':
				header('Content-Type: image/png');
				imagepng($this->image);
				break;
			case 'png':
				header('Content-Type: image/jpeg');
				imagejpeg($this->image);
				break;
			case 'gif':
				header('Content-Type: image/gif');
				imagegif($this->image);
				break;
			default:
				throw new \Exception("Type must be either png, jpg or gif");
		}
		return $this;
	}
	
	public function save($file, $format) {
		switch($format) {
			case 'png':
				imagepng($this->image, $file);
				break;
			case 'png':
				imagejpeg($this->image, $file);
				break;
			case 'gif':
				imagegif($this->image, $file);
				break;
			default:
				throw new \Exception("Type must be either png, jpg or gif");
		}
		return $this;
	}
	
	public function destroy() {
		imagedestroy($this->image);
		$this->image = null;
	}
	
	public function crop($width, $height, $x = 0, $y = 0) {
		if ($width > ($maxwidth = $this->width-$x))
			$width = $maxwidth;
		
		if ($height > ($maxheight = $this->height-$y))
			$height = $maxheight;
			
		$cropped = $this->create($width, $height);
		imagecopy($cropped, $this->image, 0, 0, $x, $y, $width, $height);
		$this->set_image($cropped, $width, $height);
		return $this;
	}
	
	public function scale($scale){
		$width = floor($this->width*$scale);
		$height = floor($this->height*$scale);
		
		$resized = $this->create($width, $height);
		imagecopyresampled($resized, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		$this->set_image($resized, $width, $height);
	}
	
	public function rotate($angle, $bg_color = 0xffffff, $bg_opacity = 0) {
		$rotated = imagerotate($this->image, $angle, $this->get_color($bg_color, $bg_opacity));
		$this->enable_alpha($rotated);
		$this->set_image($rotated, imagesx($rotated), imagesy($rotated));
	}
	
	public function flip($flip_x = false, $flip_y = false) {
		if (!$flip_x && !$flip_y)
			return $this;
			
		$x = $flip_x ? $this->width-1 : 0;;
		$width = ($flip_x?-1:1) * $this->width;
		
		$y = $flip_y ? $this->height-1 : 0;;
		$height = ($flip_y?-1:1) * $this->height;
		
		$flipped = $this->create($this->width, $this->height);
		imagecopyresampled($flipped, $this->image, 0, 0, $x, $y, $this->width, $this->height, $width, $height);
		$this->set_image($flipped, $this->width, $this->height);
		return $this;
	}
	
	public function overlay($layer, $x = 0, $y = 0) {
		imagealphablending($this->image, true);
		imagecopy($this->image, $layer->image, $x, $y, 0, 0, $layer->width, $layer->height);
		imagealphablending($canvas, false);
	}
	
	protected function draw_text($text, $size, $font_file, $x, $y, $color, $opacity, $angle) {
		$box = $this->text_size($text, $size, $font_file);
		
		$rad = deg2rad($angle);
		$offset = 0 - $box['y1'];
		$offset_x = sin($rad) * $offset;
		$offset_y = cos($rad) * $offset;
		
		$color = $this->get_color($color, $opacity);
		imagealphablending($this->image, true);
		imagettftext($this->image, $size, $angle, $x + $offset_x, $y + $offset_y, $color, $font_file, $text);
		imagealphablending($this->image, false);
		return $box;
	}
	
	public function text_metrics($text, $size, $font_file) {
		$box = imagettfbbox($size, 0, $font_file, $text);
		return array(
			'x1'     => $box[6],
			'y1'     => $box[7],
			'x2'     => $box[2],
			'y2'     => $box[3],
			'width'  => $box[2] - $box[6],
			'height' => $box[3] - $box[7]
		);
	}
}