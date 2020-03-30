<?php


namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use robertogallea\LaravelMetrics\Models\MetricCollection;
use Tests\TestCase;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use robertogallea\LaravelMetrics\Models\TimeSeriesStatistics;
use Spatie\TestTime\TestTime;

class MarkerMeterTest extends TestCase
{
    use RefreshDatabase;

    private $markerMeter;

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
    public function it_gets_all_metrics()
    {
        $this->markerMeter->mark();
        $this->markerMeter->mark();

        $this->assertCount(2, $this->markerMeter->get());
    }

    /** @test */
    public function it_gets_metrics_after_a_date()
    {
        $this->markerMeter->mark();

        TestTime::freeze();

        TestTime::subMinute();
        $this->markerMeter->mark();
        TestTime::addMinute();

        $this->assertCount(1, $this->markerMeter->get(Carbon::now()));
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

    /** @test */
    public function it_filters_metrics_after_a_date_using_fluent_interface()
    {
        TestTime::freeze();

        $this->markerMeter->mark();

        TestTime::addDay();

        $this->markerMeter->mark();

        TestTime::addDay();

        $this->assertCount(1, $this->markerMeter
            ->after(Carbon::now()->subDay())
            ->get());
    }

    /** @test */
    public function it_filters_metrics_before_a_date_using_fluent_interface()
    {
        TestTime::freeze();

        $this->markerMeter->mark();

        TestTime::addDay();

        $this->markerMeter->mark();

        TestTime::addDay();

        $this->assertCount(1, $this->markerMeter
            ->before(Carbon::now()->subDays(2))
            ->get());
    }

    /** @test */
    public function it_filters_metrics_in_a_range_using_fluent_interface()
    {
        TestTime::freeze();

        $this->markerMeter->mark();

        TestTime::addDay();

        $this->markerMeter->mark();

        TestTime::addDay();

        $this->assertCount(1, $this->markerMeter
            ->between(Carbon::now()->subDay(), Carbon::now()->subDay())
            ->get());
    }

    /**
     * @test
     * @dataProvider timePeriods
     */
    public function it_aggregates_by_time_period($timePeriod)
    {
        $data = $this->sampleSeries($timePeriod);
//        dd($data);

        $this->assertCount(3, $data);
        $this->assertEquals(0, $data->first());
        $this->assertEquals(2, $data->skip(1)->first());
        $this->assertEquals(1, $data->last());
    }

    public function timePeriods()
    {
        yield ['minute'];
        yield ['hour'];
        yield ['day'];
        yield ['month'];
        yield ['year'];
    }

    /** @test */
    public function it_saves_metadata()
    {
        $metadata = [
            'key' => 'value', 'other_key' => [
                'sub_key' => 'sub_value'
            ]
        ];

        $this->markerMeter->mark($metadata);

        $this->assertEquals($metadata, $this->markerMeter->first()->metadata);
    }

    /** @test */
    public function it_throws_execption_if_wrong_period_is_requested()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->sampleSeries('wrong-period');
    }

    private function sampleSeries($period = 'minute')
    {
        TestTime::freeze();

        $this->markerMeter->mark();
        $this->markerMeter->mark();

        TestTime::{'add' . ucfirst($period)}();

        $this->markerMeter->mark();

        $from = $period == 'month' ? Carbon::now()->subMonthNoOverflow(2) : Carbon::now()->{'sub' . ucfirst($period) . 's'}(2);
        $data = $this->markerMeter->{'by' . ucfirst($period)}(
            $from, Carbon::now(), TimeSeriesStatistics::COUNT
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