<?php


namespace Tests\Classes;


use Illuminate\Foundation\Events\Dispatchable;
use robertogallea\LaravelMetrics\Models\Interfaces\PerformsMetrics;
use robertogallea\LaravelMetrics\Models\Traits\Measurable;

class TestEvent implements PerformsMetrics
{
    use Dispatchable;
    use Measurable;

    protected $meter = 'test';

    /**
     * @param $meter
     */
    public function setMeter($meter)
    {
        $this->meter = $meter;
    }
}