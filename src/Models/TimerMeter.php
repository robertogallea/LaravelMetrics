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

    private $elapsed = null;

    public function start()
    {
        $entry = Str::random();

        cache()->put($entry, Carbon::now());

        return $entry;
    }

    public function stop($timerId)
    {
        if (!cache()->has($timerId)) {
            throw new TimerNotStartedExpcetion('Timer ' . $timerId . ' was not started');
        }

        $startedAt = cache()->get($timerId);
        cache()->forget($timerId);

        $this->elapsed = Carbon::now()->diffInSeconds($startedAt);

        $this->saveToDB($startedAt);

        return $this->elapsed;
    }

    /**
     * @return integer|null
     */
    public function getElapsed()
    {
        return $this->elapsed;
    }

    private function saveToDB(Carbon $startedAt): Metric
    {
        return Metric::create([
            config('metrics.table.columns.type') => $this->getType(),
            config('metrics.table.columns.name') => $this->getName(),
            config('metrics.table.columns.value') => $this->getElapsed(),
            config('metrics.table.columns.created_at') => $startedAt
        ]);
    }
}