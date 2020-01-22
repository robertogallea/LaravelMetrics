<?php


namespace robertogallea\LaravelMetrics\Models;


use Illuminate\Support\Collection;

class MetricCollection extends Collection
{

    public function histogram(int $numBins = 10): Collection
    {
        $found = false;
        $min = $this->min();
        $max = $this->max();
        $bins = range($min, $max - ($max-$min) / $numBins, ($max-$min) / $numBins);
        $histogram = array_fill(0, $numBins, 0);

        $this->each(function ($item) use (&$histogram, $bins, $max, $min) {
            $found = false;
            for ($i = 1; $i < sizeof($bins); $i++) {
                $found = false;
                if ($item <= $bins[$i]) {
                    $histogram[$i-1]++;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $histogram[sizeof($histogram)-1]++;
            }
        });

        return collect($histogram);
    }

    /**
     * @return MetricCollection
     */
    public function cumulative(): MetricCollection
    {
        $cumulative = 0;

        return $this->transform(function ($item, $key) use (&$cumulative) {
            $value = $item + $cumulative;
            $cumulative += $value;

            return $value;
        });
    }

    /**
     * @param string|null $column
     * @return float
     */
    public function stDev(string $column = null): float
    {
        return sqrt($this->variance($column));
    }

    /**
     * @param string|null $column
     * @return float
     */
    public function variance(string $column = null): float
    {
        $collection = $this;

        $mean = $collection->avg();
        $count = $collection->count();

        return $collection->transform(function ($value) use ($mean) {
                return (($value - $mean) * ($value - $mean));
            })->sum() / $count;
    }

    /**
     * @param MetricCollection $collection
     * @return float
     */
    public function kolmSmirn(MetricCollection $collection): float
    {
        return $this->transform(function ($item, $key) use ($collection) {
            return abs($item - ($collection->get($key)));
        })->max();
    }
}