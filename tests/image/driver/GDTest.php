<?php

class GD_Image_Test extends PHPUnit_Framework_TestCase{

	protected $image;
	protected $files_dir;
	protected $test_png;
	protected $test_jpg;
	protected $test_font;
	
	protected function setUp() {
		$this->files_dir = dirname(dirname(__DIR__)).'/files/';
		$this->test_png = $this->files_dir.'pixie.png';
		$this->test_jpg = $this->files_dir.'pixie.jpg';
		$this->test_font = $this->files_dir.'Sofia-Regular.ttf';
		$this->image = new \PHPixie\Image\GD();
	}

	public function testRead() {
		$this->image->read($this->test_png);
		$this->assertSize(278, 300);
		//$this->save();die;
	}
	
	public function testCrop() {
		$this->image->read($this->test_png);
		$this->image->crop(400, 40, 163, 62);
		$this->assertSize(115, 40);
		$this->assertPixel(1, 1, 0xf66bab, 1);
		$this->assertPixel(50,6, 0x98fcfc, 0.5);
	}

	public function testScale() {
		$this->image->read($this->test_png);
		$this->image->scale(0.5);
		//$this->image-> save($this->img_dir.'pixie1.png', 'png');
		$this->assertSize(139, 150);
		$this->assertPixel(114, 32, 0x98fcfc, 0.5);
		$this->assertPixel(80,26, 0xf76fae, 1);
		
	}
	
	public function testRotate() {
		$this->image->read($this->test_png);
		$this->image->rotate(45);
		//$this->save();
		$this->assertSize(409, 409);
		$this->assertPixel(148, 120, 0xf76fae, 1);
		$this->assertPixel(244, 128, 0x90e8ee, 0.5);
	}
	
	public function testFlip() {
		$this->image->read($this->test_png);
		$this->image->flip(true, true);
		//$this->save();
		$this->assertSize(278, 300);
		$this->assertPixel(30, 190, 0x93f5f5, 0.5);
		$this->assertPixel(170, 190, 0xf2c3a8, 1);
	}

	public function test_Overlay() {
		$this->image-> read($this->test_png);
		
		$image2 = new \PHPixie\Image\GD();
		$image2->read($this->test_png);
		$image2->flip(true);
		$this->image->overlay($image2, 10, 10);
		$this->assertSize(278, 300);
		$this->assertPixel(38, 92, 0xc8ee9e, 1);
		$this->assertPixel(261, 84, 0x93f5f5, 0.5);
	}

	
	public function test_OverlayJpg() {
		$this->image-> read($this->test_jpg);
		
		$image2 = new \PHPixie\Image\GD();
		$image2->read($this->test_png);
		$image2->flip(true);
		$this->image-> overlay($image2, 10, 10);
		$this->save();
		$this->assertSize(278, 300);
		$this->assertPixel(38, 92, 0xc8f09b, 1);
		$this->assertPixel(261, 84, 0xcafafc, 1);
	}
	
	public function testText_size() {
		$this->image->blank(300, 300);
		$size = $this->image->text_size("hello\nworld", 30, $this->test_font);
		$this->assertEquals(101, $size['width']);
		$this->assertEquals(102, $size['height']);
	}
	
	public function test_Text() {
		$this->image->read($this->test_png);
		$size = $this->image-> text("hello\nworld", 30, $this->test_font, 10, 10, 0xff0000, 0.5);
		$this->assertPixel(101, 71, 0xfc6e65, 1);
		$this->assertPixel(62, 71, 0xff0000, 0.5);
		$size = $this->image-> text("hello\ntest\nme", 30, $this->test_font, 10, 10, 0xff0000, 0.5, 0, null, 3);
		//$this->save();
		$this->assertPixel(11, 202, 0xff0000, 0.5);
		$this->assertPixel(43, 203, 0xfc6d64, 1);
	}

	public function test_TextAngle() {
		$this->image->read($this->test_png);
		$size = $this->image->text("hello\nworld\nhi", 30, $this->test_font, 40, 70, 0xff0000, 0.5, 45);
		//$this->save();
		$this->assertPixel(56, 83, 0xff0000, 0.5);
		$this->assertPixel(109, 136, 0xfb695f, 1);
	}
	
	public function test_TextWrap() {
		$this->image->read($this->test_png);
		$size = $this->image->text("Tinkerbell is a magical fairy that enjoys picking flowers and singing songs in the forest.\nShe also has a friend named Trixie", 20, $this->test_font, 10, 70, 0xff0000, 0.5, 0, 258,1.4);
		//$this->save();
		$this->assertPixel(246, 170, 0xff0000, 0.5);
		$this->assertPixel(105, 254, 0xf99a8b, 1);
	}
	
	
	
	protected function tearDown(){
		$this->image = null;
	}
	
	protected function save() {
		$this->image->save($this->files_dir.'pixie1.png', 'png');
	}
	
	protected function assertSize($width, $height) {
		$this->assertEquals($width, $this->image->width);
		$this->assertEquals($height, $this->image->height);
	}
	
	protected function assertPixel($x, $y, $color, $opacity) {
		$pixel = $this->image->get_pixel($x, $y);
		$this->assertEquals($color, $pixel['color']);
		$this->assertEquals($opacity, round($pixel['opacity'],1));
	}
}