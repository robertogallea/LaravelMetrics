<?php


namespace Tests\Unit;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use robertogallea\LaravelMetrics\Facades\Metrics;
use robertogallea\LaravelMetrics\Models\Meter;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use Tests\Classes\TestEvent;

class MetricsServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_metric_registry()
    {
        $this->assertInstanceOf(
            MetricRegistry::class,
            resolve(MetricRegistry::class)
        );
    }

    /** @test */
    public function it_registers_metric_registry_as_singleton()
    {
        $this->assertSame(
            resolve(MetricRegistry::class),
            resolve(MetricRegistry::class)
        );
    }

    /** @test */
    public function it_registers_facade()
    {
        $this->assertInstanceOf(Meter::class, \Metrics::meter('test'));
    }

    /** @test */
    public function it_registers_event_listener()
    {
        event(new TestEvent());

        $this->assertCount(1, \Metrics::meter('test')->get());
    }

    /** @test */
    public function it_registers_configuration()
    {
        $this->assertNotNull(config('metrics'));
    }

    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }

        protected function getPackageAliases($app)
    {
        return [
            'Metrics' => Metrics::class
        ];
    }
}