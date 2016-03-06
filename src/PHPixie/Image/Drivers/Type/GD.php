<?php

namespace PHPixie\Image\Drivers\Type;

/**
 * GD Image driver.
 */
class GD implements \PHPixie\Image\Drivers\Driver
{

    public function create($width, $height, $color = 0xffffff, $opacity = 0) {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        $resource = $this->buildResource($image, $width, $height);
        $resource->fillWithColor($color, $opacity);
        return $resource;
    }

    public function read($file) {
        $size = getimagesize($file);

        if (!$size)
            throw new \PHPixie\Image\Exception("File is not a valid image");

        switch($size["mime"]) {
            case "image/png":
                $image = imagecreatefrompng($file);
                break;
            case "image/jpeg":
                $image = imagecreatefromjpeg($file);
                break;
            case "image/gif":
                $image = imagecreatefromgif($file);
                break;
            default:
                throw new \PHPixie\Image\Exception("File is not a valid image");
                break;
        }

        imagealphablending($image, false);
        return $this->buildResource($image, $size[0], $size[1]);
    }

    public function load($bytes) {
        $image = imagecreatefromstring($bytes);
        imagealphablending($image, false);
        return $this->buildResource($image, imagesx($image), imagesy($image), 'png');
    }

    /**
     * @param resource $image
     * @param int $width
     * @param int $height
     * @return GD\Resource
     */
    protected function buildResource($image, $width, $height)
    {
        return new GD\Resource($image, $width, $height);
    }
}
