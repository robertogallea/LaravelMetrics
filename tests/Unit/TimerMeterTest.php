<?php


namespace Tests\Unit;


use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use robertogallea\LaravelMetrics\Exceptions\TimerNotStartedExpcetion;
use robertogallea\LaravelMetrics\Models\MeterType;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
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
    public function it_saves_to_database_when_stopped()
    {
        $timer = $this->sixtySecondsTimer();

        $this->assertDatabaseHas('metrics', [
            'type' => $timer->getType(),
            'name' => $timer->getName(),
            'value' => $timer->getElapsed()
        ]);
    }

    private function sixtySecondsTimer()
    {
        TestTime::freeze();
        $timerId = $this->timer->start();

        TestTime::addMinute();
        $this->timer->stop($timerId);

        return $this->timer;
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