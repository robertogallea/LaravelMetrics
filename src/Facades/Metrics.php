<?php


namespace robertogallea\LaravelMetrics\Facades;


use Illuminate\Support\Facades\Facade;
use robertogallea\LaravelMetrics\Models\MetricRegistry;

class Metrics extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MetricRegistry::class;
    }

}