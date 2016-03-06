<?php

namespace PHPixie\Image\Drivers\Driver;

/**
 * Base image resource.
 * Drivers have to extend this class.
 */
abstract class Resource
{

    /**
     * Image width
     * @var int
     */
    protected $width;

    /**
     * Image height
     * @var int
     */
    protected $height;

    /**
     * Image format
     * @var string
     */
    protected $format;

    /**
     * Image width
     * @return int
     */
    public function width()
    {
        return $this->width;
    }
    
    /*
     * Image height
     * @return int
     */
    public function height()
    {
        return $this->height;
    }
    
    /**
     * Resizes the image to either fit specified dimensions or to fill them (based on the $fit parameter).
     *
     * If only the width or height is provided the image will be resized according to that single dimension.
     *
     * If both height and width are present this function will behave according to the $fit parameter.
     * E.g Provided the image is 400x200 and the dimensions given were 100x100 the image will be resized to
     * 100x50 if $fit is true, or to 200x100 if it is false.
     *
     * @param int $width Width to fit or fill
     * @param int $height Height to fit or fill
     * @param bool $fit Whether to fit or fill the dimensions.
     *
     * @return \PHPixie\Image\Resource Returns self
     * @throws \PHPixie\Image\Exception If neither width or height is set
     */
    public function resize($width = null, $height = null, $fit = true)
    {
        if ($width && $height) {
            $wScale = $width / $this->width;
            $hScale = $height / $this->height;
            $scale = $fit ? min($wScale, $hScale) : max($wScale, $hScale);
        } elseif ($width) {
            $scale = $width / $this->width;
        } elseif ($height) {
            $scale = $height / $this->height;
        } else {
            throw new \PHPixie\Image\Exception("Either width or height must be set");
        }

        $this->scale($scale);
        return $this;
    }

    /**
     * Resizes the image to be at least $widthX$height in size and the crops it to those dimensions.
     * Great for creating fixed-size avatars.
     *
     * @param int $width Width to crop to
     * @param int $height Height to crop to
     *
     * @return \PHPixie\Image\Resource Returns self
     */
    public function fill($width, $height)
    {
        $this->resize($width, $height, false);
        $x = (int)($this->width - $width) / 2;
        $y = (int)($this->height - $height) / 2;
        $this->crop($width, $height, $x, $y);
        return $this;
    }

    /**
     * Destructor. Makes sure to remove the image resource from memory.
     */
    public function __destruct()
    {
        $this->destroy();
    }

    /**
     * Wraps text into lines that would fit the specified width
     *
     * @param string $text Text to wrap
     * @param int $size Font size
     * @param string $fontFile Path to font file
     * @param int $width Width in pixels to fit the text in
     *
     * @return string $text Wrapped text
     */
    protected function wrapText($text, $size, $fontFile, $width)
    {
        $blocks = explode("\n", $text);
        $lines = array();
        foreach ($blocks as $block) {
            $words = explode(' ', $block);
            $line = '';
            $lineWidth = 0;
            foreach ($words as $key => $word) {
                $prefix = $line == '' ? '' : ' ';
                $box = $this->textMetrics($prefix . $word, $size, $fontFile);
                $wordWidth = $box['width'];
                if ($line == '' || $lineWidth + $wordWidth < $width) {
                    $line .= $prefix . $word;
                    $lineWidth += $wordWidth;
                } else {
                    $lines[] = $line;
                    $line = $word;
                    $box = $this->textMetrics($word, $size, $fontFile);
                    $lineWidth = $box['width'];
                }
            }
            $lines[] = $line;
        }
        return implode("\n", $lines);
    }

    /**
     * Gets the file extension of the image
     *
     * @param string $file path to image
     *
     * @return string Extension of the image file
     */
    protected function getExtension($file)
    {
        $ext = strtolower(pathinfo($file, \PATHINFO_EXTENSION));
        if ($ext == 'jpeg') {
            $ext = 'jpg';
        }
        return $ext;
    }

    /**
     * Calculates offset between two lines of text based on font size and line spacing.
     *
     * @param int $size Font size
     * @param int $lineSpacing Line spacing multiplier.
     *
     * @return int Line spacing
     */
    protected function baseLineOffset($size, $lineSpacing)
    {
        return $size * $lineSpacing;
    }

    /**
     * Calculates text metrics of the specified text.
     *
     * Takes line spacing into account.
     * Gets width, height, ascender of the first line of text and descender of the last one.
     *
     * @param string $text Text to calculate size for
     * @param int $size Font size
     * @param string $fontFile Path to font file
     * @param int $lineSpacing Line spacing multiplier
     *
     * @return array Text metrics
     */
    public function textSize($text, $size, $fontFile, $lineSpacing = 1)
    {
        $lines = explode("\n", $text);
        $box = null;
        $ascender = 0;
        $baselineOffset = $this->baselineOffset($size, $lineSpacing);
        foreach ($lines as $k => $line) {
            $lineBox = $this->textMetrics($line, $size, $fontFile);
            if ($box == null) {
                $box = $lineBox;
                $ascender = $lineBox['ascender'];
            } else {
                $box['width'] = $lineBox['width'] > $box['width'] ? $lineBox['width'] : $box['width'];
                $box['descender'] = $lineBox['descender'];
                $box['height'] = $ascender + $k * $baselineOffset + $lineBox['descender'];
            }
        }
        return $box;
    }

    /**
     * Draws text over the image.
     *
     * @param string $text Text to draw
     * @param int $size Font size
     * @param string $fontFile Path to font file
     * @param int $x X coordinate of the baseline of the first line of text
     * @param int $y Y coordinate of the baseline of the first line of text
     * @param int $color Text color (e.g 0xffffff)
     * @param float $opacity Text opacity
     * @param int $wrapWidth Width to wrap text at. Null means no wrapping.
     * @param int $lineSpacing Line spacing multiplier
     * @param float $angle Counter clockwise text rotation angle
     *
     * @return \PHPixie\Image\Resource Returns self
     */
    public function text(
        $text,
        $size,
        $fontFile,
        $x,
        $y,
        $color = 0x000000,
        $opacity = 1,
        $wrapWidth = null,
        $lineSpacing = 1,
        $angle = 0
    ) {
        if ($wrapWidth != null) {
            $text = $this->wrapText($text, $size, $fontFile, $wrapWidth);
        }

        $lines = explode("\n", $text);
        $offset_x = 0;
        $offset_y = 0;
        $baseline = $this->baselineOffset($size, $lineSpacing);
        foreach ($lines as $line) {
            $this->drawText($line, $size, $fontFile, $x + $offset_x, $y + $offset_y, $color, $opacity, $angle);
            $rad = deg2rad($angle);
            $offset_x += sin($rad) * $baseline;
            $offset_y += cos($rad) * $baseline;
        }
        return $this;
    }

    /**
     * Gets color of the pixel at specifed coordinates.
     *
     * Returns array with 'color' and 'opacity' keys
     *
     * @param int $x X coordinate
     * @param int $y Y coordinate
     *
     * @return array Pixel color data
     */
    public abstract function getPixel($x, $y);

    /**
     * Renders and ouputs the image.
     *
     * @param string $format Image format (gif, png or jpeg)
     * @param int $quality Compression quality (0 - 100)
     *
     * @return \PHPixie\Image\Resource Returns self
     * @throw  \Exception  if the format is not supported
     */
    public abstract function render($format = 'png', $quality = 90);

    /**
     * Saves the image to file. If $format is ommited the format is guessed based on file extension.
     *
     * @param string $file File to save the image to.
     * @param string $format Image format (gif, png or jpeg)
     * @param int $quality Compression quality (0 - 100)
     *
     * @return \PHPixie\Image\Resource Returns self
     * @throw  \Exception  if the format is not supported
     */
    public abstract function save($file, $format = null, $quality = 90);

    /**
     * Destroys the image resource.
     */
    public abstract function destroy();

    /**
     * Crops the image.
     *
     * @param int $width Width to crop to
     * @param int $height Height to crop to
     * @param int $x X coordinate of crop start position
     * @param int $y Y coordinate of crop start position
     *
     * @return \PHPixie\Image\Resource Returns self
     */
    public abstract function crop($width, $height, $x = 0, $y = 0);

    /**
     * Scales the image to the specified ratio.
     *
     * @param float $scale Scale ratio
     *
     * @return \PHPixie\Image\Resource Returns self
     */
    public abstract function scale($scale);

    /**
     * Rotates the image counter clockwise.
     *
     * @param float $angle Rotation angle in degrees
     * @param int $bg_color Background color
     * @param int $bg_color Background opacity
     *
     * @return \PHPixie\Image\Resource Returns self
     */
    public abstract function rotate($angle, $bg_color = 0xffffff, $bg_opacity = 0);

    /**
     * Flips the image.
     *
     * @param bool $flip_x Whether to flip image horizontally
     * @param bool $flip_y Whether to flip image vertically
     *
     * @return \PHPixie\Image\Resource Returns self
     */
    public abstract function flip($flip_x = false, $flip_y = false);

    /**
     * Overlays another image over the current one.
     *
     * @param \PHPixie\Image\Resource $layer Image to overlay over the current one
     * @param int $x X coordinate of the overlay
     * @param int $y Y coordinate of the overlay
     *
     * @return \PHPixie\Image\Resource Returns self
     */
    public abstract function overlay($layer, $x = 0, $y = 0);

    /**
     * Gets metris of the specified text.
     *
     * Returns an array with keys 'width', 'height', 'ascender' and 'descender'.
     *
     * @param string $text Text to get metrics of
     * @param int $size Font size
     * @param string $fontFile Path to font file
     *
     * @return array Text metrics
     */
    protected abstract function textMetrics($text, $size, $fontFile);

    /**
     * Draws text over the image.
     *
     * @param string $text Text to draw
     * @param int $size Font size
     * @param string $fontFile Path to font file
     * @param int $x X coordinate of the baseline of the first line of text
     * @param int $y Y coordinate of the baseline of the first line of text
     * @param int $color Text color (e.g 0xffffff)
     * @param float $opacity Text opacity
     * @param float $angle Counter clockwise text rotation angle
     *
     * @return \PHPixie\Image\Resource Returns self
     */
    protected abstract function drawText($text, $size, $fontFile, $x, $y, $color, $opacity, $angle);

    /**
     * Gets driver specific color representation.
     *
     * @param int $color Color
     * @param float $opacity Opacity
     *
     * @return mixed Color representation
     */
    protected abstract function getColor($color, $opacity);
    
    protected function buildPixel($x, $y, $color, $opacity)
    {
        return new \PHPixie\Image\Pixel($x, $y, $color, $opacity);
    }
}
