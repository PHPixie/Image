<?php
namespace PHPixie;

/**
 * Validation Module for PHPixie
 *
 * This module is not included by default, install it using Composer
 * by adding
 * <code>
 * 		"phpixie/validate": "2.*@dev"
 * </code>
 * to your requirement definition. Or download it from
 * https://github.com/dracony/PHPixie-validate
 * 
 * To enable it add it to your Pixie class' modules array:
 * <code>
 * 		protected $modules = array(
 * 			//Other modules ...
 * 			'validate' => '\PHPixie\Validate',
 * 		);
 * </code>
 *
 * Methods of this class can be used to quickly validate a value. 
 * If you need to validate an array of values against different rules
 * use the Validator class.
 *
 * @see \PHPixie\Validate\Validator
 * @link https://github.com/dracony/PHPixie-validate Download this module from Github
 * @package    Validate
 */
class Image {
	
	/**
	 * Pixie Dependancy Container
	 * @var \PHPixie\Pixie
	 */
	public $pixie;
	
	public function __construct($pixie) {
		$this->pixie = $pixie;
	}
	
	/**
	 * Creates a Validator instance and intializes it with input data
	 *
	 * @param   array  $input  Associative array of fields and values
	 * @return  \PHPixie\Validate\Validator   Initialized Validator object
	 */
	public function read($file, $config = 'default') {
		$driver = $this->pixie->config->get("image.{$config}.driver");
		$driver = "\\PHPixie\\Image\\{$driver}";
		$image  = new $driver; 
		$image->read($file);
		return $image;
	}

	/**
	 * Creates a Validator instance and intializes it with input data
	 *
	 * @param   array  $input  Associative array of fields and values
	 * @return  \PHPixie\Validate\Validator   Initialized Validator object
	 */
	public function create($width, $height, $color = 0xffffff, $opacity = 0, $config = 'default') {
		$driver = $this->pixie->config->get("image.{$config}.driver");
		$driver = "\\PHPixie\\Image\\{$driver}";
		$image  = new $driver; 
		$image->create($width, $height, $color, $opacity);
		return $image;
	}

}
