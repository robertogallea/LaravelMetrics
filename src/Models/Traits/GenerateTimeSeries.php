<?php


namespace robertogallea\LaravelMetrics\Models\Traits;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use robertogallea\LaravelMetrics\Models\MetricCollection;
use robertogallea\LaravelMetrics\Models\TimeSeriesStatistics;

trait GenerateTimeSeries
{
    private function by(string $period, Carbon $from, Carbon $to = null, string $aggregateBy): Collection
    {
        $to = $to ?? Carbon::now();

        $timeStrings = [
            'minute' => 'Y/m/d H:i',
            'hour' => 'Y/m/d H',
            'day' => 'Y/m/d',
            'month' => 'Y/m',
            'year' => 'Y',
        ];

        $from->{'startOf' . ucfirst($period)}();
        $to->{'endOf' . ucfirst($period)}();

        $timeFormat = $timeStrings[$period];

        $dbStats = $this->getDbStats($from, $to, $timeFormat, $aggregateBy);

        $stats = $this->fillHolesInStats($from, $to, $dbStats, $timeFormat, $period);

        return new MetricCollection($stats);

        return $stats;
    }

    /**
     * @param Carbon $from
     * @param Carbon|null $to
     * @return Collection
     */
    public function byMinute(Carbon $from, Carbon $to = null, string $aggregateBy = TimeSeriesStatistics::COUNT): Collection
    {
        return $this->by('minute', $from, $to, $aggregateBy);
    }

    /**
     * @param Carbon $from
     * @param Carbon|null $to
     * @return Collection
     */
    public function byHour(Carbon $from, Carbon $to = null, string $aggregateBy = TimeSeriesStatistics::COUNT): Collection
    {
        return $this->by('hour', $from, $to, $aggregateBy);
    }

    /**
     * @param Carbon $from
     * @param Carbon|null $to
     * @return Collection
     */
    public function byDay(Carbon $from, Carbon $to = null, string $aggregateBy = TimeSeriesStatistics::COUNT): Collection
    {
        return $this->by('day', $from, $to, $aggregateBy);
    }

    /**
     * @param Carbon $from
     * @param Carbon|null $to
     * @return Collection
     */
    public function byMonth(Carbon $from, Carbon $to = null, string $aggregateBy = TimeSeriesStatistics::COUNT): Collection
    {
        return $this->by('month', $from, $to, $aggregateBy);
    }

    /**
     * @param Carbon $from
     * @param Carbon|null $to
     * @return Collection
     */
    public function byYear(Carbon $from, Carbon $to = null, string $aggregateBy = TimeSeriesStatistics::COUNT): Collection
    {
        return $this->by('year', $from, $to, $aggregateBy);
    }

    /**
     * @param Carbon $from
     * @param Carbon $to
     * @return Collection
     */
    private function getDbStats(Carbon $from, Carbon $to, $groupBy, string $aggregateBy = TimeSeriesStatistics::COUNT)
    {
        $dbStats = $this->builder($from, $to)->get()
            ->groupBy(function ($metric) use ($groupBy) {
                return $metric->created_at->format($groupBy);
            })->map(function ($group) use ($aggregateBy) {
                return $group->{$aggregateBy}(config('metrics.table.columns.value'));
            });
        return $dbStats;
    }

    private function fillHolesInStats(Carbon $from, Carbon $to, Collection $dbStats, $keyFormat, $timePeriod): Collection
    {
        $stats = new Collection();

        while ($from->lt($to)) {
            $dateStr = $from->format($keyFormat);
            $stats->put($dateStr, $dbStats[$from->format($keyFormat)] ?? 0);

            $from->{'add' . $timePeriod}();
        }

        return $stats;
    }
}