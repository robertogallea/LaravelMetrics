<?php


namespace robertogallea\LaravelMetrics\Models;


use Illuminate\Support\Str;

class MetricRegistry
{
    /**
     * @param string $name
     * @param string $type
     * @return Meter
     */
    public function meter(string $name, $type = null): Meter
    {
        $type = $type ?? MeterType::MARKER;

        $class_name = class_exists($type) ? $type : __NAMESPACE__ . "\\" . Str::studly($type);

        return new $class_name($name);
    }
}