<?php


namespace Tests\Unit;

use Carbon\Carbon;
use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use robertogallea\LaravelMetrics\Models\MarkerMeter;
use robertogallea\LaravelMetrics\Models\TimeSeriesStatistics;
use Spatie\TestTime\TestTime;
use Illuminate\Support\Collection;

class MarkerMeterTest extends TestCase
{
    use RefreshDatabase;

    private MarkerMeter $markerMeter;

    public function setUp(): void
    {
        parent::setUp();

        $this->registry = new MetricRegistry();
        $this->markerMeter = $this->registry->meter("some_metric");
    }

    /** @test */
    public function it_increments_meter()
    {
        $this->markerMeter->mark();

        $this->assertCount(1, $this->markerMeter->get());
    }

    /** @test */
    public function it_gets_metrics_after_a_date()
    {
        $this->markerMeter->mark();
        $this->markerMeter->mark();

        $this->assertCount(2, $this->markerMeter->get(Carbon::yesterday()));
    }

    /** @test */
    public function it_filters_metrics_before_a_date()
    {
        TestTime::freeze();

        $this->markerMeter->mark();
        $this->markerMeter->mark();

        TestTime::addDay();

        $this->markerMeter->mark();

        $this->assertCount(1, $this->markerMeter->get(Carbon::now()));
    }

    /** @test */
    public function it_filters_metrics_in_a_range()
    {
        TestTime::freeze();

        $this->markerMeter->mark();

        TestTime::addDay();

        $this->markerMeter->mark();

        TestTime::addDay();

        $this->assertCount(1, $this->markerMeter->get(Carbon::now()->subDay(), Carbon::now()->subDay()));
    }

    /**
     * @test
     * @dataProvider timePeriods
     */
    public function it_aggregates_by_time_period($timePeriod)
    {
        $data = $this->sampleSeries($timePeriod);

        $this->assertCount(3, $data);
        $this->assertEquals(0, $data->first());
        $this->assertEquals(2, $data->skip(1)->first());
        $this->assertEquals(1, $data->last());
    }

    public function timePeriods()
    {
        return [
            ['minute'],
            ['hour'],
            ['day'],
            ['month'],
            ['year']
        ];
    }

    /** @test */
    public function it_throws_execption_if_wrong_period_is_requested()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->sampleSeries('wrong-period');
    }

    /** @test */
    public function it_computes_std_dev()
    {
        $data = $this->sampleSeries();

        $this->assertEquals(0.81649658092773, $data->stDev());
    }

    /** @test */
    public function it_computes_variance()
    {
        $data = $this->sampleSeries();

        $this->assertEquals(0.6666666666666666, $data->variance());
    }

    /** @test */
    public function it_cumulates_histograms()
    {
        $data = $this->sampleSeries();

        tap($data->cumulative(), function ($cumulative) {
            $this->assertEquals(0, $cumulative->first());
            $this->assertEquals(2, $cumulative->skip(1)->first());
            $this->assertEquals(3, $cumulative->last());
        });

    }

    /** @test */
    public function it_computes_ks_statistics()
    {
        $data = $this->sampleSeries();

        $this->markerMeter->mark();

        $data2 = $this->markerMeter->byMinute(Carbon::now()->subMinutes(2), Carbon::now());

        $this->assertEquals(1, $data->kolmSmirn($data2));
    }

    private function sampleSeries($period = 'minute')
    {
        TestTime::freeze();

        $this->markerMeter->mark();
        $this->markerMeter->mark();

        TestTime::{'add' . ucfirst($period)}();

        $this->markerMeter->mark();

        $data = $this->markerMeter->{'by' . ucfirst($period)}(
            Carbon::now()->{'sub' . ucfirst($period) . 's'}(2), Carbon::now(), TimeSeriesStatistics::COUNT
        );

        return $data;
    }

    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }
}