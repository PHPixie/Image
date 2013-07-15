<?php

abstract class Driver extends PHPUnit_Framework_TestCase{

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
		$this->image = $this->getDriver();
	}
	
	public function testRead() {
		$img = $this->image->read($this->test_png);
		$this->assertClass($img);
		$this->assertSize(278, 300);
		$this->assertPixel(228, 64, 0x98fcfc, 0.5);
		$this->assertPixel(160, 52, 0xf76fae, 1);
	}
	
	public function testCreate() {
		$img = $this->image-> create(200, 300);
		$this->assertClass($img);
		$this->assertSize(200, 300);
		$this->assertPixel(150, 64, 0xffffff, 0);
	}
	
	public function testCreateFill() {
		$img = $this->image->create(200, 300, 0xff88ee, 0.7);
		$this->assertClass($img);
		$this->assertSize(200, 300);
		$this->assertPixel(150, 64, 0xff88ee, 0.7);
	}
	
	public function testCrop() {
		$this->image->read($this->test_png);
		$img = $this->image-> crop(400, 40, 163, 62);
		$this->assertClass($img);
		$this->assertSize(115, 40);
		$this->assertPixel(1, 1, 0xf66bab, 1);
		$this->assertPixel(50,6, 0x98fcfc, 0.5);
	}

	public function testScale() {
		$this->image->read($this->test_png);
		$img = $this->image->scale(0.5);
		$this->assertClass($img);
		$this->assertSize(139, 150);
		$this->assertPixel(114, 32, 0x98fcfc, 0.5);
		$this->assertPixel(80,26, 0xf76fae, 1);
		
	}
	
	public function testResizeFit() {
		$this->image->read($this->test_png);
		$img = $this->image->resize(200, 150);
		$this->assertClass($img);
		$this->assertSize(139, 150);
		$this->assertPixel(124, 50, 0x93f5f5, 0.5);
	}
	
	public function testResizeWidth() {
		$this->image->read($this->test_png);
		$img = $this->image->resize(139);
		$this->assertClass($img);
		$this->assertSize(139, 150);
		$this->assertPixel(124, 50, 0x93f5f5, 0.5);
	}
	
	public function testResizeHeight() {
		$this->image->read($this->test_png);
		$img = $this->image->resize(null, 150);
		$this->assertClass($img);
		$this->assertSize(139, 150);
		$this->assertPixel(124, 50, 0x93f5f5, 0.5);
	}
	
	public function testResizeFill() {
		$this->image->read($this->test_png);
		$img = $this->image->resize(139, 139, false);
		$this->assertClass($img);
		$this->assertSize(139, 150);
		$this->assertPixel(124, 50, 0x93f5f5, 0.5);
	}
	
	public function testFill() {
		$this->image->read($this->test_png);
		$img = $this->image->fill(139, 139, false);
		$this->assertClass($img);
		$this->assertSize(139, 139);
		$this->assertPixel(124, 50, 0x93f5f5, 0.5);
	}
	
	public $rotated_size;
	
	public function testRotate() {
		$this->image->read($this->test_png);
		$img = $this->image-> rotate(45);
		$this->assertClass($img);
		$this->assertSize($this->rotated_size, $this->rotated_size);
		$this->assertPixel(148, 120, 0xf76fae, 1);
		$this->assertPixel(244, 128, 0x90e8ee, 0.5);
	}
	
	public function testFlip() {
		$this->image->read($this->test_png);
		$img = $this->image->flip(true, true);
		$this->assertClass($img);
		$this->assertSize(278, 300);
		$this->assertPixel(30, 190, 0x93f5f5, 0.5);
		$this->assertPixel(170, 190, 0xf2c3a8, 1);
	}

	public function test_Overlay() {
		$this->image-> read($this->test_png);
		
		$image2 = $this->getDriver();
		$image2->read($this->test_png);
		$image2->flip(true);
		$img = $this->image-> overlay($image2, 10, 10);
		$this->assertClass($img);
		$this->assertSize(278, 300);
		$this->assertPixel(38, 92, 0xc8ee9e, 1);
		$this->assertPixel(261, 84, 0x93f5f5, 0.5);
	}

	
	public function test_OverlayJpg() {
		$this->image-> read($this->test_jpg);
		
		$image2 = $this->getDriver();
		$image2->read($this->test_png);
		$image2->flip(true);
		$img = $this->image->overlay($image2, 10, 10);
		$this->assertClass($img);
		$this->assertSize(278, 300);
		$this->assertPixel(38, 92, 0xc8f09b, 1);
		$this->assertPixel(261, 84, 0xcafafc, 1);
	}
	
	public function test_Text_size() {
		$img = $this->image->create(300, 300);
		$this->assertClass($img);
		$size = $this->image-> text_size("hello\nworld", 40, $this->test_font);
		$this->assertEquals(true, 6 > abs(101-$size['width']));
		$this->assertEquals(true, 6 > abs(75-$size['height']));
	}
	
	public function test_Text() {
		$this->image->read($this->test_png);
		$img = $this->image-> text("hello\nworld", 40, $this->test_font, 10, 54, 0xff0000, 0.5);
		$this->assertClass($img);
		$this->assertPixel(101, 73, 0xfc6f67, 1);
		$this->assertPixel(61, 85, 0xff0000, 0.5);
		$img = $this->image-> text("hello\ntest\nme", 40, $this->test_font, 10, 54, 0xff0000, 0.5, 0, null, 3);
		$this->assertClass($img);
		$this->assertPixel(26, 167, 0xff0000, 0.5);
		$this->assertPixel(32, 284, 0xfc6d64, 1);
	}

	public function test_TextAngle() {
		$this->image->read($this->test_png);
		$img = $this->image-> text("hello\nworld\nhi", 40, $this->test_font, 40, 70, 0xff0000, 0.5, 45);
		$this->assertClass($img);
		$this->assertPixel(61, 88, 0xff0000, 0.5);
		$this->assertPixel(97, 54, 0xfb695f, 1);
	}
	
	public function test_TextWrap() {
		$this->image->read($this->test_png);
		$img = $this->image->text("Tinkerbell is a magical fairy that enjoys picking flowers and singing songs in the forest.\nShe also has a friend named Trixie", 20, $this->test_font, 27, 70, 0xff0000, 0.5, 0, 258,1.4);
		$this->assertClass($img);
		$this->assertPixel(40, 175, 0xff0000, 0.5);
		$this->assertPixel(104, 120, 0xfa796d, 1);
	}
	
	
	public function test_SavePng() {
		$this->image->read($this->test_png);
		$tmp = tempnam(sys_get_temp_dir(), 'test.png');
		$this->image->save($tmp, 'png');
		$this->image->read($tmp);
		$this->assertSize(278, 300);
		$this->assertPixel(228, 64, 0x98fcfc, 0.5);
		$this->assertPixel(160, 52, 0xf76fae, 1);
		unlink($tmp);
	}
	
	public function test_SaveJpg() {
		$this->image->read($this->test_png);
		$tmp = tempnam(sys_get_temp_dir(), 'test.jpg');
		$this->image->save($tmp, 'jpeg');
		$this->image->read($tmp);
		$this->assertSize(278, 300);
		$this->assertPixel(228, 64, 0xcbfdfa, 1);
		$this->assertPixel(160, 52, 0xf76fb1, 1);
		unlink($tmp);
	}
	
	public function test_SaveGuess() {
		$this->image->read($this->test_png);
		$tmp = sys_get_temp_dir().'/test.jpg';
		$this->image->save($tmp);
		$info = getimagesize($tmp);
		$this->assertEquals('image/jpeg', $info['mime']);
		unlink($tmp);
		$tmp = sys_get_temp_dir().'/test.png';
		$this->image->save($tmp);
		$info = getimagesize($tmp);
		$this->assertEquals('image/png', $info['mime']);
		unlink($tmp);
	}
	
	protected function tearDown(){
		$this->image = null;
	}
	
	protected function save() {
		$this->image->save($this->files_dir.'pixie1.png', 'png');
	}
	
	protected function assertClass($img) {
		$this->assertEquals(true, is_subclass_of($img, '\PHPixie\Image\Driver'));
	}
	
	protected function assertSize($width, $height) {
		$this->assertEquals($width, $this->image->width);
		$this->assertEquals($height, $this->image->height);
	}
	
	protected function assertPixel($x, $y, $color, $opacity) {
		$pixel = $this->image-> get_pixel($x, $y);
		//print_r($pixel);
		//echo($color);
		$tcolor = $pixel['color'];
		$dr = abs((($tcolor >> 16) & 0xFF) - (($color >> 16) & 0xFF));
		$dg = abs((($tcolor >> 8) & 0xFF) - (($color >> 8) & 0xFF));
		$db = abs(($tcolor & 0xFF) - ($color & 0xFF));
		
		$this->assertEquals(true, 6 > max($dr, $db, $dg));
		$this->assertEquals($opacity, round($pixel['opacity'],1));
	}
}