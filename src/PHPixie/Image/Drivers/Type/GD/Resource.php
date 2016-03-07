<?php

namespace PHPixie\Image\Drivers\Type\GD;

/**
 * GD image resource.
 */
class Resource extends \PHPixie\Image\Drivers\Driver\Resource
{

    /**
     * GD image resource
     * @var resource
     */
    protected $image;

    public function __construct($image, $width, $height)
    {
        $this->setImage($image, $width, $height);
    }

    /**
     * Replaces the image resource with a new image
     *
     * @param resource $image  Image resource
     * @param int      $width  New image width
     * @param int      $height New image height
     */
    protected function setImage($image, $width, $height) {
        if($this->image !== null) {
            imagedestroy($this->image);
        }

        $this->image = $image;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Creates new GD Image
     *
     * @param int $width  Image width
     * @param int $height Image height
     *
     * @return resource New GD image resource
     */
    protected function createGd($width, $height) {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        return $image;
    }

    protected function getColor($color, $opacity) {
        $r = ($color >> 16) & 0xFF;
        $g = ($color >> 8) & 0xFF;
        $b = $color & 0xFF;
        return imagecolorallocatealpha($this->image, $r, $g, $b, 127*(1-$opacity));
    }

    public function getPixel($x, $y) {
        $pixel = imagecolorat($this->image, $x, $y);
        $rgba = imagecolorsforindex($this->image, $pixel);
        $color = ($rgba['red'] << 16) + ($rgba['green'] << 8) + $rgba['blue'];
        $opacity = 1 - $rgba['alpha'] / 127;
        return $this->buildPixel($x, $y, $color, $opacity);
    }

    /**
     * Creates image copy with white background for saving in JPEG format
     *
     * @return resource Image on white background
     */
    protected function jpgBg() {
        $bg = $this->createGd($this->width, $this->height);
        imagefilledrectangle($bg, 0, 0, $this->width, $this->height, $this->getColor(0xffffff, 1));
        imagealphablending($bg, true);
        imagecopy($bg, $this->image, 0, 0, 0, 0, $this->width, $this->height);
        imagealphablending($bg, false);
        return $bg;
    }

    public function render($format = 'png', $quality = 90) {
        switch($format) {
            case 'png':
                imagesavealpha($this->image, true);
                ob_start();
                imagepng($this->image);
                return ob_get_clean();
            case 'jpg':
                $bg = $this->jpgBg($this->image);
                ob_start();
                imagejpeg($bg, null, $quality);
                imagedestroy($bg);
                return ob_get_clean();
            case 'gif':
                ob_start();
                imagegif($this->image);
                return ob_get_clean();
            default:
                throw new \PHPixie\Image\Exception("Type must be either png, jpg or gif");
        }
    }

    public function save($file, $format = null, $quality = 90) {
        if ($format == null) {
            $format = $this->getExtension($file);
        }

        switch($format) {
            case 'png':
                imagesavealpha($this->image, true);
                imagepng($this->image, $file);
                break;
            case 'jpg':
                $bg = $this->jpgBg($this->image);
                imagejpeg($bg, $file, $quality);
                imagedestroy($bg);
                break;
            case 'gif':
                imagegif($this->image, $file);
                break;
            default:
                throw new \PHPixie\Image\Exception("Type must be either png, jpeg or gif");
        }
        return $this;
    }

    public function destroy() {
        if($this->image !== null) {
            imagedestroy($this->image);
            $this->image = null;
        }
    }

    public function crop($width, $height, $x = 0, $y = 0) {
        if ($width > ($maxWidth = $this->width-$x))
            $width = $maxWidth;

        if ($height > ($maxHeight = $this->height-$y))
            $height = $maxHeight;

        $cropped = $this->createGd($width, $height);
        imagecopy($cropped, $this->image, 0, 0, $x, $y, $width, $height);
        $this->setImage($cropped, $width, $height);
        return $this;
    }

    public function scale($scale) {
        $width = ceil($this->width*$scale);
        $height = ceil($this->height*$scale);

        $resized = $this->createGd($width, $height);
        imagecopyresampled($resized, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        $this->setImage($resized, $width, $height);
        return $this;
    }

    public function rotate($angle, $bgColor = 0xffffff, $bgOpacity = 0) {
        $rotated = imagerotate($this->image, $angle, $this->getColor($bgColor, $bgOpacity));
        imagealphablending($rotated, false);
        $this->setImage($rotated, imagesx($rotated), imagesy($rotated));
        return $this;
    }

    public function flip($flipX = false, $flipY = false) {
        if (!$flipX && !$flipY)
            return $this;

        $x = $flipX ? $this->width-1 : 0;;
        $width = ($flipX?-1:1) * $this->width;

        $y = $flipY ? $this->height-1 : 0;;
        $height = ($flipY?-1:1) * $this->height;

        $flipped = $this->createGd($this->width, $this->height);
        imagecopyresampled($flipped, $this->image, 0, 0, $x, $y, $this->width, $this->height, $width, $height);
        $this->setImage($flipped, $this->width, $this->height);
        return $this;
    }

    public function overlay($layer, $x = 0, $y = 0) {
        imagealphablending($this->image, true);
        imagecopy($this->image, $layer->image(), $x, $y, 0, 0, $layer->width, $layer->height);
        imagealphablending($this->image, false);
        return $this;
    }

    protected function drawText($text, $size, $fontFile, $x, $y, $color, $opacity, $angle) {
        $size = floor($size * 72 / 96);
        $color = $this->getColor($color, $opacity);

        imagealphablending($this->image, true);
        imagettftext($this->image, $size, $angle, $x, $y, $color, $fontFile, $text);
        imagealphablending($this->image, false);
        return $this;
    }

    protected function textMetrics($text, $size, $fontFile) {
        $size = floor($size*72/96);
        $box = imagettfbbox($size, 0, $fontFile, $text);
        return array(
            'ascender'  => -$box[7],
            'descender' => $box[3],
            'width'     => $box[2] - $box[6],
            'height'    => $box[3] - $box[7]
        );
    }

    public function image()
    {
        return $this->image;
    }

    public function fillWithColor($color, $opacity)
    {
        $color = $this->getColor($color, $opacity);
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $color);
    }
}
