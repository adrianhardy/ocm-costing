<?php
namespace App\Models\Traits;

trait ProductDimensionMutators
{
    public function getLengthCmAttribute()
    {
        return round($this->length_m * 100, 2);
    }

    public function getWidthCmAttribute()
    {
        return round($this->width_m * 100, 2);
    }

    public function getHeightCmAttribute()
    {
        return round($this->height_m * 100, 2);
    }

    public function getUnitLengthCmAttribute()
    {
        return round($this->unit_length_m * 100, 2);
    }

    public function getUnitWidthCmAttribute()
    {
        return round($this->unit_width_m * 100, 2);
    }

    public function getUnitHeightCmAttribute()
    {
        return round($this->unit_height_m * 100, 2);
    }

    public function setLengthCmAttribute($cm)
    {
        $this->length_m = $cm/100;
    }

    public function setWidthCmAttribute($cm)
    {
        $this->width_m = $cm/100;
    }

    public function setHeightCmAttribute($cm)
    {
        $this->height_m = $cm/100;
    }

    public function setUnitLengthCmAttribute($cm)
    {
        $this->unit_length_m = $cm/100;
    }

    public function setUnitWidthCmAttribute($cm)
    {
        $this->unit_width_m = $cm/100;
    }

    public function setUnitHeightCmAttribute($cm)
    {
        $this->unit_height_m = $cm/100;
    }
}
