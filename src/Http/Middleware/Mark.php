<?php


namespace robertogallea\LaravelMetrics\Http\Middleware;


use robertogallea\LaravelMetrics\Models\MeterType;
use robertogallea\LaravelMetrics\Models\MetricRegistry;

class Mark
{
    /**
     * @var MetricRegistry
     */
    private $metricRegistry;

    public function __construct(MetricRegistry $metricRegistry)
    {
        $this->metricRegistry = $metricRegistry;
    }
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $timerName
     * @return mixed
     */
    public function handle($request, \Closure $next, $timerName)
    {
        $timer = $this->metricRegistry->meter($timerName, MeterType::MARKER);
        $timer->mark();

        return $next($request);
    }
}