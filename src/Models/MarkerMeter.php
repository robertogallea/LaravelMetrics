<?php


namespace robertogallea\LaravelMetrics\Models;

use robertogallea\LaravelMetrics\Models\Traits\ComputeStatistics;
use robertogallea\LaravelMetrics\Models\Traits\GenerateTimeSeries;

class MarkerMeter extends Meter
{
    use ComputeStatistics;
    use GenerateTimeSeries;

    /**
     * @param int $value
     * @return Metric|null
     */
    public function mark(array $metadata = null) :? Metric
    {
        return Metric::create([
            config('metrics.table.columns.type') => $this->getType(),
            config('metrics.table.columns.name') => $this->getName(),
            config('metrics.table.columns.metadata') => $metadata,
        ]);
    }
}