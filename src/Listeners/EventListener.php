<?php


namespace robertogallea\LaravelMetrics\Listeners;


use robertogallea\LaravelMetrics\Models\Interfaces\PerformsMetrics;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use robertogallea\LaravelMetrics\Models\Traits\Measurable;

class EventListener
{
    /**
     * @param $event
     */
    public function handle(PerformsMetrics $event)
    {
        if ($this->isMeasurable($event)) {
            $registry = resolve(MetricRegistry::class);

            $registry->meter($event->getMeter())->mark();
        }
    }

    private function isMeasurable(PerformsMetrics $event)
    {
        return method_exists($event, 'getMeter') && is_object($event) && $event->getMeter() ;
    }
}