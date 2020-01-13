<?php


namespace Tests\Unit;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use robertogallea\LaravelMetrics\Http\Middleware\Mark;
use robertogallea\LaravelMetrics\Models\MeterType;
use robertogallea\LaravelMetrics\Models\MetricRegistry;

class MarkerMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_marks_upon_request()
    {
        $request = new Request();

        $timerName = 'test';

        $registry = new MetricRegistry();

        $middleware = new Mark($registry);

        $middleware->handle($request, function() use ($registry, $timerName) {
            $this->assertCount(1, ($registry)->meter($timerName, MeterType::MARKER)->get());
        }, $timerName);
    }

    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }
}