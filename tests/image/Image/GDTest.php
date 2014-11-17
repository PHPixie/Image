<?php
require_once('Driver.php');
class GD_Image_Test extends Driver {

	public $rotated_size = 409;
	
	public function getDriver(){
		return new \PHPixie\Image\GD();
	}

}

	