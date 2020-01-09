<?php


namespace robertogallea\LaravelMetrics\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

abstract class Meter
{
    protected string $name;

    /**
     * Meter constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return \Str::kebab(class_basename($this));
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return Collection
     */
    public function get(Carbon $from = null, Carbon $to = null): Collection
    {
        return $this->builder($from, $to)->get();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    protected function builder($from = null, $to = null): Builder
    {
        return Metric::where([
            config('metrics.table.columns.name') => $this->getName(),
            config('metrics.table.columns.type') => $this->getType()
        ])
        ->when($from, function ($q) use ($from) {
            return $q->where(config('metrics.table.columns.created_at'), '>=', $from);
        })
        ->when($to, function($q) use ($to) {
            $q->where(config('metrics.table.columns.created_at'), '<=', $to);
        });
    }

    /**
     * @return Metric
     */
    public function first(): Metric
    {
        return $this->builder()->first();
    }
}