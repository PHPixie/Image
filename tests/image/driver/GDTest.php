<?php

class GD_Image_Test extends PHPUnit_Framework_TestCase{

	protected $image;
	protected $img_dir;
	
	protected function setUp() {
		$this->img_dir = dirname(dirname(__DIR__)).'/files/';
		$this->image = new \PHPixie\Image\GD();
	}

	public function testRead() {
		$this->image->read($this->img_dir.'pixie.png');
		$this->assertSize(278, 300);
	}
	
	public function testCrop() {
		$this->image->read($this->img_dir.'pixie.png');
		$this->image->crop(400, 40, 163, 62);
		//$this->image-> save($this->img_dir.'pixie1.png', 'png');
		$this->assertSize(115, 40);
		$this->assertPixel(1, 1, 0xf66bab, 1);
		$this->assertPixel(50,6, 0x98fcfc, 0.5);
	}

	public function testScale() {
		$this->image->read($this->img_dir.'pixie.png');
		$this->image->scale(0.5);
		//$this->image-> save($this->img_dir.'pixie1.png', 'png');
		$this->assertSize(139, 150);
		$this->assertPixel(114, 32, 0x98fcfc, 0.5);
		$this->assertPixel(80,26, 0xf76fae, 1);
		
	}
	
	public function testRotate() {
		$this->image->read($this->img_dir.'pixie.png');
		$this->image->rotate(45);
		//$this->image->save($this->img_dir.'pixie1.png', 'png');
		$this->assertSize(409, 409);
		$this->assertPixel(148, 120, 0xf76fae, 1);
		$this->assertPixel(244, 128, 0x90e8ee, 0.5);
	}
	
	public function testFlip() {
		$this->image->read($this->img_dir.'pixie.png');
		$this->image->flip(true, true);
		//$this->image->save($this->img_dir.'pixie1.png', 'png');
		$this->assertSize(278, 300);
		$this->assertPixel(30, 190, 0x93f5f5, 0.5);
		$this->assertPixel(170, 190, 0xf2c3a8, 1);
	}

	public function testOverlay() {
		$this->image->read($this->img_dir.'pixie.png');
		$this->image->flip(true, true);
		//$this->image->save($this->img_dir.'pixie1.png', 'png');
		$this->assertSize(278, 300);
		$this->assertPixel(30, 190, 0x93f5f5, 0.5);
		$this->assertPixel(170, 190, 0xf2c3a8, 1);
	}
	
	protected function tearDown(){
		$this->image = null;
	}
	
	protected function assertSize($width, $height) {
		$this->assertEquals($width, $this->image->width);
		$this->assertEquals($height, $this->image->height);
	}
	
	protected function assertPixel($x, $y, $color, $opacity) {
		$pixel = $this->image-> get_pixel($x, $y);
		$this->assertEquals($color, $pixel['color']);
		$this->assertEquals($opacity, round($pixel['opacity'],1));
	}
}