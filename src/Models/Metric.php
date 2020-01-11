<?php


namespace robertogallea\LaravelMetrics\Models;


use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->table = config('metrics.table.name');

        $this->casts = [
            config('metrics.table.columns.metadata') => 'array'
        ];

        parent::__construct($attributes);
    }
}