<?php

class Image_Test extends PHPUnit_Framework_TestCase {
	
	protected $image;
	
	public function setUp() {
		$pixie = new \PHPixie\Pixie();
		$pixie->config->set('image.default.driver', 'GD');
		$pixie->config->set('image.imagick.driver', 'Imagick');
		$this->image = new \PHPixie\Image($pixie);
	}
	
	public function testRead(){
		$im = $this->image->read(dirname(__DIR__).'/files/pixie.png');
		$this->assertEquals(true, $im instanceof \PHPixie\Image\GD);
		$this->assertEquals(278, $im->width);
		$this->assertEquals(300, $im->height);
		$this->assertEquals('png', $im->format);
		$pixel = $im->get_pixel(228, 64);
		$this->assertEquals(0x98fcfc, $pixel['color']);
		$this->assertEquals(0.5, round($pixel['opacity'],1));
	}
	
	public function testLoad() {
		$bytes = file_get_contents(dirname(__DIR__).'/files/pixie.png');
		$im = $this->image->load($bytes);
		$this->assertEquals(true, $im instanceof \PHPixie\Image\GD);
		$this->assertEquals(278, $im->width);
		$this->assertEquals(300, $im->height);
		$this->assertEquals('png', $im->format);
		$pixel = $im->get_pixel(228, 64);
		$this->assertEquals(0x98fcfc, $pixel['color']);
		$this->assertEquals(0.5, round($pixel['opacity'],1));
	}
	
	public function testCreate(){
		$im = $this->image->create(300, 200, 0xffffff, 0.5, 'imagick');
		$this->assertEquals(true, $im instanceof \PHPixie\Image\Imagick);
		$this->assertEquals(300, $im->width);
		$this->assertEquals(200, $im->height);
		$this->assertEquals('png', $im->format);
		$pixel = $im->get_pixel(50, 50);
		$this->assertEquals(0xffffff, $pixel['color']);
		$this->assertEquals(0.5, round($pixel['opacity'],1));
	}

}