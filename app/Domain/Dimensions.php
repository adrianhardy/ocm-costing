<?php


namespace App\Domain;


class Dimensions
{
    private $width;
    private $height;
    private $depth;

    private $system = 'imperial';

    public function __construct($width, $height, $depth)
    {
        $this->width = $width;
        $this->height = $height;
        $this->depth = $depth;
    }

    /**
     * @return $this
     */
    public function asImperial() {
        $this->system = 'imperial';
        return $this;
    }

    /**
     * @return $this
     */
    public function asMetric() {
        $this->system = 'metric';
        return $this;
    }

    /**
     * @param string $system
     * @return float|int
     */
    public function volume($system = 'imperial') {
        $volume = $this->width * $this->depth * $this->height;
        switch (true) {
            case $this->system == $system:
                return $volume;
            case $this->system == 'metric' && $system == 'imperial':
                return $volume * 35.3147;
            case $this->system == 'imperial' && $system == 'metric':
                return $volume / 35.3147;
        }
        throw new \RuntimeException("No way to calculate volume");
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return mixed
     */
    public function getDepth()
    {
        return $this->depth;
    }

}
