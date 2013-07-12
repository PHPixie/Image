<?php

namespace PHPixie\Image;

abstract class Image {


	public $width;
	public $height;
	
		
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
		return $this;
	}
	
	public function fill($width, $height){
		$this->resize($width, $height, false);
		$x = (int)($this->width - $width) / 2;
		$y = (int)($this->height - $height) / 2;
		$this->crop($width, $height, $x, $y);
	}
	
	public function __destruct() {
		$this->destroy();
	}
	
	protected function wrap_text($text, $size, $font_file, $width) {
		$blocks = explode("\n", $text);
		$lines = array();
		foreach($blocks as $block) {
			$words = explode(' ', $block);
			$line = '';
			$line_width = 0;
			$count = count($words);
			foreach($words as $key => $word) {
				$prefix = $line == ''?'':' ';
				$box = $this->text_size($prefix.$word, $size, $font_file);
				$word_width = $box['width'];
				if ($line == '' || $line_width + $word_width < $width) {
					$line.= $prefix.$word;
					$line_width+= $word_width;
				}else {
					$lines[] = $line;
					$line = $word;
					$box = $this->text_size($word, $size, $font_file);
					$line_width = $box['width'];
				}
			}
			$lines[] = $line;
		}
		return implode("\n", $lines);
	}
	
	protected function baseline_offset($size, $line_spacing) {
		return $size * $line_spacing;
	}
	
	public function text_size($text, $size, $font_file, $line_spacing = 1.25) {
		$lines = explode("\n", $text);
		$box = null;
		$baseline = 0;
		foreach($lines as $line) {
			$line_box = $this->text_metrics($text, $size, $font_file);
			if ($box == null) {
				$box = $line_box;
			}else {
				$box['x1'] = $line_box['x1']<$box['x1'] ? $line_box['x1'] : $box['x1'];
				$box['x2'] = $line_box['x2']>$box['x2'] ? $line_box['x2'] : $box['x2'];
				$box['x2'] = $line_box['x2'] > $box['x2'] ? $line_box['x2'] : $box['x2'];
				$baseline += $this->baseline_offset($size, $line_spacing);
				$box['y2'] = $baseline + $box['y2'];
			}
		}
		return $box;
	}
	
	public function text($text, $size, $font_file, $x, $y, $color = 0x000000, $opacity = 1, $angle = 0, $wrap_width = null, $line_spacing = 1.25) {
		if ($wrap_width != null)
			$text = $this->wrap_text($text, $size, $font_file, $wrap_width);
			
		$lines = explode("\n", $text);
		$offset_x = 0;
		$offset_y = 0;
		foreach($lines as $line){
			$box = $this->draw_text($line, $size, $font_file, $x + $offset_x, $y + $offset_y, $color, $opacity, $angle);
			$offset = $this->baseline_offset($size, $line_spacing);
			$rad = deg2rad($angle);
			$offset_x += sin($rad)*$offset;
			$offset_y += cos($rad)*$offset;
		}
		return $this;
	}
	
}