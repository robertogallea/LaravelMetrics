<?php


namespace Tests\Unit;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use robertogallea\LaravelMetrics\Listeners\EventListener;
use Tests\Classes\TestEvent;

class MeasurableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_handles_measurable_events()
    {
        $mock = $this->partialMock(TestEvent::class, function($mock) {
            $mock->shouldReceive('getMeter')
                ->andReturn('test');
        });

        $listener = new EventListener();

        $result = $listener->handle($mock);
    }

    protected function getPackageProviders($app)
    {
        return [
            'robertogallea\LaravelMetrics\MetricsServiceProvider'
        ];
    }
}