<?php


namespace robertogallea\LaravelMetrics\Models;


use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('metrics.table.name');
    }
}