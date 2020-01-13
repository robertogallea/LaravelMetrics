<?php


namespace Tests\Unit;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use robertogallea\LaravelMetrics\Listeners\EventListener;
use robertogallea\LaravelMetrics\Models\MetricRegistry;
use Tests\Classes\TestEvent;

class EventListenerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_handles_measurable_events()
    {
        $listener = new EventListener();

        $listener->handle(new TestEvent());

        $registry = new MetricRegistry();

        $meter = $registry->meter('test');

        $this->assertCount(1, $meter->get());
    }

    /** @test */
    public function it_doesnt_handles_measurable_events_if_no_meter_is_provided()
    {
        $listener = new EventListener();

        $event = new TestEvent();

        $event->setMeter(null);

        $listener->handle($event);

        $registry = new MetricRegistry();

        $meter = $registry->meter('test');

        $this->assertCount(0, $meter->get());
    }


    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }
}