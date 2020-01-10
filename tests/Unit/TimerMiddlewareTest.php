<?php


namespace Tests\Unit;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use robertogallea\LaravelMetrics\Http\Middleware\MeasureTime;
use robertogallea\LaravelMetrics\Models\MeterType;
use robertogallea\LaravelMetrics\Models\Metric;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use robertogallea\LaravelMetrics\Models\TimeResolution;

class TimerMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_keeps_request_timing()
    {
        $request = new Request();

        $timerName = 'test';

        $registry = new MetricRegistry();

        $middleware = new MeasureTime($registry);

        $middleware->handle($request, function() {}, $timerName);

        $this->assertCount(1, ($registry)->meter($timerName, MeterType::TIMER)->get());
    }

    /** @test */
    public function it_usess_custom_resolution()
    {
        $request = new Request();

        $timerName = 'test';

        $registry = new MetricRegistry();

        $middleware = new MeasureTime($registry);

        $middleware->handle($request, function() {}, $timerName, TimeResolution::MILLISECONDS);

        $this->assertEquals(
            TimeResolution::MILLISECONDS,
            $registry->meter($timerName, MeterType::TIMER)->get()->first()->resolution
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }
}