<?php
require_once('Driver.php');
class Gmagick_Image_Test extends Driver {

	public $rotated_size = 410;
	
	public function getDriver(){
		return new \PHPixie\Image\Gmagick();
	}
	
}

	