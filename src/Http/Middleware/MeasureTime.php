<?php


namespace robertogallea\LaravelMetrics\Http\Middleware;


use robertogallea\LaravelMetrics\Models\MeterType;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use robertogallea\LaravelMetrics\Models\TimeResolution;

class MeasureTime
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
     * @param  string $resolution
     * @return mixed
     */
    public function handle($request, \Closure $next, $timerName, string $resolution = TimeResolution::SECONDS)
    {
        $timer = $this->metricRegistry->meter($timerName, MeterType::TIMER);
        $timerId = $timer->start();

        $result = $next($request);

        $timer->in(ucfirst($resolution))->stop($timerId);

        return $result;
    }
}