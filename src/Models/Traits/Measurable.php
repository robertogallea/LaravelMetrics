<?php


namespace robertogallea\LaravelMetrics\Models\Traits;


trait Measurable
{
    /**
     * @return string
     */
    public function getMeter():? string
    {
        return $this->meter;
    }
}