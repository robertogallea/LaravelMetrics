<?php


namespace Tests\Unit;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use robertogallea\LaravelMetrics\Listeners\EventListener;
use Tests\Classes\TestEvent;

class MeasurableTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }
}