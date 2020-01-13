<?php


namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use robertogallea\LaravelMetrics\Models\Meter;
use robertogallea\LaravelMetrics\Models\MeterType;
use robertogallea\LaravelMetrics\Models\MetricRegistry;

class MetricRegistryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Meter
     */
    private $meter;

    public function setUp() : void
    {
        parent::setUp();

        $this->registry = new MetricRegistry();
        $this->meter = $this->registry->meter("some_metric");
    }

    /** @test */
    public function it_return_meter_by_name()
    {
        $this->assertEquals('some_metric', $this->meter->getName());
    }

    /**
     * @test
     * @dataProvider meterTypes
     */
    public function it_create_metrics_by_type($name)
    {
        $meter = $this->registry->meter("some_metric", $name);

        $this->assertEquals($name, $meter->getType());
    }

    public function meterTypes()
    {
        return [
            [MeterType::MARKER],
            [MeterType::TIMER]
        ];
    }

    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }
}