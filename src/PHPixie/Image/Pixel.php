<?php

namespace PHPixie\Image;

class Pixel
{
    /**
     * @var int
     */
    protected $x;
    
    /**
     * @var int
     */
    protected $y;
    
    /**
     * @var int
     */
    protected $color;
    
    /**
     * @var float
     */
    protected $opacity;
    
    public function __construct($x, $y, $color, $opacity)
    {
        $this->x       = $x;
        $this->y       = $y;
        $this->color   = $color;
        $this->opacity = $opacity;
    }
    
    public function x()
    {
        return $this->x;
    }
    
    public function y()
    {
        return $this-y;
    }
    
    public function color()
    {
        return $this->color;
    }
    
    public function opacity()
    {
        return $this->opacity;
    }
}