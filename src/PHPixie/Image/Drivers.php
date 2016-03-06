<?php

namespace PHPixie\Image;

class Drivers
{
    protected $drivers = array();
    
    public function get($name)
    {
        if(!array_key_exists($name, $this->drivers)) {
            $method = 'build'.ucfirst($name);
            $this->drivers[$name] = $this->$method();
        }
        
        return $this->drivers[$name];
    }
    
    public function buildGd()
    {
        return new Drivers\Type\GD();
    }
    
    public function buildGmagick()
    {
        return new Drivers\Type\Gmagick();
    }
    
    public function buildImagick()
    {
        return new Drivers\Type\Imagick();
    }
}