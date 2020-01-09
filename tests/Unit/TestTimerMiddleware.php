<?php


namespace Tests\Unit;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use robertogallea\LaravelMetrics\Http\Middleware\MeasureTime;
use robertogallea\LaravelMetrics\Models\MeterType;
use robertogallea\LaravelMetrics\Models\MetricRegistry;

class TestTimerMiddleware extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_keeps_request_timing()
    {
        $request = new Request();

        $timerName = 'test';

        $registry = new MetricRegistry();

        $middleware = new MeasureTime($registry);

        $middleware->handle($request, function() use ($registry, $timerName) {
            $this->assertCount(1, ($registry)->meter($timerName, MeterType::TIMER)->get());
        }, $timerName);
    }

    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }
}