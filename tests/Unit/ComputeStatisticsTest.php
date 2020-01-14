<?php


namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use robertogallea\LaravelMetrics\Models\Meter;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use Tests\TestCase;

class ComputeStatisticsTest extends TestCase
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
    public function it_counts_to_0_if_no_meter_exists()
    {
        $this->assertEquals(0, $this->meter->count());
    }

    /** @test */
    public function it_counts_meter_by_name()
    {
        $this->meter->mark();

        $this->assertEquals(1, $this->meter->count());
    }

    /** @test */
    public function it_sums_to_0_if_meter_is_empty()
    {
        $this->assertEquals(0, $this->meter->sum());
    }

    /** @test */
    public function it_averages_to_0_if_meter_is_empty()
    {
        $this->assertEquals(0, $this->meter->average());
    }
}