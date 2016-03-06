<?php

namespace PHPixie\Image\Drivers;

/**
 * Base image resource.
 * Drivers have to extend this class.
 */
interface Driver
{
    /**
     * Creates a blank image and fill it with specified color.
     *
     * @param int $width Image width
     * @param int $height Image height
     * @param int $color Image color
     * @param float $float Color opacity
     *
     * @return \PHPixie\Image\Resource Returns self
     */
    public function create($width, $height, $color = 0xffffff, $opacity = 0);

    /**
     * Reads image from file.
     *
     * @param   string $file Image file
     *
     * @return  \PHPixie\Image\Resource Initialized Image
     */
    public function read($file);

    /**
     * Loads image data from a bytestring.
     *
     * @param   string $bytes Image data
     *
     * @return  \PHPixie\Image\Resource Initialized Image
     */
    public function load($bytes);
}
