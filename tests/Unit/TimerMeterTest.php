<?php


namespace Tests\Unit;


use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use robertogallea\LaravelMetrics\Exceptions\TimerNotStartedExpcetion;
use robertogallea\LaravelMetrics\Models\MeterType;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use robertogallea\LaravelMetrics\Models\TimeResolution;
use robertogallea\LaravelMetrics\Models\TimerMeter;
use robertogallea\LaravelMetrics\Models\TimeSeriesStatistics;
use Spatie\TestTime\TestTime;

class TimerMeterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new MetricRegistry();

        $this->timer = $this->registry->meter('test', MeterType::TIMER);
    }


    /** @test */
    public function it_can_be_started()
    {
        $timerId = $this->timer->start();

        $this->assertNotNull($timerId);
    }

    /** @test */
    public function it_can_be_stopped()
    {
        $timerId = $this->timer->start();

        $elapsed = $this->timer->stop($timerId);

        $this->assertIsNumeric($elapsed);
    }

    /** @test */
    public function it_throws_exception_if_timer_was_not_started()
    {
        $this->expectException(TimerNotStartedExpcetion::class);

        $this->timer->stop('notstartedid');
    }

    /** @test */
    public function it_returns_elapsed_time_in_seconds()
    {
        $timer = $this->sixtySecondsTimer();

        $this->assertEquals(60, $timer->getElapsed());
    }

    /** @test */
    public function it_returns_elapsed_time_in_milliseconds()
    {
        $timer = $this->sixtySecondsTimer(TimeResolution::MILLISECONDS);

        $this->assertEquals(60000, $timer->getElapsed());
    }

    /** @test */
    public function it_returns_elapsed_time_in_microseconds()
    {
        $timer = $this->sixtySecondsTimer(TimeResolution::MICROSECONDS);

        $this->assertEquals(60000000, $timer->getElapsed());
    }

    /** @test */
    public function it_returns_elapsed_time_in_minutes()
    {
        $timer = $this->sixtySecondsTimer(TimeResolution::MINUTES);

        $this->assertEquals(1, $timer->getElapsed());
    }

    /** @test */
    public function it_returns_elapsed_time_in_hours()
    {
        $timer = $this->nDaysTimer(1, TimeResolution::HOURS);

        $this->assertEquals(24, $timer->getElapsed());
    }

    /** @test */
    public function it_returns_elapsed_time_in_days()
    {
        $timer = $this->nDaysTimer(1, TimeResolution::DAYS);

        $this->assertEquals(1, $timer->getElapsed());
    }

    /** @test */
    public function it_returns_elapsed_time_in_weeks()
    {
        $timer = $this->nDaysTimer(7, TimeResolution::WEEKS);

        $this->assertEquals(1, $timer->getElapsed());
    }

    /** @test */
    public function it_returns_elapsed_time_in_months()
    {
        $timer = $this->nDaysTimer(31, TimeResolution::MONTHS);

        $this->assertEquals(1, $timer->getElapsed());
    }

    /** @test */
    public function it_returns_elapsed_time_in_years()
    {
        $timer = $this->nDaysTimer(366, TimeResolution::YEARS);

        $this->assertEquals(1, $timer->getElapsed());
    }

    /** @test */
    public function it_saves_to_database_when_stopped()
    {
        $timer = $this->sixtySecondsTimer();

        $this->assertDatabaseHas('metrics', [
            'type' => $timer->getType(),
            'name' => $timer->getName(),
            'value' => $timer->getElapsed()
        ]);
    }

    /** @test */
    public function it_saves_metadata_when_stopped()
    {
        $metadata = [
            'key' => 'value', 'other_key' => [
                'sub_key' => 'sub_value'
            ]
        ];

        $timerId = $this->timer->start();

        $this->timer->stop($timerId, $metadata);

        $this->assertEquals($metadata, $this->timer->first()->metadata);
    }

    /** @test */
    public function it_saves_metadata_when_passed_on_start()
    {
        $metadata = [
            'key' => 'value', 'other_key' => [
                'sub_key' => 'sub_value'
            ]
        ];

        $timerId = $this->timer->start($metadata);

        $this->timer->stop($timerId);

        $this->assertEquals($metadata, $this->timer->first()->metadata);
    }

    /** @test */
    public function it_leaves_cache_clear_when_stopped()
    {
        $timerId = $this->timer->start(['key' => 'value']);

        $this->timer->stop($timerId);

        $this->assertFalse(cache()->has($timerId));
        $this->assertFalse(cache()->has($timerId . TimerMeter::METADATA_CACHE_SUFFIX));
    }

    /** @test */
    public function it_saves_resolutions_to_database()
    {
        $timer = $this->sixtySecondsTimer(TimeResolution::MILLISECONDS);

        $this->assertDatabaseHas('metrics', [
            'type' => $timer->getType(),
            'name' => $timer->getName(),
            'value' => $timer->getElapsed(),
            'resolution' => TimeResolution::MILLISECONDS,
        ]);
    }

    private function nMinutesTimer(int $minutes, $resolution = TimeResolution::SECONDS)
    {
        TestTime::freeze();
        $timerId = $this->timer->start();

        $this->timer->{'in' . $resolution}();

        TestTime::addMinutes($minutes);
        $this->timer->stop($timerId);

        return $this->timer;
    }

    private function sixtySecondsTimer($resolution = TimeResolution::SECONDS)
    {
        return $this->nMinutesTimer(1, $resolution);
    }

    private function nDaysTimer(int $days, $resolution = TimeResolution::SECONDS)
    {
        return $this->nMinutesTimer($days * 60*24, $resolution);
    }

    /**
     * @test
     * @dataProvider timeSeriesStatistics
     */
    public function it_can_generate_time_series($seriesType, $expectedResult)
    {
        $startTime = Carbon::now();

        TestTime::freeze();

        $startOffsetAndDuration= [
            ['startOffset' => 0, 'duration' => 1],
            ['startOffset' => 1, 'duration' => 2],
            ['startOffset' => 1, 'duration' => 1],
            ['startOffset' => 2, 'duration' => 1],
        ];

        foreach ($startOffsetAndDuration as $item) {
            $this->generateTimer($item);
        }

        $timeSeries = $this->timer->byMinute($startTime, Carbon::now()->addMinutes(2), $seriesType);

        $this->assertEquals($expectedResult, $timeSeries->values()->all());
    }

    public function timeSeriesStatistics()
    {
        return [
            [TimeSeriesStatistics::AVERAGE, [60, 90, 60]],
            [TimeSeriesStatistics::COUNT, [1, 2, 1]],
            [TimeSeriesStatistics::MAX, [60, 120, 60]],
            [TimeSeriesStatistics::MIN, [60, 60, 60]],
        ];
    }

    private function generateTimer(array $startOffsetAndDuration)
    {
        $startOffset = $startOffsetAndDuration['startOffset'];
        $duration = $startOffsetAndDuration['duration'];

        TestTime::addMinutes($startOffset);
        $timerId = $this->timer->start();

        TestTime::addMinutes($duration);
        $this->timer->stop($timerId);
        TestTime::subMinutes($duration + $startOffset);
    }

    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }
}