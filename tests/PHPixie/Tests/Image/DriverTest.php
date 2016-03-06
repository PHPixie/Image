<?php

namespace PHPixie\Tests\Image;

abstract class DriverTest extends \PHPixie\Test\Testcase{

	protected $image;
	protected $filesDir;
	protected $testPng;
	protected $testJpg;
	protected $testFont;
    
    protected $driver;
    protected $rotatedSize;
    protected $dryRun = false;
	
	protected function setUp() {
		$this->filesDir = realpath(__DIR__.'/../../../files/').'/';
		$this->testPng = $this->filesDir.'pixie.png';
		$this->testJpg = $this->filesDir.'pixie.jpg';
		$this->testFont = $this->filesDir.'Sofia-Regular.ttf';
		$this->image = new \PHPixie\Image($this->driver);
	}
	
	public function testRead() {
        
		$img = $this->image->read($this->testPng);
		$this->assertClass($img);
		$this->assertSize($img, 278, 300);
		$this->assertPixel($img, 228, 64, 0x98fcfc, 0.5);
		$this->assertPixel($img, 160, 52, 0xf76fae, 1);
	}
	
	public function testLoad() {
		$bytes = file_get_contents($this->testPng);
		$img = $this->image->load($bytes);
		$this->assertClass($img);
		$this->assertSize($img, 278, 300);
		$this->assertPixel($img, 228, 64, 0x98fcfc, 0.5);
		$this->assertPixel($img, 160, 52, 0xf76fae, 1);
	}
		
	public function testCreate() {
		$img = $this->image->create(200, 300);
		$this->assertClass($img);
		$this->assertSize($img, 200, 300);
		$this->assertPixel($img, 150, 64, 0xffffff, 0);
	}
	
	public function testCreateFill() {
		$img = $this->image->create(200, 300, 0xff88ee, 0.7);
		$this->assertClass($img);
		$this->assertSize($img, 200, 300);
		$this->assertPixel($img, 150, 64, 0xff88ee, 0.7);
	}
	
	public function testCrop() {
		$img = $this->image->read($this->testPng);
		$img->crop(400, 40, 163, 62);
		$this->assertClass($img);
		$this->assertSize($img, 115, 40);
		$this->assertPixel($img, 1, 1, 0xf66bab, 1);
		$this->assertPixel($img, 50,6, 0x98fcfc, 0.5);
	}

	public function testScale() {
		$img = $this->image->read($this->testPng);
		$img->scale(0.5);
		$this->assertClass($img);
		$this->assertSize($img, 139, 150);
		$this->assertPixel($img, 114, 32, 0x98fcfc, 0.5);
		$this->assertPixel($img, 80,26, 0xf76fae, 1);
		
	}
	
	public function testResizeFit() {
		$img = $this->image->read($this->testPng);
		$img->resize(200, 150);
		$this->assertClass($img);
		$this->assertSize($img, 139, 150);
		$this->assertPixel($img, 124, 50, 0x93f5f5, 0.5);
	}
	
	public function testResizeWidth() {
		$img = $this->image->read($this->testPng);
		$img->resize(139);
		$this->assertClass($img);
		$this->assertSize($img, 139, 150);
		$this->assertPixel($img, 124, 50, 0x93f5f5, 0.5);
	}
	
	public function testResizeHeight() {
		$img = $this->image->read($this->testPng);
		$img->resize(null, 150);
		$this->assertClass($img);
		$this->assertSize($img, 139, 150);
		$this->assertPixel($img, 124, 50, 0x93f5f5, 0.5);
	}
	
	public function testResizeFill() {
		$img = $this->image->read($this->testPng);
		$img->resize(139, 139, false);
		$this->assertClass($img);
		$this->assertSize($img, 139, 150);
		$this->assertPixel($img, 124, 50, 0x93f5f5, 0.5);
	}
	
	public function testFill() {
		$img = $this->image->read($this->testPng);
		$img->fill(139, 139, false);
		$this->assertClass($img);
		$this->assertSize($img, 139, 139);
		$this->assertPixel($img, 124, 50, 0x93f5f5, 0.5);
	}
	
	public function testRotate() {
		$img = $this->image->read($this->testPng);
		$img->rotate(45);
		$this->assertClass($img);
        $this->assertTrue(abs($this->rotatedSize - $img->width()) < 2);
        $this->assertTrue(abs($this->rotatedSize - $img->height()) < 2);
		$this->assertPixel($img, 148, 120, 0xf76fae, 1);
		$this->assertPixel($img, 244, 128, 0x90e8ee, 0.5);
	}
	
	public function testFlip() {
		$img = $this->image->read($this->testPng);
		$img->flip(true, true);
		$this->assertClass($img);
		$this->assertSize($img, 278, 300);
		$this->assertPixel($img, 30, 190, 0x93f5f5, 0.5);
		$this->assertPixel($img, 170, 190, 0xf2c3a8, 1);
	}

	public function testOverlay() {
		$img = $this->image->read($this->testPng);
		$img2 = $this->image->read($this->testPng);
		$img2->flip(true);
		$img->overlay($img2, 10, 10);
		$this->assertClass($img);
		$this->assertSize($img, 278, 300);
		$this->assertPixel($img, 38, 92, 0xc8ee9e, 1);
		$this->assertPixel($img, 261, 84, 0x93f5f5, 0.5);
	}

	
	public function testOverlayJpg() {
		$img = $this->image->read($this->testJpg);
		$img2 = $this->image->read($this->testPng);
		$img2->flip(true);
		$img->overlay($img2, 10, 10);
		$this->assertClass($img);
		$this->assertSize($img, 278, 300);
		$this->assertPixel($img, 38, 92, 0xc8f09b, 1);
		$this->assertPixel($img, 261, 84, 0xcafafc, 1);
	}
	
	public function testTextSize() {
		$img = $this->image->create(300, 300);
		$this->assertClass($img);
		$size = $img->textSize("hello\nworld", 40, $this->testFont);
        if($this->dryRun) {
            return;
        }
		$this->assertEquals(true, 6 > abs(101-$size['width']));
		$this->assertEquals(true, 6 > abs(75-$size['height']));
	}
	
	public function testText() {
		$img = $this->image->read($this->testPng);
		$img->text("hello\nworld", 40, $this->testFont, 10, 54, 0xff0000, 0.5);
		$this->assertClass($img);
		$this->assertPixel($img, 13, 40, 0xff0000, 0.5);
		$img->text("hello\ntest\nme", 40, $this->testFont, 10, 54, 0xff0000, 0.5, null, 3);
		$this->assertClass($img);
		$this->assertPixel($img, 26, 167, 0xff0000, 0.5);
		$this->assertPixel($img, 32, 284, 0xfc6d64, 1);
	}

	public function testTextAngle() {
		$img = $this->image->read($this->testPng);
		$img->text("hello\nworld\nhi", 40, $this->testFont, 40, 70, 0xff0000, 0.5, null, 1, 45);
		$this->assertClass($img);
		$this->assertPixel($img, 61, 88, 0xff0000, 0.5);
		$this->assertPixel($img, 97, 54, 0xfb695f, 1);
	}
	
	public function testTextWrap() {
        $text = "Tinkerbell is a magical fairy that enjoys picking flowers and singing songs in the forest.\n";
        $text.= "She also has a friend named Trixie";
		$img = $this->image->read($this->testPng);
		$img->text($text, 20, $this->testFont, 27, 70, 0xff0000, 0.5, 258, 1.4);
		$this->assertClass($img);
		$this->assertPixel($img, 34, 61, 0xff0000, 0.5);
	}
	
	
	public function testSavePng() {
		$img = $this->image->read($this->testPng);
		$tmp = tempnam(sys_get_temp_dir(), 'test.png');
		$img->save($tmp, 'png');
        
		$img = $this->image->read($tmp);
		$this->assertSize($img, 278, 300);
		$this->assertPixel($img, 228, 64, 0x98fcfc, 0.5);
		$this->assertPixel($img, 160, 52, 0xf76fae, 1);
		unlink($tmp);
	}
	
	public function testSaveJpg() {
		$img = $this->image->read($this->testPng);
		$tmp = tempnam(sys_get_temp_dir(), 'test.jpg');
		$img->save($tmp, 'jpg');
        
		$img = $this->image->read($tmp);
		$this->assertSize($img, 278, 300);
		$this->assertPixel($img, 228, 64, 0xcbfdfa, 1);
		$this->assertPixel($img, 160, 52, 0xf76fb1, 1);
		unlink($tmp);
	}
	
	public function testSaveGuess() {
		$img = $this->image->read($this->testPng);
		$tmp = sys_get_temp_dir().'/test.jpg';
		$img->save($tmp);
		$info = getimagesize($tmp);
		$this->assertEquals('image/jpeg', $info['mime']);
		unlink($tmp);
        
		$tmp = sys_get_temp_dir().'/test.png';
		$img->save($tmp);
		$info = getimagesize($tmp);
		$this->assertEquals('image/png', $info['mime']);
		unlink($tmp);
	}
	
	protected function tearDown(){
		$this->image = null;
	}
	
	protected function save() {
		$this->image->save($this->filesDir.'pixie1.png', 'png');
	}
	
	protected function assertClass($img) {
		$this->assertInstanceOf('\PHPixie\Image\Drivers\Driver\Resource', $img);
	}
	
	protected function assertSize($img, $width, $height) {
		$this->assertEquals($width, $img->width());
		$this->assertEquals($height, $img->height());
	}
	
	protected function assertPixel($img, $x, $y, $color, $opacity) {
		$pixel = $img->getPixel($x, $y);
		$tcolor = $pixel->color();
        if($this->dryRun) {
            return;
        }
		$dr = abs((($tcolor >> 16) & 0xFF) - (($color >> 16) & 0xFF));
		$dg = abs((($tcolor >> 8) & 0xFF) - (($color >> 8) & 0xFF));
		$db = abs(($tcolor & 0xFF) - ($color & 0xFF));
		if (6 < max($dr, $db, $dg)) {
            echo($color);
            print_r([$x, $y,$pixel, [$dr, $db, $dg]]);

        }
		$this->assertEquals(true, 6 > max($dr, $db, $dg));
        $this->assertEquals($opacity, round($pixel->opacity(), 1));
	}
}
