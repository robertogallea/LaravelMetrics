<?php


namespace robertogallea\LaravelMetrics\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
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
    public function mark(int $value = 1) :? Metric
    {
        return Metric::create([
            config('metrics.table.columns.type') => $this->getType(),
            config('metrics.table.columns.name') => $this->getName(),
        ]);
    }
}