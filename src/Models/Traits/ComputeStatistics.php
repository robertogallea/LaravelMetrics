<?php


namespace robertogallea\LaravelMetrics\Models\Traits;


use robertogallea\LaravelMetrics\Models\MetricCollection;

trait ComputeStatistics
{
    /**
     * @return int
     */
    public function count() : int
    {
        return $this->builder()->count();
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return int
     */
    public function sum(Carbon $from = null, Carbon $to = null) : int
    {
        return $this->builder($from, $to)->sum(config('metrics.table.columns.value'));
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return float
     */
    public function average(Carbon $from = null, Carbon $to = null) : float
    {
        return $this->builder($from, $to)->avg(config('metrics.table.columns.value')) ?? 0;
    }
}