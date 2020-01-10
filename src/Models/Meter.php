<?php


namespace robertogallea\LaravelMetrics\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

abstract class Meter
{
    protected $name;

    protected $from;

    protected $to;

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
     * @param Carbon $from
     * @return Meter
     */
    public function after(Carbon $from): Meter
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param Carbon $to
     * @return Meter
     */
    public function before(Carbon $to): Meter
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param Carbon $from
     * @param Carbon $to
     * @return Meter
     */
    public function between(Carbon $from, Carbon $to): Meter
    {
        return $this->before($from)->after($to);
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return Collection
     */
    public function get(Carbon $from = null, Carbon $to = null): Collection
    {
        $this->from = $this->from ?? $from;
        $this->to = $this->to ?? $to;

        return $this->builder($this->from, $this->to)->get();
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