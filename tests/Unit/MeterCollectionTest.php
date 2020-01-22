<?php


namespace Tests\Unit;


use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use robertogallea\LaravelMetrics\Models\MetricCollection;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use robertogallea\LaravelMetrics\Models\TimeSeriesStatistics;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class MeterCollectionTest extends TestCase
{
    use RefreshDatabase;

    private $markerMeter;

    public function setUp(): void
    {
        parent::setUp();

        $this->registry = new MetricRegistry();
        $this->markerMeter = $this->registry->meter("some_metric");
    }

    /**
     * @test
     * @dataProvider histograms
     */
    public function it_computes_histograms($data, $bins, $histogram)
    {
        $data = new MetricCollection(collect($data));

        $this->assertEquals(collect($histogram), $data->histogram($bins));
    }

    public function histograms()
    {
        return [
            [[0, 1], 1, [2]],
            [[0, 0, 1], 2, [2, 1]],
            [[1, 2, 3], 3, [1, 1, 1]],
            [[1, 2, 3], 4, [1, 1, 0, 1]],
            [[0, 2, 4, 2], 3, [1, 2, 1]],
        ];
    }

    /**
     * @test
     * @dataProvider seriesHistograms
     */
    public function it_computes_histogram_for_time_series($bins, $expected)
    {
        $data = $this->sampleSeries();

        $timeSeries = $this->markerMeter->byMinute(Carbon::now()->subMinutes(2), Carbon::now());

        $histogram = $timeSeries->histogram($bins);

        $this->assertEquals(collect($expected), $histogram);
    }

    public function seriesHistograms()
    {
        return [
            [1, [3]],
            [2, [2, 1]],
            [3, [1, 1, 1]],
            [4, [1, 1, 0, 1]],
            [5, [1, 0, 1, 0, 1]],
            [6, [1, 0, 1, 0, 0, 1]],
            [7, [1, 0, 0, 1, 0, 0, 1]],
            [8, [1, 0, 0, 1, 0, 0, 0, 1]],
            [9, [1, 0, 0, 0, 1, 0, 0, 0, 1]],
        ];
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
    public function it_cumulates_time_series()
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