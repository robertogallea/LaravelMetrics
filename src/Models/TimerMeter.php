<?php


namespace robertogallea\LaravelMetrics\Models;


use Carbon\Carbon;
use Illuminate\Support\Str;
use robertogallea\LaravelMetrics\Exceptions\TimerNotStartedExpcetion;
use robertogallea\LaravelMetrics\Models\Traits\ComputeStatistics;
use robertogallea\LaravelMetrics\Models\Traits\GenerateTimeSeries;

class TimerMeter extends Meter
{
    use ComputeStatistics;
    use GenerateTimeSeries;

    public const METADATA_CACHE_SUFFIX = '_metadata';
    private $elapsed = null;

    private $resolution = TimeResolution::SECONDS;

    public function start(array $metadata = null)
    {
        $entry = Str::random();

        cache()->put($entry, Carbon::now());
        cache()->put($entry . self::METADATA_CACHE_SUFFIX, $metadata);

        return $entry;
    }

    public function stop($timerId, array $metadata = null)
    {
        if (!cache()->has($timerId)) {
            throw new TimerNotStartedExpcetion('Timer ' . $timerId . ' was not started');
        }

        $startedAt = cache()->get($timerId);
        $metadata = $metadata ?? cache()->get($timerId . self::METADATA_CACHE_SUFFIX);
        cache()->forget($timerId);
        cache()->forget($timerId . self::METADATA_CACHE_SUFFIX);

        $this->calculateElapsed($startedAt);

        $this->saveToDB($startedAt, $metadata);

        return $this->elapsed;
    }

    /**
     * @return integer|null
     */
    public function getElapsed()
    {
        return $this->elapsed;
    }

    public function inMilliseconds(): TimerMeter
    {
        $this->in(TimeResolution::MILLISECONDS);

        return $this;
    }

    public function inMicroseconds(): TimerMeter
    {
        $this->in(TimeResolution::MICROSECONDS);

        return $this;
    }

    public function inSeconds(): TimerMeter
    {
        $this->in(TimeResolution::SECONDS);

        return $this;
    }

    public function inMinutes(): TimerMeter
    {
        $this->in(TimeResolution::MINUTES);

        return $this;
    }

    public function inHours(): TimerMeter
    {
        $this->in(TimeResolution::HOURS);

        return $this;
    }

    public function inDays(): TimerMeter
    {
        $this->in(TimeResolution::DAYS);

        return $this;
    }

    public function inWeeks(): TimerMeter
    {
        $this->in(TimeResolution::WEEKS);

        return $this;
    }

    public function inMonths(): TimerMeter
    {
        $this->in(TimeResolution::MONTHS);

        return $this;
    }

    public function inYears(): TimerMeter
    {
        $this->in(TimeResolution::YEARS);

        return $this;
    }

    public function in(string $resolution): TimerMeter
    {
        $this->resolution = $resolution;

        return $this;
    }

    private function saveToDB(Carbon $startedAt, array $metadata = null): Metric
    {
        return Metric::create([
            config('metrics.table.columns.type') => $this->getType(),
            config('metrics.table.columns.name') => $this->getName(),
            config('metrics.table.columns.value') => $this->getElapsed(),
            config('metrics.table.columns.resolution') => $this->resolution,
            config('metrics.table.columns.created_at') => $startedAt,
            config('metrics.table.columns.metadata') => $metadata
        ]);
    }

    /**
     * @param Carbon $startedAt
     */
    private function calculateElapsed(Carbon $startedAt): void
    {
        $this->elapsed = Carbon::now()->{'diffIn' . $this->resolution}($startedAt);
    }
}